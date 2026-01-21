<?php

namespace App\Livewire\Tables\Reports;

use App\Models\InventoryTransfer;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use App\Models\Location;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class InventoryTransferReport extends PowerGridComponent
{
        public string $tableName = 'inventory-transfer-report-xf1yrk-table';

    public $organizationId = null;
    public $locationId = null;

    protected $listeners = [
        'inventoryTransferOrganizationFilterChanged' => 'updateOrganization',
        'inventoryTransferLocationFilterChanged' => 'updateLocation',
    ];

    public function updateOrganization($orgId)
    {
        $this->organizationId = $orgId;
        $this->locationId = null; // reset location
        $this->resetPage();
    }
    

    public function updateLocation($locId)
    {
        $this->locationId = $locId;
        $this->resetPage();
    }
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

    public function datasource(): Builder
    {
        $query = InventoryTransfer::query()
            ->with(['organization', 'fromLocation', 'toLocation'])
            ->leftJoin('users', 'users.id', '=', 'inventory_transfers.user_id')
            ->leftJoin('products', 'products.id', '=', 'inventory_transfers.product_id')
            ->leftJoin('units', 'units.id', '=', 'inventory_transfers.unit_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transfers.supplier_id')

            // Add these two lines
            ->leftJoin('locations as from_loc', 'from_loc.id', '=', 'inventory_transfers.from_location_id')
            ->leftJoin('locations as to_loc', 'to_loc.id', '=', 'inventory_transfers.to_location_id')

            ->select(
                'inventory_transfers.*',
                'users.name as user_name',
                'products.product_name as product_name',
                'units.unit_name as unit_name',
                'suppliers.supplier_name as supplier_name',
                'from_loc.name as from_location_name',
                'to_loc.name as to_location_name'
            );

        if (auth()->user()->role_id == 1) {
            if ($this->organizationId) {
                $query->where('inventory_transfers.organization_id', $this->organizationId);
            }
            if ($this->locationId) {
                $query->where('inventory_transfers.from_location_id', $this->locationId)
                      ->orWhere('inventory_transfers.to_location_id', $this->locationId);
            }
        } else {
            $query->where('inventory_transfers.organization_id', auth()->user()->organization_id);
            if ($this->locationId) {
                $query->where('inventory_transfers.from_location_id', $this->locationId)
                      ->orWhere('inventory_transfers.to_location_id', $this->locationId);
            }
        }

        return $query->orderBy('inventory_transfers.created_at', 'desc');
    }



    public function relationSearch(): array
    {
        return [
            'product' => [
                'product_name'
            ],
            'unit' => ['unit_name'],
            'supplier' => ['supplier_name'],
            'organization' => ['name'],
            'user' => ['name'],
            'fromLocation' => ['name'],
            'toLocation' => ['name'],
        ];
    }

    public function searchableFields(): array
{
    return [
        'user_name' => 'users.name',
        'unit_name' => 'units.unit_name', 
        'product_name' => 'products.product_name',
        'supplier_name' => 'suppliers.supplier_name',
        'organization_name' => 'organizations.name',
    ];
}

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('organization_name', fn($item) => e($item->organization->name ?? 'N/A'))
            ->add('reference_number')
            ->add('product_id')
            ->add('product_name')
            ->add('quantity')
            ->add('unit_id')
            ->add('unit_name')
            ->add('from_location_id')
            ->add('from_location_name', fn($item) => e($item->fromLocation->name ?? 'N/A'))
            ->add('to_location_id')
            ->add('to_location_name', fn($item) => e($item->toLocation->name ?? 'N/A'))
            ->add('supplier_id')
            ->add('supplier_name')
            ->add('organization_id')
            ->add('user_name')
            ->add('inventory_transfers.created_at', function ($model) {
                return $model->created_at
                    ? \Carbon\Carbon::parse($model->created_at)->format('m-d-Y')
                    : '';
            })
            ;
    }

    public function columns(): array
    {
        $columns = [
           
            Column::make('Created at', 'inventory_transfers.created_at')
                
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;'),

            Column::make('Transfer number', 'reference_number')

                ->searchable(),
            Column::make('Product', 'product_name')

                ->searchable(),


            Column::make('Transfered Qty', 'quantity')

                ->searchable(),

            Column::make('Unit ', 'unit_name')

                ->searchable(),

            Column::make('From', 'from_location_name', 'fromLocation.from_location_name')

                ->searchable(),

            Column::make('To', 'to_location_name')

                ->searchable(),

            Column::make('User', 'user_name')

                ->searchable(),

            Column::action('Action')->hidden()
        ];
        // if (auth()->user()->role_id == 1) {
        //     array_splice($columns, 2, 0, [

        //         Column::make('Practices', 'organization_name')
        //                                 ->searchable()
        //             ->bodyAttribute('class', 'w-12 text-xs'),
        //     ]);
        // }

        return $columns;
    }

    public function filters(): array
    {
        return [
            Filter::inputText('reference_number')
                ->placeholder('Transfer number')
                ->operators(['contains']),

            Filter::datepicker('inventory_transfers.created_at'),


            Filter::inputText('unit_name')
                ->placeholder('Unit')
                ->operators(['contains']),

            Filter::inputText('product_name')
                ->placeholder('Product')
                ->operators(['contains']),

            Filter::inputText('user_name', 'users.name')
                ->placeholder('User')
                ->operators(['contains']),

            Filter::select('organization_name', 'inventory_transfers.organization_id')
                ->dataSource(Organization::where('is_active', true)->orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            // Filter::select('from_location_name', 'from_location_id')
            //     ->dataSource(
            //         Location::where('org_id', auth()->user()->organization_id)
            //             ->when(auth()->user()->role_id == 1, function ($query) {
            //                 return $query->orWhereNotNull('org_id');
            //             })
            //             ->get()
            //     )
            //     ->optionLabel('name')
            //     ->optionValue('id'),

            Filter::inputText('from_location_name', 'from_loc.name')
                ->operators(['contains'])
                ->placeholder('From Location'),

                Filter::inputText('to_location_name', 'to_loc.name')
                ->operators(['contains'])
                ->placeholder('To Location'),

            // Filter::select('to_location_name', 'to_location_id')
            //     ->dataSource(
            //         Location::where('org_id', auth()->user()->organization_id)
            //             ->when(auth()->user()->role_id == 1, function ($query) {
            //                 return $query->orWhereNotNull('org_id');
            //             })
            //             ->get()
            //     )
            //     ->optionLabel('name')
            //     ->optionValue('id'),
            Filter::inputText('quantity')
                ->placeholder('Qty')
                ->operators(['contains']),

        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(InventoryTransfer $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: ' . $row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
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
