<?php

namespace App\Livewire\Tables\Organization\Inventory;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class InventoryList extends PowerGridComponent
{
    public string $tableName = 'inventory-list-aftzfa-table';
    public bool $showFilters = true;
    public $highlightProductId = null;
    use WithExport;
    public array $alert_quantity = [];
    public array $par_quantity = [];
    public bool $showErrorBag = true;

    public $selectedLocation = '';

    public $showSampleProducts = false;
    public $showEmptyProducts = true;


    protected $listeners = ['showSamples' => 'updateShowSampleProducts', 'inventoryIocationChanged' => 'updateLocation', 'showEmptyProductsChanged' => 'updateShowEmptyProducts'];


    public function rowClass($item): ?string
    {
        return $this->highlightProductId != $item->product_id
            ? 'bg-yellow-500 text-black font-bold border-4 border-yellow-600'
            : '';
    }

    public function updateShowEmptyProducts($showEmptyProducts)
    {
        $this->showEmptyProducts = $showEmptyProducts;
        logger($this->showEmptyProducts);
        $this->resetPage();
    }


    public function updateLocation($locationId)
    {
        $this->selectedLocation = $locationId;
        $this->resetPage();
    }

    public function updateShowSampleProducts($showSampleProducts)
    {
        $this->showSampleProducts = $showSampleProducts;
        logger($this->showSampleProducts);
        $this->resetPage();
    }

