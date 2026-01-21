<?php

namespace App\Http\Controllers\Medrep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Product;

class SamplesController extends Controller
{
    public function index(Request $request)
    {
        $locationId = $request->query('location_id');
        
        $location = Location::with('organization')->find($locationId);
        
        // Get products for the authenticated user's organization that are marked as samples
        $products = Product::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->where('is_sample', true)
            ->with(['baseUnit.unit', 'units.unit'])
            ->get();

        return view('medical_rep.send_samples', compact('location', 'products'));
    }

    public function viewSampleList(Request $request)
    {
        // This method can be expanded to fetch and display the list of samples
        // For now, it simply returns a view
        return view('medical_rep.sample-list.sample_list');
    }
}