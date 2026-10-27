<?php

namespace App\Services;

use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProspectReferralAttribution
{
    public const SESSION_KEY = 'crm_prospect_referral';

    private const MAX_AGE = 30 * 24 * 60 * 60;

    public function resolve(mixed $token): ?Prospect
    {
        if (! is_string($token) || ! preg_match('/\A[a-f0-9]{48}\z/', $token)) {
            return null;
        }
        try {
            return Prospect::where('public_token', $token)->first();
        } catch (Throwable $error) {
            // A CRM deployment/storage issue must never block a marketing page.
            Log::warning('CRM referral could not be resolved.', ['exception' => $error]);

            return null;
        }
    }

    public function capture(Request $request): void
    {
        // Explicit invalid refs clear an earlier attribution; ordinary navigation preserves it.
        $request->session()->forget(self::SESSION_KEY);
        $prospect = $this->resolve($request->query('ref'));
        if (! $prospect) {
            return;
        }
        $request->session()->put(self::SESSION_KEY, [
            'token' => $prospect->public_token, 'prospect_id' => $prospect->id,
            'captured_at' => now('UTC')->timestamp, 'utm_source' => 'linkedin', 'utm_medium' => 'message_prive',
        ]);
    }

    public function current(Request $request): ?array
    {
        $context = $request->session()->get(self::SESSION_KEY);
        if (! is_array($context)) {
            return null;
        }
        $captured = $context['captured_at'] ?? null;
        if (! is_int($captured) || $captured > now()->timestamp || $captured < now()->timestamp - self::MAX_AGE) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }
        $prospect = $this->resolve($context['token'] ?? null);
        if (! $prospect || $prospect->id !== ($context['prospect_id'] ?? null)) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return ['token' => $prospect->public_token, 'prospect_id' => $prospect->id, 'captured_at' => $captured,
            'utm_source' => 'linkedin', 'utm_medium' => 'message_prive'];
    }

    public function consume(Request $request): ?array
    {
        $context = $this->current($request);
        $request->session()->forget(self::SESSION_KEY);

        return $context;
    }
}
