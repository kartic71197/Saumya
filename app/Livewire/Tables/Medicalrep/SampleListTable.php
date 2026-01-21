<?php

namespace App\Livewire\Tables\Medicalrep;

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
use Livewire\Attributes\On;

final class SampleListTable extends PowerGridComponent
{
    public string $tableName = 'sample-list-table-q9rj1n-table';

   public bool $showFilters = true;
    use WithExport;

 

    public function setUp(): array
    {
        return [
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        return [];
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
            ->leftJoin('product_units', function($join) {
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
            ]);
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
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Product Code', 'product_code')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Product Name', 'product_name')->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
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
            Column::action('Action')->hidden(),
        ];
        
        return array_filter($cols);
    }

    public function filters(): array
    {
        return [
            Filter::inputText('product_name')->placeholder('Name')->operators(['contains']),
            Filter::inputText('product_code')->placeholder('Code')->operators(['contains']),
            Filter::select('supplier_name', 'products.product_supplier_id')
                ->dataSource(
                    Supplier::whereHas('products', function ($query) {
                        $query->where('organization_id', auth()->user()->organization_id)
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
                ->dataSource(Category::where('categories.organization_id', auth()->user()->organization_id)->where('is_active', true)
                    ->orderBy('category_name', 'asc')
                    ->get())
                ->optionLabel('category_name')
                ->optionValue('id'),
            Filter::select('subcategory', 'products.subcategory_id')
                ->dataSource(Subcategory::where('is_active', true)
                    ->orderBy('subcategory', 'asc')
                    ->get())
                ->optionLabel('subcategory')
                ->optionValue('id'),
        ];
    }

    public function actions(Product $row): array
    {
        return [
            Button::add('add-to-cart')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>')
                ->id()
                ->class('inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 transition ease-in-out duration-150')
                ->tooltip('Add to Cart')
                ->dispatch('openAddToCartModal', ['productId' => $row->id])
        ];
    }

    public function actionRules($row): array
    {
        return [
            Rule::button('add-to-cart')->when(fn() => auth()->user()->is_medical_rep)->hide(),
        ];
    }

   
}