<?php

namespace App\Http\Controllers;

use App\Models\CrmCampaignLink;
use App\Services\CrmCampaignTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrmCampaignTrackingController extends Controller
{
    private function automated(Request $request): bool
    {
        return $request->isMethod('HEAD') || str_contains(strtolower($request->header('Purpose', '').' '.$request->header('Sec-Purpose', '')), 'prefetch');
    }

    public function open(Request $request, string $token, CrmCampaignTracking $tracking)
    {
        if (! $this->automated($request)) {
            try {
                $tracking->open($token);
            } catch (Throwable $error) {
                Log::error('CRM campaign open recording failed.', ['exception' => $error]);
            }
        }

        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200,
            ['Content-Type' => 'image/gif', 'Cache-Control' => 'no-store, no-cache, must-revalidate, private', 'Pragma' => 'no-cache', 'X-Robots-Tag' => 'noindex, nofollow', 'Referrer-Policy' => 'no-referrer']);
    }

    public function click(Request $request, string $token, CrmCampaignTracking $tracking)
    {
        $link = CrmCampaignLink::where('token', $token)->firstOrFail();
        abort_unless($tracking->safeDestination($link->destination), 404);
        if (! $this->automated($request)) {
            try {
                $tracking->click($link);
            } catch (Throwable $error) {
                Log::error('CRM campaign click recording failed.', ['exception' => $error]);
            }
        }

        // Query-string destinations are ignored: only the saved URL is trusted.
        return redirect()->away($link->destination, 302)->withHeaders(['Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow']);
    }
}
