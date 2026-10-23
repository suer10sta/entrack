<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Cashier\Cashier;
use App\Models\Entreprise;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cashier (Stripe) is bound to the Entreprise, not the User: the
        // `stripe_id`, the subscriptions table and the billing logic all live
        // on the Entreprise. Without this, the webhook handlers look for the
        // customer in `users` and the local `subscriptions` table stays empty.
        Cashier::useCustomerModel(Entreprise::class);

        Paginator::useBootstrap();
        if (app()->environment('production') && str_starts_with(config('app.url'), 'https://')) {
            \URL::forceRootUrl(config('seo.url'));
            \URL::forceScheme('https');
            Paginator::useTailwind();
        }
    }
}
