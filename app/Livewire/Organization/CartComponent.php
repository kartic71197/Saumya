<?php

namespace App\Livewire\Organization;

use App\Mail\PurchaseOrderMail;
use App\Models\BillToLocation;
use App\Models\Cart;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\ShipToLocation;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Log;
use \Mysqli;
use phpseclib3\Net\SFTP;
use App\Services\Stripe\StripeInvoiceService;


class CartComponent extends Component
{
    public $cartItems = [];

    public $organization_id = null;

    public $user = null;
    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    public $units = [];

    public $billingLocations = [];
    public $selectedBillingLocation = null;
    public $shippingLocations = [];
    public $selectedShippingLocation = null;

    public $locations = [];
    public $selectedLocation = null;

    public $notifications = [];

    public $selectedLocationName = null;

    public $added_by = '';
    public $unit_id = [];
    public $quantity;
    public $showPoSuffixModal = false;
    public $generatedPurchaseOrders = [];
    public $selectedPOs = [];
    public $selectedCartItems = [];


    public $default_shipping_location = null;

    protected $listeners = ['load-cart' => 'loadCart'];

    public function mount()
    {
        $this->user = auth()->user();
        $this->organization_id = $this->user->organization_id;
        $this->billingLocations = Location::where('org_id', $this->organization_id)->where('is_active', true)->get();
        $this->shippingLocations = Location::where('org_id', $this->organization_id)->where('is_active', true)->get();
        $this->loadLocations();
        $this->selectedBillingLocation = Location::where('org_id', $this->organization_id)
            ->where('is_default', true)->where('is_active', true)->first();
        $this->default_shipping_location = Location::where('org_id', $this->organization_id)
            ->where('is_default_shipping', true)->where('is_active', true)->first();

    }

    public function loadLocations()
    {
        $user = auth()->user();
        $role = $user->role;
        if ($role?->hasPermission('all_location_cart') || $user->role_id <= 2) {
            $this->locations = Location::where('org_id', $user->organization_id)->where('is_active', true)->get();
            $this->selectedLocation = $user->location_id ?? $this->locations->first()->id;
            $this->selectedLocationName = $this->locations->firstWhere('id', $this->selectedLocation)->name ?? null;
        } else {
            $this->locations = Location::where('id', $user->location_id)->where('is_active', true)->get();
            $this->selectedLocation = $user->location_id ?? $this->locations->first()->id;
            $this->selectedLocationName = $this->locations->firstWhere('id', $this->selectedLocation)->name ?? null;
        }
        $this->loadCart();
    }

    public function updateLocation()
    {
        if ($this->default_shipping_location == null) {
            $this->selectedShippingLocation = $this->selectedLocation;
        }

        $this->selectedLocationName = $this->locations->firstWhere('id', $this->selectedLocation)->name ?? null;
        $this->loadCart();
    }

