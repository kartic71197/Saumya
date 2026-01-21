<?php

namespace App\Livewire\Tables\Reports;

use App\Models\Edi810;
use App\Models\PurchaseOrder;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Log;

final class InvoiceList extends PowerGridComponent
{
    public string $tableName = 'invoice-list-91tpu0-table';
    use WithExport;

    public $organizationId = null;
    public $locationId = null;

    // Fix: Listen to the correct event name and method
    protected $listeners = [
        'invoiceFilterChanged' => 'updateOrganization',
        'invoiceLocationFilterChanged' => 'updateLocation'
    ];

    public function updateOrganization($orgId)
    {
        $this->organizationId = $orgId;
        // Reset table pagination when organization changes
        $this->resetPage();
    }

    public function updateLocation($locId)
    {
        $this->locationId = $locId;
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
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = PurchaseOrder::query()
            ->whereHas('edi810s')
            ->with('edi810s', 'purchaseLocation')
            ->select('purchase_orders.*')
            ->leftJoin('locations', 'purchase_orders.location_id', '=', 'locations.id');

        if (auth()->user()->role_id == 1) {
            if ($this->organizationId) {
                $query->where('purchase_orders.organization_id', $this->organizationId);
            }
        } else {
            // Role >= 2 → filter by user organization
            $query->where('purchase_orders.organization_id', auth()->user()->organization_id);

            // Apply location filter if selected
            if ($this->locationId) {
                $query->where('purchase_orders.location_id', $this->locationId);
            }
        }

        $query->orderBy('purchase_orders.created_at', 'desc');
        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'edi810s' => [
                'invoice_number',
                'scac',
                'invoice_date',
                'total_amount_due',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('purchase_order_number')
            ->add('invoice_number', fn($po) => optional($po->edi810s->first())->invoice_number)
            ->add('scac', fn($po) => optional($po->edi810s->first())->scac)
            ->add(
                'invoice_date',
                fn($po) =>
                optional($po->edi810s->first())->invoice_date
                ? Carbon::parse($po->edi810s->first()->invoice_date)
                    ->format(session('date_format', 'm-d-Y') . ' H:i:s')
                : null
            )
            ->add('total_amount_due', fn($po) => session('currency', '$') .
                (optional($po->edi810s->first())->total_amount_due / 100))
            ->add('carrier_info')
            ->add('transportation_method')
            ->add('reference_qualifier')
            ->add('view', fn($po) => '<div onClick="openInvoice(\'' . $po->id . '\')" class="cursor-pointer text-blue-500 underline">View</div>')
            ->add('organization_name', fn($po) => $po->organization->name ?? '-')
            ->add('location_name', fn($po) => $po->purchaseLocation->name ?? '-');

        return $fields;
    }

    public function columns(): array
    {
        $columns = [];

        // Base columns
        $columns[] = Column::make('Id', 'id')->hidden();
        $columns[] = Column::make('Invoice Date', 'invoice_date');
        $columns[] = Column::make('Invoice Number', 'invoice_number');
        $columns[] = Column::make('Purchase Order', 'purchase_order_number')->searchable();
        $columns[] = Column::make('Total', 'total_amount_due');
        $columns[] = Column::make('Carrier', 'scac');

        // Only for super admin role
        if (auth()->user()->role_id == 1) {
            // $columns[] = Column::make('Practices', field: 'organization_name')->searchable();
            $columns[] = Column::make('Location', 'location_name')->searchable();
        }

        // View + Action
        $columns[] = Column::make('View', 'view')->visibleInExport(false);
        $columns[] = Column::action('Action')->hidden();

        return $columns;
    }

    public function filters(): array
    {
        return [
            Filter::inputText('purchase_order_number')
                ->operators(['contains'])
                ->placeholder('Search Purchase Order'),

            Filter::inputText('location_name', 'locations.name')
                ->operators(['contains'])
                ->placeholder('Search Location'),

            Filter::select('organization_name', 'purchase_orders.organization_id')
                ->dataSource(Organization::where('is_active', true)->orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(PurchaseOrder $row): array
    {
        return [
            Button::add('view')
                ->slot('View')
                ->id()
                ->class('inline-flex items-center justify-center px-4 py-2 min-w-[80px] text-xs font-semibold text-white uppercase tracking-widest rounded-md border border-transparent bg-primary-md hover:bg-primary-lt focus:bg-primary-dk active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 transition duration-150 ease-in-out')
                ->dispatch('purchase-receive-view-modal', [
                    'rowId' => $row->id,
                ]),
        ];
    }
}