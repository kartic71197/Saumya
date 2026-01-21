<?php

namespace App\Livewire\Admin\Reports;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class EdiReportList extends PowerGridComponent
{
    public string $tableName = 'edi-report-list-table';
    public $selectedOrganization = null;

    protected $listeners = ['ediFilterChanged' => 'updateOrganization'];

    public function updateOrganization($orgId)
    {
        $this->selectedOrganization = $orgId;
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
            //added for toggling columns    
            PowerGrid::header()
                ->showToggleColumns(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = PurchaseOrder::query()
            ->join('organizations', 'purchase_orders.organization_id', '=', 'organizations.id')
            ->join('locations', 'purchase_orders.location_id', '=', 'locations.id')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->where('suppliers.int_type', 'EDI')
            ->withCount(['edi810s', 'edi855s', 'edi856s'])
            ->select(
                'purchase_orders.*',
                'organizations.name as organization_name',
                'locations.name as location_name',
                'suppliers.supplier_name as supplier_name'
            );

        // Apply organization filter if selected
        if ($this->selectedOrganization) {
            $query->where('purchase_orders.organization_id', $this->selectedOrganization);
        }

        // Order by most recent purchase orders
        return $query->orderByDesc('purchase_orders.created_at');
    }
    

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('supplier_name')
            ->add('purchase_order_number')
            ->add('organization_name')
            ->add('location_name')

            // ✅ Fix: Handle null values safely for export and full selection
            // Previously we did: $row->created_at->format('d M Y')
            // This caused error when exporting all rows because some rows may not have created_at set
            // (PowerGrid sometimes returns stdClass or partially hydrated rows in full export)
            ->add('created_at_formatted', function ($row) {
                return $row->created_at
                    ? \Carbon\Carbon::parse($row->created_at)->format('d M Y')
                    : '';
            })

            ->add('edi810_status', function ($row) {
                $po = PurchaseOrder::withCount('edi810s')->find($row->id);
                $status = $po->edi810s_count > 0 ? 'Received' : 'Pending';
                $class = $status === 'Received'
                    ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full'
                    : 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full';
                return '<span class="' . $class . '">' . $status . '</span>';
            })
            ->add('edi855_status', function ($row) {
                $po = PurchaseOrder::withCount('edi855s')->find($row->id);
                $status = $po->edi855s_count > 0 ? 'Received' : 'Pending';
                $class = $status === 'Received'
                    ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full'
                    : 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full';
                return '<span class="' . $class . '">' . $status . '</span>';
            })
            ->add('edi856_status', function ($row) {
                $po = PurchaseOrder::withCount('edi856s')->find($row->id);
                $status = $po->edi856s_count > 0 ? 'Received' : 'Pending';
                $class = $status === 'Received'
                    ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full'
                    : 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full';
                return '<span class="' . $class . '">' . $status . '</span>';
            })

            // Clean text-only fields for export
            ->add('edi810_status_clean', function ($row) {
                return $row->edi810s_count > 0 ? 'Received' : 'Pending';
            })
            ->add('edi855_status_clean', function ($row) {
                return $row->edi855s_count > 0 ? 'Received' : 'Pending';
            })
            ->add('edi856_status_clean', function ($row) {
                return $row->edi856s_count > 0 ? 'Received' : 'Pending';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Purchase Order', 'purchase_order_number')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->searchable(),

            // Column::make('Practice', 'organization_name')
            //     ->headerAttribute('', ' white-space: normal !important;')
            //     ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Location', 'location_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Supplier', 'supplier_name')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('855', 'edi855_status')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('855 Status', 'edi855_status_clean')
                ->visibleInExport(true)
                ->hidden(),

            Column::make('856', 'edi856_status')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('856 Status', 'edi856_status_clean')
                ->visibleInExport(true)
                ->hidden(),

            Column::make('810', 'edi810_status')
                ->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('810 Status', 'edi810_status_clean')
                ->visibleInExport(true)
                ->hidden(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('purchase_order_number', 'purchase_orders.purchase_order_number')
                ->operators(['contains']),

            Filter::datepicker('created_at', 'purchase_orders.created_at'),
            Filter::inputText('organization_name')->operators(['contains']),
            Filter::inputText('location_name', 'locations.name')->operators(['contains']),
            Filter::inputText('supplier_name', 'suppliers.supplier_name')->operators(['contains']),
        ];
    }
}
