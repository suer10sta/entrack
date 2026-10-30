<?php

namespace App\Services;

use App\Enums\ProspectActivityType;
use App\Enums\ProspectStatus;
use App\Mail\ProspectCampaignMail;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignRecipient;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;
use Throwable;

class CrmCampaignSender
{
    public function __construct(private ProspectStatusService $statuses) {}

    public function batch(): int
    {
        $db = DB::connection();
        // Cross-process/node lock; no SMTP call is made while holding a DB transaction.
        $key = 'crm.campaign.sender:'.$db->getDatabaseName();
        $locked = $db->selectOne('SELECT pg_try_advisory_lock(hashtextextended(?, 0)) AS acquired', [$key])->acquired;
        if (! $locked) {
            return 0;
        }
        try {
            app(CrmCampaignService::class)->releaseDueScheduled();
            $this->recoverInterrupted();
            foreach (CrmCampaignRecipient::where('status', 'sent')->whereNull('crm_synced_at')->orderBy('id')->limit(100)->get() as $recipient) {
                $this->syncContact($recipient);
            }
            $this->finishCampaigns();
            if (! $this->deliveryMailerConfigured(config('mail.default'))) {
                Log::warning('CRM campaign delivery paused: configure the existing production mailer.');

                return 0;
            }
            $smtp = app(CrmCampaignSmtpBudget::class);
            $account = $smtp->accountKeys();
            $budget = max(0, min(100, max(1, config('crm.campaign_batch_size'))) - $smtp->attemptsSince($account, now('UTC')->subMinute()->toISOString()));
            $sent = 0;
            for ($i = 0; $i < $budget; $i++) {
                if ($i > 0) {
                    usleep(min(5000, max(0, config('crm.campaign_interval_ms'))) * 1000);
                }
                $recipient = $this->claim($smtp, $account);
                if (! $recipient) {
                    break;
                }
                if ($recipient->status === 'skipped') {
                    continue;
                }
                $campaign = $recipient->campaign;
                $delivered = app(CrmCampaignConsent::class)->withRecipient($recipient, function () use ($recipient, $campaign, $smtp, $account) {
                    $recipient->refresh();
                    if (! $this->sendable($recipient)) {
                        $recipient->update(['status' => 'skipped', 'error' => 'Contact désabonné, supprimé/archivé ou adresse modifiée.']);

                        return false;
                    }
                    try {
                        $message = Mail::to($recipient->email)->send(new ProspectCampaignMail($campaign->subject, $campaign->content, $campaign->preheader, $campaign->cta_text, $campaign->ctaLink(), $campaign->media['image'] ?? null, $campaign->media['attachment'] ?? null, $recipient));
                        if (! $message) {
                            throw new RuntimeException('Mailer cancelled message.');
                        }
                    } catch (Throwable $error) {
                        if ($smtp->isQuotaRefusal($error)) {
                            $smtp->defer($recipient, $account, $error);
                            Log::warning('CRM campaign SMTP quota exceeded; CRM account paused for one hour.', [
                                'recipient_id' => $recipient->id, 'campaign_id' => $campaign->id,
                                'account_keys' => $account, 'next_attempt_at' => $recipient->next_attempt_at, 'exception' => $error,
                            ]);

                            return false;
                        }
                        // A connection/timeout error can occur after acceptance. Never
                        // automatically replay it; explicit permanent refusals stay failed.
                        $uncertain = $error instanceof TransportExceptionInterface && ! $error instanceof UnexpectedResponseException;
                        $recipient->update(['status' => $uncertain ? 'uncertain' : 'failed',
                            'next_attempt_at' => null,
                            'last_smtp_code' => $error instanceof UnexpectedResponseException ? (int) $error->getCode() : null,
                            'error' => $uncertain ? 'Réception SMTP incertaine : à vérifier. Aucun renvoi automatique.' : 'Envoi refusé ou non confirmé. Aucun renvoi automatique.']);
                        Log::error('CRM campaign mail failed.', ['recipient_id' => $recipient->id, 'campaign_id' => $campaign->id, 'exception' => $error]);

                        return false;
                    }
                    // If this write fails after SMTP accepted, leave "sending" for conservative recovery.
                    $recipient->update(['status' => 'sent', 'sent_at' => now('UTC'), 'error' => null, 'next_attempt_at' => null, 'last_smtp_code' => null]);

                    return true;
                });
                if (! $delivered) {
                    continue;
                }
                $sent++;
                $this->syncContact($recipient);
            }
            $this->finishCampaigns();

            return $sent;
        } finally {
            $db->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', [$key]);
        }
    }

