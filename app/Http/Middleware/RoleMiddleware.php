<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('/login'); // Redirect to login if not authenticated
        }

        $user = Auth::user();

        // Check if the user's role is in the allowed roles
        if (!in_array($user->role, $roles)) {
            // You can redirect them, show an error, or abort
            abort(403, 'Unauthorized action.'); // Forbidden access
        }


        return $next($request);
    }
}
