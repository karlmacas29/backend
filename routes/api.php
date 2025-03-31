<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureTokenIsValid;

Route::post('/registration', [AuthController::class, 'Token_Register']);
Route::post('/login', [AuthController::class, 'Token_Login']);

Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'Token_Logout']);
    Route::get('/employees', [EmployeeController::class, 'index']);
});
