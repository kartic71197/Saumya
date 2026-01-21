<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    public function create()
    {
        if (auth()->user()->organization_id) {
            return view('dashboard');
        }
        return view('organization.create');
    }
    public function index()
    {
        $user = auth()->user();
        if ($user->role_id != '1') {
            return redirect('/dashboard')->with('error', 'You are not authorized to access this page');
        }
        // $org = Organization::where('id', $user->organization_id)->first();
        // return view('admin.organizations.index', compact('org'));
        return view('admin.organizations.index', [
            'is_rep_org' => 0
        ]);
    }

    public function medrepIndex()
    {
        $user = auth()->user();

        if ($user->role_id != '1') {
            return redirect('/dashboard')->with('error', 'You are not authorized to access this page');
        }

        return view('admin.organizations.index', [
            'is_rep_org' => 1
        ]);
    }
    
    public function get_locations($organizationId)
    {
        $locations = Location::where('org_id', $organizationId)
        ->orderBy('name')->get();
        return response()->json($locations);
    }

}
