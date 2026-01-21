<?php

namespace App\Livewire\Organization\Products;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{
    AlertParTacking,
    Brand,
    Cart,
    Category,
    Subcategory,
    Location,
    Mycatalog,
    Organization,
    Product,
    ProductUnit,
    StockCount,
    Supplier,
    Unit
};
use App\Services\Audit\ProductAuditService;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use DB;

/**
 * Class AddToInventoryComponent
 *
 * Handles adding or removing products from MyCatalog for selected locations,
 * with rules preventing removal when inventory exists.
 */
class AddToInventoryComponent extends Component
{
    /** @var int|null Product ID being managed */
    public ?int $productId = null;

    /** @var bool Modal visibility */
    public bool $showModal = false;

    /** @var bool Select all toggle */
    public bool $selectAll = false;

    /**
     * Selected locations for MyCatalog
     * @var array<int,bool>
     */
    public array $selectedLocations = [];

    /**
     * Locations that contain inventory (used in warnings/popups)
     * @var array<int, array{name:string, quantity:int}>
     */
    public array $locationsWithInventory = [];

    /** @var mixed */
    public $location;

    /** @var \App\Models\Organization */
    public Organization $organization;

    /** @var \Illuminate\Support\Collection<\App\Models\Location> */
    public $locations;

    /** @var Product|null */
    public ?Product $product = null;

    public $selectedProductId;
    public $selectedProductName;


    /**
     * Initialize component and load organization + locations
     */
    public function mount(): void
    {
        $this->organization = Organization::where('id', auth()->user()->organization_id)->firstOrFail();

        $this->product = new Product();
        $this->locations = Location::where('is_active', true)
            ->where('org_id', auth()->user()->organization_id)
            ->get();
    }

    /**
     * Open modal for managing MyCatalog assignment for a product.
     *
     * @param int $productId
     * @return void
     */
    #[On('callAddToMyCatalogModal')]
    public function openAddToMyCatalogModal(int $productId): void
    {
        $user = auth()->user();

        // Permission check
        if (!$user->role?->hasPermission('manage_mycatalog') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to manage catalog!', 'error');
            return;
        }

        $product = Product::find($productId);

        if (!$product) {
            $this->dispatch('show-notification', 'Product not found.', 'error');
            return;
        }

        $this->selectedProductId = $productId;
        $this->selectedProductName = $product->product_name;

        // Fetch active locations
        $activeLocations = Location::query()
            ->where('is_active', true)
            ->where('org_id', $user->organization_id)
            ->orderBy('name', 'asc')
            ->get();

        // Fetch MyCatalog entries
        $mycatalogEntries = Mycatalog::where('product_id', $productId)
            ->whereIn('location_id', $activeLocations->pluck('id'))
            ->get()
            ->keyBy('location_id');

        // Attach metadata to location objects
        $this->locations = $activeLocations->map(function ($location) use ($mycatalogEntries) {
            $entry = $mycatalogEntries->get($location->id);
            $location->in_mycatalog = $entry !== null;
            $location->on_hand_quantity = $entry?->total_quantity ?? 0;
            return $location;
        });

        // Prepare selections
        $this->selectedLocations = [];
        foreach ($this->locations as $location) {
            if ($location->in_mycatalog) {
                $this->selectedLocations[$location->id] = true;
            }
        }

        $this->updateSelectAllState();
        $this->locationsWithInventory = [];

        Log::info('Opening MyCatalog modal');
        $this->dispatch('open-modal', 'mycatalog-product-modal');
    }

    /**
     * Toggle a single location in selection.
     *
     * @param int $locationId
     * @return void
     */
    public function toggleLocation(int $locationId): void
    {
        $location = $this->locations->firstWhere('id', $locationId);

        if (!$location) return;

        // Attempting to uncheck
        if (!empty($this->selectedLocations[$locationId])) {
            if ($location->on_hand_quantity > 0) {
                $this->selectedLocations[$locationId] = true;

                // $this->locationsWithInventory = [[
                //     'name' => $location->name,
                //     'quantity' => $location->on_hand_quantity
                // ]];

                $this->dispatch('open-modal', 'mycatalog-inventory-warning');
                return;
            }

            unset($this->selectedLocations[$locationId]);
        }
        // Turn ON if OFF
        else {
            $this->selectedLocations[$locationId] = true;
        }
    }

