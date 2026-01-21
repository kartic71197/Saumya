<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function updateCart(Request $request)
    {
        try {
            logger($request->all());
            $validator = Validator::make($request->all(), [
                'location_id' => 'required|exists:locations,id',
                'product_id' => 'required|exists:products,id',
                'added_by' => 'required|exists:users,id',
                'quantity' => 'required|numeric|min:1',
                'unit_id' => 'required|exists:product_units,id',
                'price' => 'required|numeric|min:0',
            ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => $validator->errors(),
            //     ], 422);
            // }

            $cartData = $request->all();

            $cart = Cart::updateOrCreate(
                [
                    'location_id' => $cartData['location_id'],
                    'product_id' => $cartData['product_id'],
                ],
                [
                    'added_by' => $cartData['added_by'],
                    'quantity' => $cartData['quantity'],
                    'price' => $cartData['price'],
                    'unit_id' => $cartData['unit_id'],
                    'organization_id' => auth()->user()->organization_id,
                ]
            );
            return response()->json([
                'message' => 'Cart updated successfully',
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            logger($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong !',
            ], 500);
        }
    }

    public function deleteCartItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cartItemId' => 'required|integer|exists:carts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cartItemId = $request->input('cartItemId');
        Cart::where('id', $cartItemId)->delete();

        return response()->json(['message' => 'Cart item deleted successfully']);
    }

    public function getCart(Request $request)
    {
        try {
            // Validate filters
            $validator = Validator::make($request->all(), [
                'location_id' => 'nullable|integer|exists:locations,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $user = Auth::user();

            // Base query
            $query = Cart::with(['product.supplier', 'product.units.unit'])
                ->where('organization_id', $user->organization_id);

            // Apply optional filters
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            if ($request->filled('supplier_id')) {
                $query->whereHas('product.supplier', function ($q) use ($request) {
                    $q->where('id', $request->supplier_id);
                });
            }

            // Fetch and map data
            $cartItems = $query->get()->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'unit_id' => $cartItem->unit_id,
                    'location_id' => $cartItem->location_id,
                    'product' => [
                        'id' => $cartItem->product->id,
                        'name' => $cartItem->product->product_name,
                        'code' => $cartItem->product->product_code,
                        'base_price' => $cartItem->product->product_price,
                        'image' => $cartItem->product->image,
                        'supplier' => [
                            'id' => $cartItem->product->supplier->id ?? null,
                            'name' => $cartItem->product->supplier->supplier_name ?? null,
                        ],
                        'units' => $cartItem->product->units->map(function ($unit) {
                            return [
                                'unit_id' => $unit->unit_id,
                                'unit_name' => $unit->unit->unit_name,
                                'unit_code' => $unit->unit->unit_code,
                                'is_base_unit' => $unit->is_base_unit,
                                'operator' => $unit->operator,
                                'conversion_factor' => $unit->conversion_factor,
                            ];
                        })->values()->all(),
                    ],
                ];
            })->values()->all();

            return response()->json([
                'success' => true,
                'message' => 'Cart items retrieved successfully',
                'data' => $cartItems,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while loading cart items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
