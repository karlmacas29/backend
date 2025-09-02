<?php

use App\Http\Controllers\ApplicantSubmissionController;
use App\Http\Controllers\rater\rater_controller;
use Illuminate\Support\Facades\Route;




Route::prefix('applicant')->group(function () {
    Route::post('/submissions', [ApplicantSubmissionController::class, 'store']);
    Route::delete('/read', [ApplicantSubmissionController::class, 'read_excel']);
    Route::post('/image', [ApplicantSubmissionController::class, 'store_image']);
    Route::delete('/delete', [ApplicantSubmissionController::class, 'deleteAllUsers']);

    Route::get('/scores/{applicant}', [rater_controller::class, 'applicant_history_score']);
});
