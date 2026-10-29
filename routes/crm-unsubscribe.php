<?php

use App\Http\Controllers\CrmCampaignUnsubscribeController;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\SetUserTimezone;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/{locale}/crm/unsubscribe/{token}', CrmCampaignUnsubscribeController::class)
    ->whereIn('locale', ['fr', 'en', 'nl', 'es'])
    ->withoutMiddleware([CheckSubscription::class, SetUserTimezone::class])
    ->middleware('throttle:60,1')->name('crm.unsubscribe');
