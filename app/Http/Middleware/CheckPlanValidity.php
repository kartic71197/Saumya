<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanValidity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Skip check if not logged in or user is admin
        if (!$user || $user->role_id == 1 || $user->is_medical_rep == 1) {
            return $next($request);
        }
        if ($request->routeIs(['profile.*', 'logout'])) {
            return $next($request);
        }

        $org = $user->organization;

        // No organization or no active plan
        if ($user->organization) {
            if (!$org || !$org->plan_id) {
                return redirect()
                    ->route('pricing')
                    ->with('error', 'You do not have an active subscription.');
            }
        }

        if ($user->organization) {
            if (!$user->organization?->plan_valid || now()->greaterThan($user->organization->plan_valid)) {
                return redirect()->route('pricing')->with('error', 'You dont have valid plan plan.');
            }
        }
        return $next($request);
    }
}
