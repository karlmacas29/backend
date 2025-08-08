<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;



 // Admin-only routes

Route::prefix('dashboard')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('job/status', [DashboardController::class, 'job_post_status']);
});
