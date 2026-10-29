<?php

namespace App\Services;

use App\Models\CrmCampaignRecipient;
use App\Models\Prospect;
use Closure;
use Illuminate\Support\Facades\DB;
use LogicException;

class CrmCampaignConsent
{
    public function relatedProspects(?int $prospectId, ?int $userId, string $email)
    {
        return Prospect::withTrashed()->where(function ($query) use ($prospectId, $userId, $email) {
            $query->where('email_normalized', $email);
            if ($prospectId) {
                $query->orWhere('id', $prospectId);
            }
            if ($userId) {
                $query->orWhere('linked_user_id', $userId);
            }
        });
    }

    public function blocked(?int $prospectId, ?int $userId, string $email): bool
    {
        return $this->relatedProspects($prospectId, $userId, $email)->whereNotNull('unsubscribed_at')->exists();
    }

    public function withRecipient(CrmCampaignRecipient $recipient, Closure $work, ?Prospect $unsubscribeTarget = null): mixed
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql' || $db->transactionLevel() !== 0) {
            throw new LogicException('CRM consent coordination requires PostgreSQL outside a transaction.');
        }
        $prospects = $this->relatedProspects($recipient->prospect_id, $recipient->user_id, $recipient->email_normalized)->get(['id', 'email_normalized']);
        if ($unsubscribeTarget) {
            $prospects->push($unsubscribeTarget);
        }
        $keys = ['email:'.hash('sha256', $recipient->email_normalized)];
        foreach ($prospects as $prospect) {
            $keys[] = 'prospect:'.$prospect->id;
            if ($prospect->email_normalized) {
                $keys[] = 'email:'.hash('sha256', $prospect->email_normalized);
            }
        }
        $keys = array_values(array_unique($keys));
        sort($keys, SORT_STRING);
        $prefix = 'crm.campaign.consent:'.$db->getDatabaseName().':'.$db->selectOne('SELECT current_schema() AS name')->name.':';
        $locked = [];
        try {
            foreach ($keys as $key) {
                $db->select('SELECT pg_advisory_lock(hashtextextended(?, 0))', [$prefix.$key]);
                $locked[] = $prefix.$key;
            }

            return $work();
        } finally {
            foreach (array_reverse($locked) as $key) {
                $db->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', [$key]);
            }
        }
    }
}
