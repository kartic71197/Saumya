<?php

namespace App\Livewire\Tables\Admin;

use App\Models\Category;
use App\Models\Location;
use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Log;

final class InventoryList extends PowerGridComponent
{
    public string $tableName = 'admin-inventory-table';
    public bool $showFilters = true;
    use WithExport;

    public $selectedOrganization = null;
    public $selectedLocation = null;

    protected $listeners = [
        'inventoryFilterChanged' => 'updateFilters',
    ];

    public function updateFilters($orgId = null, $locId = null)
    {
        $this->selectedOrganization = $orgId;
        $this->selectedLocation = $locId;

        $this->resetPage();

    }

    public function setUp(): array
    {
        $this->showCheckBox();
        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header(),
            PowerGrid::footer()->showPerPage(50)->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = Mycatalog::query()
            ->join('products', 'products.id', '=', 'mycatalogs.product_id')
            ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
            ->join('organizations', 'organizations.id', '=', 'locations.org_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->join('product_units', function ($join) {
                $join->on('product_units.product_id', '=', 'products.id')
                    ->where('product_units.is_base_unit', 1);
            })
            ->join('units', 'units.id', '=', 'product_units.unit_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('subcategories', 'subcategories.id', '=', 'products.subcategory_id')
            ->where('locations.is_active', true)
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->select(
                'mycatalogs.location_id',
                'products.id as product_id',
                'products.product_code as id',
                'products.product_code',
                'products.product_name',
                'products.image as product_image',
                'products.cost',
                'units.unit_name as base_unit_name',
                'suppliers.supplier_name',
                'categories.category_name',
                'subcategories.subcategory',
                \DB::raw('SUM(mycatalogs.total_quantity) as total_qty')
            )
            ->groupBy(
                'products.product_code',
                'products.product_name',
                'products.image',
                'products.cost',
                'units.unit_name',
                'suppliers.supplier_name',
                'categories.category_name',
                'subcategories.subcategory'
            );

        // Optional filter
        if ($this->selectedOrganization) {
            Log::info('Filtering by organization:', ['org_id' => $this->selectedOrganization]);
            $query->where('organizations.id', $this->selectedOrganization);
        }

        return $query;
    }



