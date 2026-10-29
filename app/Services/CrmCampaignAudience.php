<?php

namespace App\Services;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CrmCampaignAudience
{
    public const LABELS = [
        'prospects' => 'Tous les prospects',
        'subscribers' => 'Tous les abonnés actifs',
        'trials' => 'Tous les utilisateurs en période d’essai',
        'custom' => 'Sélection personnalisée',
    ];

    public function recipients(string $audience): array
    {
        if (! in_array($audience, ['prospects', 'subscribers', 'trials'], true)) {
            throw ValidationException::withMessages(['audience' => 'Audience invalide.']);
        }
        $query = $audience === 'prospects' ? Prospect::query() : User::query()->whereHas('entreprise', function ($company) use ($audience) {
            if ($audience === 'trials') {
                $company->whereNull('type_compt')->where('trial_ends_at', '>', now('UTC')->toISOString());
            } else {
                // Local Cashier records are authoritative; no Stripe calls during audience selection.
                $company->whereHas('subscriptions', fn ($subscription) => $subscription
                    ->whereRaw("subscriptions.id = (SELECT current_sub.id FROM subscriptions AS current_sub WHERE current_sub.entreprise_id = entreprises.id ORDER BY (current_sub.type = 'default') DESC, current_sub.created_at DESC, current_sub.id DESC LIMIT 1)")
                    ->where('stripe_status', 'active')
                    ->where(fn ($end) => $end->whereNull('ends_at')->orWhere('ends_at', '>', now('UTC')->toISOString())));
            }
        });
        $recipients = [];
        foreach ($query->orderBy('id')->cursor() as $record) {
            $email = CrmCampaignService::email($record->email);
            if (! $email || isset($recipients[$email]) || app(CrmCampaignConsent::class)->blocked(
                $audience === 'prospects' ? $record->id : null,
                $audience === 'prospects' ? null : $record->id, $email)) {
                continue;
            }
            $recipients[$email] = ['name' => $record->name, 'email' => $email, 'email_normalized' => $email,
                'prospect_id' => $audience === 'prospects' ? $record->id : null,
                'user_id' => $audience === 'prospects' ? null : $record->id];
            if (count($recipients) > config('crm.campaign_max_recipients')) {
                throw ValidationException::withMessages(['audience' => 'Cette audience dépasse la limite de 5 000 destinataires. Utilisez la sélection personnalisée.']);
            }
        }

        return array_values($recipients);
    }
}
