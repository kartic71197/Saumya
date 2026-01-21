<?php

namespace App\Livewire\Tables\Admin;


use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class PurchaseOrdersList extends PowerGridComponent
{
    public string $tableName = 'purchase-orders-list-od97ha-table';
    public $selectedOrganization = '';

    /**
     * ADDED: Event listeners for organization filter changes
     * Listens for 'purchaseOrganizationFilterChanged' event from the parent component
     */

    protected $listeners = [
        'purchaseOrganizationFilterChanged' => 'updateOrganization',
    ];

    use WithExport;

    /**
     * Handle organization filter change event
     * Called when parent component dispatches 'purchaseOrganizationFilterChanged'
     * The organization ID to filter by
     */

    public function updateOrganization($orgId)
    {
        $this->selectedOrganization = $orgId;
        $this->resetPage(); // Reset pagination when organization changes
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

            // PowerGrid::header()
            //     ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = PurchaseOrder::with([
            'purchaseSupplier:id,supplier_name,supplier_email',
            'purchaseLocation:id,name',
            'organization:id,name'
        ])
            ->whereHas('purchaseSupplier')
            //  ONLY non-completed POs
            ->whereIn('status', ['ordered', 'partial'])
            ->orderBy('id', 'desc');

        // Applying organization filter if selected
        if (!empty($this->selectedOrganization)) {
            $query->where('organization_id', $this->selectedOrganization);
        }

        return $query;
    }


    public function relationSearch(): array
    {
        return [
            'purchaseSupplier' => [
                'supplier_name',
            ],
            'purchaseLocation' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('created_at_formatted', fn(PurchaseOrder $model) => Carbon::parse($model->created_at)->format('m/d/Y'))
            ->add('purchase_order_number')
            ->add('supplier_id')
            ->add('supplier_name', fn($purchaseOrder) => e($purchaseOrder?->purchaseSupplier?->supplier_name))
            ->add('organization_id')
            ->add('location_id')
            ->add('location_name', fn($purchaseOrder) => e($purchaseOrder->purchaseLocation->name))
            ->add('organization_name', fn($purchaseOrder) => e($purchaseOrder->organization->name))
            ->add('bill_to_location_id')
            ->add('ship_to_location_id')
            ->add('status', function ($row) {
                $status = $row->status;

                if ($status == 'completed') {
                    $status = 'received';
                }

                // Apply Tailwind classes based on the status
                if ($status == 'received') {
                    return '<span class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-green-800">Received</span>';
                } elseif ($status == 'ordered') {
                    return '<span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 py-0.5 px-1.5 text-xs  rounded-full border-2 border-blue-800">Ordered</span>';
                } elseif ($status == 'pending') {
                    return '<span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-yellow-800">Pending</span>';
                } elseif ($status == 'partial') {
                    return '<span class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-orange-800">Partial</span>';
                } elseif ($status == 'canceled') {
                    return '<span class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-red-800">Canceled</span>';
                }

                return $status; // default in case status doesn't match
            })

            ->add('status_export', function ($row) {
                $status = strtolower($row->status);
                if ($status === 'completed')
                    $status = 'received';
                return ucfirst($status);
            })

            ->add('total')
            ->add('created_at')
            ->add('updated_at')
            ->add('created_by', fn($purchaseOrder) => e(optional($purchaseOrder->createdUser)->name ?? 'N/A'))
            ->add('updated_by')
            ->add('invoice')
            ->add('note')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Date', 'created_at_formatted')
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Purchase order', 'purchase_order_number')
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            // Column::make('Practice', 'organization_name')->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
            //     ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Location', 'location_name')
                ->searchable()->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            // Column::make('Status', 'status')
            //     
            //     ->searchable()->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
            //     ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Supplier', 'supplier_name')->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Status', 'status')
                ->visibleInExport(false)
                ->searchable()->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Status', 'status_export')
                ->visibleInExport(true)
                ->hidden(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        // Supplier list based on selected organization from filter
        $suppliers = Supplier::query()
            ->whereIn('id', function ($query) {
                $query->select('supplier_id')
                    ->from('purchase_orders')
                    ->when($this->selectedOrganization, function ($q) {
                        $q->where('organization_id', $this->selectedOrganization);
                    });
            })
            ->orderBy('supplier_name', 'asc')
            ->get();


        // Filter locations based on selected organization from filter
        $locations = Location::query()
            ->where('is_active', true)
            ->when($this->selectedOrganization, function ($query) {
                $query->where('org_id', $this->selectedOrganization);
            })
            ->orderBy('name', 'asc')
            ->get();

        // Filter statuses based on selected organization
        $statuses = PurchaseOrder::query()
            ->when($this->selectedOrganization, function ($query) {
                $query->where('organization_id', $this->selectedOrganization);
            })
            // Allow only non-completed statuses
            ->whereIn('status', ['ordered', 'partial'])
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->get()
            ->map(fn($item) => [
                'value' => $item->status,
                'label' => ucfirst($item->status)
            ])
            ->toArray();

        return [
            Filter::datepicker('created_at_formatted', 'created_at'),
            Filter::inputText('purchase_order_number')
                ->placeholder('Purchase Order No')
                ->operators(['contains']),
            Filter::select('supplier_name', 'supplier_id')
                ->dataSource($suppliers)
                ->optionLabel('supplier_name')
                ->optionValue('id'),

            Filter::select('location_name', 'location_id')
                ->dataSource($locations)
                ->optionLabel('name')
                ->optionValue('id'),

           Filter::select('status', 'status')
            ->dataSource($statuses)
            ->optionValue('value')
            ->optionLabel('label'),


            Filter::select('organization_name', 'organization_id')
                ->dataSource(
                    Organization::where('is_active', true)
                        ->orderBy('name', 'asc')
                        ->get()
                )
                ->optionLabel('name')
                ->optionValue('id'),
            
            ];
    }

    public function actions(PurchaseOrder $row): array
    {
        return [
            Button::add('view')
                ->slot('Details')
                ->id()
                ->class('text-primary-dk font-semibold')
                ->dispatch('rowClicked', ['id' => $row->id]),
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
