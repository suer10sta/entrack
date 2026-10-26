<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\SearchConsoleConnection;
use App\Services\SearchConsoleService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class SearchConsoleController extends Controller
{
    public function index()
    {
        return view('manager.seo.index', ['connected' => SearchConsoleConnection::where('manager_id', auth('manager')->id())->exists()]);
    }

    public function connect(Request $request, SearchConsoleService $service)
    {
        $state = bin2hex(random_bytes(32));
        $request->session()->put('gsc_oauth', ['state' => $state, 'manager' => auth('manager')->id(), 'expires' => now()->addMinutes(10)->timestamp]);
        try {
            return redirect()->away((string) $service->oauth()->buildFullAuthorizationUri(['state' => $state, 'access_type' => 'offline', 'prompt' => 'consent']));
        } catch (HttpExceptionInterface $e) {
            return redirect()->route('manager.seo.index')->with('gsc_error', $e->getMessage());
        }
    }

    public function callback(Request $request, SearchConsoleService $service)
    {
        $pending = $request->session()->pull('gsc_oauth');
        if (! $pending || ! is_string($request->query('state')) || ! hash_equals($pending['state'], $request->query('state')) || $pending['manager'] !== auth('manager')->id() || $pending['expires'] < now()->timestamp) {
            return redirect()->route('manager.seo.index')->with('gsc_error', 'Autorisation expirée ou invalide. Relancez la connexion Google.');
        }
        if ($request->has('error')) {
            return redirect()->route('manager.seo.index')->with('gsc_error', 'Autorisation Google annulée ou refusée.');
        }
        $request->validate(['code' => 'required|string|max:4096']);
        try {
            $oauth = $service->oauth();
            $oauth->setCode($request->query('code'));
            $service->storeTokens(auth('manager')->id(), $service->exchange($oauth));

            return redirect()->route('manager.seo.index')->with('gsc_success', 'Google Search Console connecté.');
        } catch (HttpExceptionInterface $e) {
            return redirect()->route('manager.seo.index')->with('gsc_error', $e->getMessage());
        }
    }

    public function data(Request $request, SearchConsoleService $service, string $report)
    {
        $input = $request->validate([
            'site' => ($report === 'sites' ? 'nullable' : 'required').'|string|max:2048',
            'start' => 'nullable|date_format:Y-m-d|before_or_equal:'.now('America/Los_Angeles')->toDateString().'|after_or_equal:'.now('America/Los_Angeles')->subMonths(16)->toDateString(),
            'end' => 'nullable|date_format:Y-m-d|before_or_equal:'.now('America/Los_Angeles')->toDateString(),
            'data_state' => 'nullable|in:all,final',
            'type' => 'nullable|in:web,image,video,news',
            'refresh' => 'sometimes|boolean',
        ]);
        $start = $input['start'] ?? now('America/Los_Angeles')->subDays(28)->toDateString();
        $end = $input['end'] ?? now('America/Los_Angeles')->subDay()->toDateString();
        if ($start > $end) {
            return response()->json(['message' => 'La date de début doit précéder la date de fin.'], 422);
        }
        try {
            return response()->json($service->report(auth('manager')->id(), $report, $input['site'] ?? '', $start, $end, $input))->header('Cache-Control', 'private, no-store');
        } catch (HttpExceptionInterface $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode(), $e->getHeaders())->header('Cache-Control', 'private, no-store');
        }
    }
}
