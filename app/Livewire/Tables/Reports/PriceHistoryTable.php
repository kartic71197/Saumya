<?php

namespace App\Livewire\Tables\Reports;

use App\Models\PriceHistory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;


final class PriceHistoryTable extends PowerGridComponent
{


    use WithExport;
    public string $tableName = 'price-history-table-nqmxpt-table';

    public $organizationId = null;

    public $locationId = null;


    // Add organization filter listener
    protected $listeners = [
        'priceHistoryOrganizationFilterChanged' => 'updateOrganization',
        'priceHistoryLocationFilterChanged' => 'updateLocation'
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
        return PriceHistory::query()
            ->select([
                'price_histories.id',
                'price_histories.product_id',
                'price_histories.cost as new_cost',
                'price_histories.created_at',
                'price_histories.changed_by',

                'products.product_name',
                'products.product_code',

                'users.name as user_name',

                // 👇 Previous cost (derived)
                DB::raw('(
                SELECT ph2.cost
                FROM price_histories ph2
                WHERE ph2.product_id = price_histories.product_id
                  AND ph2.created_at < price_histories.created_at
                ORDER BY ph2.created_at DESC
                LIMIT 1
            ) as previous_cost'),
            ])

            ->join('products', 'products.id', '=', 'price_histories.product_id')
            ->leftJoin('users', 'users.id', '=', 'price_histories.changed_by')

            // 🔥 Only latest record per product
            ->whereIn('price_histories.id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('price_histories')
                    ->groupBy('product_id');
            })

            ->when(
                $this->organizationId,
                fn($q) =>
                $q->where('products.organization_id', $this->organizationId)
            )

            ->when(
                $this->locationId,
                fn($q) =>
                $q->where('products.location_id', $this->locationId)
            )

            ->orderByDesc('price_histories.created_at');
    }



    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('product_code')
            ->add('product_name')

            ->add('previous_cost')
            ->add(
                'previous_cost_formatted',
                fn($row) =>
                $row->previous_cost !== null
                ? '$' . number_format($row->previous_cost, 2)
                : '—'
            )

            ->add('new_cost')
            ->add(
                'new_cost_formatted',
                fn($row) =>
                '$' . number_format($row->new_cost, 2)
            )

            ->add(
                'changed_on',
                fn($row) =>
                Carbon::parse($row->created_at)->format('m/d/Y H:i')
            )

            ->add(
                'changed_by_display',
                fn($row) =>
                $row->changed_by == 0
                ? 'System'
                : ($row->user_name ?? 'Unknown')
            );
    }



    public function columns(): array
    {
        return [
            Column::make('Code', 'product_code')
                ->sortable()
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;'),

            Column::make('Name', 'product_name')
                ->sortable()
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;'),

            Column::make('Previous Cost', 'previous_cost_formatted')
                ->headerAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;'),

            Column::make('New Cost', 'new_cost_formatted')
                ->sortable()
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;'),

            Column::make('Invoice date', 'changed_on')
                ->sortable()
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;'),

            Column::make('Changed By', 'changed_by_display')
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 110px; white-space: normal !important;'),

            Column::action('Action'),
        ];
    }



    public function filters(): array
    {
        return [
            Filter::inputText('product_name', 'products.product_name')->operators([
                'contains'
            ])
                ->placeholder('Search product'),

            Filter::inputText('product_code', 'products.product_code')->operators([
                'contains'
            ])
                ->placeholder('Search Code'),

            Filter::inputText('user_name', 'users.name')
                ->placeholder('Search changed by')->operators([
                        'contains'
                    ]),

            Filter::datepicker('effective_from'),

            Filter::datepicker('effective_to'),
        ];
    }

    // #[\Livewire\Attributes\On('viewPriceHistory')]
    // public function viewPriceHistory($productId)
    // {
    //     logger('viewPriceHistory called with productId: ' . $productId);
    //     $this->dispatch('open-price-history-modal', productId: $productId);
    // }


    public function actions(PriceHistory $row): array
    {
        return [
            Button::add('view-history')
                ->slot('View History')
                ->class('pg-btn-white')
                ->dispatch('viewCostHistory', [
                    'productId' => $row->product_id,
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