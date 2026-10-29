<?php

namespace App\Mail;

use App\Enums\SurveyType;
use App\Models\SurveyResponse;
use App\Services\Surveys\SurveyCatalog;
use App\Services\Surveys\SurveyLink;
use Illuminate\Mail\Mailable;

class D1SurveyMail extends Mailable
{
    public function __construct(public SurveyResponse $survey, public string $surveyLocale, public bool $reminder = false)
    {
        $this->locale($surveyLocale);
    }

    public function build(): self
    {
        $catalog = app(SurveyCatalog::class);
        $q1 = collect($catalog->definition(SurveyType::D1, $this->survey->survey_version)['questions'])->firstWhere('id', 'q1');
        $choices = array_map(fn ($choice) => [
            'label' => $catalog->label($choice['label_key'], $this->surveyLocale),
            'url' => app(SurveyLink::class)->url($this->survey, $this->surveyLocale, $choice['value']),
        ], $q1['choices']);

        return $this->subject(__('surveys.ui.'.($this->reminder ? 'reminder_subject' : 'email_subject'), [], $this->surveyLocale))
            ->view('emails.survey_d1', [
                'question' => $catalog->label($q1['label_key'], $this->surveyLocale), 'choices' => $choices,
            ]);
    }
}
