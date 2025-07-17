<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


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
    Route::post('rater/logout', [AuthController::class, 'Rater_logout']);

});
Route::get('/users', [AuthController::class, 'getAllUsers']);
Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
// raters account and registration
Route::post('/rater/register', [AuthController::class, 'Raters_Register']);
Route::post('/rater/login', [AuthController::class, 'Raters_login']);
Route::get('/rater/list',[AuthController::class, 'get_all_raters']);

Route::get('/rater/name', [AuthController::class, 'get_rater_usernames']); // public

// Route::get('/rater/assign/', [AuthController::class, 'get_all_raters']);
