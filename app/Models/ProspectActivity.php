<?php

namespace App\Models;

use App\Enums\ProspectActivityType;
use Illuminate\Database\Eloquent\Model;

class ProspectActivity extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['type' => ProspectActivityType::class, 'metadata' => 'array', 'occurred_at' => 'immutable_datetime',
        'due_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class)->withTrashed();
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
