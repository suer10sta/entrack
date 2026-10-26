<?php

use App\Http\Controllers\manager\SearchConsoleController;
use Illuminate\Support\Facades\Route;

// web.php supplies the session middleware required by the Manager guard.
Route::middleware(['auth:manager', 'throttle:60,1'])->group(function () {
    Route::get('/manager/seo/google-analytics', [\App\Http\Controllers\manager\GoogleAnalyticsController::class, 'index'])->name('manager.seo.analytics');
    Route::get('/api/seo/google-analytics/{report}', [\App\Http\Controllers\manager\GoogleAnalyticsController::class, 'data'])->where('report', 'overview|traffic|pages|sources|campaigns|events|countries|devices')->name('manager.seo.analytics.data');
    Route::get('/manager/seo', [SearchConsoleController::class, 'index'])->name('manager.seo.index');
    Route::get('/api/auth/google', [SearchConsoleController::class, 'connect'])->name('manager.seo.connect');
    Route::get('/api/auth/google/callback', [SearchConsoleController::class, 'callback'])->name('manager.seo.callback');
    Route::get('/api/seo/{report}', [SearchConsoleController::class, 'data'])->where('report', 'sites|overview|queries|pages|countries|devices|sitemaps')->name('manager.seo.data');
});
