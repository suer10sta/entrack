<?php

namespace App\Models;

use App\Enums\ProspectStatus;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LogicException;

class Prospect extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'company_name', 'country', 'owner_manager_id', 'source',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'source_captured_at',
        'linked_user_id', 'linked_entreprise_id', 'linked_at', 'captured_at', 'next_follow_up_at'];

    protected $casts = ['status' => ProspectStatus::class, 'source_captured_at' => 'immutable_datetime',
        'linked_at' => 'immutable_datetime', 'captured_at' => 'immutable_datetime', 'status_changed_at' => 'immutable_datetime',
        'trial_started_at' => 'immutable_datetime', 'first_paid_at' => 'immutable_datetime', 'next_follow_up_at' => 'immutable_datetime',
        'unsubscribed_at' => 'immutable_datetime'];

    private bool $transitionInProgress = false;

    protected static function booted(): void
    {
        static::creating(function (self $prospect) {
            $prospect->public_token = bin2hex(random_bytes(24));
            $prospect->status ??= ProspectStatus::Nouveau;
            if ($prospect->status !== ProspectStatus::Nouveau) {
                throw new LogicException('Create prospects as nouveau, then use ProspectStatusService.');
            }
        });
        static::saving(function (self $prospect) {
            if ($prospect->exists && $prospect->isDirty('public_token')) {
                throw new LogicException('The public prospect token is immutable.');
            }
            $prospect->email_normalized = self::normalizeEmail($prospect->email);
            if ($prospect->exists && $prospect->isDirty('status') && ! $prospect->transitionInProgress) {
                throw new LogicException('Use ProspectStatusService to change a prospect status.');
            }
        });
    }

    public static function normalizeEmail(?string $email): ?string
    {
        return $email === null ? null : Str::lower(trim($email));
    }

    public function getLinkedinUrlAttribute(): string
    {
        return 'https://dalytrac.com/?utm_source=linkedin&utm_medium=message_prive&ref='.$this->public_token;
    }

    /** @internal Only ProspectStatusService should call this inside its transaction. */
    public function applyStatusTransition(ProspectStatus $status, DateTimeInterface $at): void
    {
        $this->transitionInProgress = true;
        try {
            $this->forceFill(['status' => $status, 'status_changed_at' => $at])->saveOrFail();
        } finally {
            $this->transitionInProgress = false;
        }
    }

    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function linkedEntreprise()
    {
        return $this->belongsTo(Entreprise::class, 'linked_entreprise_id');
    }

    public function ownerManager()
    {
        return $this->belongsTo(Manager::class, 'owner_manager_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(ProspectStatusHistory::class);
    }

    public function activities()
    {
        return $this->hasMany(ProspectActivity::class);
    }

    public function lifecycleEvents()
    {
        return $this->hasMany(CrmLifecycleEvent::class);
    }
}
