<?php

namespace App\Livewire\Tables\Organization;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Cart;
use App\Models\ProductUnit;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\Components\Checkbox;

use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

final class MasterCatalogList extends PowerGridComponent
{
    public string $tableName = 'master-catalog-list-table';
    public bool $showFilters = true;
    // public array $selectedProducts = [];

    // Holds IDs of rows selected via PowerGrid built-in checkboxes.
    public array $checkboxValues = [];


    use WithExport;



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

    /**
     * Bulk Add to Inventory action
     * - User selects multiple products from the list
     * - This button becomes useful only when more than one product is selected
     * - On click, selected product IDs are passed to a bulk modal
     * - User then chooses locations and confirms the action
     */

    public function header(): array
    {
        return [
        ];
    }

    /**
     * Handle bulk "Add to Inventory" action
     *
     * Triggered only when the PowerGrid header button is clicked.
     * Uses PowerGrid-managed checkbox state instead of JS.
     */

    #[On('bulkAddToInventory.master-catalog-list-table')]
    public function handleBulkAddToInventory()
    {
        // Selected product IDs from PowerGrid checkboxes
        $selectedIds = $this->checkboxValues;

        Log::debug('Selected product IDs for bulk action', $selectedIds);

        //  BULK RULE
        if (count($selectedIds) < 2) {
            $this->dispatch(
                'show-notification',
                'Select at least 2 products to use bulk action',
                'warning'
            );
            return;
        }
        // Forward selected product IDs to bulk inventory modal
        $this->dispatch('callBulkAddToMyCatalogModal', ['productIds' => $selectedIds]);
    }

    #[On('bulk-add-success')]
    public function clearSelectedCheckboxes(): void
    {
        // Clear PowerGrid checkbox selections
        $this->checkboxValues = [];
        // 🔹 Tell PowerGrid JS to reset its internal bulk selection
        $this->dispatch('pg-clear-bulk-selection');
    }


    public function datasource(): Builder
    {
        $orgId = auth()->user()->organization_id;

        return Product::query()
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('organizations', 'products.organization_id', '=', 'organizations.id')
            ->leftJoin('suppliers', 'products.product_supplier_id', '=', 'suppliers.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
            ->leftJoin('product_units', function ($join) {
                $join->on('products.id', '=', 'product_units.product_id')
                    ->where('product_units.is_base_unit', '=', true);
            })
            ->leftJoin('units', 'product_units.unit_id', '=', 'units.id')
            ->where('products.organization_id', $orgId)
            ->where('products.is_active', true)
            ->select([
                'products.id',
                'products.product_code',
                'products.product_name',
                'products.product_description',
                'products.image',
                'products.cost',
                'products.price',
                'products.brand_id',
                'products.category_id',
                'products.subcategory_id',
                'products.product_supplier_id',
                'products.organization_id',
                'brands.brand_name',
                'categories.category_name',
                'subcategories.subcategory',
                'suppliers.supplier_name',
                'units.unit_name',
                'organizations.name as organization_name'
            ])
            ->orderBy('products.product_name', 'asc');
    }

    public function relationSearch(): array
    {
        return [
            'brand' => ['brand_name'],
            'categories' => ['category_name'],
            'supplier' => ['supplier_name'],
            'subcategory' => ['subcategory']
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()->add('id')
            ->add('image', function ($item) {
                if (str_starts_with($item->image, 'http')) {
                    $fullImageUrl = $item->image;
                } else {
                    $images = json_decode($item->image, true);
                    $imagePath = is_array($images) && !empty($images) ? $images[0] : $item->image;
                    $fullImageUrl = asset('storage/' . $imagePath);
                }

                return '<div onclick="openImageModal(\'' . $fullImageUrl . '\')" class="cursor-pointer">
                <img class="w-10 h-10 rounded-md" src="' . $fullImageUrl . '">
            </div>';
            })
            ->add('product_name', function ($item) {
                return '<span
        class="underline cursor-pointer text-blue-600 hover:text-blue-800"
        onclick="openProductModal(\'' . e($item->id) . '\', \'catalog\')">'
                    . e($item->product_name) .
                    '</span>';
            })
            //adding export field for product name to avoid html in export
            ->add('product_name_export', fn($item) => $item->product_name)

            ->add('product_code')
            ->add('brand_name', function ($item) {
                return $item->brand_name;
            })
            ->add('unit_name', function ($item) {
                return $item->unit_name;
            })
            ->add('product_description')
            ->add('formatted_cost', function ($item) {
                $currency = session('currency', '$');
                return $currency . ' ' . number_format($item->cost, 2);
            })
            ->add('category_name', function ($item) {
                return $item->category_name;
            })
            ->add('subcategory', function ($item) {
                return $item->subcategory;
            })
            ->add('supplier_name', function ($item) {
                return $item->supplier_name;
            });
    }

    public function columns(): array
    {
        $cols = [
            Column::make('Image', 'image')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),
            Column::make('Product Code', 'product_code')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Product Name', 'product_name')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),
            // adding export column for product name to avoid html in export
            Column::make('Product Name', 'product_name_export')
                ->visibleInExport(true)
                ->hidden(),
            Column::make('Manufacturer', 'brand_name')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Units', 'unit_name')->searchable(),
            !auth()->user()->is_medical_rep ? Column::make('Cost', 'formatted_cost')->searchable() : null,
            Column::make('Category', 'category_name')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Sub-category', 'subcategory')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Supplier', 'supplier_name')->searchable(),
            Column::make('Description', 'product_description')
                ->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->hidden(),
            Column::action('Action'),
        ];

        return array_filter($cols);
    }