    public function relationSearch(): array
    {
        return [
            'product' => ['product_name'],
            'location' => ['name'],
            'organization' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('product_image', function ($item) {
                $image = $item->product_image;
                if (!str_starts_with($image, 'http')) {
                    $images = json_decode($image, true);
                    $imagePath = is_array($images) && !empty($images) ? $images[0] : $image;
                    $image = asset('storage/' . $imagePath);
                }
                return '<img src="' . e($image) . '" class="w-10 h-10 rounded-md"/>';
            })
            ->add('product_code')
            ->add('product_name', function ($item) {
                return '<span class="underline cursor-pointer text-blue-600 hover:text-blue-800'
                    . ($item->is_sample ? ' text-green-600' : '')
                    . '" onclick="openProductModal(\'' . e($item->product_id) . '\', \'inventory\', \''
                    . e($this->selectedLocation) . '\')">'
                    . e($item->product_name) . '</span>';
            })
            ->add('product_name_export', fn($item) => e($item->product_name))
            ->add('supplier_name')
            ->add('category_name', function ($item) {
                return $item->category_name ?? 'N/A';
            })
            ->add('subcategory', function ($item) {
                return $item->subcategory ?? 'N/A';
            })
            ->add('organization_name')
            // ->add('location_name')
            ->add('total_quantity', fn($item) => $item->total_qty ?? 0)
            ->add('base_unit_name')
            ->add('cost_unit', fn($item) => '$' . number_format($item->cost ?? 0, 2))
            ->add('cost_unit_export', fn($item) => '$' . number_format($item->cost ?? 0, 2))
            ->add(
                'formatted_created_at',
                fn($item) =>
                date('Y-m-d H:i A', strtotime($item->created_at))
            )
            ->add('total_cost', function ($item) {
                return '<div>$' . number_format($item->total_qty * $item->cost, 2) . '</div>';
            })
            ->add('total_cost_export', fn($item): string => '$' . number_format($item->total_qty * $item->cost, 2));

    }

    public function columns(): array
    {
        return [
            Column::make('Image', 'product_image')->visibleInExport(false),
            Column::make('Code', 'product_code')->searchable(),
            Column::make('Product', 'product_name')
                ->visibleInExport(false)
                ->headerAttribute('', 'white-space: normal !important;')
                ->bodyAttribute('', 'white-space: normal !important;'),
            Column::make('Product', 'product_name_export')
                ->visibleInExport(true)
                ->hidden(),
            // Column::make('Organization', 'organization_name')->searchable(),
            // Column::make('Location', 'location_name')->searchable(),
            Column::make('Category', 'category_name')->searchable(),
            Column::make('Sub-Category', 'subcategory')->searchable(),
            Column::make('Supplier', 'supplier_name')->searchable(),
            Column::make('Available', 'total_quantity'),
            Column::make('Unit', 'base_unit_name'),
            Column::make('Cost', 'cost_unit')->visibleInExport(false),
            Column::make('Cost', 'cost_unit_export')
                ->visibleInExport(true)
                ->hidden(),
            Column::make('Total Cost', 'total_cost')->visibleInExport(false),
            Column::make('Total Cost', 'total_cost_export')
                ->visibleInExport(true)
                ->hidden(),

            // Column::make('Created At', 'formatted_created_at'),
        ];
    }

 public function filters(): array
{
    // Locations filtered by selected organization
    $locationQuery = Location::where('is_active', true)
        ->when($this->selectedOrganization, function ($query) {
            $query->where('org_id', $this->selectedOrganization);
        })
        ->orderBy('name');

    // Suppliers filtered by selected organization via Mycatalog and Locations
    $supplierQuery = Supplier::where('is_active', true)
        ->when($this->selectedOrganization, function ($query) {
            $query->whereIn('id', function ($subQuery) {
                $subQuery->select('products.product_supplier_id')
                    ->from('products')
                    ->join('mycatalogs', 'mycatalogs.product_id', '=', 'products.id')
                    ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
                    ->where('locations.org_id', $this->selectedOrganization)
                    ->distinct();
            });
        })
        ->orderBy('supplier_name');

    // Categories filtered by selected organization via Mycatalog and Locations
    $categoryQuery = Category::where('is_active', true)
        ->when($this->selectedOrganization, function ($query) {
            $query->whereIn('id', function ($subQuery) {
                $subQuery->select('products.category_id')
                    ->from('products')
                    ->join('mycatalogs', 'mycatalogs.product_id', '=', 'products.id')
                    ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
                    ->where('locations.org_id', $this->selectedOrganization)
                    ->distinct();
            });
        })
        ->selectRaw('MIN(id) as id, TRIM(LOWER(category_name)) as normalized_name, category_name')
        ->groupBy('normalized_name', 'category_name')
        ->orderBy('category_name');

    // Subcategories filtered by selected organization via Mycatalog and Locations
    $subcategoryQuery = Subcategory::where('is_active', true)
        ->when($this->selectedOrganization, function ($query) {
            $query->whereIn('id', function ($subQuery) {
                $subQuery->select('products.subcategory_id')
                    ->from('products')
                    ->join('mycatalogs', 'mycatalogs.product_id', '=', 'products.id')
                    ->join('locations', 'locations.id', '=', 'mycatalogs.location_id')
                    ->where('locations.org_id', $this->selectedOrganization)
                    ->distinct();
            });
        })
        ->orderBy('subcategory');

    return [
        Filter::inputText('product_name')->placeholder('Product Name')->operators(['contains']),
        Filter::inputText('product_code')->placeholder('Code')->operators(['contains']),

        Filter::select('location_name', 'locations.id')
            ->dataSource($locationQuery->get())
            ->optionLabel('name')
            ->optionValue('id'),

        Filter::select('supplier_name', 'products.product_supplier_id')
            ->dataSource($supplierQuery->get())
            ->optionLabel('supplier_name')
            ->optionValue('id'),

        Filter::select('category_name', 'products.category_id')
            ->dataSource($categoryQuery->get())
            ->optionLabel('category_name')
            ->optionValue('id'),

        Filter::select('subcategory', 'products.subcategory_id')
            ->dataSource($subcategoryQuery->get())
            ->optionLabel('subcategory')
            ->optionValue('id'),
    ];
}




}
