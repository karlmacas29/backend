<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantSubmissionController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/applicant/read', [ApplicantSubmissionController::class, 'read_excel']);
