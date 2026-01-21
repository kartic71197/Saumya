<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\StockCount;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends Controller
{
    /**
     * Inject the StockService dependency.
     *
     * @param StockService $stockService
     */
    public function __construct(private StockService $stockService)
    {
        // Laravel will automatically resolve and inject the StockService instance.
    }

    /**
     * Create a new sale record and update stock levels accordingly.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Validates request data, checks stock availability,
     * creates a sale record, updates stock quantities,
     * and handles all operations in a database transaction.
     */
    public function create(Request $request)
    {
        /**
         * Step 1: Validate the incoming request data.
         * Ensures that required fields are present and valid.
         */
        $validator = Validator::make($request->all(), [
            'sale_number' => 'required|string|unique:sales,sale_number',
            'product_id' => 'required|integer|exists:products,id',
            'stock_id' => 'nullable|integer|exists:stock_counts,id',
            'quantity' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'location_id' => 'required|integer|exists:locations,id',
        ]);

        // If validation fails, return an error response.
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /**
         * Step 2: Begin a database transaction.
         * Ensures that all database operations succeed or fail together.
         */
        DB::beginTransaction();

        try {
            /**
             * Step 3: Retrieve the related stock entry if stock_id is provided.
             * Used to verify if sufficient quantity is available.
             */
            $stock = $request->stock_id ? StockCount::find($request->stock_id) : null;

            // If stock exists but not enough quantity available, abort.
            if ($stock && $stock->on_hand_quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for this batch.',
                ], Response::HTTP_BAD_REQUEST);
            }

            /**
             * Step 4: Create a new sale record.
             * Links the sale to the current authenticated user.
             */
            $sale = Sale::create([
                'sale_number' => $request->sale_number,
                'product_id' => $request->product_id,
                'stock_id' => $request->stock_id,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'unit' => $request->unit,
                'location_id' => $request->location_id,
                'user_id' => Auth::id(),
            ]);

            /**
             * Step 5: If stock is linked, update its quantity.
             * Uses StockService for centralized inventory logic.
             */
            if ($stock) {
                $newQty = max(0, $stock->on_hand_quantity - $request->quantity);

                $this->stockService->updateStock(
                    $request->product_id,
                    $request->location_id,
                    [
                        'quantity' => $newQty,
                        'unit' => $stock->product->units[0]->unit->id ?? $request->unit,
                        'batch_number' => $stock->batch_number,
                        'expiry_date' => $stock->expiry_date,
                    ]
                );
            }

            /**
             * Step 6: Commit the transaction.
             * Confirms both the sale creation and stock update.
             */
            DB::commit();

            // ✅ Success response with created sale data.
            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully.',
                'data' => $sale,
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            /**
             * Step 7: Rollback the transaction on any failure.
             * Ensures no partial data changes are saved.
             */
            DB::rollBack();

            // Log full error details for debugging in production.
            Log::error('Sale creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a safe and user-friendly error message.
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the sale.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
