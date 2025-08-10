<?php

use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', [UsersController::class, 'getAuthenticatedUser']);
});

// Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
//     Route::get('/user', [UsersController::class, 'getAuthenticatedUser']);
// });
