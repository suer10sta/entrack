<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Models\SurveyAction;
use App\Models\SurveyResponse;
use Closure;
use Illuminate\Support\Facades\DB;
use LogicException;

class SurveyEmailGate
{
    public function forCompany(int $companyId, Closure $work): mixed
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql' || $db->transactionLevel() !== 0) {
            throw new LogicException('Survey delivery requires PostgreSQL outside a transaction.');
        }
        // Session lock covers reservation, SMTP and result, without an open DB transaction during SMTP.
        $key = 'survey-email:'.$db->getDatabaseName().':'.($db->selectOne('select current_schema() as name')->name).':'.$companyId;
        $locked = $db->selectOne('SELECT pg_try_advisory_lock(hashtextextended(?, 0)) AS acquired', [$key])->acquired;
        if (! $locked) {
            return null;
        }
        try {
            return $work();
        } finally {
            $db->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', [$key]);
        }
    }

    public function allows(int $companyId): bool
    {
        $cutoff = now('UTC')->subDays(3)->toIso8601String();
        if (SurveyResponse::where('entreprise_id', $companyId)
            ->where(fn ($query) => $query->where('sent_at', '>', $cutoff)->orWhere('reminder_sent_at', '>', $cutoff))->exists()) {
            return false;
        }

        // Include uncertain/in-progress deliveries: acceptance by SMTP cannot be rolled back.
        return ! SurveyAction::where('action_type', SurveyActionType::EmailAttempted)
            ->where('triggered_at', '>', $cutoff)
            ->whereHas('survey', fn ($query) => $query->where('entreprise_id', $companyId))->exists();
    }
}
