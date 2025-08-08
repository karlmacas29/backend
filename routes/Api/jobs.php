<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\OnCriteriaJobController;
use App\Http\Controllers\SubmissionController;






Route::prefix('job-batches-rsp')->middleware(['auth:sanctum'])->group(function () {

    Route::get('/index', [JobBatchesRspController::class, 'index']);
    Route::post('/store', [JobBatchesRspController::class, 'store']);
    Route::delete('/job-batches-rsp/{id}', [JobBatchesRspController::class, 'destroy']);
    Route::get('/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);
    Route::post('applicant/view/{id}', [JobBatchesRspController::class, 'getApplicants']);
    Route::post('/get/view/', [JobBatchesRspController::class, 'get_submission_table']);
    Route::get('/list', [JobBatchesRspController::class, 'job_list']);
    Route::delete('/delete/{id}', [JobBatchesRspController::class, 'destroy']);
    Route::get('/applicant/view/{id}', [JobBatchesRspController::class, 'get_applicant']);
    Route::post('/{job_batches_id}', [JobBatchesRspController::class, 'update']);
    Route::get('/job-post', [JobBatchesRspController::class, 'job_post']);
    Route::post('/applicant/evaluation/{applicantId}', [SubmissionController::class, 'evaluation']);
});







Route::prefix('criteria')->middleware(['auth:sanctum'])->group(function () {

    Route::get('/index', [OnCriteriaJobController::class, 'index']);
    Route::post('/store', [OnCriteriaJobController::class, 'store']);
    Route::post('/{id}', [OnCriteriaJobController::class, 'update']);
    Route::delete('/{id}', [OnCriteriaJobController::class, 'destroy']);
    Route::get('/{PositionID}/{ItemNo}', [OnCriteriaJobController::class, 'show']);
});
