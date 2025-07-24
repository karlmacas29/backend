<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaterAuthController;


//Admin Registration -- this is for admin
Route::post('/registration', [AuthController::class, 'Token_Register']);
Route::post('/login', [AuthController::class, 'Token_Login']);
Route::get('/role', [AuthController::class, 'get_role']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'Token_Logout']);
    // Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/users/{id}', [AuthController::class, 'getUserById']);
    Route::put('/users/{id}', [AuthController::class, 'updateUser']);
    // Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);


});

Route::get('/users', [AuthController::class, 'getAllUsers']);
Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
// raters account and registration



Route::post('/rater/register', [RaterAuthController::class, 'Raters_Register']);
Route::get('/rater/list', [RaterAuthController::class, 'get_all_raters']);
Route::get('/rater/name', [RaterAuthController::class, 'get_rater_usernames']); // public

Route::post('/rater/login', [RaterAuthController::class, 'Raters_login']);
Route::middleware('auth:sanctum')->group(function () {
  // Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
    Route::get('rater/assigned-job-batches', [RaterAuthController::class, 'getAssignedJobs']);

    Route::post('rater/logout', [RaterAuthController::class, 'Rater_logout']);
    Route::get('/rater/assign', [RaterAuthController::class, 'fetch_rater_assign']); // public

    Route::post('rater/changepassword', [RaterAuthController::class, 'change_password']);
});
Route::delete('rater/{id}', [RaterAuthController::class, 'deleteUser']);
// Route::delete('/rater/delete', [RaterAuthController::class, 'deleteAllUsers']);
