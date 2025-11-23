<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantSubmissionController;
use App\Http\Controllers\EmailController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/applicant/read', [ApplicantSubmissionController::class, 'read_excel']);

Route::get('/email', [EmailController::class, 'sendEmail']);
Route::view('/interview', 'mail-template.interview');
Route::view('/qualified', 'mail-template.qualified');
Route::view('/unqualified', 'mail-template.unqualified');
