<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyticsBusinessEvents
{
    public function rememberContext(int $enterprise, array $context): void
    {
        try {
            DB::table('analytics_contexts')->updateOrInsert(['entreprise_id' => $enterprise],
                ['context' => Crypt::encryptString(json_encode($context)), 'updated_at' => now(), 'created_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Analytics context could not be saved.');
        }
    }

    public function registration(int $enterprise, int $user, array $context): void
    {
        $this->rememberContext($enterprise, $context);
        $this->record('sign_up', 'registration:'.$user, $context, ['method' => 'email']);
    }

    public function payment(int $enterprise, array $invoice, ?string $plan): void
    {
        try {
            $subscription = $invoice['subscription'] ?? $invoice['parent']['subscription_details']['subscription'] ?? null;
            if (empty($invoice['id']) || ! $subscription || ($invoice['billing_reason'] ?? '') !== 'subscription_create'
                || (empty($invoice['paid']) && ($invoice['status'] ?? '') !== 'paid') || ($invoice['amount_paid'] ?? 0) <= 0) {
                return;
            }
            $stored = DB::table('analytics_contexts')->where('entreprise_id', $enterprise)->value('context');
            if (! $stored) {
                return;
            }
            $context = json_decode(Crypt::decryptString($stored), true);
            $params = ['transaction_id' => $invoice['id'], 'subscription_id' => is_array($subscription) ? $subscription['id'] : $subscription,
                'value' => $this->amount($invoice['amount_paid'], $invoice['currency'] ?? 'eur'), 'currency' => strtoupper($invoice['currency'] ?? '')];
            $params['price'] = $params['value'];
            if ($plan) {
                $params['plan'] = ucfirst($plan);
            }
            $interval = $invoice['lines']['data'][0]['price']['recurring']['interval'] ?? $invoice['lines']['data'][0]['plan']['interval'] ?? null;
            if (in_array($interval, ['month', 'year'])) {
                $params['billing'] = $interval === 'year' ? 'yearly' : 'monthly';
            }
            $this->record('subscription_success', 'subscription:'.$params['subscription_id'], $context, $params);
        } catch (\Throwable $e) {
            Log::warning('Analytics payment event skipped.');
        }
    }

    public function record(string $name, string $businessKey, array $context, array $params): void
    {
        if (! in_array($name, ['sign_up', 'subscription_success'], true) || empty($context['granted']) || empty($context['client_id'])) {
            return;
        }
        try {
            $key = hash('sha256', $businessKey);
            $params['event_id'] = $key;
            $params['engagement_time_msec'] = 1;
            if (! empty($context['session_id']) && now()->timestamp - ($context['captured_at'] ?? 0) < 86400) {
                $params['session_id'] = (int) $context['session_id'];
            }
            $payload = ['client_id' => $context['client_id'], 'consent' => ['ad_user_data' => 'DENIED', 'ad_personalization' => 'DENIED'],
                'timestamp_micros' => (int) round(microtime(true) * 1000000), 'events' => [['name' => $name, 'params' => $params]]];
            DB::table('analytics_business_events')->insertOrIgnore(['event_key' => $key, 'event_name' => $name,
                'status' => 'pending', 'payload' => Crypt::encryptString(json_encode($payload)), 'created_at' => now(), 'updated_at' => now()]);
            // No sending without a configured secret; pending records remain available for diagnosis.
            if (! config('analytics.api_secret')) {
                return;
            }
            // At-most-once claim: never resend on webhook replay or an ambiguous network timeout.
            if (! DB::table('analytics_business_events')->where('event_key', $key)->where('status', 'pending')->update(['status' => 'sending', 'updated_at' => now()])) {
                return;
            }
            $payload = json_decode(Crypt::decryptString(DB::table('analytics_business_events')->where('event_key', $key)->value('payload')), true);
            dispatch(function () use ($key, $payload) {
                try {
                    $response = Http::timeout(4)->connectTimeout(2)->post('https://www.google-analytics.com/mp/collect?'.http_build_query([
                        'measurement_id' => config('analytics.measurement_id'), 'api_secret' => config('analytics.api_secret')]), $payload);
                    DB::table('analytics_business_events')->where('event_key', $key)->update(['status' => $response->successful() ? 'sent' : 'failed', 'updated_at' => now()]);
                } catch (\Throwable $e) {
                    DB::table('analytics_business_events')->where('event_key', $key)->update(['status' => 'uncertain', 'updated_at' => now()]);
                    Log::warning('Analytics delivery uncertain; no automatic resend to avoid duplicate conversions.');
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::warning('Analytics business event could not be recorded.');
        }
    }

    private function amount(int $minor, string $currency): float
    {
        $currency = strtolower($currency);
        $zero = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

        return $minor / (in_array($currency, $zero, true) ? 1 : (in_array($currency, ['bhd', 'jod', 'kwd', 'omr', 'tnd'], true) ? 1000 : 100));
    }
}
