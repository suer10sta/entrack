<?php

namespace App\Services\Surveys;

use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;

class D6ActionService extends SurveyActionService
{
    public const CONTACT_KEY = 'D6:CREATE_CONTACT';

    public const PROCESSED_KEY = 'D6:ACTIONS_EVALUATED';

    protected SurveyType $type = SurveyType::D6;

    protected ContactSource $source = ContactSource::D6Survey;

    protected string $origin = 'Questionnaire D6 — Non-renouvellement';

    protected function canProcess(SurveyResponse $survey): bool
    {
        return app(D6StageGuard::class)->allows($survey);
    }

    protected function rules(array $a, int $companyId): array
    {
        $map = ['Je rencontre un problème technique' => ['technique', 'Problème technique lié au non-renouvellement'], 'Dalytrac ne répond pas suffisamment à mes besoins' => ['besoins', 'Besoins insuffisamment couverts'], 'Une fonctionnalité essentielle me manque' => ['fonctionnalite', 'Fonctionnalité manquante']];
        if (! isset($map[$a['q1'] ?? ''])) {
            return [];
        }
        [$key,$label] = $map[$a['q1']];

        return ['q1.'.$key => $label];
    }

    protected function signals(SurveyResponse $s, array $a): void
    {
        $signals = [];
        $reason = $a['q1'];
        $positive = in_array($a['q2'] ?? null, ['Oui, certainement', 'Probablement'], true);
        if ($reason === 'Mon activité a baissé ou est en pause' && $positive) {
            $signals['BUSINESS_PAUSED'] = [SurveyActionType::BusinessPaused, ['signal' => 'client_en_pause']];
        }
        if ($reason === 'Je prévois de reprendre prochainement') {
            $signals['RETURN_PLANNED'] = [SurveyActionType::ReturnPlanned, ['signal' => 'reprise_prevue']];
        }
        if ($reason === 'Dalytrac ne répond pas suffisamment à mes besoins') {
            $signals['NEEDS_NOT_COVERED'] = [SurveyActionType::NeedsNotCovered, ['signal' => 'besoins_non_couverts']];
        }
        if ($reason === 'Le prix de mon forfait ne me convient plus') {
            $signals['PRICE_BARRIER'] = [SurveyActionType::PriceBarrier, ['signal' => 'frein_prix']];
        }
        if ($reason === 'J’utilise désormais une autre solution') {
            $signals['COMPETITOR_CHOSEN'] = [SurveyActionType::CompetitorChosen, ['signal' => 'autre_solution']];
        }
        if (in_array($a['q3'] ?? null, ['Oui, un forfait plus léger', 'Oui, un forfait plus complet'], true)) {
            $signals['ALTERNATIVE_PLAN'] = [SurveyActionType::AlternativePlan, ['signal' => 'autre_forfait_potentiel', 'preference' => $a['q3']]];
        }
        $stop = [];
        // The PDF says another solution OR negative return intention stops commercial follow-ups.
        if ($reason === 'J’utilise désormais une autre solution') {
            $stop[] = 'competitor';
        }
        if (in_array($a['q2'] ?? null, ['Probablement pas', 'Non'], true)) {
            $stop[] = 'negative_intent';
        }
        if (($a['q4'] ?? null) === 'Uniquement à ma demande') {
            $stop[] = 'on_request_only';
        }
        $due = null;
        $months = ['Dans un mois' => 1, 'Dans trois mois' => 3, 'Dans six mois' => 6];
        if ($stop) {
            $signals['STOP_FOLLOW_UP'] = [SurveyActionType::StopCommercial, ['scope' => 'D6', 'reasons' => $stop]];
        } elseif (isset($months[$a['q4'] ?? ''])) {
            $due = $s->completed_at->utc()->addMonthsNoOverflow($months[$a['q4']]);
            $signals['RECALL_PLANNED'] = [SurveyActionType::RecallPlanned, ['scope' => 'D6', 'due_at' => $due->toIso8601String(), 'months' => $months[$a['q4']]]];
        }
        $s->update(['recall_due_at' => $due]);
        foreach ($signals as $key => [$type,$detail]) {
            $s->actions()->create(['action_type' => $type, 'action_key' => 'D6:'.$key, 'detail' => json_encode($detail, JSON_THROW_ON_ERROR), 'triggered_at' => now('UTC')]);
        }
    }
}
