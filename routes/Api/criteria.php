<?php

use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;



// get
Route::post('/store/criteria',[CriteriaController::class,'store_criteria']);

//post
Route::get('/view/criteria/{job_batches_rsp_id}', [CriteriaController::class, 'view_criteria']);

//delete
Route::delete('/criteria/{id}', [CriteriaController::class, 'delete']);
