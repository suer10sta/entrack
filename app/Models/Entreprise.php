<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription as CashierSubscription;
use App\Models\Alert;
use App\Models\billings_budget;
use App\Models\calendars;
use App\Models\Client;
use App\Models\client_contacts;
use App\Models\client_contacts_devis;
use App\Models\credit_note;
use App\Models\devis_details;
use App\Models\devis_history;
use App\Models\Employees;
use App\Models\employees_tasks;
use App\Models\entrepriseCharges;
use App\Models\EnterpriseRolePermission;
use App\Models\invoices_history;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\project_teams;
use App\Models\Role;
use App\Models\smtpConfig;
use App\Models\Tasks;
use App\Models\tasks_timer;
use App\Models\team_tasks;
use App\Models\Teams;
use App\Models\todoliste;
use App\Models\User;
use App\Models\work_days_repports;
use App\Models\note_de_frais;
use App\Models\Permission;

class Entreprise extends Model
{
    use HasFactory, Billable;

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'billit_api_key',
    ];

    protected $attributes = ['invoice_prefix' => 'F', 'invoice_number_format' => 'year_month_number'];

    protected $fillable = [
        'name', 
        'phone',
        'email',
        'Address',
        'country',
        'city',
        'zip_code',
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
        'invoice_start_number',
        'invoice_number_format',
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
        'trial_ends_at',
        'account_status',
        'auto_renew',
        'billit_api_key',
        'failed_payment_count',
        'last_payment_failed_at',
        'onboarding_completed_at',
    ];

    public function getAddressAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        $parts = array_filter([$this->country, $this->city, $this->zip_code], function ($part) {
            return !is_null($part) && $part !== '';
        });

        return implode(', ', $parts);
    }

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'auto_renew' => 'boolean',
        'billit_api_key' => 'encrypted',
    ];

    /**
     * Whether Billit can be called for this entreprise.
     */
    public function isBillitConfigured(): bool
    {
        return filled($this->getRawOriginal('billit_api_key'));
    }

    /**
     * Safely get the decrypted Billit API key.
     * Returns null when the stored value cannot be decrypted with the current APP_KEY.
     */
    public function getBillitApiKeyDecrypted(): ?string
    {
        try {
            return $this->billit_api_key;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return null;
        }
    }

    public function user()
    {
        return $this->hasMany(User::class, 'entreprise_id');
    }

    /**
     * Delete this entreprise and all related records in a single transaction.
     *
     * This is intended for irreversible clean-up (e.g. when a manager decides to
     * fully remove an entreprise and all its data from the platform).
     */
    public function deleteWithData(): void
    {
        DB::transaction(function () {
            $entrepriseId = $this->id;

            // Core user-related data
            User::where('entreprise_id', $entrepriseId)->delete();
            Employees::where('entreprise_id', $entrepriseId)->delete();

            // Roles / permissions
            //
            // IMPORTANT:
            // - Roles and permissions are shared across entreprises (platform-wide),
            //   and are linked to a specific entreprise through the
            //   `enterprise_role_permissions` pivot table.
            // - Deleting entries directly from `roles` or `permissions` here can
            //   accidentally wipe global roles/permissions for all entreprises.
            //
            // So when an entreprise is deleted we only remove its links in the
            // pivot table and leave the master roles/permissions intact.
            
            // Financial configuration and budgets
            smtpConfig::where('entreprise_id', $entrepriseId)->delete();
            entrepriseCharges::where('entreprise_id', $entrepriseId)->delete();
            billings_budget::where('entreprise_id', $entrepriseId)->delete();

            // Clients and related contacts
            client_contacts::where('entreprise_id', $entrepriseId)->delete();
            client_contacts_devis::where('entreprise_id', $entrepriseId)->delete();
            Client::where('entreprise_id', $entrepriseId)->delete();

            // Projects and files
            ProjectFile::where('entreprise_id', $entrepriseId)->delete();
            project_teams::where('entreprise_id', $entrepriseId)->delete();
            Project::where('entreprise_id', $entrepriseId)->delete();

            // Tasks and time tracking
            Tasks::where('entreprise_id', $entrepriseId)->delete();
            employees_tasks::where('entreprise_id', $entrepriseId)->delete();
            team_tasks::where('entreprise_id', $entrepriseId)->delete();
            tasks_timer::where('entreprise_id', $entrepriseId)->delete();

            // Teams
            Teams::where('entreprise_id', $entrepriseId)->delete();

            // Calendars / alerts / todos / work reports
            calendars::where('entreprise_id', $entrepriseId)->delete();
            Alert::where('entreprise_id', $entrepriseId)->delete();
            todoliste::where('entreprise_id', $entrepriseId)->delete();
            work_days_repports::where('entreprise_id', $entrepriseId)->delete();

            // Devis / notes de frais / credit notes / invoices
            devis_details::where('entreprise_id', $entrepriseId)->delete();
            devis_history::where('entreprise_id', $entrepriseId)->delete();
            note_de_frais::where('entreprise_id', $entrepriseId)->delete();
            credit_note::where('entreprise_id', $entrepriseId)->delete();
            invoices_history::where('entreprise_id', $entrepriseId)->delete();

            // Finally delete the entreprise record itself
            $this->delete();
        });
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
     * Whether the company currently has an active paid subscription on Stripe.
     *
     * Checks the local Cashier record first, then falls back to the Stripe API
     * so that entreprises created before the Cashier wiring fix (no local
     * subscription row) are still protected against double billing.
     */
    public function hasActivePaidSubscription(): bool
    {
        $activeStatuses = ['active', 'trialing', 'past_due', 'unpaid'];

        $subscription = $this->currentSubscription();
        if ($subscription && in_array((string) $subscription->stripe_status, $activeStatuses, true)) {
            return true;
        }

        $stripeSubscription = $this->latestStripeSubscriptionFromApi();
        if ($stripeSubscription) {
            return in_array((string) ($stripeSubscription->status ?? ''), $activeStatuses, true);
        }

        return false;
    }

    /**
     * Check if the company has a canceled subscription.
     * Only returns true if they have a plan (type_compt) and the subscription is canceled.
     */
    public function hasCanceledSubscription(): bool
    {
        // Only check for canceled subscription if they have a plan (not on trial)
        if (!$this->type_compt) {
            return false;
        }

        // If account_status is explicitly 'inactive', subscription is canceled.
        // BUT the webhook also sets account_status = 'inactive' after 3 failed
        // payments (see StripeWebhookController::handleInvoicePaymentFailed),
        // which is NOT a cancellation. In that case keep checking the real
        // status so the failing-payment message is shown instead.
        if ($this->account_status === 'inactive' && ! $this->isBlockedByFailedPayments()) {
            return true;
        }

        // Check if they have a subscription with canceled status in database
        $subscription = $this->currentSubscription();
        if ($subscription) {
            $canceledStatuses = ['canceled', 'incomplete_expired'];
            $status = (string) $subscription->stripe_status;
            if (in_array($status, $canceledStatuses, true)) {
                return true;
            }
        }

        // If they have a plan and stripe_id, check Stripe API for subscription status
        if ($this->stripe_id) {
            try {
                $stripeSubscription = $this->latestStripeSubscriptionFromApi();
                if ($stripeSubscription) {
                    $canceledStatuses = ['canceled', 'incomplete_expired'];
                    $status = (string) ($stripeSubscription->status ?? '');
                    if (in_array($status, $canceledStatuses, true)) {
                        return true;
                    }
                }
                // If no subscription found in Stripe but they have a plan and stripe_id,
                // it might be canceled - but we'll rely on account_status for this case
            } catch (\Throwable $e) {
                // If we can't check Stripe, fall back to account_status check above
            }
        }

        return false;
    }

    /**
     * Certain entreprises can be exempted from blocking logic.
     * For example, entreprise with ID 43 should never be blocked.
     */
    public function isBlockingExempt(): bool
    {
        return (int) $this->id === 43;
    }

    /**
     * Check if the trial has expired AND they have NOT upgraded.
     * This is the "block" condition.
     */
    public function isTrialExpired(): bool
    {
        // Exempt entreprise(s) from being treated as trial-expired
        if ($this->isBlockingExempt()) {
            return false;
        }

        // If the company already has a paid plan, never treat it as trial-expired
        if ($this->hasActiveSubscription()) {
            return false;
        }

        // Legacy accounts may have no trial date at all (trial_ends_at IS NULL).
        // A missing date must never be interpreted as "already expired",
        // otherwise any account created before trial tracking existed would
        // be locked out immediately without recourse. They keep access
        // (getUserLimit treats a null trial date like an active trial).
        if (!$this->trial_ends_at) {
            return false;
        }

        // Otherwise: trial date is in the past AND they do not have a paid plan
        return $this->trial_ends_at->isPast();
    }

    /**
     * Check if the account should be blocked due to consecutive failed payments.
     * Blocks after 3 consecutive failures.
     */
    public function isBlockedByFailedPayments(): bool
    {
        return $this->failed_payment_count >= 3;
    }

    /**
     * Increment the failed payment counter.
     */
    public function recordFailedPayment(): void
    {
        $this->increment('failed_payment_count');
        $this->update(['last_payment_failed_at' => now()]);
    }

    /**
     * Reset the failed payment counter (called on successful payment).
     */
    public function resetFailedPaymentCount(): void
    {
        $this->update(['failed_payment_count' => 0, 'last_payment_failed_at' => null]);
    }

    /**
     * Map a Stripe Price ID back to a DalyTrac plan key (solo/premium/pro).
     * Used to converge local subscription state from Stripe events when the
     * user never returns to the success URL. Returns null when unknown.
     */
    public static function planForStripePrice(?string $priceId): ?string
    {
        if (! $priceId) {
            return null;
        }

        foreach ((array) config('services.stripe.prices', []) as $plan => $configuredId) {
            if ($configuredId && hash_equals((string) $configuredId, $priceId)) {
                return (string) $plan;
            }
        }

        return null;
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
            default => $this->isOnTrial() || !$this->trial_ends_at ? PHP_INT_MAX : 1, // Unlimited on trial or if no trial date is configured, 1 if expired
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

        $currentUserCount = $this->user()
            ->whereDoesntHave('employee', fn ($q) => $q->where('archived', true))
            ->count();
        return $currentUserCount < $limit;
    }

    /**
     * Return the most relevant current subscription record.
     */
    public function currentSubscription(): ?CashierSubscription
    {
        $subscription = $this->subscription('default');
        if ($subscription) {
            return $subscription;
        }

        // Fallback for legacy data where subscription type wasn't saved as "default".
        return $this->subscriptions()
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Resolve latest Stripe subscription directly from Stripe API.
     */
    public function latestStripeSubscriptionFromApi(): ?object
    {
        if (!$this->stripe_id) {
            return null;
        }

        try {
            $response = Cashier::stripe()->subscriptions->all([
                'customer' => $this->stripe_id,
                'status' => 'all',
                'limit' => 10,
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        $subscriptions = collect($response->data ?? []);
        if ($subscriptions->isEmpty()) {
            return null;
        }

        $activeStatuses = ['active', 'trialing', 'past_due', 'unpaid'];
        $activeSubscription = $subscriptions
            ->first(fn ($sub) => in_array((string) ($sub->status ?? ''), $activeStatuses, true));

        if ($activeSubscription) {
            return $activeSubscription;
        }

        return $subscriptions
            ->sortByDesc(fn ($sub) => (int) ($sub->created ?? 0))
            ->first();
    }

    /**
     * Compare the local renewal preference with Stripe's current renewal state.
     */
    public function renewalSyncStatus(): array
    {
        $expectedRenewal = $this->auto_renew ? 'auto' : 'manual';
        $subscription = $this->currentSubscription();

        if ($subscription) {
            // In Cashier terms, grace period means Stripe is set to cancel at period end.
            $stripeRenewal = $subscription->onGracePeriod() ? 'manual' : 'auto';
            $isSynced = $expectedRenewal === $stripeRenewal;

            return [
                'state' => $isSynced ? 'synced' : 'out_of_sync',
                'is_synced' => $isSynced,
                'expected' => $expectedRenewal,
                'stripe' => $stripeRenewal,
                'label' => $isSynced
                    ? 'Your renewal preference is active and confirmed.'
                    : 'We are still updating your renewal preference. Please refresh in a few seconds.',
            ];
        }

        $stripeSubscription = $this->latestStripeSubscriptionFromApi();
        if ($stripeSubscription) {
            $stripeRenewal = !empty($stripeSubscription->cancel_at_period_end) ? 'manual' : 'auto';
            $isSynced = $expectedRenewal === $stripeRenewal;

            return [
                'state' => $isSynced ? 'synced' : 'out_of_sync',
                'is_synced' => $isSynced,
                'expected' => $expectedRenewal,
                'stripe' => $stripeRenewal,
                'label' => $isSynced
                    ? 'Your renewal preference is active and confirmed.'
                    : 'We are still updating your renewal preference. Please refresh in a few seconds.',
            ];
        }

        return [
            'state' => 'unknown',
            'is_synced' => false,
            'expected' => $expectedRenewal,
            'stripe' => null,
            'label' => 'No active Stripe subscription found.',
        ];
    }
}

