<?php

namespace App\Livewire\Organization\Products;

use App\Models\AlertParTacking;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockCount;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\Audit\ProductAuditService;
use App\Services\Pricing\PriceHistoryService;
use DB;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Str;

class MasterCatalogComponents extends Component
{
    use WithFileUploads;
    public Product $product;
    public $id = '';
    public $brands = [];
    public $brand_id;
    public $product_id = '';
    public $category_id;
    public $subcategory_id;
    public $product_name = '';
    public $product_code = '';
    public $product_supplier_id = '';

    public $product_description = '', $product_price = '0', $product_cost = '', $is_deleted = false, $created_by = '', $updated_by = '', $manufacture_code = '', $is_approved = false, $approved_by = '', $selectedCategory = null, $units = [], $availableUnits = [], $notifications = [], $baseUnitName = '', $locations = [], $locationData = [], $catalogCount, $product_base_unit, $baseUnit = '', $images, $price, $cost, $organization, $existingImage, $length, $width, $height, $weight, $categories = [], $dose;

    public $selectedProductId;
    public $selectedProductName;
    public $selectedProductCost;
    public $locationQuantities = [];
    public $locationUnits = [];

    public $isBatch = '';
    public $brand_search = '';
    public $selected_brand_name = '';
    public $show_dropdown = false;

    public $filtered_brands = [];
    public $subcategories = [];

    public function mount()
    {
        $this->brands = Brand::where('organization_id', auth()->user()->organization_id)->where('brand_is_active', true)
            ->get();
        $this->filtered_brands = $this->brands;

        $this->categories = Category::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', operator: '1')
            ->orderBy('category_name')
            ->get();

        $this->subcategories = collect();


        $this->catalogCount = MyCatalog::leftJoin('products', 'mycatalogs.product_id', '=', 'products.id')
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('products.is_active', true)
            ->count();

        $this->organization = Organization::where('id', auth()->user()->organization_id)->first();

        $this->product = new Product();
        $this->availableUnits = Unit::where('is_active', true)->get();
        $this->baseUnit = '';
        $this->units[] = [
            'unit_id' => '',
            'operator' => 'multiply',
            'conversion_factor' => 1,
            'is_base_unit' => 0
        ];
        $this->locations = Location::where('is_active', true)->where('org_id', auth()->user()->organization_id)->get();
        foreach ($this->locations as $location) {
            $this->locationData[$location->id] = [
                'alert_quantity' => $location->alert_quantity ?? 3,
                'par_quantity' => $location->par_quantity ?? 10,
            ];
        }

    }
    public function updatedCategoryId($value)
    {
        // Reset dose when category changes
        $this->dose = '';

        // Reset subcategory when category changes
        $this->subcategory_id = null;

        // Load subcategories for the selected category
        if ($value) {
            $this->subcategories = Subcategory::where('category_id', $value)
                ->where('is_active', true)
                ->get();
        } else {
            $this->subcategories = collect();
        }
    }

    public function getSelectedCategoryProperty()
    {
        // Ensure categories are loaded
        if (!$this->categories) {
            return null;
        }

        return $this->categories->firstWhere('id', $this->category_id);
    }

    public function getIsBiologicalCategoryProperty()
    {
        // Add null checks
        if (!$this->category_id || !$this->categories) {
            return false;
        }

        $category = $this->getSelectedCategoryProperty();
        return $category && strtolower($category->category_name) === 'biological';
    }
    public function updatedBrandSearch()
    {
        if (empty($this->brand_search)) {
            $this->filtered_brands = $this->brands;
            $this->brand_id = '';
            $this->selected_brand_name = '';
            $this->show_dropdown = false;
        } else {
            $this->show_dropdown = true;
            $this->filtered_brands = $this->brands->filter(function ($brand) {
                return stripos($brand->brand_name, $this->brand_search) !== false;
            });
        }

    }
    public function showDropdown()
    {
        $this->show_dropdown = true;
        if (empty($this->brand_search)) {
            $this->filtered_brands = $this->brands;
        }
    }

    public function hideDropdown()
    {
        // Small delay to allow clicking on dropdown items
        $this->dispatch('hide-dropdown-delayed');
    }

