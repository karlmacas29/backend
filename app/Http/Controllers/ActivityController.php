<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    //

    public function index()
    {
        $activities = Activity::where('causer_id', auth()->id())
            ->where('causer_type', 'App\\Models\\User')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
}
