<?php

namespace App\Livewire\Organization\Products;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{
    Location,
    Mycatalog,
    Organization,
    Product
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bulk Add To Inventory Component
 *
 * Purpose:
 * This component handles adding multiple products to inventory
 * across one or more locations in a single action.
 *
 * How this helps:
 * - Saves time when managing large product catalogs
 * - Avoids repetitive single-product inventory actions
 * - Ensures consistent inventory setup across locations
 *
 * User flow:
 * 1. User selects multiple products from the master catalog
 * 2. Opens the bulk inventory modal
 * 3. Selects one or more locations
 * 4. System adds only missing inventory entries
 *    (existing ones are safely skipped)
 *
 * This keeps inventory operations fast, safe, and predictable.
 */


class BulkAddToInventoryComponent extends Component
{
    /** @var array<int> */
    public array $productIds = [];

    /** @var bool */
    public bool $selectAll = false;

    /** @var array<int,bool> */
    public array $selectedLocations = [];

    /** @var \Illuminate\Support\Collection */
    public $locations;

    public Organization $organization;

    /**
     * Mount
     */
    public function mount(): void
    {
        $this->organization = Organization::findOrFail(auth()->user()->organization_id);

        $this->locations = Location::where('is_active', true)
            ->where('org_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Opens the bulk inventory modal for selected products
     *
     * What it does:
     * - Validates user permission to manage inventory
     * - Normalizes selected product IDs
     * - Opens the location selection modal
     * This ensures only authorized users
     * can perform bulk inventory updates.
     */

    #[On('callBulkAddToMyCatalogModal')]
    public function openBulkAddToMyCatalogModal(array $productIds): void
    {
        $user = auth()->user();

        if (!$user->role?->hasPermission('manage_mycatalog') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to manage inventory.', 'error');
            return;
        }
        Log::debug('Bulk modal opened with product IDs', ['raw' => $productIds]);

        //  FLATTEN product IDs 
        $this->productIds = collect($productIds)
            ->flatten()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        Log::debug('Normalized product IDs', [
            'productIds' => $this->productIds,
            'count' => count($this->productIds),
        ]);

        if (empty($this->productIds)) {
            $this->dispatch('show-notification', 'No products selected.', 'error');
            return;
        }

        //  RESET selections on modal open
        $this->selectedLocations = [];
        $this->selectAll = false;

        $this->dispatch('open-modal', 'bulk-mycatalog-product-modal');
    }


    /**
     * Handles selecting or unselecting a location
     *
     * Used inside the bulk modal to allow users
     * to control where selected products will be added.
     */

    public function toggleLocation(int $locationId): void
    {
        if (isset($this->selectedLocations[$locationId])) {
            unset($this->selectedLocations[$locationId]);
        } else {
            $this->selectedLocations[$locationId] = true;
        }
    }

    /**
     * Toggle select all locations
     */
    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            // Select all locations
            $this->selectedLocations = [];
            foreach ($this->locations as $location) {
                $this->selectedLocations[$location->id] = true;
            }
        } else {
            // Deselect all locations
            $this->selectedLocations = [];
        }
    }

    /**
     * Executes bulk inventory add operation
     *
     * How it works:
     * - Loops through selected products and locations
     * - Adds inventory only if it does not already exist
     * - Skips existing entries to avoid duplicates
     *
     * Result:
     * - Safe bulk insert
     * - No overwrites
     * - Clear success and skip feedback to the user
     */

    public function updateBulkMyCatalogLocations(): void
    {
        $user = auth()->user();
        try {
            DB::beginTransaction();

            $addedCount = 0;
            $skippedCount = 0;
            $productIds = $this->productIds;
            $locationIds = array_keys($this->selectedLocations);

            Log::debug('Bulk add started', [
                'product_count' => count($productIds),
                'location_count' => count($locationIds)
            ]);

            if (empty($locationIds)) {
                $this->dispatch('show-notification', 'Please select at least one location.', 'error');
                return;
            }

            foreach ($productIds as $productId) {
                foreach ($locationIds as $locationId) {
                    // Check if entry already exists
                    $exists = Mycatalog::where('product_id', $productId)
                        ->where('location_id', $locationId)
                        ->exists();

                    if (!$exists) {
                        // ADD ONLY - Create new entry
                        Mycatalog::create([
                            'product_id' => $productId,
                            'location_id' => $locationId,
                            'total_quantity' => 0,
                            'alert_quantity' => 0, // default alert qty to be 0
                            'par_quantity' => 3, // default par qty to be 3
                            'created_by' => $user->id,
                        ]);
                        $addedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }

            DB::commit();

            // Show success message with stats
            if ($addedCount > 0 && $skippedCount > 0) {
                $message = "Successfully added products to selected locations. Added {$addedCount} new entries. Skipped {$skippedCount} existing entries.";
            } elseif ($addedCount > 0) {
                $message = "Successfully added all products to selected locations.";
            } elseif ($skippedCount > 0) {
                $message = "All selected products are already in the selected locations.";
            } else {
                $message = "No products were added.";
            }


            $this->dispatch('show-notification', $message, 'success');
            $this->dispatch('close-modal', 'bulk-mycatalog-product-modal');
            $this->dispatch('bulk-add-success');
            $this->dispatch('mycatalog-updated');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Bulk MyCatalog error: ' . $e->getMessage());

            $this->dispatch(
                'show-notification',
                'Failed to add products to locations. Please try again.',
                'error'
            );
        }
    }

    public function render()
    {
        return view('livewire.organization.products.bulk-add-to-inventory-component');
    }
}