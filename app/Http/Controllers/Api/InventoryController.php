<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatchInventory;
use App\Models\BatchPicking;
use App\Models\Cart;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\PickingDetailsModel;
use App\Models\PickingModel;
use App\Models\Product;
use App\Models\StockCount;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

class InventoryController extends Controller
{

    public function __construct(private \App\Services\StockService $stockService)
    {
        //
        $this->stockService = $stockService;
    }
    public function pickUpdate(Request $request)
    {
        try {
            $data = $request->all();

            $stock_id = $data['stock_id'];
            $qty = $data['qty_to_pick'];

            $organization_id = auth()->user()->organization_id;
            $created_by = auth()->user()->id;
            $stock = StockCount::find($stock_id);

            $pickingNumber = PickingModel::generatePickingNumber();

            $product = Product::with('unit')->where('id', $stock->product_id)->first();
            if (!$product) {
                return response()->json(['success' => false, 'message' => "Product not found"]);
            }

            $isBiological = $product->categories()
                ->where('category_name', 'like', '%biological%')
                ->exists();

            if ($isBiological) {
                return response()->json(['success' => false, 'message' => "Biological products can not be picked from mobile."]);
            }
            $total = $product->cost * $qty;

            $picking = PickingModel::create([
                'picking_number' => $pickingNumber,
                'organization_id' => $organization_id,
                'location_id' => $stock->location_id,
                'user_id' => $created_by,
                'total' => $total,
            ]);
            $unit_name = $product->unit?->first()?->unit?->unit_name;
            $pickingDetails = PickingDetailsModel::create([
                'picking_id' => $picking->id,
                'product_id' => $product->id,
                'picking_quantity' => $qty,
                'picking_unit' => $unit_name,
                'net_unit_price' => $product->cost,
                'sub_total' => $total,
            ]);

            $this->stockService->updateStock(
                $stock->product_id,
                $stock->location_id,
                [
                    'quantity' => $stock->on_hand_quantity - $qty,
                    'unit' => $stock->product->units[0]->unit->id,
                    'batch_number' => $stock->batch_number,
                    'expiry_date' => $stock->expiry_date,
                ]
            );

            // $stock->on_hand_quantity -= $qty;
            // $stock->save();
            return response()->json(['success' => true, 'message' => "Quick pick successful"]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function inventoryStatus(Request $request)
    {
        try {
            // return response()->json(['success' => true, 'message' => auth()->user()]);
            $location_id = $request->input('location_id');
            $per_page = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $searchTerm = $request->input('search_term', '');
            $category = $request->input('category', '');

            Log::info('User: ' . auth()->user());

            $stock = Mycatalog::with(['product.unit.unit', 'product.brand', 'product.supplier', 'product.categories'])
                ->where('mycatalogs.location_id', $location_id)
                ->whereHas('product', function ($query) {
                    $query->where('is_active', 1)
                        ->where('organization_id', auth()->user()->organization_id);
                });

            if (!empty($searchTerm)) {
                $stock->whereHas('product', function ($query) use ($searchTerm) {
                    $query->where('product_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('product_name', 'like', '%' . $searchTerm . '%');
                });
            }
            if (!empty($category)) {
                $stock->whereHas('product', function ($query) use ($category) {
                    $query->where('category_id', '=', $category);
                });
            }

            $paginatedStocks = $stock->paginate($per_page, ['*'], 'page', $page);


            $paginated_data = $paginatedStocks->map(function ($product) use ($location_id) {
                $image = null;

                if (str_starts_with($product->product->image, 'http')) {
                    $image = $product->product->image;
                } else {
                    $arr = json_decode($product->product->image, true);
                    // Ensure it's a non-empty array and the first item is a valid string
                    if (is_array($arr) && !empty($arr) && !empty($arr[0])) {
                        $image = asset('storage/' . $arr[0]);
                    }
                }
                // Log::info('Product ID: ' . $product->product->id);
                // Log::info('Location ID inside map: ' . $location_id);
                $cart_info = Cart::where('product_id', $product->product->id)
                    ->where('location_id', $location_id)
                    ->first();

                Log::info($cart_info);

                return [
                    'id' => $product->id,
                    'imageUrl' => $image,
                    'product_id' => $product->product->id,
                    'product_code' => $product->product->product_code,
                    'product_name' => $product->product->product_name,
                    'brand' => $product->product->brand?->brand_name,
                    'category' => $product->product->category?->category_name,
                    'product_cost' => $product->product->cost,
                    'variant_id' => null,
                    'product_qty' => $product->total_quantity,
                    'unit_id' => $product->product->unit->first()?->unit?->id,
                    'unit' => $product->product->unit->first()?->unit?->unit_name,
                    'alert_qty' => $product->alert_quantity,
                    'par_qty' => $product->par_quantity,
                    'cart_info' => $cart_info
                ];
            });


            return response()->json([
                'success' => true,
                'message' => '',
                'product_data' => $paginated_data,
                'search_term' => $searchTerm,
                'pagination' => [
                    'total' => $paginatedStocks->total(),
                    'per_page' => $paginatedStocks->perPage(),
                    'current_page' => $paginatedStocks->currentPage(),
                    'last_page' => $paginatedStocks->lastPage(),
                    'from' => $paginatedStocks->firstItem(),
                    'to' => $paginatedStocks->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function pickByCode(Request $request)
    {
        try {
            $user = auth()->user();
            // Validate inputs
            $validator = Validator::make($request->all(), [
                'location_id' => 'required|exists:locations,id',
                'code' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            $location_id = $request->input('location_id');
            $code = trim($request->input('code'));

            if ($user->role_id != 2 && $location_id != $user->location_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to access this location.'
                ]);
            }

            // Check location status
            $location = Location::find($location_id);
            if (!$location || !$location->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "Warehouse is not active or doesn't exist"
                ]);
            }

            // Find product
            $product = Product::with(['unit'])->where('product_code', $code)
                ->where('organization_id', auth()->user()->organization_id)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid product code"
                ]);
            }

            $data = StockCount::where('product_id', $product->id)
                ->where('location_id', $location_id)
                ->where('on_hand_quantity', '>', 0)
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "No stock found for {$product->product_name} at {$location->name}"
                ]);
            }
            if (!empty($product->image)) {
                if (str_starts_with($product->image, 'http')) {
                    $product->imageUrl = $product->image;
                } else {
                    $arr = json_decode($product->image, true);
                    if (is_array($arr) && !empty($arr[0])) {
                        $product->imageUrl = asset('storage/' . $arr[0]);
                    }
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Stock found successfully',
                'data' => $data,
                'product' => $product,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in pickByCode: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request'
            ]);
        }
    }

    public function uploadImage(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Validate the image
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB
            ]);

