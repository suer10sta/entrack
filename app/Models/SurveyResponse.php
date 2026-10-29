<?php

namespace App\Models;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Subscription;

class SurveyResponse extends Model
{
    use HasUuids;

    protected $dateFormat = 'Y-m-d H:i:sP';

    protected $fillable = [
        'survey_type', 'survey_version', 'cycle_key', 'entreprise_id', 'user_id',
        'subscription_id', 'email', 'status', 'sent_at', 'reminder_sent_at', 'clicked_at',
        'completed_at', 'recall_due_at', 'converted_after_answer', 'converted_at', 'contact_id',
    ];

    protected $hidden = ['token'];

    protected $attributes = ['cycle_key' => 'once', 'converted_after_answer' => false];

    protected $casts = [
        'survey_type' => SurveyType::class, 'survey_version' => 'integer', 'status' => SurveyStatus::class,
        'sent_at' => 'immutable_datetime', 'reminder_sent_at' => 'immutable_datetime',
        'clicked_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime',
        'recall_due_at' => 'immutable_datetime', 'converted_at' => 'immutable_datetime',
        'converted_after_answer' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $survey) {
            $survey->status ??= SurveyStatus::Sent;
            // Hide the cryptographically random bearer token from serialization.
            $survey->token ??= bin2hex(random_bytes(32));
        });
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(SurveyAction::class, 'survey_id');
    }
}
