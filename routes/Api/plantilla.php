<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlantillaController;
use App\Http\Controllers\OnFundedPlantillaController;
use App\Http\Controllers\DesignationQSController;
use App\Http\Controllers\StructureDetailController;

Route::get('/plantilla/test', [PlantillaController::class, 'test']);

Route::get('/plantilla/ControlNo', [PlantillaController::class, 'getMaxControlNo']);
Route::get('/plantilla', [PlantillaController::class, 'index']);
Route::get('/plantilla/office/rater', [PlantillaController::class, 'fetch_office_rater']);

Route::get('/office', [PlantillaController::class, 'arrangement']); // this is for the modal fetching  fetching the employye
Route::get('/active', [PlantillaController::class, 'vwActiveGet']);

Route::get('/plantillaData', [PlantillaController::class, 'vwActiveGet']);
Route::post('/plantillaData/qs', [DesignationQSController::class, 'getDesignation']);

Route::get('/on-funded-plantilla/by-funded/{positionID}/{itemNO}', [OnFundedPlantillaController::class, 'showByFunded']);


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/plantilla/funded', OnFundedPlantillaController::class);
    Route::post('/structure-details/update-funded', [StructureDetailController::class, 'updateFunded']);
    Route::post('/structure-details/update-pageno', [StructureDetailController::class, 'updatePageNo']);
});
// Route::delete('/plantilla/delete/{id}', [OnFundedPlantillaController::class, 'destroy']);

Route::delete('/plantilla/delete/all', [OnFundedPlantillaController::class, 'deleteAllPlantillas']);
// Route::get('/plantilla/service/{ControlNo}', [PlantillaController::class, 'getAllData']);


Route::prefix('plantilla')->group(function () {
    Route::get('/appointment/{ControlNo}', [PlantillaController::class, 'getAllData']);
});
