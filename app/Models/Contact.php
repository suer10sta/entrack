<?php

namespace App\Models;

use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = ['ip', 'name', 'email', 'subject', 'message', 'is_active_acc', 'status', 'source', 'ticket_num', 'user_id', 'entreprise_id'];

    protected $casts = ['is_active_acc' => 'boolean', 'status' => ContactStatus::class, 'source' => ContactSource::class];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactReply::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailSendsLog::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ContactEvent::class);
    }
}