    public function selectBrand($brandId, $brandName)
    {
        $this->brand_id = $brandId;
        $this->brand_search = $brandName;
        $this->selected_brand_name = $brandName;
        $this->show_dropdown = false;
    }
    public function updatedBaseUnit($value)
    {
        $this->baseUnit = $value;
        $this->units = [];
    }

    // Adding this method to reset and open the Add Product modal
public function openAddProductModal()
{
    // Reset all form properties
    $this->reset([
        'product_name',
        'product_code',
        'product_supplier_id',
        'product_description',
        'manufacture_code',
        'cost',
        'price',
        'category_id',
        'subcategory_id',
        'baseUnit',
        'units',
        'images',
        'isBatch',
        'brand_search',
        'brand_id',
        'weight',
        'length',
        'width',
        'height',
        'dose',
        'id',
        'existingImage', 
        'selected_brand_name', 
        'show_dropdown', 
    ]);
    
    // Reset validation errors
    $this->resetErrorBag();
    $this->resetValidation();
    
    // Reinitialize units with one empty row
    $this->units = [
        [
            'unit_id' => '',
            'operator' => 'multiply',
            'conversion_factor' => 1,
            'is_base_unit' => 0
        ]
    ];
    
    // Reset category-specific properties
    $this->subcategories = collect();
    $this->isBatch = '';
    
    // Reset brand dropdown
    $this->filtered_brands = $this->brands;
    
    // Open the modal
    $this->dispatch('open-modal', 'add-product-modal');
}
    public function createProduct()
    {
        $user = auth()->user();
        $role = $user->role;
        if (!$role?->hasPermission('add_products') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to add Products!', 'error');
            return;
        }
        $this->validate([
            'product_name' => [
                'required',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('organization_id', auth()->user()->organization_id);
                }),
            ],
            'product_code' => [
                'required',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('organization_id', auth()->user()->organization_id);
                }),
            ],
            'product_supplier_id' => 'required',
            'baseUnit' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
        ]);

        $this->validate([
            'units.*.unit_id' => 'nullable|exists:units,id',
            'units.*.operator' => 'required|in:add,subtract,multiply,divide',
            'units.*.conversion_factor' => 'required|numeric|min:0.1',
        ]);

        $category = Category::find($this->category_id);
        if ($category && strtolower($category->category_name) == 'biological') {
            $this->validate([
                'dose' => 'required',
            ]);
        }
        try {
            DB::beginTransaction();
            $this->price = $this->price != '' ? $this->price : $this->cost;
            // **Store Images First**
            $uploadedImages = [];
            if (!empty($this->images)) {
                foreach ($this->images as $image) {
                    $uploadedImages[] = $image->store('product_images', 'public');
                }
            }
            if ($this->isBatch == 'true') {
                $isBatch = true;
            } else {
                $isBatch = false;
            }

            if ($this->brand_search != '') {
                $brand = Brand::where('organization_id', auth()->user()->organization_id)
                    ->where('brand_name', 'like', '%' . $this->brand_search . '%')
                    ->first();
                if ($brand) {
                    $this->brand_id = $brand->id;
                } else {
                    // If brand doesn't exist, create a new one
                    $newBrand = Brand::create([
                        'brand_name' => $this->brand_search,
                        'organization_id' => auth()->user()->organization_id,
                        'brand_is_active' => true,
                    ]);
                    $this->brand_id = $newBrand->id;
                }
            }
            if (auth()->user()->is_medical_rep && $this->rep_supplier_search != '') {
                $supplier = Supplier::where('supplier_name', 'like', '%' . $this->brand_search . '%')
                    ->first();
                if ($supplier) {
                    $this->product_supplier_id = $supplier->id;
                } else {
                    $newSupplier = Supplier::create([
                        'supplier_name' => $this->rep_supplier_search,
                        'verified' => false,
                        'is_active' => true,
                        'supplier_slug' => Str::slug($this->rep_supplier_search)
                    ]);
                    $this->product_supplier_id = $newSupplier->id;
                }
            }
            if (!$isBatch && strtolower($category->category_name) == 'biological') {
                $isBatch = true;
            }
            // **Create Product**
            $product = new Product();
            $product->product_name = $this->product_name;
            $product->brand_id = $this->brand_id;
            $product->product_code = $this->product_code;
            $product->product_supplier_id = $this->product_supplier_id;
            $product->product_description = $this->product_description;
            $product->manufacture_code = $this->manufacture_code;
            $product->created_by = auth()->user()->id;
            $product->updated_by = auth()->user()->id;
            $product->image = json_encode($uploadedImages);
            $product->organization_id = auth()->user()->organization_id;
            $product->category_id = $this->category_id;
            $product->subcategory_id = $this->subcategory_id;
            $product->cost = $this->cost ?? 0;
            $product->price = $this->price ?? 0;
            $product->has_expiry_date = $isBatch;
            $product->weight = $this->weight ?? 0;
            $product->length = $this->length ?? 0;
            $product->width = $this->width ?? 0;
            $product->height = $this->height ?? 0;
            $product->is_sample = auth()->user()->is_medical_rep;
            $product->dose = $this->dose ?? 'N/A';

            if (!$product->save()) {
                throw new \Exception('Failed to save product');
            }

            $productUnits = [];

            $productUnits[] = [
                'product_id' => $product->id,
                'unit_id' => $this->baseUnit,
                'is_base_unit' => 1,
                'operator' => 'multiply',
                'conversion_factor' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ($this->units as $unit) {
                if (empty($unit['unit_id'])) {
                    continue;
                }
                $productUnits[] = [
                    'product_id' => $product->id,
                    'unit_id' => $unit['unit_id'],
                    'is_base_unit' => 0,
                    'operator' => strval($unit['operator']),
                    'conversion_factor' => floatval($unit['conversion_factor']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach ($productUnits as $unit) {
                if (!DB::table('product_units')->insert($unit)) {
                    throw new \Exception('Failed to save product unit');
                }
            }
            DB::commit();

            $auditService = app(\App\Services\InventoryAuditService::class);
            $auditService->logMasterCatalogChange(
                $product->id,
                'Created',
                'Product has been created in Master Catalog'
            );


            $this->dispatch('pg:eventRefresh-master-catalog-list-table');
            $this->dispatch('close-modal', 'add-product-modal');
            $this->reset([
                'product_name',
                'product_code',
                'product_supplier_id',
                'product_description',
                'manufacture_code',
                'cost',
                'price',
                'category_id',
                'baseUnit',
                'units',
                'images',
                'isBatch',
                'brand_search',
                'weight',
                'length',
                'width',
                'height'
            ]);

        } catch (\Exception | \Throwable $e) {
            DB::rollback();
            Log::error("Error while adding product: " . $e->getMessage(), [
                'product_data' => $this->only(['product_name', 'product_code']),
                'units_data' => $this->units,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/organization/catalog')->with('error', 'Something went wrong while adding the product. Please try again.');
        }
    }
    public function addUnit()
    {
        $this->units[] = [
            'unit_id' => '',
            'operator' => 'multiply',
            'conversion_factor' => 1,
            'is_base_unit' => 0
        ];
    }
    private function isBiologicalCategory()
    {
        return $this->getIsBiologicalCategoryProperty();
    }

    public function removeUnit($index)
    {
        if ($index !== 0 && count($this->units) > 1) {
            unset($this->units[$index]);
            $this->units = array_values($this->units);
        }
    }

    #[On('toggleMyCatalog')]
    public function toggleMyCatalog($rowId)
    {
        logger("Toggling My Catalog for product ID: " . $rowId);
        $this->product_id = $rowId;
        $org_id = auth()->user()->organization_id;
        // Get all active locations for the organization
        $locations = Location::where('is_active', true)
            ->where('org_id', $org_id)
            ->get();

        // Count how many locations already have this product
        $existingCount = Mycatalog::where('product_id', $rowId)
            ->whereIn('location_id', $locations->pluck('id'))
            ->join('products', 'mycatalogs.product_id', '=', 'products.id')
            ->where('products.organization_id', $org_id)
            ->distinct('mycatalogs.location_id')
            ->count('mycatalogs.location_id');

        $inAllLocations = ($existingCount === $locations->count());

        if ($inAllLocations) {
            // Remove product from My Catalog
            Mycatalog::where('product_id', $this->product_id)
                ->whereIn('location_id', $locations->pluck('id'))
                ->delete();

            StockCount::where('product_id', $this->product_id)
                ->where('on_hand_quantity', 0)
                ->delete();

            $event = 'Removed';
            $message = "Following Product has been removed from the catalog";
            $auditService = app(\App\Services\InventoryAuditService::class);
            $auditService->logMyCatalogChangeCreation(
                $rowId,
                $event,
                $message
            );
            // $this->addNotification('Product removed from My Catalog', 'error');
        } else {
            // Use bulk insert for better performance
            $catalogData = [];
            $stockData = [];

            foreach ($locations as $location) {
                $exists = Mycatalog::where('product_id', $rowId)
                    ->where('location_id', $location->id)
                    ->exists();

                if (!$exists) {
                    $totalStock = StockCount::where('organization_id', $org_id)
                        ->where('product_id', $rowId)
                        ->where('location_id', $location->id)
                        ->sum('on_hand_quantity');

                    $catalogData[] = [
                        'product_id' => $rowId,
                        'location_id' => $location->id,
                        'total_quantity' => $totalStock,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $stockExists = StockCount::where('organization_id', $org_id)
                    ->where('product_id', $rowId)
                    ->where('location_id', $location->id)
                    ->exists();

                if (!$stockExists) {
                    $stockData[] = [
                        'organization_id' => $org_id,
                        'product_id' => $rowId,
                        'location_id' => $location->id,
                        'on_hand_quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert for better performance
            if (!empty($catalogData)) {
                Mycatalog::insert($catalogData);
            }
            if (!empty($stockData)) {
                StockCount::insert($stockData);
            }
        }
        // Refresh both tables
        $this->dispatch('pg:eventRefresh-master-catalog-list-table');
        $this->dispatch('pg:eventRefresh-my-catalog-list-cnalxn-table');
    }

    /**
     * Open the "Add to Cart" modal for the selected product.
     *
     * This method:
     * - Verifies user permission to add items to the cart.
     * - Ensures the product exists.
     * - Loads all active organization locations, excluding:
     *      • Locations where the product already exists in the cart, or
     *      • Locations where the product already exists in Mycatalog.
     * - Fetches all product units.
     * - Initializes quantity and unit defaults for available locations.
     * - Opens the modal if any eligible locations remain.
     *
     * @param  string  $productId  The ID of the product to add to the cart.
     * @return void
     */

    #[On('openAddToCartModal')]

    public function openAddToCartModal($productId)
    {
        $user = auth()->user();

        //  Step 1: Verify permission to add to cart
        if (!$user->role?->hasPermission('add_to_cart') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to add products to the cart!', 'error');
            return;
        }

        //  Step 2: Validate product existence
        $product = Product::find($productId);
        if (!$product) {
            $this->dispatch('show-notification', 'Product not found.', 'error');
            return;
        }

        //  Step 3: Get location IDs where this product is already present in the cart
        $cartLocationIds = Cart::where('product_id', $productId)
            ->where('organization_id', $user->organization_id)
            ->pluck('location_id')
            ->toArray();

        logger("Cart Location IDs: " . implode(", ", $cartLocationIds));

        //  Step 4: Get location IDs where this product is already present in Mycatalog
        $mycatalogLocationIds = Mycatalog::where('product_id', $productId)
            ->where('total_quantity', '>', 0)
            ->pluck('location_id')
            ->toArray();

        logger("Mycatalog Location IDs: " . implode(", ", $mycatalogLocationIds));


        //  Step 5: Merge both exclusion lists (cart + mycatalog)
        $excludedLocationIds = array_unique(array_merge($cartLocationIds, $mycatalogLocationIds));

        logger("Excluded Location IDs: " . implode(", ", $excludedLocationIds));

        //  Step 6: Get active locations excluding those already having the product
        $this->locations = Location::query()
            ->where('is_active', true)
            ->where('org_id', $user->organization_id)
            ->whereNotIn('id', $excludedLocationIds)
            ->orderBy('name', 'asc')
            ->get();

        //  Step 7: If no eligible locations remain, show notification
        if ($this->locations->isEmpty()) {
            $this->dispatch('show-notification', 'This product already exists in all eligible locations (Cart or Mycatalog).', 'info');
            return;
        }

        //  Step 8: Assign product details
        $this->selectedProductId = $productId;
        $this->selectedProductName = $product->product_name;
        $this->selectedProductCost = $product->cost;

        //  Step 9: Fetch product units with safe fallback
        $this->units = ProductUnit::with('unit')
            ->where('product_id', $productId)
            ->get()
            ->map(function ($productUnit) {
                return [
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit->unit_name ?? 'Unknown',
                    'is_base_unit' => $productUnit->is_base_unit,
                    'operator' => $productUnit->operator,
                    'conversion_factor' => $productUnit->conversion_factor,
                ];
            })
            ->toArray();

        //  Step 10: Initialize quantity and unit defaults for each available location
        $baseUnit = collect($this->units)->firstWhere('is_base_unit', true);

        foreach ($this->locations as $location) {
            $locationId = $location->id;
            logger("Setting defaults for location ID: " . $locationId);
            // Default PAR quantity or fallback to 1
            $mycatalog = Mycatalog::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->first();

            $this->locationQuantities[$locationId] = $mycatalog?->par_quantity ?? 1;
            $this->locationUnits[$locationId] = $baseUnit['unit_id'] ?? null;
        }

        //  Step 11: Open modal for adding product
        $this->dispatch('open-modal', 'cart-product-modal');
    }

    #[On('openAddToMyCatalogModal')]
    public function callAddToInventoryComponent($productId)
    {
        logger('Forwarding to AddToInventoryComponent with product ID: ' . $productId);

        $this->dispatch('callAddToMyCatalogModal', productId: $productId)
            ->to(AddToInventoryComponent::class);
    }


    #[On('edit-product')]
    public function startedit($rowId)
    {
        $user = auth()->user();
        $role = $user->role;
        if (!$role?->hasPermission('edit_products') && $user->role_id > 2) {
            $this->dispatch('You don\'t have permission to Edit Products!', 'error');
            return;
        }
        $this->reset();
        $this->categories = Category::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', operator: '1')
            ->orderBy('category_name')
            ->get();
        $productData = Product::findOrFail($rowId);
        $this->organization = Organization::where('id', auth()->user()->organization_id)->first();
        $this->brands = Brand::where('organization_id', auth()->user()->organization_id)->where('brand_is_active', true)
            ->get();
        $this->id = $rowId;
        $this->product_name = $productData->product_name;
        $this->brand_id = $productData->brand_id;
        $this->product_code = $productData->product_code;
        $this->product_supplier_id = $productData->product_supplier_id;
        $this->product_description = $productData->product_description;
        $this->manufacture_code = $productData->manufacture_code;
        $this->cost = $productData->cost;
        $this->price = $productData->price;
        $this->category_id = $productData->category_id;
        $this->subcategory_id = $productData->subcategory_id;
        $this->isBatch = $productData->has_expiry_date ? true : false;
        $this->weight = $productData->weight ?? 0;
        $this->length = $productData->length ?? 0;
        $this->width = $productData->width ?? 0;
        $this->height = $productData->height ?? 0;
        $this->brand_search = $this->brands->where('id', $this->brand_id)->first()->brand_name ?? '';

        // Load subcategories for the selected category
        if ($this->category_id) {
            $this->subcategories = Subcategory::where('category_id', $this->category_id)
                ->where('is_active', true)
                ->get();
        } else {
            $this->subcategories = collect();
        }
        // Set dose if biological category - use same logic as your computed property
        if ($this->isBiologicalCategory()) {
            $this->dose = $productData->dose ?? '';
        } else {
            $this->dose = ''; // Clear dose for non-biological products
        }

        $productUnits = ProductUnit::where('product_id', $rowId)->get();
        $baseUnitData = $productUnits->where('is_base_unit', 1)->first();
        logger($productUnits);
        logger($baseUnitData);
        $this->baseUnit = $baseUnitData ? $baseUnitData->unit_id : null;
        $this->availableUnits = Unit::where('is_active', true)->get();
        $this->units = [];

        // Add other units
        $index = 0;
        foreach ($productUnits->where('is_base_unit', 0) as $productUnit) {
            $this->units[$index] = [
                'unit_id' => $productUnit->unit_id,
                'operator' => $productUnit->operator,
                'conversion_factor' => $productUnit->conversion_factor,
                'is_base_unit' => 0
            ];
            $index++;
        }


        // Set the existing image
        $existingImages = json_decode($productData->image, true) ?? [];
        $this->existingImage = $existingImages[0] ?? null;

        $this->dispatch('open-modal', 'edit-product-modal');
    }

    public function addEditUnit()
    {
        $this->units[] = [
            'unit_id' => '',
            'operator' => 'multiply',
            'conversion_factor' => 1,
            'is_base_unit' => 0
        ];
    }
    public function removeEditUnit($index)
    {
        if ($index >= 0 && count($this->units) >= 0) {
            unset($this->units[$index]);
            $this->units = array_values($this->units);
        }
    }
    public function updateProduct(ProductAuditService $auditService)
    {
        // Validate the input 

        $this->validate([
            'product_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'product_code')
                    ->ignore($this->id)
                    ->where(fn($query) => $query
                        ->where('organization_id', auth()->user()->organization_id)
                        ->where('is_active', true)),
            ],
            'product_name' => 'required|string|max:255',
            'product_supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'manufacture_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'manufacture_code')
                    ->ignore($this->id)
                    ->where(fn($query) => $query
                        ->where('organization_id', auth()->user()->organization_id)
                        ->where('is_active', true)),
            ],
            'product_description' => 'nullable|string|max:1000',
            'baseUnit' => 'required|exists:units,id',

            'units.*.unit_id' => 'nullable|exists:units,id',
            'units.*.operator' => 'nullable|in:multiply,divide',
            'units.*.conversion_factor' => 'nullable|numeric|min:0.01',

            'images.*' => 'nullable|image|max:2048',
        ]);
        $category = Category::find($this->category_id);
        if ($category && strtolower($category->category_name) == 'biological') {
            $this->validate([
                'dose' => 'required',
            ]);
        }


        try {

            if ($this->brand_search != '') {
                $brand = Brand::where('organization_id', auth()->user()->organization_id)
                    ->where('brand_name', 'like', '%' . $this->brand_search . '%')
                    ->first();
                if ($brand) {
                    $this->brand_id = $brand->id;
                } else {
                    // If brand doesn't exist, create a new one
                    $newBrand = Brand::create([
                        'brand_name' => $this->brand_search,
                        'organization_id' => auth()->user()->organization_id,
                        'brand_is_active' => true,
                    ]);
                    $this->brand_id = $newBrand->id;
                }
            }
            $product = Product::findOrFail($this->id);

            /** -------------------------------
             * PRICE CHANGE HANDLING
             * ------------------------------- */
            if (
                (float) $product->cost !== (float) $this->cost ||
                (float) $product->price !== (float) $this->price
            ) {
                app(PriceHistoryService::class)->changePrice(
                    $product,
                    $this->price,
                    $this->cost,
                    auth()->user()->id
                );
            }

            $oldValues = $product->only([
                'brand_id',
                'product_code',
                'product_name',
                'product_supplier_id',
                'manufacture_code',
                'product_description',
                'cost',
                'price',
                'category_id',
                'subcategory_id',
                'has_expiry_date',
                'weight',
                'length',
                'width',
                'height',
                'dose',
                'image'
            ]);

            $oldUnits = ProductUnit::where('product_id', $product->id)
                ->get(['unit_id', 'operator', 'conversion_factor', 'is_base_unit'])
                ->toArray();

            // **Handle Image Updates**
            $existingImages = null;

            // Check if existing image starts with http (external URL)
            if (!empty($product->image) && str_starts_with($product->image, 'http')) {
                if (empty($this->images)) {
                    logger("Case 1: Keep the same external URL if no new images uploaded");
                    $existingImages = $product->image; // ✅ keep direct link
                } else {
                    logger("Case 2: Remove the external URL if new images are uploaded");
                    $existingImages = null; // we will replace with uploaded images
                }
            } else {
                logger("Case 3: Already stored as JSON (local images)");
                $existingImages = json_decode($product->image, true) ?? [];
            }

            $uploadedImages = [];

            // Store new images if uploaded
            if (!empty($this->images)) {
                foreach ($this->images as $image) {
                    $uploadedImages[] = $image->store('product_images', 'public');
                }
            }

            // ✅ Save logic
            if (!empty($uploadedImages)) {
                // Store new uploads as JSON array
                $product->image = json_encode($uploadedImages);
            } else {
                // If existing was direct link → keep it as is
                // If existing was JSON → re-encode it
                $product->image = is_array($existingImages)
                    ? json_encode($existingImages)
                    : $existingImages;
            }


            if ($this->isBatch == 'true') {
                $isBatch = true;
            } else {
                $isBatch = false;
            }
            // **Update Product Data**
            $product->update([
                'brand_id' => $this->brand_id,
                'product_code' => $this->product_code,
                'product_name' => $this->product_name,
                'product_supplier_id' => $this->product_supplier_id,
                'manufacture_code' => $this->manufacture_code,
                'product_description' => $this->product_description,
                'cost' => $this->cost ?? 0,
                'price' => $this->price ?? $this->cost,
                'category_id' => $this->category_id,
                'subcategory_id' => $this->subcategory_id,

                'updated_by' => auth()->user()->id,
                'has_expiry_date' => $isBatch,
                'weight' => $this->weight ?? 0,
                'length' => $this->length ?? 0,
                'width' => $this->width ?? 0,
                'height' => $this->height ?? 0,
                'dose' => $this->dose
            ]);

            // **Manage Product Units**
            ProductUnit::where('product_id', $product->id)->delete();
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_id' => $this->baseUnit,
                'operator' => 'multiply',
                'conversion_factor' => 1.00,
                'is_base_unit' => true
            ]);

            foreach ($this->units as $unit) {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'unit_id' => $unit['unit_id'],
                    'operator' => $unit['operator'],
                    'conversion_factor' => $unit['conversion_factor'],
                    'is_base_unit' => false
                ]);
            }

            $newValues = $product->only(array_keys($oldValues));

            $newUnits = ProductUnit::where('product_id', $product->id)
                ->get(['unit_id', 'operator', 'conversion_factor', 'is_base_unit'])
                ->toArray();

            // === Log all changes (fields + units) ===
            $auditService->logProductUpdate($product, $oldValues, $newValues, $oldUnits, $newUnits);

            // **Emit success message and close modal**
            $this->dispatch('pg:eventRefresh-master-catalog-list-table');
            $this->dispatch('close-modal', 'edit-product-modal');
            // $this->reset();
            $this->dispatch('show-notification', 'Product updated successfully !', 'success');
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }

    public function downloadSampleCsv()
    {
        $headers = [
            'product_code',
            'product_name',
            'manufacture_code',
            'manufacturer',
            'base_unit_code',
            'product_description',
            'category',
            'sub_category',
            'cost',
            'price',
        ];

        $sampleData = [
            [
                'CODE001',
                'Sample Product 1',
                'MFG001',
                'Astrazeneca',
                'EA',
                'Description for product 1',
                'Office supplies',
                'S-C-2',
                '120',
                '60',
            ],
            [
                'CODE002',
                'Sample Product 2',
                'MFG001',
                'Apotex Corp',
                'DZ',
                'Description for product 2',
                'Office supplies',
                'S-C-5',
                '90',
                '78',
            ],
        ];
        $csv = implode(',', $headers) . "\n";
        foreach ($sampleData as $row) {
            $csv .= implode(',', $row) . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'sample_products_import.csv');
    }

    public function deleteProduct()
    {
        $product = Product::where('id', $this->id)->first();
        if ($product) {
            $product->is_active = false;
            $product->save();
            $auditService = app(\App\Services\InventoryAuditService::class);

            $auditService->logMasterCatalogChange(
                $product->id,
                'Removed',
                'Product has been removed from Master Catalog'
            );
            $this->dispatch('pg:eventRefresh-master-catalog-list-wvwykb-table');
            $this->dispatch('close-modal', 'edit-product-modal');
            $this->dispatch('show-notification', 'Product deleted successfully !', 'success');
        } else {
            $this->dispatch('show-notification', 'Product not found !', 'error');
            $this->dispatch('show-notification', 'Product deleted successfully !', 'success');
        }
    }

    public function render()
    {
        $organization_suppliers = Supplier::orderBy('supplier_name')->where('is_active', true)
            // ->where('verified', true)
            ->get();

        return view('livewire.organization.products.master-catalog-components', compact('organization_suppliers'));
    }
}