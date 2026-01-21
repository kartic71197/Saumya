<?php

namespace App\Livewire\Tables\Organization\Picking;

use App\Models\Category;
use App\Models\Location;
use App\Models\StockCount;
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
use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\Supplier;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use Illuminate\View\View;



final class PickingInventoryList extends PowerGridComponent
{
    public string $tableName = 'picking-inventory-list-q7yfsl-table';
    public bool $showFilters = false;

    public $selectedLocation = '';
    public $showSampleProducts = false;

    public function noDataLabel(): string|View
    {
        // return 'We could not find any dish matching your search.';
        return view('no_data.pickingdata');
    }

    protected $listeners = ['showSamples' => 'updateShowSampleProducts', 'pickingLocationChanged' => 'updateLocation'];

    use WithExport;
    public function boot(): void
    {
        // config(['livewire-powergrid.filter' => 'outside']);
        $this->selectedLocation = auth()->user()->location_id ?? null;
    }

    public function updateLocation($locationId)
    {
        $this->selectedLocation = $locationId;
        $this->resetPage();
    }

    public function updateShowSampleProducts($showSampleProducts)
    {
        $this->showSampleProducts = $showSampleProducts;
        $this->resetPage();
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
                ->withoutLoading()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id')
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id')
            ->join('suppliers', 'suppliers.id', '=', 'products.product_supplier_id')
            ->join('product_units', function ($join) {
                $join->on('product_units.product_id', '=', 'products.id')
                    ->where('product_units.is_base_unit', 1);
            })
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->join('units', 'units.id', '=', 'product_units.unit_id')
            ->where('suppliers.is_active', true)
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('stock_counts.on_hand_quantity', '>', 0)
            // ->where(function ($q) {
            //     $q->where('categories.category_name', '!=', 'biological')
            //         ->orWhereNull('categories.category_name');
            // })
            ->select(
                'stock_counts.*',
                'products.id as product_id',
                'products.is_sample as is_sample',
                'products.image as product_image',
                'products.product_name as product_name',
                'products.product_code as product_code',
                'locations.name as location_name',
                'units.unit_name as base_unit_name',
                'suppliers.supplier_name as supplier_name',
                'suppliers.supplier_slug as supplier_slug',
                'categories.category_name as category_name'
            );


        if ($this->selectedLocation) {
            $query->where('stock_counts.location_id', $this->selectedLocation);
        }
        if ($this->showSampleProducts) {
            $query->where('products.is_sample', 1);
        }
        // $query->where(function ($q) {
        //     $q->whereNull('stock_counts.expiry_date')
        //         ->orWhere('stock_counts.expiry_date', '>=', now());
        // });

        $query->orderBy('products.product_name', 'asc')
            ->orderByRaw("CASE WHEN stock_counts.expiry_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('stock_counts.expiry_date', 'asc');



        return $query;
    }

    public function relationSearch(): array
    {
        return [
            // 'product' => [
            //     'product_name',
            // ],
            // 'location' => [
            //     'name',
            // ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('product_code')
            ->add('base_unit_name')
            ->add('product_name', function ($item) {
                return '<span
        class="underline cursor-pointer text-blue-600 hover:text-blue-800' . ($item->is_sample ? ' text-green-600' : '') . '"
        onclick="openProductModal(\'' . e($item->product_id) . '\')">'
                    . e($item->product_name) .
                    '</span>';
            })
            ->add('product_name_export', function ($item) {
                return $item->product_name;
            })
            ->add('alert_par', function ($item) {
                return '<div class=' . ($item->on_hand_quantity < $item->alert_quantity ? 'text-red-600' : '') . '>' . $item->alert_quantity . '/' . $item->par_quantity . '</div>';
            })
            ->add('location_name')
            ->add('category_name')

            ->add('on_hand_quantity', function ($item) {
                return (int) abs($item->on_hand_quantity);
            })

            ->add('batch_number')
            ->add('expiry_date', function ($item) {
                return $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('m-d-Y') : '';
            })
            ->add('par_quantity')
            ->add('alert_quantity')
            ->add('organization_id')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Code', 'product_code')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Product', 'product_name')->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('Product', 'product_name_export')
                ->visibleInExport(true)
                ->hidden(),

            Column::make('Category', 'category_name')->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Available', 'on_hand_quantity')

                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Base Unit', 'base_unit_name')

                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Alert', 'alert_quantity')

                ->hidden()
                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Par', 'par_quantity')

                ->hidden()
                ->searchable(),
            Column::make('Batch/Lot', 'batch_number')

                ->searchable(),

            Column::make('Expiration', 'expiry_date')

                ->searchable(),

            Column::make('Location', 'location_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('product_name')
                ->placeholder('Name')
                ->operators(['contains']),

            Filter::inputText('product_code')
                ->placeholder('Code')
                ->operators(['contains']),

            Filter::inputText('batch_number')
                ->placeholder('Batch/Lot')
                ->operators(['contains']),

            Filter::select('location_name', 'stock_counts.location_id')
                ->dataSource(
                    Location::where('org_id', auth()->user()->organization_id)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                )
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('category_name', 'categories.id') // Ensure correct field reference
                ->dataSource(Category::where('organization_id', auth()->user()->organization_id)->where('is_active', true)->orderBy('category_name')->get())
                ->optionLabel('category_name')
                ->optionValue('id'),
        ];
    }


    public function actions(StockCount $row): array
    {
        return [
            Button::add('edit')
                ->slot('Pick')
                ->id()
                ->class('inline-flex items-center justify-center w-24 px-4 py-2 bg-green-500 dark:bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 dark:hover:bg-green-500 focus:bg-green-500 dark:focus:bg-green-500 active:bg-green-500 dark:active:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150')
                ->dispatch('pickProduct', ['rowId' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}