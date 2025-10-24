<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\EnterpriseRolePermission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define a custom gate check for all permissions
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            // Check enterprise-specific permissions
            return $user->roles()
                ->whereHas('enterprisePermissions', function ($query) use ($user, $ability) {
                    $query->where('entreprise_id', $user->entreprise_id)
                        ->whereHas('permission', function ($q) use ($ability) {
                            $q->where('name', $ability);
                        });
                })
                ->exists();
        });
    }
}
