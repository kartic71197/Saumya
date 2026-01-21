<?php

namespace App\Livewire\Tables\User;

use App\Models\StockCount;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use App\Models\Cart;
use App\Models\Location;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\Supplier;
use PowerComponents\LivewirePowerGrid\Facades\Rule;

final class InventoryTransferList extends PowerGridComponent
{
    public string $tableName = 'inventory-transfer-list-aagmwq-table';

    public bool $showFilters = false;
    use WithExport;

    // public function boot(): void
    // {
    //     config(['livewire-powergrid.filter' => 'outside']);
    // }
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
        $query = StockCount::query()
            ->join('products', 'products.id', '=', 'stock_counts.product_id') // Join with products table
            ->join('locations', 'locations.id', '=', 'stock_counts.location_id') // Join with locations table
            ->join('product_units', function ($join) {
                $join->on('product_units.product_id', '=', 'products.id')
                    ->where('product_units.is_base_unit', 1);
            })
            ->join('units', 'units.id', '=', 'product_units.unit_id')
            ->where('products.organization_id', auth()->user()->organization_id)
            ->where('products.is_active', true)
            ->select(
                'stock_counts.*',
                'units.unit_name as unit_name',
                'products.product_name as product_name',
                'products.product_code as product_code',
                'locations.name as location_name' // Alias to avoid conflicts
            )
            ->where('stock_counts.on_hand_quantity', '>', '0'); // Ensure stock is available

        // Restrict location access for role_id = 3
        if (auth()->check() && auth()->user()->role_id == 3) {
            $query->where('stock_counts.location_id', auth()->user()->location_id);
        }

        return $query;
    }


    public function relationSearch(): array
    {
        return [
            'product' => ['product_name'],
            'location' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('product.product_code')
            ->add('product_name')
            ->add('location_name')
            ->add('batch_number')
            ->add('expiry_date')
            ->add('on_hand_quantity')
            ->add('par_quantity')
            ->add('alert_quantity')
            ->add('organization_id')
            ->add('unit_name')
            ->add('qty-unit',function($model){
                return $model->on_hand_quantity.' / '.$model->unit_name;
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Code', 'product_code'),
            Column::make('Product', 'product_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Lot#', 'batch_number')
                
                ->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Expiry Date', 'expiry_date')
                
                ->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            Column::make('Available', 'qty-unit', 'on_hand_quantity')
                
                ->searchable()
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),
            // Column::make('Base Unit', 'unit_name')
            //     
            //     ->searchable(),
            Column::make('Alert', 'alert_quantity')
                
                ->hidden()
                ->searchable(),
            Column::make('Location', 'location_name')
                
                ->searchable(),

            Column::make('Par', 'par_quantity')
                
                ->hidden()
                ->searchable(),
            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        $filters = [
            Filter::inputText('product_code')
                ->placeholder('Code')
                ->operators(['contains']),
            Filter::inputText('product_name')
                ->placeholder('Product')
                ->operators(['contains']),
            Filter::inputText('on_hand_quantity')
                ->placeholder('Available')
                ->operators(['contains']),
            Filter::inputText('unit_name')
                ->placeholder('Base Unit')
                ->operators(['contains']),
            Filter::inputText('batch_number')
                ->placeholder('Lot#')
                ->operators(['contains']),
            Filter::inputText('expiry_date')
                ->placeholder('Expiry Date')
                ->operators(['contains'])

        ];

        // Show location filter only if the user's role ID is 2
        if (auth()->check() && auth()->user()->role_id == 2) {
            $filters[] = Filter::select('location_name', 'location_id') // Match alias from `datasource()`
                ->dataSource(Location::where('org_id', auth()->user()->organization_id)->where('is_active', true)->get())
                ->optionLabel('name')
                ->optionValue('id');
        }

        return $filters;
    }

    public function actions(StockCount $row): array
    {
        return [
            Button::add('edit')
                ->slot('Transfer')
                ->id()
                ->class('inline-flex items-center justify-center px-4 py-2 bg-primary-md dark:bg-primary-md border border-transparent rounded-md font-semibold text-xs text-white dark:bg-primary-md uppercase tracking-widest hover:bg-primary-lt dark:hover:bg-primary-lt focus:bg-primary-dk dark:focus:bg-primary-dk active:bg-primary-dk dark:active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 min-w-[80px]')
                ->dispatch('transferProduct', ['rowId' => $row->id])
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
