<?php

namespace App\Livewire;

use App\Models\Mycatalog;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ProductSupplier;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Models\Location;
use App\Models\ProductUnit;
use App\Models\Cart;
use App\Models\StockCount;
use App\Models\Picking;
use App\Models\PickingDetailsModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ProductDetailsComponent extends Component
{
    public $showModal = false;
    public $product = null;
    public $latestPurchaseOrders = [];
    public $productId = null;
    public $selectedLocationId = null;
    public $product_name = '';
    public $organization_id = '';
    public $location_name = '';
    public $product_cost = 0;
    public $added_by = '';
    public $unit_id = '';
    public $total = 0;
    public $context = '';
    public $par_quantity = null;

    public $quantity = '';
    public $units = [];

    public $locations = [];
    public $highlightProductId = null;

    public $batchDetails = [];
    public $isLoadingBatchDetails = false;

    public $latestPickups = [];

    public $msg = '';


    #[On('openProductDetailBrowser')]
    public function openProductDetailBrowser($id, $context = null, $location_id = null, $par_quantity = null)
    {

        logger($location_id);
        $this->msg = '';
        $this->productId = $id;
        $this->selectedLocationId = $location_id;
        $this->context = $context;
        $this->isLoadingBatchDetails = true;
        $this->loadProductDetails();
        $this->loadLatestPurchaseOrders();

        if ($this->context === 'top_pickups') {
            $this->loadLatestPickups();
        }
        $this->showModal = true;

        $this->par_quantity = $par_quantity;
        $this->selectedLocationId = $location_id;
        $this->context = $context;


        //     logger("Modal opened with:", [
        //     'productId' => $this->productId,
        //     'context' => $this->context,
        //     'selectedLocationId' => $this->selectedLocationId,
        //     'showModal' => $this->showModal
        // ]);



    }

    public function removeFromInventory()
    {
        if (!$this->selectedLocationId) {
            $this->dispatch('show-notification', 'Please Select a location first.', 'error');
            $this->msg = 'Please Select a location first.';
            return;
        }
        if (!$this->productId) {
            $this->dispatch('show-notification', 'Product not found.', 'error');
            return;
        }

        $catalog = Mycatalog::where('product_id', $this->productId)
            ->where('location_id', $this->selectedLocationId)
            ->first();
        if ($catalog->total_quantity > 0) {
            $this->dispatch('show-notification', 'Product with available quantity can not be removed.', 'error');
            $this->msg = 'Product with available quantity can not be removed.';
            return;
        }

        $catalog->delete();
        $this->dispatch('show-notification', 'Product removed from Inventory successfully.', 'error');
        $this->showModal = false;
    }


    public function loadLatestPickups()
    {
        $latestPickups = PickingDetailsModel::select(
            'pickings.picking_number',
            'pickings.created_at as date',
            'locations.name as location',
            'picking_details.picking_quantity as quantity',
            'picking_details.picking_unit as unit'
        )
            ->join('pickings', 'picking_details.picking_id', '=', 'pickings.id')
            ->join('locations', 'pickings.location_id', '=', 'locations.id')
            ->where('picking_details.product_id', $this->productId)
            ->orderByDesc('pickings.created_at')
            ->limit(5)
            ->get()
            ->map(function ($pickup) {
                if (!empty($pickup->date)) {
                    $pickup->date = Carbon::parse($pickup->date)->format('d M Y');
                } else {
                    $pickup->date = 'N/A'; // or any default text
                }
                return $pickup;
            });

        $this->latestPickups = $latestPickups;
    }


    public function loadProductDetails()
    {
        try {
            $this->product = Product::with([
                'supplier',
                'categories',
                'brand',
            ])->find($this->productId);

            if ($this->context === 'inventory' && $this->product) {
                $this->loadBatchDetails();
            }
        } catch (\Exception $e) {
            logger()->error("Error loading product details: " . $e->getMessage());
        }
    }

    // Load batch details if in inventory context
    private function loadBatchDetails()
    {
        // If no location is selected, skip everything
        if (!$this->selectedLocationId) {
            $this->batchDetails = [];
            $this->isLoadingBatchDetails = false;
            return;
        }

        try {
            $this->isLoadingBatchDetails = true;

            $this->batchDetails = StockCount::where('product_id', $this->productId)
                ->when($this->selectedLocationId, function ($query) {
                    $query->where('location_id', $this->selectedLocationId);
                })
                ->with('location', 'product')
                ->get()
                ->map(function ($item) {
                    $expiryDate = $item->expiry_date;
                    $formattedDate = 'N/A';
                    $status = 'N/A';

                    if ($expiryDate) {
                        try {
                            $carbonDate = is_string($expiryDate)
                                ? \Carbon\Carbon::parse($expiryDate)
                                : $expiryDate;

                            $formattedDate = $carbonDate->format('Y-m-d');
                            $status = $this->getExpiryStatus($carbonDate);
                        } catch (\Exception $e) {
                            logger()->error("Error parsing expiry date: " . $e->getMessage());
                        }
                    }

                    // Always return consistent keys
                    return [
                        'batch_number' => $item->batch_number ?? 'N/A',
                        'expiry_date' => $formattedDate,
                        'quantity' => $item->on_hand_quantity ?? 0,
                        'status' => $status
                    ];
                })
                ->toArray();

        } catch (\Exception $e) {
            logger()->error("Error loading batch details: " . $e->getMessage());
            $this->batchDetails = [];
        } finally {
            $this->isLoadingBatchDetails = false;
        }
    }

    private function getExpiryStatus($expiryDate)
    {
        if (!$expiryDate)
            return 'N/A';

        $today = now();

        if ($expiryDate->isPast()) {
            return 'Expired';
        }

        $daysUntilExpiry = $expiryDate->diffInDays($today);

        if ($daysUntilExpiry <= 7)
            return 'Critical';
        if ($daysUntilExpiry <= 30)
            return 'Expiring Soon';
        return 'Good';
    }


    public function loadLatestPurchaseOrders()
    {
        if (!$this->product)
            return;

        // Get latest 5 purchase orders that include this product
        $this->latestPurchaseOrders = PurchaseOrder::whereHas('purchasedProducts', function ($query) {
            $query->where('product_id', $this->productId);
        })
            ->orderBy('created_at', 'desc')
            ->with([
                'createdUser',
                'purchasedProducts' => function ($query) {
                    $query->where('product_id', $this->productId)
                        ->with('unit'); // Include unit relation
                }
            ])
            ->limit(5)
            ->get()
            ->map(function ($po) {
                $poDetail = $po->purchasedProducts->first();

                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number ?? 'PO-' . str_pad($po->id, 6, '0', STR_PAD_LEFT),
                    'status' => $po->status,
                    'total_amount' => $po->total_amount,
                    'order_date' => $po->created_at->format('Y-m-d'),
                    'created_by' => $po->createdUser->name ?? 'N/A',
                    'ordered_quantity' => $poDetail?->quantity ?? 0,
                    'ordered_unit' => $poDetail?->unit?->unit_name ?? 'N/A',
                    'status_badge_class' => $this->getStatusBadgeClass($po->status)
                ];
            });
    }


    private function getStatusBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
            'ordered' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'received' => 'bg-green-100 text-green-800 border-green-200',
            'cancelled' => 'bg-red-100 text-red-800 border-red-200',
            'partial' => 'bg-orange-100 text-orange-800 border-orange-200',
            default => 'bg-green-100 text-green-800 border-green-200'
        };
    }
    public function incrementQuantity()
    {

        $this->quantity++;
        $this->updateFinalPrice();

    }
    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
            $this->updateFinalPrice();
        }
    }

    public function updateFinalPrice()
    {
        if (!$this->unit_id || !$this->quantity) {
            $this->total = 0;
            return;
        }

        $productUnit = ProductUnit::where('product_id', $this->productId)
            ->where('unit_id', $this->unit_id)
            ->first();

        if (!$productUnit) {
            $this->total = 0;
            return;
        }

        $conversionFactor = $productUnit->conversion_factor ?? 1;

        // Simplified conversion logic
        $basePrice = $productUnit->operator === 'multiply'
            ? $this->product_cost / $conversionFactor
            : $this->product_cost * $conversionFactor;

        $this->total = $basePrice * $this->quantity;
    }
    public function addToCart()
    {
        $user = auth()->user();
        $role = $user->role;

        logger()->info('--- addToCart Called ---', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'selectedLocationId' => $this->selectedLocationId,
            'productId' => $this->productId,
            'quantity' => $this->quantity,
            'unit_id' => $this->unit_id,
        ]);

        if (!$role?->hasPermission('add_to_cart') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to Add/Remove Products from cart!', 'error');
            return;
        }

        $location = Location::find($this->selectedLocationId);
        $this->location_name = $location ? $location->name : 'Unknown Location';

        if (!$this->selectedLocationId) {
            if (auth()->user()->role_id == '2') {
                $this->dispatch('show-notification', 'Assign a location to yourself in the Users Section.', 'error');
            }
            if (auth()->user()->role_id == '3') {
                $this->dispatch('show-notification', 'Contact your Admin to assign a location.', 'error');
            }
            return;
        }

        if ($this->product) {
            $this->product_name = $this->product->product_name;
            $this->product_cost = $this->product->cost;
        } else {
            $this->dispatch('show-notification', 'Product not found.', 'error');
            return;
        }

        $this->added_by = auth()->id();


        $this->units = ProductUnit::with('unit')
            ->where('product_id', $this->productId)
            ->get()
            ->map(function ($productUnit) {
                return [
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit->unit_name,
                    'is_base_unit' => $productUnit->is_base_unit,
                    'operator' => $productUnit->operator,
                    'conversion_factor' => $productUnit->conversion_factor,
                ];
            });
        $baseUnit = $this->units->firstWhere('is_base_unit', true);

        if ($baseUnit) {
            $this->unit_id = $baseUnit['unit_id'];
            $this->quantity = $this->par_quantity;
            $this->updateFinalPrice();
        }

        $this->dispatch('open-modal', 'add-product-to-cart');
    }
    public function addProductToCart()
    {
        // Ensure all required data is available
        if (!$this->productId || !$this->unit_id || !$this->quantity || !$this->total || !$this->selectedLocationId) {
            $this->dispatch('show-notification', 'Missing required information to add the product to the cart.', 'error');
            return;
        }
        $cart = Cart::create([
            'product_id' => $this->productId,
            'organization_id' => auth()->user()->organization_id,
            'location_id' => $this->selectedLocationId,
            'added_by' => auth()->user()->id,
            'quantity' => $this->quantity,
            'price' => $this->total,
            'unit_id' => $this->unit_id,
        ]);
        $event = 'Added';
        $message = 'Following Product is added to cart';
        $auditService = app(\App\Services\InventoryAuditService::class);
        $auditService->logCartChanges(
            $cart->id,
            $event,
            $message
        );

        $this->dispatch('cartUpdated')->to('CartIcon');
        $this->dispatch('pg:eventRefresh-inventory-list-aftzfa-table');
        $this->dispatch('close-modal', 'add-product-to-cart');
        $this->dispatch('show-notification', 'Product added to the cart successfully.', 'success');
    }
    public function closeModal()
    {
        $this->showModal = false;
        $this->showCart = false;
        $this->product = null;
        $this->latestPurchaseOrders = [];
        $this->productId = null;
    }
    public function canAddToCart()
    {
        if (!$this->productId) {
            return false;
        }

        $exists = Cart::where('product_id', $this->productId)
            ->where('organization_id', auth()->user()->organization_id)
            ->where('location_id', $this->selectedLocationId)
            ->exists();

        return !$exists;
    }

    public function render()
    {
        return view('livewire.product-details-component');
    }
}