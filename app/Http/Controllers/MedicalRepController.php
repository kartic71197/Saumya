<?php

namespace App\Http\Controllers;


use App\Models\MedrepOrgAccess;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalRepController extends Controller
{
    public function myRequests()
    {
        $userId = auth()->id();
        $orgId = auth()->user()->organization_id;

        $requests = MedrepOrgAccess::with('medicalRepresentative')
            ->where('org_id', $orgId)
            ->latest()
            ->get();

        return view('organization.medrep.request', compact('requests'));
    }

    public function approveRequest($id)
    {
        $request = MedrepOrgAccess::findOrFail($id);
        $request->update([
            'is_approved' => true,
            'is_rejected' => false,
        ]);

        return back()->with('success', 'Request approved.');
    }

    public function rejectRequest($id)
    {
        $request = MedrepOrgAccess::findOrFail($id);
        $request->update([
            'is_approved' => false,
            'is_rejected' => true,
        ]);

        return back()->with('success', 'Request rejected.');
    }
}