    public function boot(): void
    {
        // config(['livewire-powergrid.filter' => 'outside']);
        if (!$this->selectedLocation) {
            $this->selectedLocation = auth()->user()->location_id ?? null;
        }

    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('export')
                ->striped()
                ->columnWidth([
                    2 => 30,
                ])
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = Mycatalog::query()
            ->join('products', 'products.id', '=', 'mycatalogs.product_id')
            ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->join('product_units', function ($join) {
                $join->on('product_units.product_id', '=', 'products.id')
                    ->where('product_units.is_base_unit', 1);
            })
            ->join('units', 'units.id', '=', 'product_units.unit_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('subcategories', 'subcategories.id', '=', 'products.subcategory_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('locations.org_id', auth()->user()->organization_id)
            ->select(
                'mycatalogs.*',
                'products.id as product_id',
                'products.is_sample as is_sample',
                'products.image as product_image',
                'products.product_name as product_name',
                'products.product_code as product_code',
                'products.cost',
                'locations.name as location_name',
                'units.unit_name as base_unit_name',
                'suppliers.supplier_name as supplier_name',
                'suppliers.supplier_slug as supplier_slug',
                'categories.category_name as category_name',
                'subcategories.subcategory as subcategory'
            );

        if ($this->selectedLocation) {
            $query->where('mycatalogs.location_id', $this->selectedLocation);

            if ($this->highlightProductId) {
                logger()->info("Highlight Product ID Filter with Location: " . $this->highlightProductId);
                $query->orderByRaw("CASE WHEN products.id = ? THEN 0 ELSE 1 END", [$this->highlightProductId]);
            }
        } else {
            // No location filter applied
            if ($this->highlightProductId) {
                logger()->info("Highlight Product ID Filter with NO Location: " . $this->highlightProductId);
                $query->where('products.id', $this->highlightProductId);
            }
        }
        if ($this->showSampleProducts) {
            $query->where('products.is_sample', true);
        }
        // if ($this->showEmptyProducts) {
        //     $query->where('mycatalogs.total_quantity', '=', '0');
        // }
        if (!$this->showEmptyProducts) {
            $query->where('mycatalogs.total_quantity', '>', '0');
        }

        logger()->debug($query->toSql(), $query->getBindings());

        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'product' => ['product_name'], // Ensure 'product_name' is searchable
            'location' => ['name'], // Ensure 'name' from location is searchable
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('product_image', function ($item) {
                // Check if product_image starts with "http"
                if (str_starts_with($item->product_image, 'http')) {
                    $fullImageUrl = $item->product_image;
                } else {
                    $images = json_decode($item->product_image, true);

                    // Ensure $images is an array and not empty
                    $imagePath = is_array($images) && !empty($images) ? $images[0] : $item->product_image;
                    $fullImageUrl = asset('storage/' . $imagePath);
                }

                return '<div onclick="openImageModal(\'' . $fullImageUrl . '\')" class="cursor-pointer">
                <img class="w-10 h-10 rounded-md" src="' . $fullImageUrl . '">
            </div>';
            })
            ->add('base_unit_name')
            ->add('export_product_name', fn($item) => $item->product_name)
            ->add('product_name', function ($item) {
                return '<span
        class="underline cursor-pointer text-blue-600 hover:text-blue-800' . ($item->is_sample ? ' text-green-600' : '') . '"
        onclick="openProductModal(\'' . e($item->product_id) . '\', \'inventory\', \'' . e($this->selectedLocation) . '\')">'
                    . e($item->product_name) .
                    '</span>';
            })
            ->add('product_code')
            ->add('supplier_name')
            ->add('category_name', function ($item) {
                return $item->category_name ?? 'N/A';
            })
            ->add('subcategory', function ($item) {
                return $item->subcategory ?? 'N/A';
            })
            ->add('location_name')
            ->add('total_quantity', fn($item) => $item->total_quantity ?? 0)
            ->add('par_quantity')
            ->add('alert_quantity')
            ->add('cost-unit', function ($item) {
                return '$' . number_format($item->cost ?? 0, 2) . ' / ' . $item->base_unit_name;
            })
            ->add('unit', function ($item) {
                return $item->base_unit_name;
            })
            // ->add('batch_number')
            // ->add('expiry_date_export', function ($item) {
            //     return $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '';
            // })
            // ->add('expiry_date', function ($item) {
            //     $expiryDate = \Carbon\Carbon::parse($item->expiry_date);
            //     $today = \Carbon\Carbon::today();
            //     if (empty($item->expiry_date) || is_null($item->expiry_date)) {
            //         return '';
            //     }
            //     // Check if expired
            //     if ($expiryDate && $expiryDate->isPast()) {
            //         return '<span class="text-red-600 font-bold">' . $expiryDate->format('Y-m-d') . ' <small></small></span>';
            //     }

            //     // Check if expiring soon (within 30 days)
            //     if ($expiryDate && $expiryDate->diffInDays($today) <= 30 && $expiryDate->isFuture()) {
            //         return '<span class="text-yellow-500 font-bold">' . $expiryDate->format('Y-m-d') . ' <small></small></span>';
            //     }

            //     // Normal display for future dates
            //     return '<span class="text-green-600">' . $expiryDate->format('Y-m-d') . '</span>';
            // })
            ->add('alert_par', function ($item) {
                $class = $item->total_quantity < $item->alert_quantity ? 'text-red-600 underline cursor-pointer' : 'underline cursor-pointer';
                return '<div onClick="openAlertParModal(\'' . e($item->id) . '\')" class="' . $class . '">'
                    . $item->alert_quantity . '/' . $item->par_quantity .
                    '</div>';
            })

            ->add('organization_id')
            ->add('formatted_created_at', fn($item) => date(session('date_format', 'Y-m-d') . ' ' . session('time_format', 'H:i A'), strtotime($item->created_at)));
    }

    public function columns(): array
    {
        return [

            Column::make('Product ID', 'product_id')->hidden(),


            Column::make('Image', 'product_image')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),


            Column::make('Code', 'product_code')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),


            Column::make('Product', 'product_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('Product Name', 'export_product_name')
                ->visibleInExport(true)
                ->hidden(),

            Column::make('Available', 'total_quantity')

                ->searchable(),

            Column::make('Unit', 'unit', 'unit')
                ->searchable(),

            Column::make('Category', 'category_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Sub-Category', 'subcategory')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Supplier', 'supplier_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),





            // Column::make('Base Unit', 'base_unit_name')
            //     
            //     ->searchable(),


            // Column::make('Batch/Lot', 'batch_number')
            //     
            //     ->searchable()->hidden(),

            // Column::make('Expiration', 'expiry_date')
            //     
            //     ->searchable()
            //     ->visibleInExport(false),

            // Column::make('Expiration', 'expiry_date_export')
            //     ->visibleInExport(true)
            //     ->hidden(),

            Column::make('Alert/Par', 'alert_par')

                ->searchable()
                ->visibleInExport(false),


            Column::make('Par', 'par_quantity')
                ->visibleInExport(true)
                ->hidden(),

            Column::make('Alert', 'alert_quantity')
                ->visibleInExport(true)
                ->hidden(),


            Column::make('Created at', 'formatted_created_at')

                ->searchable()
                ->hidden(),

            Column::make('Location', 'location_name')->searchable(),


            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        $filters = [
            Filter::inputText('product_name')
                ->placeholder('Product Name')
                ->operators(['contains']),
            Filter::inputText('product_code')
                ->placeholder('Code')
                ->operators(['contains']),
            // Filter::inputText('cost')
            //     ->placeholder('Cost')
            //     ->operators(['contains']),

            /**
             * ISSUE:
             * Supplier dropdown was showing suppliers (e.g. "ALK") that had no records
             * in the inventory (mycatalogs) table.
             * FIX:
             * Supplier filter datasource is now tied directly to `mycatalogs`
             * using `whereExists`, ensuring:
             *  - Supplier has at least one product in inventory
             *  - Inventory belongs to the same organization
             *  - Inventory quantity > 0
             *  - Location filter (if selected) is respected
             *
             * RESULT:
             * Supplier dropdown now shows ONLY suppliers that actually have
             * inventory data visible in the table.
             */

            Filter::select('supplier_name', 'products.product_supplier_id')
                ->dataSource(
                    Supplier::query()
                        ->where('suppliers.is_active', true)
                        ->where('suppliers.verified', true)
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('products')
                                ->join('mycatalogs', 'mycatalogs.product_id', '=', 'products.id')
                                ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
                                ->whereColumn('products.product_supplier_id', 'suppliers.id')
                                ->where('products.organization_id', auth()->user()->organization_id)
                                ->where('products.is_active', true)
                                ->where('locations.org_id', auth()->user()->organization_id)
                                ->where('mycatalogs.total_quantity', '>=', 0)
                                ->when($this->selectedLocation, function ($q) {
                                    $q->where('mycatalogs.location_id', $this->selectedLocation);
                                });
                        })
                        ->orderBy('supplier_name', 'asc')
                        ->get()
                )
                ->optionLabel('supplier_name')
                ->optionValue('id'),
            Filter::select('category_name', 'products.category_id')
                ->dataSource(
                    Category::where('organization_id', auth()->user()->organization_id)
                        ->where('is_active', true)
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('products')
                                ->join('mycatalogs', 'mycatalogs.product_id', '=', 'products.id')
                                ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
                                ->whereColumn('products.category_id', 'categories.id')
                                ->where('products.organization_id', auth()->user()->organization_id)
                                ->where('products.is_active', true)
                                ->where('locations.org_id', auth()->user()->organization_id)
                                ->when($this->selectedLocation, function ($q) {
                                    $q->where('mycatalogs.location_id', $this->selectedLocation);
                                });
                        })
                        ->orderBy('category_name', 'asc')
                        ->get()
                )
                ->optionLabel('category_name')
                ->optionValue('id'),

            Filter::select('location_name', 'mycatalogs.location_id')
                ->dataSource(
                    Location::where('org_id', auth()->user()->organization_id)
                        ->where('is_active', true)
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('mycatalogs')
                                ->join('products', 'products.id', '=', 'mycatalogs.product_id')
                                ->whereColumn('mycatalogs.location_id', 'locations.id')
                                ->where('products.organization_id', auth()->user()->organization_id)
                                ->where('products.is_active', true)
                                // ->where('mycatalogs.total_quantity', '>', 0)
                                ;
                        })
                        ->orderBy('name', 'asc')
                        ->get()
                )
                ->optionLabel('name')
                ->optionValue('id'),


            Filter::select('subcategory', 'products.subcategory_id')
                ->dataSource(
                    Subcategory::where('is_active', true)
                        ->whereHas('category', function ($categoryQuery) {
                            $categoryQuery->where('organization_id', auth()->user()->organization_id)
                                ->where('is_active', true);
                        })
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('products')
                                ->join('mycatalogs', 'mycatalogs.product_id', '=', 'products.id')
                                ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
                                ->whereColumn('products.subcategory_id', 'subcategories.id')
                                ->where('products.organization_id', auth()->user()->organization_id)
                                ->where('products.is_active', true)
                                ->where('locations.org_id', auth()->user()->organization_id)
                                ->when($this->selectedLocation, function ($q) {
                                    $q->where('mycatalogs.location_id', $this->selectedLocation);
                                });
                        })
                        ->orderBy('subcategory', 'asc')
                        ->get()
                )
                ->optionLabel('subcategory')
                ->optionValue('id'),
        ];

        return $filters;
    }

    protected function rules()
    {
        return [
            // 'alert_quantity.*' => ['required', 'numeric', 'min:0'],
            // 'par_quantity.*' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function messages()
    {
        return [
            'alert_quantity.*.required' => 'Alert quantity is required',
            'alert_quantity.*.numeric' => 'Alert quantity must be a number',
            'alert_quantity.*.min' => 'Alert quantity must be at least 0',
            'par_quantity.*.required' => 'Par quantity is required',
            'par_quantity.*.numeric' => 'Par quantity must be a number',
            'par_quantity.*.min' => 'Par quantity must be at least 0',
        ];
    }

    // public function onUpdatedEditable(string|int $id, string $field, string $value): void
    // {
    //     // Validate the input
    //     if ($field === 'alert_quantity' || $field === 'par_quantity') {
    //         // Clean value and ensure it's numeric
    //         $cleanValue = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    //         if (!is_numeric($cleanValue)) {
    //             $this->addError($field, 'Must be a valid number');
    //             $this->dispatch('pg:skipNextRefresh');
    //             return;
    //         }

    //         // Update the database
    //         try {
    //             StockCount::query()->findOrFail($id)->update([
    //                 $field => (float) $cleanValue,
    //             ]);
    //             // Success notification
    //             $this->dispatch('notify', [
    //                 'type' => 'success',
    //                 'message' => ucfirst(str_replace('_', ' ', $field)) . ' updated successfully!'
    //             ]);
    //         } catch (\Exception $e) {
    //             // Error notification
    //             $this->addError($field, 'Failed to update: ' . $e->getMessage());
    //             $this->dispatch('pg:skipNextRefresh');
    //         }
    //     }
    // }

    function actions(Mycatalog $row): array
    {
        $inv = Mycatalog::find($row->id);

        $existingCartItem = Cart::where('product_id', $inv->product_id)
            ->where('location_id', $inv?->location_id)
            ->first();

        $cartQty = $existingCartItem?->quantity ?? 0;
        $inCart = $existingCartItem !== null;

        // Common SVG cart icon
        $cartIcon = '
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg"
             width="24" height="24" fill="' . ($inCart ? 'currentColor' : 'gray') . '" viewBox="0 0 24 24">
            <path stroke="' . ($inCart ? 'currentColor' : 'gray') . '"
                 stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4
                 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4
                 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"/>
        </svg>
    ';

        // Qty badge if > 0 - positioned top-right with red background
        $qtyBadge = $cartQty > 0
            ? '<span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold rounded-full h-6 w-6 flex items-center justify-center">' . $cartQty . '</span>'
            : '';

        // Button style
        $btnClass = $inCart
            ? 'relative inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md
        font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600
        focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150'
            : 'relative inline-flex items-center px-4 py-2 bg-white border-2 rounded-md
        font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-green-500 hover:text-white
        focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150';

        return [
            Button::add('cartIconClick')
                ->slot('<div class="flex items-center">' . $cartIcon . $qtyBadge . '</div>')
                ->id()
                ->class($btnClass)
                ->dispatch('cartIconClick', ['rowId' => $row->id]),
        ];
    }


}