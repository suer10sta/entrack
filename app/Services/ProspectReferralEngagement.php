<?php

namespace App\Services;

use App\Enums\ProspectActivityType;
use App\Enums\ProspectStatus;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProspectReferralEngagement
{
    public function record(Request $request): void
    {
        // LinkedIn previews and the Manager testing their own link are not prospect engagement.
        $purpose = strtolower($request->header('Purpose', '').' '.$request->header('Sec-Purpose', '').' '.$request->header('X-Purpose', ''));
        if (! $request->isMethod('GET') || auth('manager')->check() || str_contains($purpose, 'prefetch') || str_contains($purpose, 'preview')
            || preg_match('/LinkedInBot|facebookexternalhit|Facebot|Twitterbot|Slackbot|Discordbot|TelegramBot|Googlebot|bingbot/i', $request->userAgent() ?? '')) {
            return;
        }
        $prospect = app(ProspectReferralAttribution::class)->resolve($request->query('ref'));
        if (! $prospect) {
            return;
        }
        try {
            DB::transaction(function () use ($prospect) {
                $locked = Prospect::whereKey($prospect->id)->lockForUpdate()->first();
                if (! $locked || ! in_array($locked->status, [ProspectStatus::Nouveau, ProspectStatus::Contacte], true)) {
                    return;
                }
                $at = now('UTC');
                $updated = app(ProspectStatusService::class)->transition($locked, ProspectStatus::Engage, 'linkedin_click',
                    note: 'Lien LinkedIn personnalisé consulté.', idempotencyKey: 'linkedin-click:'.$locked->id, occurredAt: $at);
                // A processed receipt protects a later manual status reset as well as refreshes.
                if ($updated->status !== ProspectStatus::Engage) {
                    return;
                }
                $locked->activities()->create(['type' => ProspectActivityType::Automatic, 'subject' => 'Lien LinkedIn consulté',
                    'body' => 'Le lien personnalisé envoyé via LinkedIn a été consulté.', 'occurred_at' => $at,
                    'metadata' => ['event' => 'linkedin_link_clicked', 'utm_source' => 'linkedin', 'utm_medium' => 'message_prive']]);
            }, 3);
        } catch (Throwable $error) {
            // CRM must never block the public landing page or change registration/account matching.
            Log::warning('CRM LinkedIn engagement could not be recorded.', ['prospect_id' => $prospect->id, 'exception' => $error]);
        }
    }
}
