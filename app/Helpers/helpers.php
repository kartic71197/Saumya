<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('activeOrgId')) {
    function activeOrgId()
    {
        // If logged in
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        // Superadmin viewing a specific org
        if ($user->role_id == 1 && session()->has('active_organization_id')) {
            return session('active_organization_id');
        }

        // Normal user → their own org
        return $user->organization_id;
    }
}
