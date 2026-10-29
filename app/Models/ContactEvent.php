<?php

namespace App\Models;

use App\Enums\ContactEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['contact_id', 'manager_id', 'event_type', 'metadata'];

    protected $casts = ['event_type' => ContactEventType::class, 'metadata' => 'array', 'created_at' => 'immutable_datetime'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
