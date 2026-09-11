<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:manager') or ->middleware('role:supervisor')
     * This checks if the user's role is AT LEAST the specified level.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        if (!auth()->user()->isAtLeast($role)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
