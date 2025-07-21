<?php

use App\Http\Controllers\ApplicantSubmissionController;
use Illuminate\Support\Facades\Route;



Route::post('/applicant/submissions', [ApplicantSubmissionController::class, 'store']);
Route::delete('/applicant', [ApplicantSubmissionController::class, 'deleteAllUsers']);