            // Store the image
            $imagePath = $request->file('image')->store('product_images', 'public');
            // $existingImages = json_decode($product->image ?? '[]', true);
            $existingImages = [];
            $existingImages[] = $imagePath;
            $product->image = json_encode($existingImages);
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image_path' => asset('storage/' . $imagePath),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function batchUpdate(Request $request)
    {
        try {
            // Validate inputs
            $validator = Validator::make($request->all(), [
                'product_code' => 'required|string|max:255',
                'location_id' => 'required|exists:locations,id',
                'user_id' => 'required|exists:users,id',
                'batches' => 'required|array|min:1',
                'batches.*.batch_id' => 'required|exists:batch_inventories,id',
                'batches.*.quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            $productCode = $request->input('product_code');
            $locationId = $request->input('location_id');
            $userId = $request->input('user_id');
            $batchesToPick = $request->input('batches');

            // Verify location is active
            $location = Location::find($locationId);
            if (!$location || !$location->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "Warehouse is not active or doesn't exist"
                ]);
            }

            // Verify product exists and belongs to user's organization
            $product = Product::where('product_code', $productCode)
                ->where('organization_id', auth()->user()->organization_id)
                ->where('is_active', 1)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product not found"
                ]);
            }

            // Verify product has expiry date
            if (!$product->has_expiry_date) {
                return response()->json([
                    'success' => false,
                    'message' => "This product doesn't support batch picking"
                ]);
            }

            // Validate batches and check availability
            $validatedBatches = [];
            $totalPickedQty = 0;
            $processedBatches = [];

            foreach ($batchesToPick as $batchData) {
                $batchId = $batchData['batch_id'];
                $requestedQty = $batchData['quantity'];

                // Get batch details
                $batch = BatchInventory::where('id', $batchId)
                    ->where('product_id', $product->id)
                    ->where('location_id', $locationId)
                    ->where('organization_id', auth()->user()->organization_id)
                    ->first();

                if (!$batch) {
                    return response()->json([
                        'success' => false,
                        'message' => "Batch not found or doesn't belong to this product/location"
                    ]);
                }

                // Check if batch has expired
                if (Carbon::parse($batch->expiry_date)->isPast()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot pick from expired batch: {$batch->batch_number}"
                    ]);
                }

                // Check available quantity
                if ($batch->quantity < $requestedQty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient quantity in batch {$batch->batch_number}. Available: {$batch->quantity}, Requested: {$requestedQty}"
                    ]);
                }

                $validatedBatches[] = [
                    'batch' => $batch,
                    'picked_quantity' => $requestedQty
                ];

                $totalPickedQty += $requestedQty;
                $processedBatches[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date,
                    'picked_quantity' => $requestedQty,
                    'remaining_quantity' => $batch->quantity - $requestedQty
                ];
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Update batch inventories
                foreach ($validatedBatches as $validatedBatch) {
                    $batch = $validatedBatch['batch'];
                    $pickedQty = $validatedBatch['picked_quantity'];

                    // Update batch inventory
                    $batch->quantity -= $pickedQty;
                    $batch->save();
                    $pickingNumber = PickingModel::generatePickingNumber();
                    // Create picking transaction record
                    BatchPicking::create([
                        'picking_number' => $pickingNumber,
                        'location_id' => $locationId,
                        'batch_id' => $batch->id,
                        'organization_id' => auth()->user()->organization_id,
                        'user_id' => $userId,
                        'product_id' => $product->id,
                        'picking_quantity' => $pickedQty,
                        'picking_unit' => $product->unit?->first()?->unit?->unit_name,
                        'net_unit_price' => $product->cost,
                        'total_amount' => $product->cost * $pickedQty,
                        'chart_number' => null
                    ]);
                }

                // Update stock count (if you maintain aggregate stock)

                $batch->quantity -= $totalPickedQty;
                $batch->save();


                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Successfully picked {$totalPickedQty} units from " . count($validatedBatches) . " batches",
                    'data' => [
                        'product_name' => $product->product_name,
                        'product_code' => $productCode,
                        'total_picked_quantity' => $totalPickedQty,
                        'batches_processed' => $processedBatches,
                        'location_name' => $location->name
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in batchUpdate: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing batch picking'
            ]);
        }
    }

}
