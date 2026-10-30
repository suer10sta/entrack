<?php

namespace App\Services\Surveys;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveyLink
{
    public function url(SurveyResponse $survey, string $locale, ?string $choice = null): string
    {
        return URL::temporarySignedRoute('surveys.'.strtolower($survey->survey_type->value), $survey->sent_at->addDays(15), array_filter([
            'locale' => $locale, 'survey' => $survey->id, 'token' => $survey->token, 'q1' => $choice,
        ], fn ($value) => $value !== null));
    }

    public function resolve(Request $request, string $id, string $token, SurveyType $type = SurveyType::D1): SurveyResponse
    {
        if (! Str::isUuid($id) || ! $request->hasValidSignature()) {
            throw new HttpException(403, 'invalid_link');
        }
        $survey = SurveyResponse::find($id);
        if (! $survey || $survey->survey_type !== $type || ! hash_equals($survey->token, $token)
            || ! $survey->sent_at || (int) $request->query('expires') !== $survey->sent_at->addDays(15)->timestamp) {
            throw new HttpException(403, 'invalid_link');
        }

        return $survey;
    }

    public function assertOpen(SurveyResponse $survey): void
    {
        if (in_array($survey->status, [SurveyStatus::Expired, SurveyStatus::Cancelled], true)) {
            throw new HttpException(410, 'closed');
        }
        if (in_array($survey->status, [SurveyStatus::Answered, SurveyStatus::Abandon], true)) {
            throw new HttpException(409, 'already_closed');
        }
        if (! $survey->sent_at || now('UTC')->gte($survey->sent_at->addDays(15))
            || ($survey->reminder_sent_at && now('UTC')->gte($survey->reminder_sent_at->addDays(10)))) {
            throw new HttpException(410, 'invalid_link');
        }
    }
}
