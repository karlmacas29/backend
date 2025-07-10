<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;

Route::get('/logs', [LogController::class, 'index']);
Route::middleware('auth:sanctum')->post('/logs/auth', [LogController::class, 'logAuth']);