    public function filters(): array
    {
        $orgId = auth()->user()->organization_id;
        return [
            Filter::inputText('product_name')->placeholder('Name')->operators(['contains']),
            Filter::inputText('product_code')->placeholder('Code')->operators(['contains']),
            Filter::select('supplier_name', 'products.product_supplier_id')
                ->dataSource(
                    Supplier::whereHas('products', function ($query) use ($orgId) {
                        $query->where('organization_id', $orgId)
                            ->where('is_active', true);
                    })
                        ->where('is_active', true)
                        ->orderBy('supplier_name', 'asc')
                        ->get()
                )
                ->optionLabel('supplier_name')
                ->optionValue('id'),
            Filter::select('brand_name', 'products.brand_id')
                ->dataSource(collection: Brand::where('brand_is_active', true)->where('organization_id', auth()->user()->organization_id)
                    ->orderBy('brand_name', 'asc')->get())
                ->optionLabel('brand_name')
                ->optionValue('id'),
            Filter::select('category_name', 'products.category_id')
                ->dataSource(
                    Category::whereHas('products', function ($query) use ($orgId) {
                        $query->where('organization_id', $orgId)
                            ->where('is_active', true);
                    })
                        ->where('organization_id', $orgId)
                        ->where('is_active', true)
                        ->orderBy('category_name', 'asc')
                        ->get()
                )
                ->optionLabel('category_name')
                ->optionValue('id'),

            Filter::select('subcategory', 'products.subcategory_id')
                ->depends(['category_name'])
                ->dataSource(function ($depends) use ($orgId) {
                    // Base query for subcategories that have products
                    $query = Subcategory::query()
                        ->whereHas('products', function ($query) use ($orgId) {
                        $query->where('organization_id', $orgId)
                            ->where('is_active', true);
                    })
                        ->where('is_active', true);

                    // If a category is selected, filter subcategories by that category
                    if (!empty($depends['category_name'])) {
                        $query->where('category_id', $depends['category_name']);
                    }

                    return $query->orderBy('subcategory', 'asc')->get();
                })
                ->optionLabel('subcategory')
                ->optionValue('id'),
        ];
    }

    /**
     * Clear subcategory filter when category changes
     */
    public function updatedFiltersCategoryName($value): void
    {
        // Clear subcategory filter when category changes
        if (isset($this->filters['subcategory'])) {
            unset($this->filters['subcategory']);
        }

        // Optional: Force refresh of the filters
        $this->dispatch('pg:eventRefresh-default');
    }

    /**
     * Alternative approach using updatedFilters for broader control
     */
    public function updatedFilters($value, $key): void
    {
        // If category filter was changed, clear subcategory filter
        if ($key === 'category_name' && isset($this->filters['subcategory'])) {
            unset($this->filters['subcategory']);
            $this->dispatch('pg:eventRefresh-default');
        }
    }

    public function actions(Product $row): array
    {
        return [
            Button::add('add-to-mycatalog')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>')
                ->id()
                ->class('inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 transition ease-in-out duration-150')
                ->tooltip('Add to My Inventory')
                ->dispatch('openAddToMyCatalogModal', ['productId' => $row->id])
        ];
    }

    public function actionRules($row): array
    {
        return [
            Rule::button('add-to-cart')->when(fn() => auth()->user()->is_medical_rep)->hide(),
        ];
    }
}