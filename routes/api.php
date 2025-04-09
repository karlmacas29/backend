<?php

use App\Http\Controllers\Api\PlantillaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\RatersController;
use App\Http\Controllers\Api\ViewActiveController;

Route::get('/raters', [RatersController::class, 'index']);

Route::get('/plantilla', [PlantillaController::class, 'index']);
Route::get('/plantillaData', [PlantillaController::class, 'vwActiveGet']);

Route::get('/vw-Active', [ViewActiveController::class, 'getActiveCount']);

//check user
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json([
            'message' => 'Token expired or invalid'
        ], 401);
    }
    return $user;
});

Route::post('/registration', [AuthController::class, 'Token_Register']);
Route::post('/login', [AuthController::class, 'Token_Login']);

Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'Token_Logout']);
});
