<?php

use App\Http\Controllers\manager\QuestionnaireController;
use Illuminate\Support\Facades\Route;

Route::get('questionnaires', [QuestionnaireController::class, 'index'])->name('questionnaires.index');
Route::get('questionnaires/export', [QuestionnaireController::class, 'export'])->name('questionnaires.export');
Route::post('questionnaires/seen', [QuestionnaireController::class, 'seen'])->name('questionnaires.seen');
Route::get('questionnaires/{survey}', [QuestionnaireController::class, 'show'])->whereUuid('survey')->name('questionnaires.show');
