<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\rater\rater_controller;
use App\Http\Controllers\RaterAuthController;


//Admin Registration -- this is for admin
Route::post('/login', [AuthController::class, 'Token_Login']);
Route::get('/role', [AuthController::class, 'get_role']);

        // Admin-only routes



Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/list', [rater_controller::class, 'get_all_raters']);
    Route::post('/logout', [AuthController::class, 'Token_Logout']);
    Route::put('/users/{id}', [AuthController::class, 'updateUser']);
    Route::get('/users/{id}', [AuthController::class, 'getUserById']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
    Route::post('/edit/{id}', [RaterAuthController::class, 'editRater']);
    Route::post('/registration', [AuthController::class, 'Token_Register']);
    Route::post('/register', [RaterAuthController::class, 'Raters_Register']);
});
