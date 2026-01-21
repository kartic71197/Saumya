<?php

namespace App\Livewire\Organization\Inventory;


use App\Models\CycleCount;
use App\Models\StockCount;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Cycle;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CycleCountComponent extends Component
{
    public $selectedLocation = '';
    public $search = '';
    public $selectedCategory = '';
    public $cycle_code = 'CC01';
    public $cycle_name = '';
    public $currentPage = 1;
    public $perPage = 10;
    public $schedule_date;
    public $current_date;

    public $location_id = '';
    public $description = '';
    public $assignments = [];
    public $subcategoriesList = [];

    // public $existingCycles = [];
    // public $showExistingData = false;
    public $showCreateModal = false;

    public $users = [];




    protected $listeners = [
        'locationChanged' => 'handleLocationChange',
        'searchChanged' => 'handleSearchChange',
        'categoryChanged' => 'handleCategoryChange',
        'pageChanged' => 'handlePageChange'
    ];

    public function mount(Cycle $cycle)
    {
        $this->current_date = now()->toDateString();
        $this->schedule_date = $this->current_date; 
        $user = Auth::user();

        if ((int) $user->role_id !== 2) {
            $this->dispatch(
                'notify',
                message: 'You do not have permission to view this page!',
                type: 'error'
            );
            return redirect()->route('dashboard');
        }

        // $this->cycle = $cycle->load('cycleCounts.product', 'cycleCounts.user');

        $this->users = \App\Models\User::where('organization_id', Auth::user()->organization_id)
            ->where('is_active', true)
            ->where('system_locked', false)
            ->orderBy('name')
            ->get();
        $this->assignments = [
            ['user_id' => null, 'category_id' => null, 'subcategory_id' => null],
        ];

        $this->generateCycleName();
    }

    public function generateCycleName()
    {
        $this->cycle_code = Cycle::generateCycleName();
    }

    // Open create modal
    public function openCreateModal()
    {
        // Only generate new name if modal is being opened fresh (not already open)
        if (!$this->showCreateModal) {
            $this->generateCycleName();
        }
        $this->schedule_date = now()->toDateString();
        $this->showCreateModal = true;
        $this->resetErrorBag();
        $this->resetCycleForm();
    }
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
         $this->resetCycleForm();
         $this->schedule_date = $this->current_date;
        // Don't reset the cycle name - keep it for next time modal opens
    }

   public function addRow()
{
    $this->assignments[] = ['user_id' => null, 'category_id' => null, 'subcategory_id' => null];
    $this->subcategoriesList[count($this->assignments) - 1] = collect();
}

    public function removeRow($index)
    {
        unset($this->assignments[$index]);
        $this->assignments = array_values($this->assignments);
    }

    public function getHasActiveCycleProperty()
    {
        if (!$this->location_id) {
            return false;
        }

        return Cycle::where('location_id', $this->location_id)
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();
    }

