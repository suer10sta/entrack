<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;

class SurveyAnswer extends Model
{
    use HasUuids;

    protected $dateFormat = 'Y-m-d H:i:sP';

    public const MAX_VALUE_LENGTH = 2000;

    protected $fillable = ['survey_id', 'question_id', 'value'];

    protected static function booted(): void
    {
        static::saving(function (self $answer) {
            Validator::make($answer->getAttributes(), [
                'value' => ['present', 'string', 'max:'.self::MAX_VALUE_LENGTH],
            ])->validate();
        });
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_id');
    }
}
