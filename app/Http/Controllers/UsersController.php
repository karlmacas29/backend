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
        $user = $request->user()->load(['rspControl:id,user_id,viewDashboardstat,viewPlantillaAccess,modifyPlantillaAccess,viewJobpostAccess,modifyJobpostAccess,viewActivityLogs,userManagement,viewRater,modifyRater,viewCriteria,modifyCriteria,viewReport']);

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
                'viewDashboardstat' => optional($user->rspControl)->viewDashboardstat ?? false,
                'viewPlantillaAccess' => optional($user->rspControl)->viewPlantillaAccess ?? false,

                'modifyPlantillaAccess' => optional($user->rspControl)->modifyPlantillaAccess ?? false,
                'viewJobpostAccess' => optional($user->rspControl)->viewJobpostAccess ?? false,

                'modifyJobpostAccess' => optional($user->rspControl)->modifyJobpostAccess ?? false,

                'viewActivityLogs' => optional($user->rspControl)->viewActivityLogs ?? false,

                'userManagement' => optional($user->rspControl)->userManagement ?? false,
                'viewRater' => optional($user->rspControl)->viewRater ?? false,

                'modifyRater' => optional($user->rspControl)->modifyRater ?? false,

                'viewCriteria' => optional($user->rspControl)->viewCriteria?? false,

                'modifyCriteria' => optional($user->rspControl)->modifyCriteria ?? false,
                'viewReport' => optional($user->rspControl)->viewReport ?? false,

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

        // Load only the required relationships including pivot status
        $user->load([
            'job_batches_rsp:id,Office,Position'
        ]);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'position' => $user->position,
            'active' => $user->active,
            'rspControl' => $user->rspControl,

            // ⭐ ADDED STATISTICS
            'assigned_jobs_count' => $user->total_assigned,
            'completed_jobs_count' => $user->total_completed,
            'pending_jobs_count' => $user->total_pending,
            'completion_rate' => $user->completion_rate . '%',

            // ⭐ Optional: Include job list assigned to the rater
            'assigned_jobs' => $user->job_batches_rsp->map(function ($job) {
                return [
                    'id' => $job->id,
                    'Office' => $job->Office,
                    'Position' => $job->Position,
                    'status' => $job->pivot->status, // from pivot
                    'assigned_at' => $job->pivot->created_at,
                ];
            }),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Authenticated rater retrieved successfully',
            'data' => $userData
        ]);
    }
}
