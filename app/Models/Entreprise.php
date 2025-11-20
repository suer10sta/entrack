<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;  

class Entreprise extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name', 
        'phone',
        'email',
        'Address',
        'image', 
        'is_activated', 
        'duration',
        'tva',
        'bank_name',
        'iban',
        'swift',
        'bic',
        'entreprise_id',
        'entreprise_number',
        'general_conditions',
        'invoice_prefix',
        'second_reminder',
        'third_reminder',
        'devis_prefix',
        'notedefraisPrefix',
        'creditnoteprefix',
        'devis_start_number',
        'monthBudget',
        'general_conditions_Devis',
        'invoice_body',
        'devis_body',
        'emails_backup',
        'workdays_emails',
        'peppol_id',
        'peppol_sheme',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'type_compt',
        'trial_ends_at'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'entreprise_id');
    }

    public function isOnTrial(): bool
    {
        // Check if a trial date is set and it's in the future
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the company has an active, paid subscription.
     */
    public function hasActiveSubscription(): bool
    {
        // 'type_compt' is not null, meaning they have chosen a plan
        return !is_null($this->type_compt);
    }

    /**
     * Check if the trial has expired AND they have NOT upgraded.
     * This is the "block" condition.
     */
    public function isTrialExpired(): bool
    {
        // Trial date is in the past AND they do not have a paid plan
        return $this->trial_ends_at && $this->trial_ends_at->isPast() && !$this->hasActiveSubscription();
    }

    /**
     * Get the user limit for the company's current plan.
     */
    public function getUserLimit(): int
    {
        // Use a 'match' statement for clean logic
        return match ($this->type_compt) {
            'solo' => 1,
            'premium' => 10,
            'pro' => PHP_INT_MAX, // PHP_INT_MAX is a good way to say "unlimited"
            default => $this->isOnTrial() ? PHP_INT_MAX : 1, // Unlimited on trial, 1 if expired
        };
    }

    /**
     * Check if the company can add more users.
     */
    public function canAddMoreUsers(): bool
    {
        $limit = $this->getUserLimit();
        if ($limit === PHP_INT_MAX) {
            return true; 
        }

        $currentUserCount = $this->user()->count();
        return $currentUserCount < $limit;
    }
}

