<?php

use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;



// get
Route::post('/store/criteria', [CriteriaController::class, 'store']);

//post
Route::get('/view/criteria/{job_batches_rsp_id}', [CriteriaController::class, 'view_criteria']);




Route::prefix('criteria')->group(function () {
    Route::get('/{job_batches_rsp_id}', [CriteriaController::class, 'show']);
    // Route::post('/store/criteria', [CriteriaController::class, 'store_criteria']);
    Route::delete('/{criteria_id}', [CriteriaController::class, 'delete']);
    Route::delete('/{id}', [CriteriaController::class, 'delete']);

});
