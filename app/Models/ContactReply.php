<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactReply extends Model
{
    protected $fillable = ['contact_id', 'manager_id', 'message', 'submission_key'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailSendsLog::class, 'contact_reply_id');
    }
}
