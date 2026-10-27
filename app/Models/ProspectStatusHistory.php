<?php

namespace App\Models;

use App\Enums\ProspectStatus;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class ProspectStatusHistory extends Model
{
    protected $table = 'prospect_status_history';

    protected $guarded = ['id'];

    protected $casts = ['from_status' => ProspectStatus::class, 'to_status' => ProspectStatus::class, 'occurred_at' => 'immutable_datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Status history is append-only.'));
        static::deleting(fn () => throw new LogicException('Status history is append-only.'));
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class)->withTrashed();
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function lifecycleEvent()
    {
        return $this->belongsTo(CrmLifecycleEvent::class);
    }
}
