<?php

namespace App\Services\Surveys;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;

class D5ActionService extends SurveyActionService
{
    public const CONTACT_KEY = 'D5:CREATE_CONTACT';

    public const PROCESSED_KEY = 'D5:ACTIONS_EVALUATED';

    protected SurveyType $type = SurveyType::D5;

    protected ContactSource $source = ContactSource::D5Survey;

    protected string $origin = 'Questionnaire D5 — Après premier achat';

    protected function priority(array $answers): ContactPriority
    {
        return ($answers['q2'] ?? null) === 'Non, à cause d’un problème technique' ? ContactPriority::High : ContactPriority::Normal;
    }

    protected function rules(array $answers, int $companyId): array
    {
        $rules = [];
        if (($answers['q2'] ?? null) === 'Non, à cause d’un problème technique') {
            $rules['q2.technique'] = 'Problème technique après abonnement';
        }
        if (($answers['q2'] ?? null) === 'Oui, avec quelques difficultés') {
            $rules['q2.difficultes'] = 'Difficultés d’utilisation';
        }
        if (in_array($answers['q4'] ?? null, ['Un conseil d’utilisation', 'Une assistance technique', 'Une démonstration complémentaire', 'Une aide pour faire adopter l’outil à mon équipe'], true)) {
            $rules['q4.accompagnement'] = 'Demande d’accompagnement';
        }

        return $rules;
    }

    protected function signals(SurveyResponse $survey, array $answers): void
    {
        $signals = [];
        if (($answers['q2'] ?? null) === 'Non, mon équipe n’a pas encore commencé') {
            $signals['ADOPTION_RISK'] = [SurveyActionType::AdoptionRisk, ['signal' => 'risque_adoption', 'reason' => 'equipe_non_demarree']];
        }
        if (($answers['q3'] ?? null) === 'Il me manque des possibilités') {
            $signals['UPSELL'] = [SurveyActionType::Upsell, ['signal' => 'opportunite_upsell', 'reason' => 'forfait_insuffisant']];
        }
        if (($answers['q3'] ?? null) === 'Il est plus complet que nécessaire') {
            $signals['RENEWAL_RISK'] = [SurveyActionType::RenewalRisk, ['signal' => 'risque_non_renouvellement', 'reason' => 'forfait_surdimensionne']];
        }
        foreach ($signals as $key => [$type,$detail]) {
            $survey->actions()->create(['action_type' => $type, 'action_key' => 'D5:'.$key, 'detail' => json_encode($detail, JSON_THROW_ON_ERROR), 'triggered_at' => now('UTC')]);
        }
    }
}
