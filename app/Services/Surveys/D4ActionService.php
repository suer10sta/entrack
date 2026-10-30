<?php

namespace App\Services\Surveys;

use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;

class D4ActionService extends SurveyActionService
{
    public const CONTACT_KEY = 'D4:CREATE_CONTACT';

    public const PROCESSED_KEY = 'D4:ACTIONS_EVALUATED';

    protected SurveyType $type = SurveyType::D4;

    protected ContactSource $source = ContactSource::D4Survey;

    protected string $origin = 'Questionnaire D4 — Fin d’essai sans achat';

    protected function canProcess(SurveyResponse $survey): bool
    {
        return app(D4StageGuard::class)->allows($survey);
    }

    protected function rules(array $answers, int $companyId): array
    {
        $rules = [];
        $reasons = ['La prise en main était trop difficile' => ['onboarding', 'Difficulté d’onboarding'],
            'Il manque une fonctionnalité essentielle' => ['fonctionnalite', 'Fonctionnalité essentielle manquante'],
            'Je dois obtenir l’accord d’un décideur' => ['decideur', 'Décision interne avant abonnement']];
        if (isset($reasons[$answers['q1'] ?? ''])) {
            [$key,$label] = $reasons[$answers['q1']];
            $rules['q1.'.$key] = $label;
        }
        if (($answers['q4'] ?? null) === 'Oui') {
            $rules['q4.appel'] = 'Demande d’échange de 20 minutes après l’essai';
        }

        return $rules;
    }

    protected function signals(SurveyResponse $survey, array $answers): void
    {
        $signals = [];
        $reason = $answers['q1'];
        if ($reason === 'Le prix des forfaits ne me convient pas') {
            $signals['PRICE_BARRIER'] = [SurveyActionType::PriceBarrier, ['signal' => 'frein_prix']];
        }
        if (in_array($reason, ['Je n’ai pas eu le temps de tester suffisamment', 'Ce n’était pas le bon moment pour mon entreprise'], true)) {
            $signals['PROJECT_FOLLOW_UP'] = [SurveyActionType::ProjectFollowUp, ['signal' => 'relance_projet', 'context' => 'fin_essai', 'reason' => $reason]];
        }
        if (($answers['q3'] ?? null) === 'Davantage de temps pour tester') {
            $signals['TRIAL_EXTENSION_REQUESTED'] = [SurveyActionType::TrialExtensionRequested, ['signal' => 'extension_trial_potentielle', 'decision' => 'manager_required']];
        }
        if ($reason === 'Je n’ai pas perçu assez de gain de temps') {
            $signals['INSUFFICIENT_VALUE'] = [SurveyActionType::InsufficientValue, ['signal' => 'valeur_insuffisante']];
        }
        if ($reason === 'J’ai choisi une autre solution') {
            $signals['COMPETITOR_CHOSEN'] = [SurveyActionType::CompetitorChosen, ['signal' => 'autre_solution']];
        }
        foreach ($signals as $key => [$type,$detail]) {
            $survey->actions()->create(['action_type' => $type, 'action_key' => 'D4:'.$key, 'detail' => json_encode($detail, JSON_THROW_ON_ERROR), 'triggered_at' => now('UTC')]);
        }
    }
}
