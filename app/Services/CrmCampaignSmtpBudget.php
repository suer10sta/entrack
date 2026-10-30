<?php

namespace App\Services;

use App\Models\CrmCampaignRecipient;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;
use Throwable;

class CrmCampaignSmtpBudget
{
    public function accountKey(): string
    {
        return $this->accountKeys()[0];
    }

    public function accountKeys(): array
    {
        // Charge all possible transports conservatively for composite mailers.
        // Overlapping pools/aliases must share the underlying SMTP account budget.
        $accounts = array_unique($this->accounts((string) config('mail.default')));
        $keys = array_map(fn ($account) => hash('sha256', $account), $accounts);
        sort($keys);

        return $keys;
    }

    private function accounts(string $name, array $seen = []): array
    {
        if (in_array($name, $seen, true)) {
            return [];
        }
        $mailer = config('mail.mailers.'.$name, []);
        if (in_array($mailer['transport'] ?? '', ['failover', 'roundrobin'], true)) {
            $accounts = [];
            foreach ($mailer['mailers'] ?? [] as $child) {
                $accounts = array_merge($accounts, $this->accounts($child, [...$seen, $name]));
            }

            return $accounts;
        }

        return [strtolower(trim((string) ($mailer['transport'] ?? $name))).':'.
            strtolower(trim((string) ($mailer['host'] ?? ''))).':'.
            strtolower(trim((string) ($mailer['username'] ?? config('mail.from.address', ''))))];
    }

    public function available(string|array $account): bool
    {
        if (DB::table('crm_campaign_smtp_pauses')->whereIn('account_key', (array) $account)
            ->where('paused_until', '>', now('UTC')->toISOString())->exists()) {
            return false;
        }

        return $this->attemptsSince($account, now('UTC')->subHour()->toISOString()) < min(150, max(1, (int) config('crm.campaign_hourly_limit', 150)));
    }

    public function attemptsSince(string|array $account, string $since): int
    {
        $count = 0;
        foreach ((array) $account as $key) {
            $count = max($count, DB::table('crm_campaign_smtp_attempts')->where('account_key', $key)->where('attempted_at', '>', $since)->count());
        }
        // Legacy attempts have no known SMTP account. Count conservatively until
        // they age out; deployment must not grant a fresh allowance immediately.
        $legacy = CrmCampaignRecipient::where('attempted_at', '>', $since)->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('crm_campaign_smtp_attempts')
                ->whereColumn('recipient_id', 'crm_campaign_recipients.id');
        })->count();

        return $count + $legacy;
    }

    public function record(CrmCampaignRecipient $recipient, string|array $account): void
    {
        foreach ((array) $account as $key) {
            DB::table('crm_campaign_smtp_attempts')->insert([
                'recipient_id' => $recipient->id, 'account_key' => $key, 'attempted_at' => now('UTC')->toISOString(),
            ]);
        }
        $recipient->update(['status' => 'sending', 'attempted_at' => now('UTC'),
            'attempts_count' => $recipient->attempts_count + 1, 'next_attempt_at' => null]);
    }

    public function isQuotaRefusal(Throwable $error): bool
    {
        // OVH uses 550 for the temporary hourly quota. A bare 550, mailbox-full
        // error or timeout is not sufficient evidence that a retry is safe.
        return $error instanceof UnexpectedResponseException
            && in_array((int) $error->getCode(), [421, 450, 451, 452, 550, 552, 554], true)
            && preg_match('/\bMail quota exceeded\b.*\b(?:per hour|hourly)\b/is', $error->getMessage()) === 1;
    }

    public function defer(CrmCampaignRecipient $recipient, string|array $account, Throwable $error): void
    {
        DB::transaction(function () use ($recipient, $account, $error) {
            $until = now('UTC')->addHour();
            foreach ((array) $account as $key) {
                DB::table('crm_campaign_smtp_pauses')->upsert([
                    ['account_key' => $key, 'paused_until' => $until->toISOString()],
                ], ['account_key'], ['paused_until']);
            }
            $recipient->update(['status' => 'pending', 'next_attempt_at' => $until,
                'last_smtp_code' => (int) $error->getCode(),
                'error' => 'Quota horaire SMTP atteint. Nouvelle tentative automatique après '.$until->format('d/m/Y H:i').' UTC.']);
        });
    }
}
