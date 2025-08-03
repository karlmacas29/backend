<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;



Route::get('dashboard', [DashboardController::class, 'index']);

Route::get('job/status', [DashboardController::class, 'job_post_status']);


