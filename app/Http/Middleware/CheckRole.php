<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        // Accepts one or more roles, e.g. ('admin'), ('rater'), ('admin', 'rater')
        if ($user && in_array($user->role_id, $roles)) {
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized.'], 403);
    }
}
