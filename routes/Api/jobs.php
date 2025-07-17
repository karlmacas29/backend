<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\OnCriteriaJobController;

Route::apiResource('job-batches-rsp', JobBatchesRspController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('/job-batches-rsp/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);

Route::apiResource('on-criteria-job', OnCriteriaJobController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('/on-criteria-job/{PositionID}/{ItemNo}', [OnCriteriaJobController::class, 'show']);