// For creating cycles
public function submitCycle()
{
    if ($this->schedule_date && \Carbon\Carbon::parse($this->schedule_date)->lt(now()->startOfDay())) {
            return;
    }
    // Validate basic fields
      $this->validate([
            'location_id' => 'required|exists:locations,id',
            'cycle_code' => 'required|string|max:255',
            'cycle_name' => 'nullable|string|max:255', 
            'schedule_date' => 'required|date',
        ]);

    // Clear ALL previous assignment errors
    $this->resetErrorBag();

    // Check for field-level errors in partially filled rows
    $hasFieldErrors = false;
    $hasCompleteAssignment = false;

    foreach ($this->assignments as $index => $assignment) {
        $userFilled = !empty($assignment['user_id']);
        $categoryFilled = !empty($assignment['category_id']);
        $subcategoryFilled = !empty($assignment['subcategory_id']);

        // If any field is filled but not all, show field errors
        if ($userFilled || $categoryFilled || $subcategoryFilled) {
            if (!$userFilled) {
                $this->addError("assignments.{$index}.user_id", 'User is required.');
                $hasFieldErrors = true;
            }
            if (!$categoryFilled) {
                $this->addError("assignments.{$index}.category_id", 'Category is required.');
                $hasFieldErrors = true;
            }
            if (!$subcategoryFilled) {
                $this->addError("assignments.{$index}.subcategory_id", 'Subcategory is required.');
                $hasFieldErrors = true;
            }
        }

        // Track if we have at least one complete assignment
        if ($userFilled && $categoryFilled && $subcategoryFilled) {
            $hasCompleteAssignment = true;
        }
    }

    // If there are field errors or no complete assignments, stop
    if ($hasFieldErrors || !$hasCompleteAssignment) {
        if (!$hasCompleteAssignment) {
            $this->addError('assignments', 'Please complete at least one assignment with all fields filled.');
        }
        return;
    }

    // If we get here, proceed with cycle creation...
    try {
        DB::beginTransaction();
        
        // Your existing cycle creation code...
        $cycle = new \App\Models\Cycle();
        $cycle->cycle_code = $this->cycle_code;
        $cycle->cycle_name = $this->cycle_name;
        $cycle->organization_id = Auth::user()->organization_id;
        $cycle->location_id = $this->selectedLocation;
        $cycle->created_by = auth()->id();
        $cycle->status = 'pending';
        $cycle->schedule_date = $this->schedule_date ?? now()->toDateString();
        $cycle->save();

        foreach ($this->assignments as $assignment) {
            if (!empty($assignment['user_id']) && !empty($assignment['category_id']) && !empty($assignment['subcategory_id'])) {
                $categoryId = $assignment['category_id'];
                $subcategoryId = $assignment['subcategory_id'];
                $userId = $assignment['user_id'];

                // Build product query based on subcategory selection
                $productQuery = Product::where('category_id', $categoryId)
                    ->where('organization_id', Auth::user()->organization_id)
                    ->where('is_active', true);

                if ($subcategoryId === 'no-subcategory') {
                    $productQuery->whereNull('subcategory_id');
                } else {
                    $productQuery->where('subcategory_id', $subcategoryId);
                }

                $products = $productQuery->get();

                if ($products->isEmpty()) {
                    continue; 
                }

                $productIds = $products->pluck('id')->toArray();

                $stockCounts = StockCount::whereIn('product_id', $productIds)
                    ->where('location_id', $this->selectedLocation)
                    ->get()
                    ->groupBy('product_id');

                foreach ($products as $product) {
                    $stockCountList = $stockCounts->get($product->id, collect());
                    if ($stockCountList->isEmpty()) {
                        continue;
                    }

                    foreach ($stockCountList as $stockCount) {
                        $expectedQty = (int) ($stockCount->on_hand_quantity ?? 0);

                        CycleCount::create([
                            'cycle_id' => $cycle->id,
                            'cycle_name' => $this->cycle_name,
                            'user_id' => $userId,
                            'category_id' => $categoryId,
                            'product_id' => $product->id,
                            'batch_number' => $stockCount->batch_number ?? null,
                            'expiry_date' => $stockCount->expiry_date ?? null,
                            'expected_qty' => $expectedQty,
                            'counted_qty' => null,
                            'variance' => null,
                            'status' => 'pending',
                        ]);
                    }
                }
            }
        }

        DB::commit();

        $this->dispatch('pg:eventRefresh-cycle-list-ybwibg-table');
        $this->loadExistingCycles($this->selectedLocation);
        $this->dispatch('closeCycleModal');
        $this->resetCycleForm();
        $this->dispatch('notify', message: 'Cycle created successfully!');
        $this->dispatch('cycleCountUpdated');
        $this->generateCycleName();

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Cycle creation failed: ' . $e->getMessage());
        session()->flash('error', 'Failed to create cycle: ' . $e->getMessage());
    }
}
 public function getAvailableCategories($rowIndex)
{
    $row = $this->assignments[$rowIndex] ?? null;
    if (!$row) return collect();

    $orgId = Auth::user()->organization_id;

    // Fetch all active categories that have products IN MYCATALOG WITH VALID LOCATIONS
    $allCategories = Category::where('organization_id', $orgId)
        ->where('is_active', 1)
        ->get()
        ->filter(function ($cat) use ($orgId) {
            // Check if category has any active products IN MYCATALOG WITH VALID LOCATIONS
            return Product::where('category_id', $cat->id)
                ->where('organization_id', $orgId)
                ->where('is_active', 1)
                ->whereExists(function ($query) use ($orgId) {
                    $query->select(DB::raw(1))
                          ->from('mycatalogs')
                          ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                          ->whereColumn('mycatalogs.product_id', 'products.id')
                          ->where('locations.org_id', $orgId)
                          ->where('locations.is_active', true);
                })
                ->exists();
        });

    $available = $allCategories->filter(function ($cat) use ($row, $rowIndex, $orgId) {
        // 1. Check if category has any subcategories with products IN MYCATALOG WITH VALID LOCATIONS
        $hasSubcategoriesWithProducts = Subcategory::where('category_id', $cat->id)
            ->where('is_active', 1)
            ->whereHas('products', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId)
                  ->where('is_active', 1)
                  ->whereExists(function ($query) use ($orgId) {
                      $query->select(DB::raw(1))
                            ->from('mycatalogs')
                            ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                            ->whereColumn('mycatalogs.product_id', 'products.id')
                            ->where('locations.org_id', $orgId)
                            ->where('locations.is_active', true);
                  });
            })
            ->exists();

        // 2. Check if category has products without subcategory IN MYCATALOG WITH VALID LOCATIONS
        $hasNoSubProducts = Product::where('category_id', $cat->id)
            ->whereNull('subcategory_id')
            ->where('organization_id', $orgId)
            ->where('is_active', 1)
            ->whereExists(function ($query) use ($orgId) {
                $query->select(DB::raw(1))
                      ->from('mycatalogs')
                      ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                      ->whereColumn('mycatalogs.product_id', 'products.id')
                      ->where('locations.org_id', $orgId)
                      ->where('locations.is_active', true);
            })
            ->exists();

        // 3. Check if category has no subcategories at all but has products IN MYCATALOG WITH VALID LOCATIONS
        $hasNoSubcategoriesButHasProducts = !$hasSubcategoriesWithProducts && 
            Product::where('category_id', $cat->id)
                ->where('organization_id', $orgId)
                ->where('is_active', 1)
                ->whereExists(function ($query) use ($orgId) {
                    $query->select(DB::raw(1))
                          ->from('mycatalogs')
                          ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                          ->whereColumn('mycatalogs.product_id', 'products.id')
                          ->where('locations.org_id', $orgId)
                          ->where('locations.is_active', true);
                })
                ->exists();

        // 4. Get assigned subcategories for this category
        $assignedSubcatsForThisCategory = collect($this->assignments)
            ->filter(fn($a, $index) => 
                $index !== $rowIndex && 
                !empty($a['subcategory_id']) && 
                $a['category_id'] == $cat->id
            )
            ->pluck('subcategory_id')
            ->toArray();

        // 5. Check if "No Subcategory" is assigned for this category
        $noSubAssignedForThisCategory = in_array('no-subcategory', $assignedSubcatsForThisCategory);

        // 6. Determine if category should appear
        $shouldAppear = false;

        if ($hasSubcategoriesWithProducts) {
            // Category has subcategories - check if any are unassigned
            $unassignedSubcats = $this->getSubcategories($cat->id, $rowIndex)
                ->reject(fn($sub) => $sub->id === 'no-subcategory')
                ->isNotEmpty();
            $shouldAppear = $unassignedSubcats;
        }

        // Show category if it has "No Subcategory" products that aren't assigned
        if ($hasNoSubProducts || $hasNoSubcategoriesButHasProducts) {
            $shouldAppear = $shouldAppear || !$noSubAssignedForThisCategory;
        }

        // 7. Always show if selected in current row
        $isSelectedInRow = ($row['category_id'] ?? null) == $cat->id;

        return $shouldAppear || $isSelectedInRow;
    });

    return $available->values();
}

