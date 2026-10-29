<?php

namespace App\Services;

use App\Models\CrmCampaignRecipient;
use App\Models\Prospect;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrmCampaignUnsubscribe
{
    public function __construct(private CrmCampaignConsent $consent, private ProspectStatusService $statuses) {}

    public function url(CrmCampaignRecipient $recipient, string $locale = 'fr'): ?string
    {
        $prospect = $recipient->prospect_id
            ? Prospect::withTrashed()->find($recipient->prospect_id)
            : $this->consent->relatedProspects(null, $recipient->user_id, $recipient->email_normalized)->orderBy('id')->first();
        if (! $prospect) {
            return null;
        }
        $encrypted = Crypt::encryptString(json_encode([
            'purpose' => 'crm_campaign_unsubscribe_v1', 'recipient_id' => $recipient->id,
            'prospect_id' => $prospect->id, 'prospect_token' => $prospect->public_token,
            'email_hash' => hash('sha256', $recipient->email_normalized),
        ], JSON_THROW_ON_ERROR));

        return route('crm.unsubscribe', ['locale' => $locale, 'token' => rtrim(strtr($encrypted, '+/', '-_'), '=')]);
    }

    public function resolve(string $token): array
    {
        if (strlen($token) > 2048 || ! preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
            throw new NotFoundHttpException;
        }
        try {
            $data = json_decode(Crypt::decryptString(strtr($token, '-_', '+/')), true, 16, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            throw new NotFoundHttpException;
        }
        if (! is_array($data) || ($data['purpose'] ?? null) !== 'crm_campaign_unsubscribe_v1'
            || ! is_int($data['recipient_id'] ?? null) || ! is_int($data['prospect_id'] ?? null)
            || ! is_string($data['prospect_token'] ?? null) || ! is_string($data['email_hash'] ?? null)) {
            throw new NotFoundHttpException;
        }
        $recipient = CrmCampaignRecipient::find($data['recipient_id']);
        $prospect = Prospect::withTrashed()->find($data['prospect_id']);
        if (! $recipient || ! $prospect || ! hash_equals($prospect->public_token, $data['prospect_token'])
            || ! hash_equals(hash('sha256', $recipient->email_normalized), $data['email_hash'])
            || ($recipient->prospect_id && (int) $recipient->prospect_id !== $prospect->id)) {
            throw new NotFoundHttpException;
        }

        return [$recipient, $prospect];
    }

    public function confirm(string $token): Prospect
    {
        [$recipient, $prospect] = $this->resolve($token);

        return $this->consent->withRecipient($recipient, function () use ($token) {
            [$recipient, $prospect] = $this->resolve($token);

            return $this->statuses->unsubscribeFromCampaigns($prospect, $recipient->id);
        }, $prospect);
    }
}
