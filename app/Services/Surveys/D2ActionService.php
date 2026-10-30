<?php

namespace App\Services\Surveys;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;

class D2ActionService extends SurveyActionService
{
    public const CONTACT_KEY = 'D2:CREATE_CONTACT';

    public const PROCESSED_KEY = 'D2:ACTIONS_EVALUATED';

    protected SurveyType $type = SurveyType::D2;

    protected ContactSource $source = ContactSource::D2Survey;

    protected string $origin = 'Questionnaire D2 — Premier usage';

    protected function rules(array $answers, int $companyId): array
    {
        $rules = [];
        if (($answers['q1'] ?? null) === 'blocage') {
            $rules['q1.blocage'] = 'Blocage lors du premier usage';
        }
        if (($answers['q4'] ?? null) === 'Oui, une courte démonstration') {
            $rules['q4.demonstration'] = 'Demande de démonstration';
        }
        if (($answers['q4'] ?? null) === 'Oui, un guide ciblé') {
            $rules['q4.guide'] = 'Demande de guide';
        }

        return $rules;
    }

    protected function priority(array $answers): ContactPriority
    {
        return ($answers['q1'] ?? null) === 'blocage' ? ContactPriority::High : ContactPriority::Normal;
    }

    protected function signals(SurveyResponse $survey, array $answers): void
    {
        if (in_array($answers['q1'] ?? null, ['pas_temps', 'pas_donnees'], true)) {
            $survey->actions()->create(['action_type' => SurveyActionType::ProjectFollowUp,
                'action_key' => 'D2:PROJECT_FOLLOW_UP', 'detail' => json_encode(['signal' => 'relance_projet', 'reason' => $answers['q1']], JSON_THROW_ON_ERROR),
                'triggered_at' => now('UTC')]);
        }
        if (($answers['q1'] ?? null) === 'usage_reel' && in_array($answers['q3'] ?? null, ['Oui, clairement', 'En partie'], true)) {
            $survey->actions()->create(['action_type' => SurveyActionType::ValueConfirmed,
                'action_key' => 'D2:VALUE_CONFIRMED', 'detail' => json_encode(['signal' => 'valeur_constatee', 'result' => $answers['q3']], JSON_THROW_ON_ERROR),
                'triggered_at' => now('UTC')]);
        }
    }
}
