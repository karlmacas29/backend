<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ViewActiveController;

Route::post('/vw-Active/status', [ViewActiveController::class, 'getStatus']);
Route::get('/vw-Active', [ViewActiveController::class, 'getActiveCount']);
Route::get('/vw-Active/Sex', [ViewActiveController::class, 'getSexCount']);
Route::get('/vw-Active/count', [ViewActiveController::class, 'allCountStatus']);
Route::get('/vw-Active/all', [ViewActiveController::class, 'fetch_all_employee']);
