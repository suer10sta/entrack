<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLifecycleEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['payload' => 'array', 'occurred_at' => 'immutable_datetime', 'processed_at' => 'immutable_datetime'];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(ProspectStatusHistory::class, 'lifecycle_event_id');
    }
}
