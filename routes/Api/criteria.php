<?php

use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;




// Admin-only routes
// Route::middleware(['auth:sanctum', 'prefix' => 'criteria'])->group(function () {

//     Route::post('/store', [CriteriaController::class, 'store']);
//     Route::delete('/{id}', [CriteriaController::class, 'delete']);
//     Route::delete('/{criteria_id}', [CriteriaController::class, 'delete']);
//     Route::get('/{job_batches_rsp_id}', [CriteriaController::class, 'show']);
//     Route::get('/view/{job_batches_rsp_id}', [CriteriaController::class, 'view_criteria']);

// });
Route::prefix('criteria')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/store', [CriteriaController::class, 'store']);
    Route::delete('/{id}', [CriteriaController::class, 'delete']);
    Route::delete('/{criteria_id}', [CriteriaController::class, 'delete']);
    Route::get('/{job_batches_rsp_id}', [CriteriaController::class, 'show']);
    Route::get('/view/{job_batches_rsp_id}', [CriteriaController::class, 'view_criteria']);
});
