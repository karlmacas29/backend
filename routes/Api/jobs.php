<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\OnCriteriaJobController;
use App\Http\Controllers\SubmissionController;

// Route::apiResource('job-batches-rsp', JobBatchesRspController::class)->only(['index', 'store' ,'destroy',]);
Route::get('/job-batches-rsp', [JobBatchesRspController::class, 'index']);
Route::post('/job-batches-rsp', [JobBatchesRspController::class, 'store']);
Route::delete('/job-batches-rsp/{id}', [JobBatchesRspController::class, 'destroy']);

Route::get('/job-batches-rsp/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);

// Route::apiResource('on-criteria-job', OnCriteriaJobController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('/on-criteria-job', [OnCriteriaJobController::class, 'index']);
Route::post('/on-criteria-job', [OnCriteriaJobController::class, 'store']);
Route::post('/on-criteria-job/{id}', [OnCriteriaJobController::class, 'update']);
Route::delete('/on-criteria-job/{id}', [OnCriteriaJobController::class, 'destroy']);

Route::get('/on-criteria-job/{PositionID}/{ItemNo}', [OnCriteriaJobController::class, 'show']);

// Route::get('/job-batches-rsp', [JobBatchesRspController::class, 'index']);

Route::post('/job-batches-rsp/applicant/view/{id}', [JobBatchesRspController::class, 'getApplicants']);
Route::post('/job-batches-rsp/get/view/', [JobBatchesRspController::class, 'get_submission_table']);

// Route::get('/job-batches-rsp/office', [JobBatchesRspController::class, 'office']);

// Route::middleware(['auth:sanctum', 'role:1', 'prefix' => ])->group(function () {
//     // Admin-only routes

// });


Route::get('/job-batches-rsp/list', [JobBatchesRspController::class, 'job_list']); // fetching the all job post on the admin

Route::delete('/job/delete/{id}', [JobBatchesRspController::class, 'destroy']); // delete job post  with the criteria and pdf

Route::get('/job-batches-rsp/applicant/view/{id}', [JobBatchesRspController::class, 'get_applicant']);


Route::post('/job-post/{job_batches_id}/', [JobBatchesRspController::class, 'update']);

Route::get('/job-post', [JobBatchesRspController::class, 'job_post']); // fetching all job post

// Route::post('/job-post/store', [JobBatchesRspController::class, 'store']);

Route::post('/job-batches-rsp/applicant/evaluation/{applicantId}', [SubmissionController::class, 'evaluation']);
