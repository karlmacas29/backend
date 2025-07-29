<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\rater\rater_controller;
use App\Http\Controllers\RaterAuthController;


//Admin Registration -- this is for admin
Route::post('/login', [AuthController::class, 'Token_Login']);
Route::get('/role', [AuthController::class, 'get_role']);

Route::middleware('auth:sanctum')->group(function () {

    //get
    Route::get('/rater/list', [rater_controller::class, 'get_all_raters']);
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/users/{id}', [AuthController::class, 'getUserById']);
    // Route::get('/users', [AuthController::class, 'getAllUsers']);

    //post
    Route::post('/registration', [AuthController::class, 'Token_Register']);
    Route::post('/logout', [AuthController::class, 'Token_Logout']);
    Route::post('/rater/register', [RaterAuthController::class, 'Raters_Register']);

    //put
    Route::put('/users/{id}', [AuthController::class, 'updateUser']);

    //delete
    // Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);

});

// Route::get('/users', [AuthController::class, 'getAllUsers']);
// Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
// raters account and registration

//get
Route::get('/rater/name', [rater_controller::class, 'get_rater_usernames']);

//post
Route::post('/rater/login', [RaterAuthController::class, 'Raters_login']);



Route::middleware('auth:sanctum')->group(function () {
    //get
    // Route::get('/rater/assign', [RaterAuthController::class, 'fetch_rater_assign']);

    //post
    Route::post('rater/logout', [RaterAuthController::class, 'Rater_logout']);
    Route::post('rater/changepassword', [RaterAuthController::class, 'change_password']);
    // Route::post('/rater/edit/{id}', [RaterAuthController::class, 'editRater']);

    //delete
    // Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
});
Route::post('/rater/edit/{id}', [RaterAuthController::class, 'editRater']);
// Route::get('rater/users', [AuthController::class, 'getAllUsers']);
// Route::delete('rater/{id}', [RaterAuthController::class, 'deleteUser']);
// // Route::delete('/rater/delete', [RaterAuthController::class, 'deleteAllUsers']);
// Route::post('/rater/get/{id}', [RaterAuthController::class, 'get_applicant']);
// Route::get('/rater/assigned-job-batches', [RaterAuthController::class, 'getAssignedJobs']);
