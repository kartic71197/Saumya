<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
        $user = auth()->user();
        $categories = Category::where('is_active', true)
            ->where('organization_id', $user->organization_id)
            ->select('id', 'category_name') // Only select needed fields
            ->orderBy('category_name', 'asc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}