public function getSubcategories($categoryId, $rowIndex = null)
{
    $orgId = Auth::user()->organization_id;

    // 1. Get subcategories that actually have products
    $allSubcategories = Subcategory::where('category_id', $categoryId)
        ->where('is_active', true)
        ->get()
        ->filter(function ($sub) use ($orgId) {
            return Product::where('subcategory_id', $sub->id)
                ->where('organization_id', $orgId)
                ->where('is_active', 1)
                ->whereExists(function ($query) use ($orgId) {
                    $query->select(DB::raw(1))
                          ->from('mycatalogs')
                          ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                          ->whereColumn('mycatalogs.product_id', 'products.id')
                          ->where('locations.org_id', $orgId)
                          ->where('locations.is_active', true);
                })
                ->exists();
        });

    // 2. All subcategories already assigned in other rows FOR THIS SPECIFIC CATEGORY
    $assignedForThisCategory = collect($this->assignments)
        ->filter(fn($a) => !empty($a['subcategory_id']) && $a['category_id'] == $categoryId)
        ->pluck('subcategory_id')
        ->toArray();

    // 3. Subcategory selected in THIS row only
    $selectedThisRow = null;
    if ($rowIndex !== null && isset($this->assignments[$rowIndex]['subcategory_id'])) {
        $selectedThisRow = $this->assignments[$rowIndex]['subcategory_id'];
    }

    // 4. Filter out subcategories assigned in other rows except current one
    $availableSubcategories = $allSubcategories->reject(function ($sub) use ($assignedForThisCategory, $selectedThisRow) {
        return in_array($sub->id, $assignedForThisCategory) && $sub->id != $selectedThisRow;
    });

    // 5. Check if category has NO subcategories at all
    $categoryHasNoSubcategories = Subcategory::where('category_id', $categoryId)
        ->where('is_active', true)
        ->doesntExist();

    // 6. Check if category has products without subcategory
    $hasNoSubProducts = Product::where('category_id', $categoryId)
        ->whereNull('subcategory_id')
        ->where('organization_id', $orgId)
        ->where('is_active', 1)
        ->whereExists(function ($query) use ($orgId) {
            $query->select(DB::raw(1))
                  ->from('mycatalogs')
                  ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                  ->whereColumn('mycatalogs.product_id', 'products.id')
                  ->where('locations.org_id', $orgId)
                  ->where('locations.is_active', true);
        })
        ->exists();

    // 7. Check if category has any products at all (for case where no subcategories exist)
    $categoryHasProducts = Product::where('category_id', $categoryId)
        ->where('organization_id', $orgId)
        ->where('is_active', 1)
        ->whereExists(function ($query) use ($orgId) {
            $query->select(DB::raw(1))
                  ->from('mycatalogs')
                  ->join('locations', 'mycatalogs.location_id', '=', 'locations.id')
                  ->whereColumn('mycatalogs.product_id', 'products.id')
                  ->where('locations.org_id', $orgId)
                  ->where('locations.is_active', true);
        })
        ->exists();

    // Show "No Subcategory" option if:
    // - Category has products without subcategory, OR
    // - Category has no subcategories at all but has products
    $shouldShowNoSubcategory = $hasNoSubProducts || 
                              ($categoryHasNoSubcategories && $categoryHasProducts);

    \Log::debug('No Subcategory debug for category ' . $categoryId, [
        'hasNoSubProducts' => $hasNoSubProducts,
        'categoryHasNoSubcategories' => $categoryHasNoSubcategories,
        'categoryHasProducts' => $categoryHasProducts,
        'shouldShowNoSubcategory' => $shouldShowNoSubcategory,
        'assignedForThisCategory' => $assignedForThisCategory,
        'selectedThisRow' => $selectedThisRow
    ]);

    if ($shouldShowNoSubcategory) {
        // Check if "No Subcategory" is already assigned FOR THIS CATEGORY in other rows
        $noSubAssignedForThisCategory = in_array('no-subcategory', $assignedForThisCategory);
        $noSubSelectedInThisRow = $selectedThisRow === 'no-subcategory';
        
        // Show "No Subcategory" if:
        // - It's not assigned for this category in other rows, OR
        // - It's selected in this current row
        if (!$noSubAssignedForThisCategory || $noSubSelectedInThisRow) {
            $availableSubcategories->push((object)[
                'id' => 'no-subcategory',
                'subcategory' => 'No Subcategory'
            ]);
            
            \Log::debug('ADDED No Subcategory for category ' . $categoryId, [
                'noSubAssignedForThisCategory' => $noSubAssignedForThisCategory,
                'noSubSelectedInThisRow' => $noSubSelectedInThisRow
            ]);
        } else {
            \Log::debug('SKIPPED No Subcategory for category ' . $categoryId . ' - already assigned elsewhere for this category');
        }
    }

    \Log::debug('Final subcategories for category ' . $categoryId, [
        'final' => $availableSubcategories->pluck('subcategory')->toArray()
    ]);

    return $availableSubcategories->values();
}
    public function getLocationsProperty()
    {
        return \App\Models\Location::orderBy('name')
            ->where('is_active', 1)
            ->where('org_id', Auth::user()->organization_id)
            ->get(['id', 'name']);
    }

    public function getCategoriesProperty()
    {
        return \App\Models\Category::orderBy('category_name')
            ->where('is_active', 1)
            ->where('organization_id', Auth::user()->organization_id)
            ->get(['id', 'category_name']);
    }

    public function handleLocationChange($locationId)
    {
        $this->selectedLocation = $locationId;
        $this->currentPage = 1;
        $this->search = '';
        $this->selectedCategory = '';

        $this->loadExistingCycles($locationId);
    }

    public function loadExistingCycles($locationId)
    {
        if ($locationId) {
            $this->existingCycles = \App\Models\Cycle::with(['location', 'user'])
                ->where('location_id', $locationId)
                ->where('organization_id', Auth::user()->organization_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            $this->showExistingData = count($this->existingCycles) > 0;

            \Log::debug('Existing cycles loaded:', [
                'count' => count($this->existingCycles),
                'location_id' => $locationId
            ]);
        } else {
            $this->existingCycles = [];
            $this->showExistingData = false;
        }
    }
public function updatedAssignments($key, $value)
{
    if (!str_contains($key, '.')) {
        // Not a nested field, ignore
        return;
    }

    [$index, $field] = explode('.', $key, 2); // use 2 parts max

    if ($field === 'category_id') {
        $categoryId = $value;

        // Fetch subcategories from DB for this row
        $subcategories = Subcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();

        $this->subcategoriesList[$index] = $subcategories;

        \Log::debug('Subcategories fetched', [
            'row_index' => $index,
            'category_id' => $categoryId,
            'count' => $subcategories->count(),
            'subcategories' => $subcategories->pluck('subcategory')->toArray()
        ]);
    }
}



    public function handleSearchChange($search)
    {
        $this->search = $search;
        $this->currentPage = 1;
    }

    public function handleCategoryChange($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->currentPage = 1;
    }

    public function handlePageChange($page)
    {
        $this->currentPage = $page;
    }

    public function getProductsData()
    {
        if (!$this->selectedLocation) {
            return [
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 0,
                'to' => 0
            ];
        }

        try {
            $query = StockCount::with(['product', 'product.category'])
                ->where('location_id', $this->selectedLocation)
                ->where('stock_counts.organization_id', auth()->user()->organization_id)
                ->whereHas('product', function ($q) {
                    $q->where('is_active', true);
                });

            if ($this->search) {
                $query->whereHas('product', function ($q) {
                    $q->where('product_name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_code', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->selectedCategory) {
                $query->whereHas('product', function ($q) {
                    $q->where('category_id', $this->selectedCategory);
                });
            }

            $total = $query->count();
            $lastPage = ceil($total / $this->perPage);
            $offset = ($this->currentPage - 1) * $this->perPage;

            $products = $query->join('products', 'stock_counts.product_id', '=', 'products.id')
                ->orderBy('products.product_name')
                ->select('stock_counts.*')
                ->offset($offset)
                ->limit($this->perPage)
                ->get();

            return [
                'data' => $products->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->product_name,
                        'product_code' => $item->product->product_code,
                        'batch_number' => $item->batch_number,
                        'expiry_date' => $item->expiry_date,
                        'on_hand_quantity' => $item->on_hand_quantity ?? 0,
                        'category_name' => $item->product->category->category_name ?? 'N/A'
                    ];
                }),
                'total' => $total,
                'current_page' => $this->currentPage,
                'last_page' => $lastPage,
                'from' => $offset + 1,
                'to' => min($offset + $this->perPage, $total)
            ];

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load products: ' . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 0,
                'to' => 0
            ];
        }
    }

    public function createBulkCycleCounts($data)
    {
        try {
            $locationId = $data['locationId'] ?? null;
            $cycleName = $data['cycleName'] ?? null;
            $countedQuantities = $data['countedQuantities'] ?? [];

            // Validate
            if (!$locationId) {
                return ['success' => false, 'message' => 'Please select a location.'];
            }

            // Filter out empty values
            $validQuantities = [];
            foreach ($countedQuantities as $stockCountId => $qty) {
                if ($qty !== null && $qty !== '' && is_numeric($qty)) {
                    $validQuantities[$stockCountId] = $qty;
                }
            }

            if (empty($validQuantities)) {
                return ['success' => false, 'message' => 'Please enter counted quantities for at least one product.'];
            }

            // Generate unique count ID
            $lastCount = CycleCount::where('organization_id', Auth::user()->organization_id)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = $lastCount ? (int) substr($lastCount->count_id, 3) + 1 : 1;
            $countId = 'CC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $successCount = 0;

            foreach ($validQuantities as $stockCountId => $countedQty) {
                $stockCount = StockCount::find($stockCountId);
                logger($cycleName);
                if ($stockCount) {
                    // Create cycle count record
                    CycleCount::create([
                        'count_id' => $countId,
                        'product_id' => $stockCount->product_id,
                        'batch_number' => $stockCount->batch_number,
                        'expiry_date' => $stockCount->expiry_date,
                        'organization_id' => Auth::user()->organization_id,
                        'location_id' => $locationId,
                        'expected_qty' => $stockCount->on_hand_quantity ?? 0,
                        'counted_qty' => (float) $countedQty,
                        'variance' => (float) $countedQty - ($stockCount->on_hand_quantity ?? 0),
                        'cycle_name' => $cycleName,
                        'status' => 'completed',
                        'user_id' => Auth::id(),
                        'counted_at' => now()
                    ]);

                    // Update stock count
                    $stockCount->update(['on_hand_quantity' => (float) $countedQty]);
                    $this->dispatch('pg:eventRefresh-cycle-count-list-ybwibg-table');
                    $successCount++;
                }
            }

            // Reset internal state
            $this->selectedLocation = '';
            $this->search = '';
            $this->selectedCategory = '';
            $this->cycle_name = '';
            $this->currentPage = 1;

            return ['success' => true, 'message' => $successCount . ' cycle counts completed successfully!'];

        } catch (\Exception $e) {
            \Log::error('Cycle count creation failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create cycle counts. Please try again.'];
        }
    }

    public function updatedSelectedLocation()
    {
        $this->dispatch('cycleCountLocationChanged', $this->selectedLocation);
    }

      public function resetCycleForm()
    {
        $this->description = '';

        // Keep one empty assignment row so UI stays consistent
        $this->assignments = [
            ['user_id' => null, 'category_id' => null, 'subcategory_id' => null]
        ];
        $this->subcategoriesList = [];
    }

    public function render()
    {
        return view('livewire.organization.inventory.cycle-count-component', [
            'locations' => $this->locations,
            'categories' => $this->categories,
            'productsData' => $this->getProductsData()
        ]);
    }

}