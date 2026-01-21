<?php

namespace App\Livewire\Tables\Reports;

use App\Models\Organization;
use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use App\Models\Location;
use App\Models\Supplier;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Livewire\WithPagination;

final class PurchaseReportList extends PowerGridComponent
{
    public string $tableName = 'purchase-report-list-slevws-table';

    public $organizationId = null;
    public $locationId = null; 

    
    protected $listeners = [
        'organizationFilterChanged' => 'updateOrganization',
        'locationFilterChanged' => 'updateLocation' 
    ];
    public function updateOrganization($orgId)
    {
        $this->organizationId = $orgId;
        $this->resetPage(); // Reset pagination when organization changes
    }

    public function updateLocation($locId)
    {
        $this->locationId = $locId;
        $this->resetPage();
    }


    // #[\Livewire\Attributes\Reactive]
    // public ?string $fromDate = null;

    // #[\Livewire\Attributes\Reactive]
    // public ?string $toDate = null;

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
            // PowerGrid::header()
            //     ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {

        // logger('From Date: ' . $this->fromDate . ' | To Date: ' . $this->toDate);   
        $query = PurchaseOrder::with(['purchaseSupplier', 'purchaseLocation', 'organization'])
            ->select(
                'purchase_orders.*',
                'purchase_orders.created_at as purchase_order_created_at',
            )
            ->orderBy('purchase_orders.id', 'desc');
        if (auth()->user()->role_id == 1) {
            // Apply organization filter if selected
            if ($this->organizationId) {
                $query->where('purchase_orders.organization_id', $this->organizationId);
            }
            // Apply location filter if selected (for super admin)
            if ($this->locationId) {
                $query->where('purchase_orders.location_id', $this->locationId);
            }
        } else {
            // Non-admin users: always filter by their organization
            $query->where('purchase_orders.organization_id', auth()->user()->organization_id);
            
            // Apply location filter if selected (for non-admin)
            if ($this->locationId) {
                $query->where('purchase_orders.location_id', $this->locationId);
            }
            
            // Show only completed or canceled orders for non-admins
            // $query->whereIn('purchase_orders.status', ['completed', 'canceled']);
        }
        // if ($this->fromDate) {
        //     $query->whereDate('purchase_orders.created_at', '>=', $this->fromDate);
        // }

        // if ($this->toDate) {
        //     $query->whereDate('purchase_orders.created_at', '<=', $this->toDate);
        // }
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
            'organization' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('purchase_order_number', fn($purchaseOrder) => e($purchaseOrder->purchase_order_number))
            ->add('supplier_id')
            ->add('name', fn($purchaseOrder) => e($purchaseOrder->organization->name))
            ->add('supplier_name', fn($purchaseOrder) => e($purchaseOrder->purchaseSupplier?->supplier_name))
            ->add('location_name', fn($purchaseOrder) => e($purchaseOrder->purchaseLocation->name))
            ->add('organization_id')
            ->add('location_id')
            ->add('product_name')
            ->add('quantity')
            ->add('bill_to_location_id')
            ->add('ship_to_location_id')
            ->add('status', function ($purchaseOrder) {
                $status = $purchaseOrder->status;
                if ($status == 'completed') {
                    $status = 'received';
                }
                return ucfirst($status);
            })
            ->add('status_display', function ($purchaseOrder) {
                $status = $purchaseOrder->status;
                if ($status == 'completed') {
                    $status = 'received';
                }

                $classes = match (strtolower($status)) {
                    'received' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 border-green-800',
                    'canceled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border-red-800',
                    'partial' => 'bg-yellow-100 text-yellow-800 dark:bg-red-900 dark:text-yellow-300 border-yellow-800',
                    'ordered' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border-blue-800',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-gray-500',
                };

                return '<span class="' . $classes . ' py-0.5 px-1.5 text-xs rounded-full border-2 font-semibold">'
                    . ucfirst($status) .
                    '</span>';
            })

