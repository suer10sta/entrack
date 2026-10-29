<?php

use App\Http\Controllers\SurveyD1Controller;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\SetUserTimezone;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/{locale}/questionnaires/D1/{survey}/{token}', SurveyD1Controller::class)
    ->whereIn('locale', ['fr', 'en', 'nl', 'es'])
    ->withoutMiddleware([CheckSubscription::class, SetUserTimezone::class])
    ->middleware('throttle:60,1')->name('surveys.d1');
