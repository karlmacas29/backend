<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Token expired or invalid'], 401);
    }

    $user = User::with('rspControl')
        ->select('id', 'name', 'username', 'position', 'active')
        ->find($user->id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
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

    return response()->json([
        'status' => true,
        'message' => 'Authenticated user retrieved successfully',
        'data' => $userData
    ]);
});
