<?php

use App\Http\Controllers\SurveyD1Controller;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\SetUserTimezone;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/{locale}/questionnaires/D1/{survey}/{token}', SurveyD1Controller::class)
    ->whereIn('locale', ['fr', 'en', 'nl', 'es'])
    ->withoutMiddleware([CheckSubscription::class, SetUserTimezone::class])
    ->middleware('throttle:60,1')->name('surveys.d1');

Route::match(['GET', 'POST'], '/{locale}/questionnaires/D2/{survey}/{token}', \App\Http\Controllers\SurveyD2Controller::class)
    ->whereIn('locale', ['fr', 'en', 'nl', 'es'])
    ->withoutMiddleware([CheckSubscription::class, SetUserTimezone::class])
    ->middleware('throttle:60,1')->name('surveys.d2');
Route::match(['GET', 'POST'], '/{locale}/questionnaires/D3/{survey}/{token}', \App\Http\Controllers\SurveyD3Controller::class)
    ->whereIn('locale', ['fr', 'en', 'nl', 'es'])
    ->withoutMiddleware([CheckSubscription::class, SetUserTimezone::class])
    ->middleware('throttle:60,1')->name('surveys.d3');

Route::match(['GET', 'POST'], '/{locale}/questionnaires/D4/{survey}/{token}', \App\Http\Controllers\SurveyD4Controller::class)
    ->whereIn('locale', ['fr', 'en', 'nl', 'es'])
    ->withoutMiddleware([CheckSubscription::class, SetUserTimezone::class])
    ->middleware('throttle:60,1')->name('surveys.d4');
