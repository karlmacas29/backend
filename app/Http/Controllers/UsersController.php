<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{

    // this is for admin user
    public function getAuthenticatedUser(Request $request)
    {
        // Get the authenticated user with relationships in a single query
        $user = $request->user()->load(['rspControl:id,user_id,isFunded,isUserM,isRaterM,isCriteria,isDashboardStat']);

        if (!$user) {
            return response()->json(['message' => 'Token expired or invalid'], 401);
        }

        // Select only needed fields
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'position' => $user->position,
            'active' => $user->active,
            'permissions' => [
                'isFunded' => optional($user->rspControl)->isFunded ?? false,
                'isUserM' => optional($user->rspControl)->isUserM ?? false,
                'isRaterM' => optional($user->rspControl)->isRaterM ?? false,
                'isCriteria' => optional($user->rspControl)->isCriteria ?? false,
                'isDashboardStat' => optional($user->rspControl)->isDashboardStat ?? false,
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Authenticated user retrieved successfully',
            'data' => $userData
        ]);
    }

    // this is for rater
    public function getAuthenticatedrater(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Token expired or invalid'], 401);
        }

        // Load only the required relationships
        $user->load([
            'job_batches_rsp:id,Office,Position' // Adjust fields as needed
        ]);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'position' => $user->position,
            'active' => $user->active,
            'rspControl' => $user->rspControl,
            // 'assigned_jobs' => $user->job_batches_rsp,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Authenticated rater retrieved successfully',
            'data' => $userData
        ]);
    }
}
