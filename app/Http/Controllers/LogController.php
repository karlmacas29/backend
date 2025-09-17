<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::orderBy('date_performed', 'desc')->get();
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
