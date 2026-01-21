<?php

namespace App\Livewire\Tables\Reports;

use App\Models\InventoryAdjust;
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
use App\Models\PickingDetailsModel;
use App\Models\PickingModel;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class InventoryAdjustReport extends PowerGridComponent
{
    public string $tableName = 'inventory-adjust-list-cob6cj-table';

    public $organizationId = null; // Add this
    public $locationId = null;

    // Add organization filter listener
    protected $listeners = ['inventoryAdjustOrganizationFilterChanged' => 'updateOrganization',
'inventoryAdjustLocationFilterChanged' => 'updateLocation',
];

    public function updateOrganization($orgId)
    {
        $this->organizationId = $orgId;
        $this->locationId = null; 
        $this->resetPage(); // Reset pagination when organization changes
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
        $query =  InventoryAdjust::query()
            ->with(['organization'])
            ->leftJoin('users', 'users.id', '=', 'inventory_adjusts.user_id')
            ->leftJoin('products', 'products.id', '=', 'inventory_adjusts.product_id')
            ->leftJoin('units', 'units.id', '=', 'inventory_adjusts.unit_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_adjusts.supplier_id')
            ->select(
                'inventory_adjusts.*',
                'users.name as user_name',
                'products.product_name as product_name',
                'units.unit_name as unit_name',
                'suppliers.supplier_name as supplier_name',
            );

        if (auth()->user()->role_id == 1) {
            if ($this->organizationId) {
                $query->where('inventory_adjusts.organization_id', $this->organizationId);
            }
            if ($this->locationId) {
                $query->where('inventory_adjusts.location_id', $this->locationId);
            }
        } else {
            // Non-admin: only their organization and optionally location
            $query->where('inventory_adjusts.organization_id', auth()->user()->organization_id);
            if ($this->locationId) {
                $query->where('inventory_adjusts.location_id', $this->locationId);
            }
        }
         $query->orderBy('inventory_adjusts.created_at', 'desc');

        return $query;

    }




    public function relationSearch(): array
    {
        return [
            'organization' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name', fn($item) => e($item->organization->name))
            ->add('reference_number')
            ->add('product_id')
            ->add('product_name')
            ->add('user_name')
            ->add('quantity')
            ->add('previous_quantity')
            ->add('new_quantity')
            ->add('unit_id')
            ->add('supplier_id')
            ->add('organization_id')
            ->add('user_id')
            ->add('inventory_adjusts.created_at', function ($model) {
                return $model->created_at
                ? \Carbon\Carbon::parse($model->created_at)->format('m-d-Y')
                : null;
            });
    }

    public function columns(): array
    {
        $columns = [

            Column::make('Created at', 'inventory_adjusts.created_at')
                
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;'),

            Column::make('Adjustment number', 'reference_number')
                
                ->searchable(),

            Column::make('Product', 'product_name')
                
                ->searchable()
                ->headerAttribute('', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('', 'min-width: 0; white-space: normal !important;'),

            Column::make('Unit', 'unit_id')
                
                ->searchable(),

            Column::make('Old Quantity', 'previous_quantity')
                
                ->searchable(),

            Column::make('New Quantity', 'new_quantity')
                
                ->searchable(),

            Column::make('Supplier id', 'supplier_id')
                
                ->searchable()
                ->hidden(),

            Column::make('Organization id', 'organization_id')
                
                ->searchable()
                ->hidden(),

            Column::make('User', 'user_name')
                
                ->searchable(),

            Column::action('Action')->hidden()
        ];
        // if (auth()->user()->role_id == 1) {
        //     array_splice($columns, 2, 0, [
        //         Column::make('Practices', 'name')
                    
        //             ->searchable()
        //             ->bodyAttribute('class', 'w-12 text-xs '),
        //     ]);
        // }
        

        return $columns;
    }

    public function filters(): array
    {
        return [
            Filter::inputText('reference_number')
                ->placeholder('Adjustment number')
                ->operators(['contains']),
            Filter::inputText('product_name')
                ->placeholder('Product')
                ->operators(['contains']),
            Filter::inputText('unit_id')
                ->placeholder('Unit')
                ->operators(['contains']),
            Filter::inputText('user_name', 'users.name')
                ->placeholder('User')
                ->operators([
                    'contains',
                ]),
                Filter::select('name', 'inventory_adjusts.organization_id')
                ->dataSource(Organization::where('is_active',true)->orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::inputText('previous_quantity')
                ->placeholder('Old Qty')
                ->operators(['contains']),
            Filter::inputText('new_quantity')
                ->placeholder('New Qty')
                ->operators(['contains']),
            Filter::datepicker('inventory_adjusts.created_at'),

        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(InventoryAdjust $row): array
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
