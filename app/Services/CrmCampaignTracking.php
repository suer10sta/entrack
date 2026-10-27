<?php

namespace App\Services;

use App\Enums\ProspectStatus;
use App\Mail\ProspectCampaignMail;
use App\Models\CrmCampaignLink;
use App\Models\CrmCampaignRecipient;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrmCampaignTracking
{
    public function decorate(ProspectCampaignMail $mail, CrmCampaignRecipient $recipient): void
    {
        // Persist the opaque token before SMTP. Existing recipients are initialized lazily.
        CrmCampaignRecipient::whereKey($recipient->id)->whereNull('tracking_token')->update(['tracking_token' => bin2hex(random_bytes(32))]);
        $recipient->refresh();
        $mail->pixelUrl = route('crm.tracking.open', ['token' => $recipient->tracking_token]);
        if ($mail->ctaUrl) {
            $mail->ctaUrl = $this->link($recipient, $mail->ctaUrl);
        }
        $html = '';
        $text = '';
        $offset = 0;
        preg_match_all('~https?://[^\\s<>"\']+~iu', $mail->content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as [$match, $position]) {
            $url = rtrim($match, '.,;:!?)]}');
            $before = substr($mail->content, $offset, $position - $offset);
            $html .= e($before);
            $text .= $before;
            if ($this->safeDestination($url)) {
                $destination = $recipient->campaign->destinationLink($url);
                $tracked = $this->link($recipient, $destination);
                $html .= '<a href="'.e($tracked).'" style="color:#2D7080">Consulter le lien</a>';
                $text .= $tracked;
            } else {
                $html .= e($url);
                $text .= $url;
            }
            $suffix = substr($match, strlen($url));
            $html .= e($suffix);
            $text .= $suffix;
            $offset = $position + strlen($match);
        }
        $tail = substr($mail->content, $offset);
        $mail->trackedContentHtml = nl2br($html.e($tail));
        $mail->content = $text.$tail;
    }

    private function link(CrmCampaignRecipient $recipient, string $destination): string
    {
        if (! $this->safeDestination($destination)) {
            throw new \RuntimeException('URL de campagne invalide.');
        }
        $link = CrmCampaignLink::firstOrCreate(['recipient_id' => $recipient->id, 'destination_hash' => hash('sha256', $destination)],
            ['token' => bin2hex(random_bytes(32)), 'destination' => $destination]);

        return route('crm.tracking.click', ['token' => $link->token]);
    }

    public function safeDestination(string $url): bool
    {
        $parts = parse_url($url);

        return strlen($url) <= 8192 && ! preg_match('/[\r\n]/', $url) && filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && ! isset($parts['user']) && ! isset($parts['pass']);
    }

    public function open(string $token): void
    {
        DB::transaction(function () use ($token) {
            $recipient = CrmCampaignRecipient::where('tracking_token', $token)->lockForUpdate()->first();
            if (! $recipient || $recipient->status !== 'sent' || ! $recipient->sent_at) {
                return;
            }
            $recipient->update(['first_opened_at' => $recipient->first_opened_at ?? now('UTC'), 'open_count' => $recipient->open_count + 1]);
        }, 3);
    }

    public function click(CrmCampaignLink $link): void
    {
        $counted = DB::transaction(function () use ($link) {
            // Always acquire recipient before link/prospect, consistently with the sender.
            $recipient = CrmCampaignRecipient::whereKey($link->recipient_id)->lockForUpdate()->first();
            if (! $recipient || $recipient->status !== 'sent' || ! $recipient->sent_at) {
                return false;
            }
            $locked = CrmCampaignLink::whereKey($link->id)->lockForUpdate()->firstOrFail();
            $at = now('UTC');
            $locked->update(['first_clicked_at' => $locked->first_clicked_at ?? $at, 'click_count' => $locked->click_count + 1]);
            $recipient->update(['first_clicked_at' => $recipient->first_clicked_at ?? $at, 'click_count' => $recipient->click_count + 1, 'last_clicked_url' => $locked->destination]);

            return true;
        }, 3);
        if (! $counted) {
            return;
        }
        try {
            DB::transaction(function () use ($link) {
                $recipient = CrmCampaignRecipient::whereKey($link->recipient_id)->lockForUpdate()->firstOrFail();
                $prospect = Prospect::whereKey($recipient->prospect_id)->lockForUpdate()->first();
                if ($prospect && $prospect->status === ProspectStatus::Contacte) {
                    app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Engage, 'campaign_click',
                        note: 'Lien de campagne email cliqué.', idempotencyKey: 'campaign-click:'.$recipient->id,
                        occurredAt: $recipient->first_clicked_at);
                }
            }, 3);
        } catch (Throwable $error) {
            // Keep the recorded click and redirect; a later click can retry the idempotent CRM sync.
            Log::error('CRM campaign click synchronization failed.', ['recipient_id' => $link->recipient_id, 'exception' => $error]);
        }
    }
}
