<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ViewActiveController;



Route::prefix('vw-Active')->group(function () {

    Route::post('/status', [ViewActiveController::class, 'getStatus']);
    Route::get('/', [ViewActiveController::class, 'getActiveCount']);
    Route::get('/Sex', [ViewActiveController::class, 'getSexCount']);
    Route::get('/count', [ViewActiveController::class, 'allCountStatus']);
    Route::get('/all', [ViewActiveController::class, 'fetch_all_employee']);

});
