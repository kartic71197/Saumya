<?php

namespace App\Livewire\Organization\Inventory;
use App\Models\Cart;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockCount;
use App\Models\Unit;
use Livewire\Attributes\On;
use Illuminate\Http\Request;

use Livewire\Component;

class InventoryComponent extends Component
{


    public $product_name = '';
    public $product_id = '';
    public $organization_id = '';
    public $location_id = '';
    public $location_name = '';
    public $product_cost = 0;
    public $added_by = '';
    public $unit_id = '';
    public $total = 0;

    public $quantity = '';
    public $units = [];

    public $locations = [];
    public $selectedLocation = null;
    public $highlightProductId = null;

    public $showSampleProducts = false;
    public $showEmptyProducts = true;

    public $stockCount;
    public $location;

    public $alert_quantity;
    public $par_quantity;

    protected $queryString = ['selectedLocation'];

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
    public function updatedShowSampleProducts()
    {
        $this->dispatch('showSamples', $this->showSampleProducts);
    }
    public function updatedShowEmptyProducts()
    {
        $this->dispatch('showEmptyProductsChanged', $this->showEmptyProducts);
    }

    public function updatedSelectedLocation()
    {
        $this->dispatch('inventoryIocationChanged', $this->selectedLocation);
    }

    public function mount(Request $request)
    {
        $this->locations = Location::where('org_id', auth()->user()->organization_id)->where('is_active', true)->orderBy('name')->get();
        $this->selectedLocation = auth()->user()->location_id ?? null;

        $this->highlightProductId = $request->query('product_id');
    }

    public function openAlertParModal($stockId)
    {
        // ISSUE: Earlier we used a wrong/unassigned variable ($id vs $stockId)
        // and sometimes relied on $selectedLocation from the table. 
        // This caused the modal to always show the location of the table filter 
        // (e.g., Lucknow) instead of the actual stock location (e.g., Kanpur).
        //
        // FIX: Fetch the stock record directly by its ID with 'location' relationship,
        // ignoring table filters. Now the modal always shows the correct product location.
        // Fetch the Mycatalog row with its location, ignoring table filters
        $this->stockCount = Mycatalog::with('location', 'product')->findOrFail($stockId);

        $this->alert_quantity = $this->stockCount->alert_quantity;
        $this->par_quantity = $this->stockCount->par_quantity;

        // Set location properly for modal display
        $this->location = $this->stockCount->location;

        $this->dispatch('open-modal', 'update-alert-par-modal');
    }


    public function updateAlertPar()
    {
        $this->validate([
            'alert_quantity' => 'required|numeric|min:0',
            'par_quantity' => 'required|numeric|min:0',
        ]);

        try {
            $updatedRecords = Mycatalog::where('location_id', $this->stockCount->location_id)
                ->where('product_id', $this->stockCount->product_id)
                ->update([
                    'alert_quantity' => $this->alert_quantity,
                    'par_quantity' => $this->par_quantity,
                ]);


            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Alert and Par quantities updated successfully!'
            ]);

            $this->dispatch('close-modal', 'update-alert-par-modal');

            $this->reset(['stockCount', 'location', 'alert_quantity', 'par_quantity']);

            $this->dispatch('pg:eventRefresh-inventory-list-aftzfa-table');

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update: ' . $e->getMessage()
            ]);
        }
    }

    public function updateFinalPrice()
    {
        if (!$this->unit_id || !$this->quantity) {
            $this->total = 0;
            return;
        }

        $productUnit = ProductUnit::where('product_id', $this->product_id)
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

    #[On('cartIconClick')]
    public function cartIconClick($rowId)
    {
        $user = auth()->user();
        $role = $user->role;
        if (!$role?->hasPermission('add_to_cart') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to Add/Remove Products from cart!', 'error');
            return;
        }
        $inv = Mycatalog::where('id', $rowId)->first();

        $existingCartItem = Cart::where('product_id', $inv->product_id)
            ->where('location_id', $inv->location_id)
            ->first();

        if ($existingCartItem) {
            $auditService = app(\App\Services\InventoryAuditService::class);
            $event = 'Removed';
            $message = 'Following Product is removed from cart';
            $auditService->logCartChanges(
                $existingCartItem?->id,
                $event,
                $message
            );
            $existingCartItem->delete();
            $this->dispatch('cartUpdated')->to('CartIcon');
            $this->dispatch('pg:eventRefresh-inventory-list-aftzfa-table');
            return;
        }

        $this->location_name = $inv->location->name;
        $location = $inv->location_id;

        if ($location == null) {
            if (auth()->user()->role_id == '2') {
                $this->dispatch('show-notification', 'Assign a location to yourself in the Users Section.', 'error');
            }
            if (auth()->user()->role_id == '3') {
                $this->dispatch('show-notification', 'Contact your Admin to assign a location.', 'error');
            }
            return;
        }

        $this->product_id = $inv->product_id;
        $this->location_id = $location;
        $product = Mycatalog::find(id: $rowId);

        if (!$product) {
            $this->dispatch('show-notification', 'Product not found.', 'error');
            return;
        }
        $catalogData = Mycatalog::where('product_id', $this->product_id)->first();

        $this->product_name = $product->product->product_name;
        $this->product_cost = $product->product->cost;
        $this->added_by = auth()->user()->id;

        $this->units = ProductUnit::with('unit')
            ->where('product_id', $this->product_id)
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
            $this->quantity = $inv->par_quantity;
            $this->updateFinalPrice();
        }

        $this->dispatch('open-modal', 'add-product-to-cart');
    }

    public function addProductToCart()
    {
        // Ensure all required data is available
        if (!$this->product_id || !$this->unit_id || !$this->quantity || !$this->total || !$this->location_id) {
            $this->dispatch('show-notification', 'Missing required information to add the product to the cart.', 'error');
            return;
        }
        $cart = Cart::create([
            'product_id' => $this->product_id,
            'organization_id' => auth()->user()->organization_id,
            'location_id' => $this->location_id,
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

    public function render()
    {
        return view('livewire.organization.inventory.inventory-component', [
            'highlightProductId' => $this->highlightProductId,
            'selectedLocation' => $this->selectedLocation,
        ]);
    }
}