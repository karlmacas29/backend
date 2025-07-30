<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\OnCriteriaJobController;
use App\Http\Controllers\SubmissionController;

Route::apiResource('job-batches-rsp', JobBatchesRspController::class)->only(['index', 'store',  'destroy',]);
Route::get('/job-batches-rsp/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);

Route::apiResource('on-criteria-job', OnCriteriaJobController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('/on-criteria-job/{PositionID}/{ItemNo}', [OnCriteriaJobController::class, 'show']);


// Route::get('/job-batches-rsp', [JobBatchesRspController::class, 'index']);

Route::post('/job-batches-rsp/applicant/view/{id}', [JobBatchesRspController::class, 'getApplicants']);
Route::post('/job-batches-rsp/get/view/', [JobBatchesRspController::class, 'get_submission_table']);

// Route::get('/job-batches-rsp/office', [JobBatchesRspController::class, 'office']);


Route::get('/job-batches-rsp/list', [JobBatchesRspController::class, 'job_list']);

Route::delete('/job/delete/{id}', [JobBatchesRspController::class, 'destroy']);


Route::get('/job-batches-rsp/applicant/view/{id}', [JobBatchesRspController::class, 'get_applicant']);


Route::post('/job-batches-rsp/applicant/evaluation/{applicant_id}', [SubmissionController::class, 'update_status']);

Route::post('/job-post/{job_batches_id}/', [JobBatchesRspController::class, 'update']);
