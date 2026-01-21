<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FieldRepController extends Controller
{
    /**
     * Shows the Field Reps page.
     * Only Super Admin (role_id = 1) is allowed to access this module.
     */
    public function index()
    {
        // Restrict access to Super Admin users only
        if (!auth()->check() || auth()->user()->role_id != '1') {
            return redirect()->back()->with('error', 'You are not authorized to access this module.');
        }

        return view('admin.field-reps.index');
    }
}
