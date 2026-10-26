<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\SearchConsoleConnection;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class GoogleAnalyticsController extends Controller
{
    public function index()
    {
        return view('manager.analytics.index', ['connected' => SearchConsoleConnection::where('manager_id', auth('manager')->id())->exists(), 'propertyId' => config('analytics.property_id')]);
    }

    public function data(Request $request, GoogleAnalyticsService $analytics, string $report)
    {
        $input = $request->validate(['days' => 'sometimes|integer|in:7,28,90,180', 'refresh' => 'sometimes|boolean']);
        try {
            return response()->json($analytics->report(auth('manager')->id(), $report, (int) ($input['days'] ?? 28), $request->boolean('refresh')))->header('Cache-Control', 'private, no-store');
        } catch (HttpExceptionInterface $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode(), $e->getHeaders())->header('Cache-Control', 'private, no-store');
        }
    }
}
