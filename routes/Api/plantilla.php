<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlantillaController;
use App\Http\Controllers\OnFundedPlantillaController;
use App\Http\Controllers\DesignationQSController;
use App\Http\Controllers\StructureDetailController;

Route::get('/plantilla', [PlantillaController::class, 'index']);
Route::get('/plantillaData', [PlantillaController::class, 'vwActiveGet']);
Route::post('/plantillaData/qs', [DesignationQSController::class, 'getDesignation']);

Route::get('/on-funded-plantilla/by-funded/{positionID}/{itemNO}', [OnFundedPlantillaController::class, 'showByFunded']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/plantilla/funded', OnFundedPlantillaController::class);
    Route::post('/structure-details/update-funded', [StructureDetailController::class, 'updateFunded']);
    Route::post('/structure-details/update-pageno', [StructureDetailController::class, 'updatePageNo']);
});