    /**
     * Handle select-all checkbox logic.
     *
     * @param bool $value
     * @return void
     */
    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            foreach ($this->locations as $location) {
                $this->selectedLocations[$location->id] = true;
            }
            return;
        }

        // Trying to unselect all
        $inventoryIssues = [];

        foreach ($this->locations as $location) {
            if ($location->on_hand_quantity > 0) {
                $inventoryIssues[] = [
                    'name' => $location->name,
                    'quantity' => $location->on_hand_quantity
                ];
                $this->selectedLocations[$location->id] = true;
            } else {
                unset($this->selectedLocations[$location->id]);
            }
        }

        if ($inventoryIssues) {
            $this->locationsWithInventory = $inventoryIssues;
            $this->dispatch('open-modal', 'mycatalog-inventory-warning');
            $this->selectAll = true;
        }
    }

    /**
     * Update select-all checkbox based on individual selections.
     *
     * @return void
     */
    private function updateSelectAllState(): void
    {
        $total = $this->locations->count();
        $selected = count($this->selectedLocations);

        $this->selectAll = ($total > 0 && $total === $selected);
    }

    /**
     * Save MyCatalog changes: add/remove product from selected locations.
     *
     * @return void
     */
    public function updateMyCatalogLocations(): void
    {
        $user = auth()->user();

        try {
            DB::beginTransaction();

            $locationsToAdd = [];
            $locationsToRemove = [];
            $locationsWithInventoryToRemove = [];

            foreach ($this->locations as $location) {
                $locId = $location->id;
                $isSelected = $this->selectedLocations[$locId] ?? false;

                $existing = Mycatalog::where('location_id', $locId)
                    ->where('product_id', $this->selectedProductId)
                    ->first();

                $hasInventory = $location->on_hand_quantity > 0;

                // Add if selected and not existing
                if ($isSelected && !$existing) {
                    $locationsToAdd[] = $locId;
                }
                // Remove if unselected and existing
                elseif (!$isSelected && $existing) {
                    if ($hasInventory) {
                        $locationsWithInventoryToRemove[] = [
                            'name'     => $location->name,
                            'quantity' => $location->on_hand_quantity
                        ];
                    } else {
                        $locationsToRemove[] = $locId;
                    }
                }
            }

            // Block removal if inventory exists
            if ($locationsWithInventoryToRemove) {
                $this->locationsWithInventory = $locationsWithInventoryToRemove;
                DB::rollBack();
                $this->dispatch('open-modal', 'mycatalog-inventory-warning');
                return;
            }

            // Add entries
            foreach ($locationsToAdd as $locId) {
                Mycatalog::updateOrCreate(
                    ['product_id' => $this->selectedProductId, 'location_id' => $locId],
                    [
                        'total_quantity' => 0,
                        // Initialize inventory thresholds when product is added to a location
                        // alert_quantity = 0 → no alert until stock drops
                        // par_quantity   = 3 → default minimum preferred stock
                        'alert_quantity' => 0,
                        'par_quantity' => 3,
                        'created_by' => $user->id
                    ]
                );
            }

            // Remove entries
            if ($locationsToRemove) {
                Mycatalog::where('product_id', $this->selectedProductId)
                    ->whereIn('location_id', $locationsToRemove)
                    ->delete();
            }

            DB::commit();

            // Success message
            $added = count($locationsToAdd);
            $removed = count($locationsToRemove);

            if ($added && $removed) {
                $msg = "Added {$added} and removed {$removed} location(s) from catalog.";
            } elseif ($added) {
                $msg = "Added {$added} location(s) to catalog.";
            } elseif ($removed) {
                $msg = "Removed {$removed} location(s) from catalog.";
            } else {
                $msg = "No changes were made.";
            }

            $this->dispatch('show-notification', $msg, 'success');
            $this->dispatch('close-modal', 'mycatalog-product-modal');
            $this->dispatch('mycatalog-updated');
        }

        catch (\Throwable $e) {
            DB::rollBack();
            Log::error("MyCatalog update error: ".$e->getMessage());
            $this->dispatch('show-notification', 'An error occurred while updating catalog.', 'error');
        }
    }

    /**
     * Render Livewire component view.
     */
    public function render()
    {
        return view('livewire.organization.products.add-to-inventory-component');
    }
}
