<?php

use App\Http\Controllers\Api\PlantillaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\RatersController;
use App\Http\Controllers\Api\ViewActiveController;
use App\Http\Controllers\DesignationQSController;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OnCriteriaJobController;
use App\Http\Controllers\OnFundedPlantillaController;
use App\Http\Controllers\RatersBatchController;
use App\Http\Controllers\StructureDetailController;
use App\Http\Controllers\xPDSController;
use App\Models\User;

//Notice: when route with middleware requires bearer token else will return error 401
//Notice: when route with apiResource use CRUD => POST-Create , PUT-Update , DELETE-Delete , GET-Read

Route::get('/raters', [RatersController::class, 'index']);
// for CRUD api Raters Batch
Route::apiResource('/raters_batch', RatersBatchController::class);

//plantilla and funded plantilla
Route::get('/plantilla', [PlantillaController::class, 'index']);
Route::get('/plantillaData', [PlantillaController::class, 'vwActiveGet']);
Route::post('/plantillaData/qs', [DesignationQSController::class, 'getDesignation']);
Route::middleware('api')->group(function () {
    Route::apiResource('/plantilla/funded', OnFundedPlantillaController::class);
});

//update plantilla in PageNo and Funded Separately
// Route to update only the Funded status
Route::post('/structure-details/update-funded', [StructureDetailController::class, 'updateFunded']);
Route::get('/on-funded-plantilla/by-funded/{positionID}/{itemNO}', [OnFundedPlantillaController::class, 'showByFunded']);
// Route to update only the PageNo
Route::post('/structure-details/update-pageno', [StructureDetailController::class, 'updatePageNo']);

//get PDS
Route::post('/xPDS', [xPDSController::class, 'getPersonalDataSheet']);

//active employee
Route::post('/vw-Active/status', [ViewActiveController::class, 'getStatus']);
Route::get('/vw-Active/Sex', [ViewActiveController::class, 'getSexCount']);

Route::get('/vw-Active', [ViewActiveController::class, 'getActiveCount']);
Route::get('/vw-Active/count', [ViewActiveController::class, 'allCountStatus']);
//check user
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Token expired or invalid'
        ], 401);
    }

    // Reload user with rspControl relationship
    $user = User::with('rspControl')
        ->select('id', 'name', 'username', 'position', 'active')
        ->find($user->id);

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    $userData = $user->toArray();

    $permissions = $user->rspControl ? [
        'isFunded' => $user->rspControl->isFunded,
        'isUserM' => $user->rspControl->isUserM,
        'isRaterM' => $user->rspControl->isRaterM,
        'isCriteria' => $user->rspControl->isCriteria,
        'isDashboardStat' => $user->rspControl->isDashboardStat,
    ] : [
        'isFunded' => false,
        'isUserM' => false,
        'isRaterM' => false,
        'isCriteria' => false,
        'isDashboardStat' => false,
    ];

    $userData['permissions'] = $permissions;

    unset($userData['rsp_control']); // Clean up if you don’t want the nested relationship

    return response()->json([
        'status' => true,
        'message' => 'Authenticated user retrieved successfully',
        'data' => $userData
    ]);
});
//login and register
Route::post('/registration', [AuthController::class, 'Token_Register']);
Route::post('/login', [AuthController::class, 'Token_Login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'Token_Logout']);

    // User management routes
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/users/{id}', [AuthController::class, 'getUserById']);
    Route::put('/users/{id}', [AuthController::class, 'updateUser']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
});

//logs
Route::middleware('auth:sanctum')->post('/logs/auth', [LogController::class, 'logAuth']);
Route::get('/logs', [LogController::class, 'index']);


//Job Post RSP
Route::apiResource('job-batches-rsp', JobBatchesRspController::class);
Route::get('/job-batches-rsp/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);

Route::apiResource('on-criteria-job', OnCriteriaJobController::class);
Route::get('/on-criteria-job/{PositionID}/{ItemNo}', [OnCriteriaJobController::class, 'show']);
