<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RatersController;
use App\Http\Controllers\RatersBatchController;

Route::get('/raters', [RatersController::class, 'index']);
Route::apiResource('/raters_batch', RatersBatchController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/raters_batch', RatersBatchController::class)->except(['index', 'show']);


    
});
