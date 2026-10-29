<?php

namespace App\Models;

use App\Enums\ContactEmailStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSendsLog extends Model
{
    protected $table = 'email_sends_log';

    protected $fillable = ['contact_id', 'contact_reply_id', 'user_id', 'manager_id', 'recipient_email', 'subject', 'body', 'status', 'attempted_at', 'sent_at', 'failed_at', 'error_message'];

    protected $casts = ['status' => ContactEmailStatus::class, 'attempted_at' => 'immutable_datetime', 'sent_at' => 'immutable_datetime', 'failed_at' => 'immutable_datetime'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(ContactReply::class, 'contact_reply_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
