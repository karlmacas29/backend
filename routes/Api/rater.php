<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\rater\rater_controller;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RaterAuthController;
use App\Http\Controllers\SubmissionController;

Route::get('/rater/name', [rater_controller::class, 'get_rater_usernames']);
Route::post('/rater/login', [RaterAuthController::class, 'Raters_Login']);



Route::middleware('auth:sanctum')->group(function () {
    //get
    Route::get('/rater', [UsersController::class, 'getAuthenticatedrater']);
    Route::get('rater/users', [AuthController::class, 'getAllUsers']);
    Route::get('/rater/assigned-job-batches', [rater_controller::class, 'getAssignedJobs']);

    //post

    //delete
    Route::delete('rater/{id}', [RaterAuthController::class, 'deleteUser']);
    Route::post('/rating/score', [rater_controller::class, 'store_score']);
});


Route::get('/rater/criteria/applicant/{id}', [rater_controller::class, 'get_criteria_applicant']);
Route::delete('/rating/score/{id}', [rater_controller::class, 'delete']);

Route::get('rating/index', [SubmissionController::class, 'index']);
Route::delete('rating/delete/{id}', [SubmissionController::class, 'delete']);
Route::post('/rating/draft', [rater_controller::class, 'draft_score']);
