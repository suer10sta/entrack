<?php

namespace App\Services;

use App\Models\CrmCampaign;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CrmCampaignService
{
    public static function email(?string $email): ?string
    {
        $email = Prospect::normalizeEmail($email);

        return $email && ! Validator::make(['email' => $email], ['email' => 'required|email:rfc|max:255'])->fails() ? $email : null;
    }

    public function select(CrmCampaign $campaign, array $data): array
    {
        return DB::transaction(function () use ($campaign, $data) {
            $campaign = CrmCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            abort_unless($campaign->status === 'draft', 409, 'Cette campagne ne peut plus être modifiée.');
            $audience = $data['audience'] ?? 'custom';
            if ($audience !== 'custom') {
                $recipients = app(CrmCampaignAudience::class)->recipients($audience);
                $campaign->recipients()->delete();
                foreach ($recipients as $recipient) {
                    $campaign->recipients()->create($recipient);
                }
                $campaign->update(['audience' => $audience, 'revision' => $campaign->revision + 1]);

                return ['added' => count($recipients), 'ignored' => 0];
            }
            $campaign->update(['audience' => 'custom']);
            if ($data['mode'] === 'clear') {
                $campaign->recipients()->delete();
                $campaign->increment('revision');

                return ['added' => 0, 'ignored' => 0];
            }
            $query = Prospect::query();
            if ($data['mode'] === 'all') {
                if (! empty($data['status'])) {
                    $query->where('status', $data['status']);
                }
                if (isset($data['source']) && $data['source'] !== '') {
                    $query->where('source', $data['source']);
                }
                if ($query->count() > config('crm.campaign_max_recipients')) {
                    throw ValidationException::withMessages(['recipients' => 'Affinez les filtres : 5 000 prospects maximum par sélection.']);
                }
            } else {
                $visible = $data['visible_ids'] ?? [];
                $selected = array_values(array_intersect($visible, $data['selected_ids'] ?? []));
                $campaign->recipients()->whereIn('prospect_id', array_diff($visible, $selected))->delete();
                $query->whereIn('id', $selected);
            }
            $existing = $campaign->recipients()->get(['prospect_id', 'email_normalized']);
            $existingProspects = $existing->pluck('prospect_id')->filter()->flip();
            $emails = array_fill_keys($existing->pluck('email_normalized')->all(), true);
            $added = $ignored = 0;
            foreach ($query->orderBy('id')->get() as $prospect) {
                if ($existingProspects->has($prospect->id)) {
                    continue;
                }
                $email = self::email($prospect->email);
                if (! $email || isset($emails[$email])) {
                    $ignored++;

                    continue;
                }
                if ($existing->count() + $added >= config('crm.campaign_max_recipients')) {
                    throw ValidationException::withMessages(['recipients' => 'Une campagne peut contenir au plus 5 000 destinataires.']);
                }
                $campaign->recipients()->create(['prospect_id' => $prospect->id, 'name' => $prospect->name, 'email' => $email, 'email_normalized' => $email]);
                $emails[$email] = true;
                $added++;
            }

            $campaign->increment('revision');

            return compact('added', 'ignored');
        });
    }

    public function enqueue(CrmCampaign $campaign, ?int $expectedRevision = null): void
    {
        DB::transaction(function () use ($campaign, $expectedRevision) {
            $campaign = CrmCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            // Replayed send requests never create another recipient or resend a campaign.
            if ($campaign->status !== 'draft') {
                return;
            }
            if ($expectedRevision !== null && (int) $campaign->revision !== $expectedRevision) {
                throw ValidationException::withMessages(['campaign' => 'La campagne ou sa sélection a changé. Rechargez la page et vérifiez le groupe avant de confirmer.']);
            }
            if (trim($campaign->content) === '') {
                throw ValidationException::withMessages(['content' => 'Rédigez le contenu de l’email avant l’envoi.']);
            }
            if ((bool) $campaign->cta_text !== (bool) $campaign->cta_url || ($campaign->cta_url && ! $campaign->ctaLink())) {
                throw ValidationException::withMessages(['cta_url' => 'Complétez le texte et une URL HTTP(S) valide pour le bouton.']);
            }
            if (! $campaign->recipients()->exists()) {
                throw ValidationException::withMessages(['recipients' => 'Sélectionnez et enregistrez au moins un destinataire avant l’envoi.']);
            }
            if ($campaign->scheduled_at && ! $campaign->scheduled_at->isFuture()) {
                throw ValidationException::withMessages(['scheduled_date' => 'La date et l’heure de programmation doivent être dans le futur (UTC).']);
            }
            $campaign->recipients()->update(['status' => 'pending']);
            $campaign->update(['status' => $campaign->scheduled_at ? 'scheduled' : 'queued', 'queued_at' => $campaign->scheduled_at ? null : now('UTC')]);
        });
    }

    public function releaseDueScheduled(): void
    {
        CrmCampaign::where('status', 'scheduled')->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now('UTC')->toISOString())
            ->update(['status' => 'queued', 'queued_at' => now('UTC')->toISOString()]);
    }
}
