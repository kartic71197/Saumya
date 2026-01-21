<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role;
        if (auth()->user()->role_id <= 2 || $role?->hasPermission('view_cart')) {
            return view('organization.cart.index');
        }

    }
    public function addMultipleFromPO(Request $request)
{
    Log::info('Add multiple cart request: ' . $request);

    $products = $request->input('products', []);

    if (empty($products)) {
        return response()->json(['success' => false, 'message' => 'No products provided']);
    }

    try {
        foreach ($products as $item) {
            Cart::create([
                'product_id'     => $item['product_id'],
                'organization_id'=> $item['organization_id'],
                'location_id'    => $item['location_id'],
                'added_by'       => $item['added_by'],
                'quantity'       => $item['quantity'],
                'price'          => $item['price'],
                'unit_id'        => $item['unit_id'],
            ]);

            Log::info('Added to cart: ', $item);
        }

        return response()->json(['success' => true, 'message' => 'Products added to the cart successfully']);
    } catch (\Exception $e) {
        Log::error('Add to cart failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to add products to cart']);
    }
}
public function checkExisting(Request $request)
{
    $products = collect($request->input('products', []));

    if ($products->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No products provided'
        ]);
    }

    $productIds = $products->pluck('product_id');

    // Find existing cart items
    $existing = Cart::with('product') 
        ->whereIn('product_id', $productIds)
        ->where('organization_id', $products->first()['organization_id'] ?? null)
        ->where('location_id', $products->first()['location_id'] ?? null)
        ->get();

    return response()->json([
        'success' => true,
        'existing_ids'   => $existing->pluck('product_id'),
        'existing_names' => $existing->pluck('product.product_name') 
    ]);
}


}