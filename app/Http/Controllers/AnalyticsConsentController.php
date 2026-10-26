<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsBusinessEvents;
use Illuminate\Http\Request;

class AnalyticsConsentController extends Controller
{
    public function __invoke(Request $request, AnalyticsBusinessEvents $events)
    {
        $input = $request->validate([
            'granted' => 'required|boolean', 'client_id' => ['nullable', 'string', 'max:100', 'regex:/^[0-9]+\.[0-9]+$/'],
            'session_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'revision' => 'required|integer|min:0',
        ]);
        if (($request->session()->get('analytics_context.revision', 0)) > $input['revision']) {
            return response()->noContent()->header('Cache-Control', 'no-store');
        }
        $context = ['granted' => $input['granted'], 'client_id' => $input['granted'] ? ($input['client_id'] ?? null) : null,
            'session_id' => $input['granted'] ? ($input['session_id'] ?? null) : null, 'revision' => $input['revision'], 'captured_at' => now()->timestamp];
        $request->session()->put('analytics_context', $context);
        if ($id = $request->user()?->entreprise_id) {
            $events->rememberContext((int) $id, $context);
        }

        return response()->noContent()->header('Cache-Control', 'no-store');
    }
}
