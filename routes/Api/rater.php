<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\rater\rater_controller;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RaterAuthController;
use App\Http\Controllers\SubmissionController;

Route::middleware('auth:sanctum')->group(function () {
   //get
    Route::get('/rater', [UsersController::class, 'getAuthenticatedrater']);
    Route::get('rater/users', [AuthController::class, 'getAllUsers']);
    Route::get('/rater/assigned-job-batches', [rater_controller::class, 'getAssignedJobs']);

    //post

    //delete
    Route::delete('rater/{id}', [RaterAuthController::class, 'deleteUser']);
});


Route::get('/rater/criteria/applicant/{id}', [rater_controller::class, 'get_criteria_applicant']);
Route::post('rating/score', [rater_controller::class, 'store_score']);

Route::get('rating/index', [SubmissionController::class, 'index']);
Route::delete('rating/delete/{id}', [SubmissionController::class, 'delete']);