    public function loadCart()
    {
        $query = Cart::with(['product.supplier', 'product.units.unit'])
            ->where('organization_id', auth()->user()->organization_id);

        // Add location filter if a location is selected
        if ($this->selectedLocation) {
            $query->where('location_id', $this->selectedLocation);
        }

        $this->cartItems = $query->get()
            ->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'unit_id' => $cartItem->unit_id,
                    'product' => [
                        'id' => $cartItem->product->id,
                        'name' => $cartItem->product->product_name,
                        'code' => $cartItem->product->product_code,
                        'base_price' => $cartItem->product->product_price,
                        'image' => $cartItem->product->image,
                        'supplier' => [
                            'id' => $cartItem->product->supplier->id,
                            'name' => $cartItem->product->supplier->supplier_name
                        ],
                        'units' => $cartItem->product->units->map(function ($unit) {
                            return [
                                'unit_id' => $unit->unit_id,
                                'unit_name' => $unit->unit->unit_name,
                                'unit_code' => $unit->unit->unit_code,
                                'is_base_unit' => $unit->is_base_unit,
                                'operator' => $unit->operator,
                                'conversion_factor' => $unit->conversion_factor
                            ];
                        })->values()->all()
                    ]
                ];
            })->values()->all();

        foreach ($this->cartItems as $item) {
            $this->unit_id[$item['id']] = $item['unit_id'];
        }
        $this->calculateTotals();
    }

    public function savePurchaseOrdersWithSuffixes()
    {
        $user = auth()->user();
        $role = $user->role;

        $hasApproveAllPermission = $role?->hasPermission('approve_all_cart') || $user->role_id <= 2;
        $hasApproveOwnPermission = $role?->hasPermission('approve_own_cart') && $user->location_id == $this->selectedLocation;

        if (!$hasApproveAllPermission && !$hasApproveOwnPermission) {
            $this->addNotification('You do not have permission to create a purchase order.', 'danger');
            return;
        }

        if (!$this->selectedShippingLocation || $this->selectedShippingLocation == '0') {
            $this->addNotification('Shipping location is not provided.', 'danger');
            return;
        }

        if (!$this->selectedBillingLocation || $this->selectedBillingLocation == '0') {
            $this->addNotification('Billing location is not provided. Contact support!', 'danger');
            return;
        }

        if (empty($this->generatedPurchaseOrders)) {
            $this->addNotification('No purchase orders to save.', 'danger');
            return;
        }

        foreach ($this->generatedPurchaseOrders as $po) {
            if (empty($po['supplier_id']))
                continue;

            $supplier = Supplier::find($po['supplier_id']);
            if (!$supplier || !$supplier->is_active) {
                $this->addNotification("Supplier with ID {$po['supplier_id']} not found or inactive.", 'danger');
                continue;
            }

            $cartItems = Cart::whereIn('id', $po['cart_ids'])->get();
            if ($cartItems->isEmpty())
                continue;

            $billToLocation = $this->selectedBillingLocation;
            $shipToLocation = Location::find($this->selectedShippingLocation);

            $billToLocationSupplier = BillToLocation::where('location_id', $billToLocation->id)
                ->where('supplier_id', $supplier->id)
                ->first();

            $shipToLocationSupplier = ShipToLocation::where('location_id', $shipToLocation->id)
                ->where('supplier_id', $supplier->id)
                ->first();

            if (!$billToLocationSupplier) {
                $this->addNotification("Billing info missing for {$supplier->supplier_name}.", 'danger');
                continue;
            }
            if (!$shipToLocationSupplier) {
                $this->addNotification("Shipping info missing for {$supplier->supplier_name}.", 'danger');
                continue;
            }

            try {
                DB::beginTransaction();

                // === Generate final PO number with suffix ===
                $suffix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $po['suffix'] ?? ''));
                $poNumber = $po['auto_number'] . (!empty($suffix) ? "-{$suffix}" : '');

                // === Create Purchase Order ===
                $purchaseOrder = PurchaseOrder::create([
                    'purchase_order_number' => $poNumber,
                    'supplier_id' => $supplier->id,
                    'organization_id' => $this->organization_id,
                    'location_id' => $this->selectedLocation,
                    'bill_to_location_id' => $billToLocation->id,
                    'ship_to_location_id' => $shipToLocation->id,
                    'created_by' => $this->user->id,
                    'updated_by' => $this->user->id,
                    'status' => 'ordered',
                    'total' => $cartItems->sum('price'),
                    'note' => 'Waiting for Supplier\'s confirmation',
                    'bill_to_number' => $billToLocationSupplier?->bill_to,
                    'ship_to_number' => $shipToLocationSupplier?->ship_to,
                ]);

                $auditService = app(\App\Services\PurchaseOrderAuditService::class);

                foreach ($cartItems as $cart) {
                    PurchaseOrderDetail::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $cart->product->id,
                        'quantity' => $cart->quantity,
                        'sub_total' => $cart->price,
                        'unit_id' => $cart->unit_id,
                    ]);

                    $auditService->logPurchaseOrderCreation($purchaseOrder, [
                        'product_id' => $cart->product->id,
                        'quantity' => $cart->quantity,
                        'sub_total' => $cart->price,
                        'unit_id' => $cart->unit_id,
                    ]);
                }

                if ($purchaseOrder->purchaseSupplier->supplier_slug == 'solm_email') {
                    // Create Stripe Invoice
                    $stripeInvoiceService = app(StripeInvoiceService::class);
                    $invoice = $stripeInvoiceService->createInvoice($purchaseOrder);
                }

                // === Clear supplier’s cart items ===
                Cart::whereIn('id', $po['cart_ids'])->delete();

                // === Email/Status Logic ===
                if (strtolower(auth()->user()->organization->plan->name) === 'free trial') {

                    $this->addNotification("Free trial: POs not sent to suppliers.", 'danger');
                    $purchaseOrder->note = "Current plan does not support EDI/EMAIL integration with {$supplier->supplier_name}";
                    $purchaseOrder->is_order_placed = true;

                } else {

                    // Determine next scheduled send time
                    $nextSendTime = $this->getNextOrderSendTime();

                    if ($supplier->supplier_slug == 'henryschien') {
                        // Henry Schein also uses scheduled sending
                        //changed message and removed time
                        $purchaseOrder->note = "Order will be placed to the supplier soon.";
                    } elseif ($supplier->supplier_email) {
                        $purchaseOrder->note = "Order will be placed to the supplier soon.";
                    }
                }


                $purchaseOrder->save();
                DB::commit();

                $this->addNotification("Purchase Order {$poNumber} saved for {$supplier->supplier_name}.", 'success');

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("PO Save Failed: {$e->getMessage()}");
                $this->addNotification("Failed to save PO for {$supplier->supplier_name}.", 'danger');
            }
        }

        //  Close modal and redirect when done
        $this->generatedPurchaseOrders = [];
        $this->dispatch('close-modal', 'po_suffix_modal');

        $remainingCarts = Cart::where('organization_id', $this->organization_id)
            ->where('location_id', $this->selectedLocation)
            ->count();

        if ($remainingCarts === 0) {
            return redirect('/purchase');
        }
    }

    private function getNextOrderSendTime(): string
    {
        $now = now(); // current time

        $first = now()->setTime(12, 0); // 12 PM
        $second = now()->setTime(17, 0); // 5 PM

        if ($now->lt($first)) {
            return '12 PM';
        } elseif ($now->lt($second)) {
            return '5 PM';
        }

        // After 5 PM → next day 12 PM
        return '12 PM tomorrow';
    }



    public function sanitizeSuffix($index)
    {
        if (!isset($this->generatedPurchaseOrders[$index])) {
            return;
        }
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->generatedPurchaseOrders[$index]['suffix'] ?? ''));

        $this->generatedPurchaseOrders[$index]['suffix'] = $clean;
    }

    public function cancelPoSuffixModal()
    {
        $this->generatedPurchaseOrders = [];
        $this->dispatch('close-modal', 'po_suffix_modal');
        $this->showPoSuffixModal = false;
    }




    public function updateUnitPrice($cartItemId, $productId, $unitId)
    {
        $cartItem = Cart::find($cartItemId);
        $productUnits = ProductUnit::where('product_id', $productId)->get();
        // Find the current unit and the target unit
        $targetUnit = $productUnits->firstWhere('unit_id', $unitId);
        if (!$targetUnit) {
            return;
        }
        // Get base price
        $basePrice = $cartItem->product->cost;
        $selectedUnitPrice = $targetUnit->operator === 'multiply'
            ? $basePrice * $targetUnit->conversion_factor
            : $basePrice / $targetUnit->conversion_factor;
        // Update cart item with new unit and price
        $cartItem->update([
            'unit_id' => $unitId,
            'price' => $selectedUnitPrice * $cartItem->quantity
        ]);
        $this->loadCart();
    }

    public function updateQuantity($cartItemId, $quantity)
    {
        if ($quantity > 0) {
            $cartItem = Cart::find($cartItemId);

            if ($cartItem) {
                $oldQuantity = $cartItem->quantity;
                $oldPrice = $cartItem->price;

                // Calculate new price using (old price / old quantity) * new quantity
                $newPrice = ($oldPrice / $oldQuantity) * $quantity;

                $cartItem->update([
                    'quantity' => $quantity,
                    'price' => $newPrice
                ]);

                $this->loadCart();
            }
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->cartItems)->sum(function ($item) {
            return $item['price'];
        });

        $this->tax = 0;
        $this->total = $this->subtotal + $this->tax;
    }

    // public function removeItem($cartItemId)
    // {
    //     $event = 'Removed';
    //     $message = 'Following Product is removed from cart';
    //     $auditService = app(\App\Services\InventoryAuditService::class);
    //     $auditService->logCartChanges(
    //         $cartItemId,
    //         $event,
    //         $message
    //     );

    //     Cart::find($cartItemId)->delete();

    //     $this->loadCart();
    // }

    public function removeItem($cartItemId)
    {
        if (!$cartItemId) {
            return;
        }

        $cartItem = Cart::find($cartItemId);

        if (!$cartItem) {
            return;
        }

        $event = 'Removed';
        $message = 'Following Product is removed from cart';

        $auditService = app(\App\Services\InventoryAuditService::class);
        $auditService->logCartChanges(
            $cartItemId,
            $event,
            $message
        );

        $cartItem->delete();
        $this->loadCart();
    }

    public function addNotification($message, $type = 'success')
    {
        // Prepend new notifications to the top of the array
        array_unshift($this->notifications, [
            'id' => uniqid(),
            'message' => $message,
            'type' => $type,
        ]);
        $this->notifications = array_slice($this->notifications, 0, 5);
    }

    public function removeNotification($id)
    {
        $this->notifications = array_values(
            array_filter($this->notifications, function ($notification) use ($id) {
                return $notification['id'] !== $id;
            }),
        );
    }
    public function createPurchaseOrder()
    {
        logger('=== createPurchaseOrder called ===');

        if ($this->default_shipping_location != null) {
            $this->selectedShippingLocation = $this->default_shipping_location->id;
            logger('Default shipping location set: ' . $this->selectedShippingLocation);
        }

        $user = auth()->user();
        $role = $user->role;

        $hasApproveAllPermission = $role?->hasPermission('approve_all_cart') || $user->role_id <= 2;
        $hasApproveOwnPermission = $role?->hasPermission('approve_own_cart') && $user->location_id == $this->selectedLocation;

        logger('Permission check:', [
            'hasApproveAll' => $hasApproveAllPermission,
            'hasApproveOwn' => $hasApproveOwnPermission,
        ]);

        if (!$hasApproveAllPermission && !$hasApproveOwnPermission) {
            $this->addNotification('You do not have permission to create a purchase order.', 'danger');
            logger('Permission denied.');
            return;
        }

        if (!$this->selectedShippingLocation || $this->selectedShippingLocation == '0') {
            $this->addNotification('Shipping location is not provided.', 'danger');
            logger('No shipping location.');
            return;
        }

        if (!$this->selectedBillingLocation || $this->selectedBillingLocation == '0') {
            $this->addNotification('Billing location is not provided. Contact support!', 'danger');
            logger('No billing location.');
            return;
        }

        $carts = Cart::where('organization_id', $this->organization_id)
            ->where('location_id', $this->selectedLocation)
            ->get();

        logger('Cart count: ' . $carts->count());

        if ($carts->isEmpty()) {
            $this->addNotification('Cart is empty. Cannot create a purchase order.', 'danger');
            logger('Cart empty, aborting.');
            return;
        }

        $groupedBySupplier = $carts->groupBy(fn($cart) => $cart->product->product_supplier_id ?? null);
        $this->generatedPurchaseOrders = [];

        // Get last PO number from DB and continue numbering
        $latest = PurchaseOrder::orderBy('id', 'desc')->first();
        $lastNumber = 0;

        if ($latest && preg_match('/PO-\d{4}-(\d+)/', $latest->purchase_order_number, $matches)) {
            $lastNumber = intval($matches[1]);
        }

        // Generate new POs sequentially for each supplier
        foreach ($groupedBySupplier as $supplierId => $cartItems) {
            if (!$supplierId) {
                logger('Cart item without supplier ID found.');
                continue;
            }

            $supplier = Supplier::where('id', $supplierId)->where('is_active', true)->first();
            if (!$supplier) {
                $this->addNotification("Supplier with ID {$supplierId} is either inactive or not found.", 'danger');
                continue;
            }

            $billToLocation = $this->selectedBillingLocation;
            $billToLocationSupplier = BillToLocation::where('location_id', $billToLocation->id)
                ->where('supplier_id', $supplier->id)
                ->first();

            if (!$billToLocationSupplier) {
                $this->addNotification("Billing information is missing for {$supplier->supplier_name}. Contact Support!", 'danger');
                continue;
            }

            $shipToLocation = Location::find($this->selectedShippingLocation);
            $shipToLocationSupplier = ShipToLocation::where('location_id', $shipToLocation->id)
                ->where('supplier_id', $supplier->id)
                ->first();

            if (!$shipToLocationSupplier) {
                $this->addNotification("Shipping information is missing for supplier {$supplier->supplier_name}.", 'danger');
                continue;
            }

            //  Increment safely and generate next number
            $lastNumber++;
            $poNumber = 'PO-' . now()->year . '-' . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);

            // Double-check DB for duplicates just in case (concurrent runs)
            while (PurchaseOrder::where('purchase_order_number', $poNumber)->exists()) {
                $lastNumber++;
                $poNumber = 'PO-' . now()->year . '-' . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);
            }

            logger("Generated PO number {$poNumber} for supplier {$supplier->supplier_name}");

            $this->generatedPurchaseOrders[] = [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->supplier_name,
                'auto_number' => $poNumber,
                'cart_ids' => $cartItems->pluck('id')->toArray(),
                'suffix' => '',
            ];
        }

        logger('Generated POs:', $this->generatedPurchaseOrders);

        if (empty($this->generatedPurchaseOrders)) {
            $this->addNotification('No valid suppliers found for creating purchase orders.', 'danger');
            logger('No valid POs generated.');
            return;
        }

        //  Show suffix modal after generating numbers
        $this->dispatch('open-modal', 'po_suffix_modal');
    }

    public function render()
    {
        return view('livewire.organization.cart.cart-component');
    }
}