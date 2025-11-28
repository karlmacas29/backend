<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::orderBy('date_performed', 'desc')->get();
        return response()->json($logs, 200);
    }

    public function activityLogs()
    {
        $logs = DB::table('activity_log')
            ->select('id', 'log_name', 'Description', 'properties', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                // Decode properties JSON if it's a valid JSON string
                $props = json_decode($log->properties, true);

                // Add ip and userAgent at top-level if they exist
                $log->ip = $props['ip'] ?? null;
                $log->userAgent = $props['user_agent'] ?? null;

                // Format created_at → "Nov 23, 2025"
                $log->created_at_formatted = \Carbon\Carbon::parse($log->created_at)
                    ->format('F d, Y');

                return $log;
            });

        return response()->json($logs, 200);
    }


    public function logAuth(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        Log::create([
            'user_id' => $user->id,
            'username' => $user->name,
            'actions' => $request->action, // Logged In or Logged Out
            'position' => $user->position,
            'date_performed' => now()->setTimezone('Asia/Manila'),
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip()
        ]);

        return response()->json(['message' => 'Log added successfully'], 201);
    }
}
