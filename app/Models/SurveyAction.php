<?php

namespace App\Models;

use App\Enums\SurveyActionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SurveyAction extends Model
{
    use HasUuids;

    protected $dateFormat = 'Y-m-d H:i:sP';

    public $timestamps = false;

    protected $fillable = ['survey_id', 'action_type', 'action_key', 'detail', 'triggered_at'];

    protected $casts = ['action_type' => SurveyActionType::class, 'triggered_at' => 'immutable_datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Survey actions are append-only.'));
        static::deleting(fn () => throw new LogicException('Survey actions are append-only.'));
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_id');
    }
}
