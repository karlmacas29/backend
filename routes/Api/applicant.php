<?php

use App\Http\Controllers\ApplicantSubmissionController;
use Illuminate\Support\Facades\Route;



Route::post('/applicant/submissions', [ApplicantSubmissionController::class, 'store']);
Route::delete('/applicant/read', [ApplicantSubmissionController::class, 'read_excel']);
Route::delete('/applicant', [ApplicantSubmissionController::class, 'deleteAllUsers']);
Route::post('/applicant/image', [ApplicantSubmissionController::class, 'store_image']);

// Route::get('/applicant/image/view', [ApplicantSubmissionController::class, 'read_excel']);
