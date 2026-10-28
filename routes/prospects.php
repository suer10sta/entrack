<?php

use App\Http\Controllers\manager\ProspectController;
use Illuminate\Support\Facades\Route;

Route::prefix('manager/seo')->name('manager.')->middleware('auth:manager')->group(function () {
    Route::get('prospects/import', [\App\Http\Controllers\manager\ProspectImportController::class, 'index'])->name('prospects.import');
    Route::post('prospects/import', [\App\Http\Controllers\manager\ProspectImportController::class, 'store'])->middleware('throttle:10,1')->name('prospects.import.store');
    Route::patch('prospects/{prospect}/status', [ProspectController::class, 'status'])->name('prospects.status');
    Route::post('prospects/{prospect}/activities', [ProspectController::class, 'activity'])->name('prospects.activities.store');
    Route::resource('prospects', ProspectController::class)->except('destroy');
    Route::delete('prospects/{prospect}', [ProspectController::class, 'archive'])->name('prospects.archive');
    Route::get('campaigns', [\App\Http\Controllers\manager\CrmCampaignController::class, 'index'])->name('campaigns.index');
    Route::post('campaigns', [\App\Http\Controllers\manager\CrmCampaignController::class, 'store'])->name('campaigns.store');
    Route::get('campaigns/create', [\App\Http\Controllers\manager\CrmCampaignController::class, 'create'])->name('campaigns.create');
    Route::post('campaigns/{campaign}/setup', [\App\Http\Controllers\manager\CrmCampaignController::class, 'setup'])->name('campaigns.setup');
    Route::get('campaigns/{campaign}/sent-email', [\App\Http\Controllers\manager\CrmCampaignController::class, 'sentEmail'])->name('campaigns.sent-email');
    Route::get('campaigns/{campaign}/sent-email/content', [\App\Http\Controllers\manager\CrmCampaignController::class, 'sentEmailContent'])->name('campaigns.sent-email.content');
    Route::get('campaigns/{campaign}/preview', [\App\Http\Controllers\manager\CrmCampaignController::class, 'preview'])->name('campaigns.preview');
    Route::get('campaigns/{campaign}', [\App\Http\Controllers\manager\CrmCampaignController::class, 'show'])->name('campaigns.show');
    Route::delete('campaigns/{campaign}', [\App\Http\Controllers\manager\CrmCampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::patch('campaigns/{campaign}', [\App\Http\Controllers\manager\CrmCampaignController::class, 'update'])->name('campaigns.update');
    Route::post('campaigns/{campaign}/recipients', [\App\Http\Controllers\manager\CrmCampaignController::class, 'recipients'])->name('campaigns.recipients');
    Route::post('campaigns/{campaign}/send', [\App\Http\Controllers\manager\CrmCampaignController::class, 'send'])->middleware('throttle:10,1')->name('campaigns.send');

});
