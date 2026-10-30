<?php

namespace App\Mail;

use App\Models\SurveyResponse;
use App\Services\Surveys\SurveyCatalog;
use App\Services\Surveys\SurveyLink;
use Illuminate\Mail\Mailable;

class QuestionnaireMail extends Mailable
{
    protected string $ui = 'surveys.ui';

    public function __construct(public SurveyResponse $survey, public string $surveyLocale, public bool $reminder = false)
    {
        $this->locale($surveyLocale);
    }

    public function build(): self
    {
        $catalog = app(SurveyCatalog::class);
        $q1 = collect($catalog->definition($this->survey->survey_type, $this->survey->survey_version)['questions'])->firstWhere('id', 'q1');
        $choices = array_map(fn ($choice) => [
            'label' => $catalog->label($choice['label_key'], $this->surveyLocale),
            'url' => app(SurveyLink::class)->url($this->survey, $this->surveyLocale, $choice['value']),
        ], $q1['choices']);

        return $this->subject(__($this->ui.'.'.($this->reminder ? 'reminder_subject' : 'email_subject'), [], $this->surveyLocale))
            ->view('emails.survey_d1', [
                'surveyUi' => $this->ui, 'question' => $catalog->label($q1['label_key'], $this->surveyLocale), 'choices' => $choices,
            ]);
    }
}
