<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\xPDSController;

Route::post('/xPDS', [xPDSController::class, 'getPersonalDataSheet']);