            ->add('total', function ($model) {
                return session('currency', '$') . ' ' . number_format($model->total ?? 0, 2);
            })
            ->add('updated_at')
            ->add('purchase_orders.created_at', function ($model) {
                return $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('m-d-Y') : '';
            })
            ->add('updated_by')
            ->add('invoice')
            ->add('note')
            ->add('ack_view', function ($purchaseOrder) {
                if (!empty($purchaseOrder->edi855s) && count($purchaseOrder->edi855s) > 0) {
                    return str_replace('PO-', 'ACK-', $purchaseOrder->purchase_order_number);
                }
                return '-';
            })
            ->add('ack_view_display', function ($purchaseOrder) {
                if (!empty($purchaseOrder->edi855s) && count($purchaseOrder->edi855s) > 0) {
                    $po = e($purchaseOrder->purchase_order_number);
                    $ackNumber = str_replace('PO-', 'ACK-', $purchaseOrder->purchase_order_number);
                    return <<<HTML
        <button wire:click="\$dispatch('previewEdi855', {poNumber: '{$po}'})"
            class="text-nowrap inline-flex items-center px-3 py-1 text-blue-700 hover:text-blue-900 underline font-semibold hover:bg-gray-50 transition">
            {$ackNumber}
        </button>
        HTML;
                }
                return '-';
            });
    }
    public function columns(): array
    {
        $columns = [
            Column::make('Created at', 'purchase_orders.created_at')

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            Column::make('Purchase Order', 'purchase_order_number')

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            Column::make('Supplier', 'supplier_name')
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            // Column::make('Location', 'location_name')
            //     ->bodyAttribute('class', 'w-16 text-xs '),

            Column::make('Status', 'status_display', 'status')
                ->searchable()

                ->bodyAttribute('class', 'w-12 text-xs')
                ->visibleInExport(false),

            Column::make('Status', 'status')
                ->searchable()

                ->visibleInExport(true)
                ->hidden(),

            Column::make('Ack', 'ack_view_display')

                ->bodyAttribute('class', 'w-12 text-xs text-center')
                ->visibleInExport(false),

            Column::make('Ack', 'ack_view')

                ->visibleInExport(true)
                ->hidden(),

            Column::make('Total', 'total')

                ->searchable(),
            

            
        ];
        // if (auth()->user()->role_id == 1) {
        //     array_splice($columns, 2, 0, [
        //         Column::make('Practices', 'name')

        //             ->searchable()
        //             ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
        //             ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
        //     ]);
        // }
         if (auth()->user()->role_id == 1) {
            $columns[] = Column::make('Location', 'location_name')
                ->bodyAttribute('class', 'w-16 text-xs');
        };
        // Add Action column at the end
        $columns[] = Column::action('Action');

        return $columns;
    }


    public function filters(): array
{
    // Locations filtered by selected organization
    $locationQuery = Location::whereHas('purchaseOrders')
        ->when($this->organizationId, function ($query) {
            $query->where('org_id', $this->organizationId);
        })
        ->orderBy('name');

    // Suppliers filtered by selected organization via purchase orders
    $supplierQuery = Supplier::whereHas('purchaseOrders') 
        ->when($this->organizationId, function ($query) {
            $query->whereIn('id', function ($subQuery) {
                $subQuery->select('purchase_orders.supplier_id')
                    ->from('purchase_orders')
                    ->where('organization_id', $this->organizationId)
                    ->distinct();
            });
        })
        ->orderBy('supplier_name');

    // Organizations
    $organizationQuery = Organization::query()
        ->when($this->organizationId, function ($query) {
            $query->where('id', $this->organizationId);
        })
        ->orderBy('name');

    return [
        Filter::inputText('purchase_order_number')->placeholder('Purchase Order No')->operators(['contains']),
        Filter::inputText('product_name')->placeholder('Product')->operators(['contains']),
        Filter::inputText('quantity')->placeholder('Quantity')->operators(['contains']),
        Filter::select('status', 'status')
            ->dataSource([
                ['value' => 'completed', 'label' => 'Received'],
                ['value' => 'ordered', 'label' => 'Ordered'],
                ['value' => 'canceled', 'label' => 'Canceled'],
                ['value' => 'partial', 'label' => 'Partial'],
                ['value' => 'pending', 'label' => 'Pending'],
            ])
            ->optionLabel('label')
            ->optionValue('value'),
        Filter::inputText('product_code')->placeholder('Code')->operators(['contains']),

        Filter::select('supplier_name', 'supplier_id')
            ->dataSource($supplierQuery->get())
            ->optionLabel('supplier_name')
            ->optionValue('id')
            ,

        Filter::select('name', 'organization_id')
            ->dataSource($organizationQuery->get())
            ->optionLabel('name')
            ->optionValue('id'),

        Filter::select('location_name', 'location_id')
            ->dataSource($locationQuery->get())
            ->optionLabel('name')
            ->optionValue('id'),

        Filter::inputText('total', 'total')->operators(['contains'])
            ->placeholder('Total'),

        Filter::datepicker('purchase_orders.created_at', 'purchase_orders.created_at'),
    ];
}



    #[On('showViewModal')]
    public function showViewModal($rowId)
    {
        $this->dispatchBrowserEvent('showViewModal', ['rowId' => $rowId]);
    }
    public $visibleHistoryRows = [];

    public function toggleHistory($index)
    {
        if (in_array($index, $this->visibleHistoryRows)) {
            // Remove from visible array (hide)
            $this->visibleHistoryRows = array_filter($this->visibleHistoryRows, function ($item) use ($index) {
                return $item !== $index;
            });
        } else {
            // Add to visible array (show)
            $this->visibleHistoryRows[] = $index;
        }
    }

    public function isHistoryVisible($index)
    {
        return in_array($index, $this->visibleHistoryRows);
    }


    public function actions(PurchaseOrder $row): array
    {
        return [
            Button::add('view')
                ->slot('
                    View
                ')
                ->id()
                ->class('inline-flex items-center justify-center px-4 py-2 min-w-[80px] text-xs font-semibold text-white uppercase tracking-widest rounded-md border border-transparent bg-primary-md hover:bg-primary-lt focus:bg-primary-dk active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 transition duration-150 ease-in-out')
                ->dispatch('purchase-receive-view-modal', [
                    'rowId' => $row->id,
                ]),
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
