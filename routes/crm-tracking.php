<?php

use App\Http\Controllers\CrmCampaignTrackingController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:1200,1')->prefix('crm/t')->name('crm.tracking.')->group(function () {
    Route::get('o/{token}.gif', [CrmCampaignTrackingController::class, 'open'])->where('token', '[a-f0-9]{64}')->name('open');
    Route::get('c/{token}', [CrmCampaignTrackingController::class, 'click'])->where('token', '[a-f0-9]{64}')->name('click');
});
