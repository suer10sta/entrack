<?php

namespace App\Services\Surveys;

use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Models\Contact;
use App\Models\SurveyResponse;

class D3ActionService extends SurveyActionService
{
    public const CONTACT_KEY = 'D3:CREATE_CONTACT';

    public const PROCESSED_KEY = 'D3:ACTIONS_EVALUATED';

    protected SurveyType $type = SurveyType::D3;

    protected ContactSource $source = ContactSource::D3Survey;

    protected string $origin = 'Questionnaire D3 — Valeur et intention d’achat';

    public function strongIntent(array $answers): bool
    {
        return in_array($answers['q4'] ?? null, ['Très probable', 'Plutôt probable'], true);
    }

    protected function rules(array $answers, int $companyId): array
    {
        $rules = [];
        if (($answers['q3'] ?? null) === 'Un problème technique') {
            $rules['q3.technique'] = 'Question technique avant achat';
        }
        if (($answers['q3'] ?? null) === 'Une fonctionnalité manque') {
            $rules['q3.fonctionnalite'] = 'Fonctionnalité manquante';
        }
        if (in_array($answers['q1'] ?? null, ['1', '2'], true)) {
            $rules['q1.gain_faible'] = 'Valeur perçue faible — entretien de prise en main';
        }
        if (($answers['q3'] ?? null) === 'Le prix des forfaits' && $this->strongIntent($answers)) {
            $rules['q3.prix_intention_forte'] = 'Questions restantes avant achat — prix';
        }

        return $rules;
    }

    protected function signals(SurveyResponse $survey, array $answers): void
    {
        $signals = [];
        if ($this->strongIntent($answers)) {
            $signals['PURCHASE_INTENT'] = [SurveyActionType::PurchaseIntent, ['signal' => 'forte_intention_achat', 'likelihood' => $answers['q4'], 'plan' => $answers['q5'] ?? null]];
        }
        if (($answers['q3'] ?? null) === 'Le prix des forfaits') {
            $signals['PRICE_BARRIER'] = [SurveyActionType::PriceBarrier, ['signal' => 'frein_prix']];
        }
        if (($answers['q3'] ?? null) === 'Je dois obtenir l’accord d’un responsable') {
            $signals['DECISION_APPROVAL'] = [SurveyActionType::DecisionApproval, ['signal' => 'validation_decideur']];
        }
        foreach ($signals as $key => [$type, $detail]) {
            $survey->actions()->create(['action_type' => $type, 'action_key' => 'D3:'.$key, 'detail' => json_encode($detail, JSON_THROW_ON_ERROR), 'triggered_at' => now('UTC')]);
        }
    }

    public function process(SurveyResponse $survey): ?Contact
    {
        $contact = parent::process($survey);
        if (! app(D3BusinessMailer::class)->send($survey)) {
            throw new \RuntimeException('D3 business email delivery pending or unconfirmed.');
        }

        return $contact;
    }

    public function retryCompany(int $companyId): int
    {
        $failures = 0;
        SurveyResponse::where('entreprise_id', $companyId)->where('survey_type', SurveyType::D3)->whereNotNull('completed_at')
            ->whereHas('actions', fn ($q) => $q->where('action_key', 'answered:final'))
            ->eachById(function ($survey) use (&$failures) {
                if (! $this->processSafely($survey)) {
                    $failures++;
                }
            }, 100);

        return $failures;
    }
}