    private function deliveryMailerConfigured(string $name, array $seen = []): bool
    {
        if (in_array($name, $seen, true)) {
            return false;
        }
        $mailer = config('mail.mailers.'.$name, []);
        $transport = $mailer['transport'] ?? null;
        if (! $transport || in_array($transport, ['log', 'array'], true)) {
            return false;
        }
        if (in_array($transport, ['failover', 'roundrobin'], true)) {
            if (empty($mailer['mailers'])) {
                return false;
            }
            foreach ($mailer['mailers'] as $child) {
                if (! $this->deliveryMailerConfigured($child, [...$seen, $name])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function claim(CrmCampaignSmtpBudget $smtp, array $account): ?CrmCampaignRecipient
    {
        return DB::transaction(function () use ($smtp, $account) {
            // The outer session advisory lock serializes this check + reservation
            // across all CRM campaigns/processes sharing the database.
            if (! $smtp->available($account)) {
                return null;
            }
            $recipient = CrmCampaignRecipient::where('status', 'pending')
                ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now('UTC')->toISOString()))
                ->whereHas('campaign', fn ($q) => $q->whereIn('status', ['queued', 'sending'])->whereNull('crm_campaigns.deleted_at'))
                ->orderBy('id')->lockForUpdate()->first();
            if (! $recipient) {
                return null;
            }
            if (! $this->sendable($recipient)) {
                $recipient->update(['status' => 'skipped', 'error' => 'Contact désabonné, supprimé/archivé ou adresse modifiée/invalide depuis la sélection.']);

                return $recipient;
            }
            $smtp->record($recipient, $account);
            CrmCampaign::whereKey($recipient->campaign_id)->where('status', 'queued')->update(['status' => 'sending', 'started_at' => now('UTC')->toISOString()]);

            return $recipient;
        });
    }

    private function sendable(CrmCampaignRecipient $recipient): bool
    {
        $contact = $recipient->prospect_id ? Prospect::find($recipient->prospect_id) : \App\Models\User::find($recipient->user_id);

        return $contact && CrmCampaignService::email($contact->email) === $recipient->email_normalized
            && ! app(CrmCampaignConsent::class)->blocked($recipient->prospect_id, $recipient->user_id, $recipient->email_normalized);
    }

    private function syncContact(CrmCampaignRecipient $recipient): void
    {
        try {
            DB::transaction(function () use ($recipient) {
                DB::statement("SET LOCAL TIME ZONE 'UTC'");
                $recipient = CrmCampaignRecipient::whereKey($recipient->id)->lockForUpdate()->firstOrFail();
                if ($recipient->status !== 'sent' || $recipient->crm_synced_at) {
                    return;
                }
                $prospect = Prospect::whereKey($recipient->prospect_id)->lockForUpdate()->first();
                if ($prospect) {
                    if ($prospect->status === ProspectStatus::Nouveau) {
                        $this->statuses->transition($prospect, ProspectStatus::Contacte, 'campaign',
                            note: 'Email de campagne accepté par le serveur de messagerie.',
                            idempotencyKey: 'campaign:'.$recipient->campaign_id.':prospect:'.$prospect->id, occurredAt: $recipient->sent_at);
                    }
                    $prospect->activities()->create(['type' => ProspectActivityType::Campaign,
                        'subject' => \Illuminate\Support\Str::limit('Campagne email envoyée : '.$recipient->campaign->name, 255, ''),
                        'body' => 'Objet : '.$recipient->campaign->subject,
                        'occurred_at' => $recipient->sent_at,
                        'metadata' => ['campaign_id' => $recipient->campaign_id, 'campaign_recipient_id' => $recipient->id]]);
                }
                $recipient->update(['crm_synced_at' => now('UTC'), 'crm_error' => null]);
            }, 3);
        } catch (Throwable $error) {
            $recipient->update(['crm_error' => 'Email envoyé ; mise à jour CRM en attente.']);
            Log::error('CRM campaign contact synchronization failed; mail will not be resent.', ['recipient_id' => $recipient->id, 'exception' => $error]);
        }
    }

    private function recoverInterrupted(): void
    {
        CrmCampaignRecipient::where('status', 'sending')->where('attempted_at', '<', now('UTC')->subMinutes(max(5, config('crm.campaign_stale_minutes')))->toISOString())
            ->update(['status' => 'uncertain', 'error' => 'Envoi interrompu : réception à vérifier. Aucun renvoi automatique.']);
    }

    private function finishCampaigns(): void
    {
        foreach (CrmCampaign::whereIn('status', ['queued', 'sending'])->whereDoesntHave('recipients', fn ($q) => $q->whereIn('status', ['pending', 'sending']))->get() as $campaign) {
            $issues = $campaign->recipients()->whereIn('status', ['failed', 'uncertain', 'skipped'])->exists();
            $campaign->update(['status' => $issues ? ($campaign->recipients()->where('status', 'sent')->exists() ? 'completed_with_errors' : 'failed') : 'completed', 'completed_at' => now('UTC')]);
        }
    }
}
