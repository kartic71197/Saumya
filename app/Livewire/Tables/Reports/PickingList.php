<?php

namespace App\Livewire\Tables\Reports;

use App\Models\Location;
use App\Models\Organization;
use App\Models\User;
use App\Models\PickingDetailsModel;
use App\Models\PickingModel;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;


final class PickingList extends PowerGridComponent
{
    public string $tableName = 'picking-list-over1t-table';

    public $organizationId = null;

    public $locationId = null;

    // #[\Livewire\Attributes\Reactive]
    // public ?string $fromDate = null;

    // #[\Livewire\Attributes\Reactive]
    // public ?string $toDate = null;


    // Add organization filter listener
    protected $listeners = [
        'pickingOrganizationFilterChanged' => 'updateOrganization',
        'pickingLocationFilterChanged' => 'updateLocation'
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
        $query = PickingModel::query()
            ->with(['organization'])
            ->leftJoin('picking_details', 'pickings.id', '=', 'picking_details.picking_id')
            ->leftJoin('products', 'picking_details.product_id', '=', 'products.id')
            ->leftJoin('locations', 'pickings.location_id', '=', 'locations.id')
            ->leftJoin('users', 'pickings.user_id', '=', 'users.id')
            ->select(
                'pickings.id',
                'pickings.organization_id',
                'pickings.picking_number',
                'pickings.created_at',
                'locations.name as location_name',
                'users.name as user_name',
                'products.product_name',
                'picking_details.picking_unit as picking_unit',
                'picking_details.picking_quantity as picking_quantity',
                'picking_details.sub_total as total_price',
            );

        // Super Admin logic
        if (auth()->user()->role_id == 1) {
            // Apply organization filter if selected
            if ($this->organizationId) {
                $query->where('pickings.organization_id', $this->organizationId);
            }
            // Apply location filter if selected (for super admin)
            if ($this->locationId) {
                $query->where('pickings.location_id', $this->locationId);
            }
        } else {
            // Non-admin users: always filter by their organization
            $query->where('pickings.organization_id', auth()->user()->organization_id);

            // Apply location filter if selected (for non-admin)
            if ($this->locationId) {
                $query->where('pickings.location_id', $this->locationId);
            }
        }

        // Show recent picking first
        $query->orderBy('pickings.created_at', 'desc');

        return $query;
    }



    public function relationSearch(): array
    {
        return [
            'organization' => [
                'name',
            ],
            'locations' => [
                'name',
            ],

        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('pickings.created_at', function ($model) {
                return $model->created_at
                    ? \Carbon\Carbon::parse($model->created_at)->format('m-d-Y')
                    : null;
            })
            ->add('name', fn($item) => e($item->organization->name))
            ->add('picking_number')
            ->add('location_name', function ($model) {
                return $model->location_name ?? 'N/A'; // Fetch location name
            })
            ->add('product_name', function ($model) {
                return $model->pickingDetails->map(function ($detail) {
                    return optional($detail->product)->product_name;
                })->filter()->implode(', ') ?? 'N/A';
            })
            ->add('unit', function ($model) {
                return optional($model->pickingDetails)->pluck('picking_unit')->implode(', ') ?? 'N/A'; // Fetch unit
            })
            ->add('picking_quantity')
            ->add('total_price', function ($model) {
                return session('currency', '$') . ' ' . number_format($model->total_price, 2);
            })

            ->add('user_name', function ($model) {
                return $model->user_name ?? 'N/A'; // Fetch location name
            });
    }

    public function columns(): array
    {
        $columns = [
            Column::make('Created at', 'pickings.created_at')

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 220px; white-space: normal !important;'),
            Column::make('Picking number', 'picking_number')

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('User', 'user_name') // Show location name

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            // Column::make('Location', 'location_name') // Show location name

            //     ->searchable()
            //     ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
            //     ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            Column::make('Product Name', 'product_name') // Added Product Name

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            Column::make('Unit', 'unit')
                ->searchable()
                ->headerAttribute('max-w-sm', 'min-width: 0; max-width: 100px !important; white-space: normal !important;')
                ->bodyAttribute('max-w-sm', 'min-width: 0; max-width: 100px !important; white-space: normal !important;'),

            Column::make('Quantity', 'picking_quantity') // Added Quantity

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Total Price', 'total_price') // Corrected column for total price

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::action('Action')->hidden(),
        ];
        if (auth()->user()->role_id == 1) {
            $columns[] =  Column::make('Location', 'location_name') // Show location name

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;');
        }

        // if (auth()->user()->role_id == 1) {
        //     array_splice($columns, 3, 0, [
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
            Filter::inputText('picking_number')->placeholder('Picking Number')->operators(['contains']),

            Filter::boolean('is_active')->label('Active', 'Inactive'),



            Filter::datepicker('pickings.created_at'),

            // Filter::number('total_price', 'picking_details.sub_total')
            //     ->thousands('.')
            //     ->decimal(',')
            //     ->placeholder('lowest', 'highest'),

            Filter::inputText('total_price', 'picking_details.sub_total')->operators([
                'contains'
            ])
                ->placeholder('Total'),



            // Filter::inputText('picking_quantity')
            //     ->placeholder('Qty')->operators(['contains']),


            Filter::inputText('product_name')->placeholder('Product')->operators([
                'contains'
            ]),

            // 🔹 Location Filter
            Filter::inputText('location_name', 'locations.name')->operators([
                'contains'
            ]),

            // 🔹 Organization Filter
            Filter::select('name', 'pickings.organization_id')
                ->dataSource(Organization::orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('user_name', 'pickings.user_id')
                ->dataSource(
                    User::query()
                        ->when($this->organizationId, function ($q) {
                            // Only include users that appear in pickings for this org
                            $q->whereIn('id', function ($sub) {
                                $sub->select('user_id')
                                    ->from('pickings')
                                    ->where('organization_id', $this->organizationId)
                                    ->distinct();
                            });
                        })
                        ->when(!$this->organizationId, function ($q) {
                            $q->whereIn('id', function ($sub) {
                                $sub->select('user_id')
                                    ->from('pickings')
                                    ->distinct();
                            });
                        })
                        ->select('id', 'name')
                        ->orderBy('name')
                        ->get()
                        ->unique('name') 
                        ->values()
                )
                ->optionLabel('name')
                ->optionValue('id'),


            Filter::inputText('unit', 'picking_unit') // Match the field name and column name
                ->placeholder('Picking Unit')
                ->operators(['contains']),

        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(PickingModel $row): array
    {
        return [


            Button::add('view')
                ->slot('Details')
                ->id($row->picking_id)
                ->class('text-primary-dk font-semibold')
                ->dispatch('rowClicked', ['id' => $row->id])


            // Button::add('edit')
            //     ->slot('
            //         <span class="min-w-8 cursor-pointer" x-data="{ loading: false }" x-on:click="loading = true; $wire.call(\'startEdit\', ' . $row->id . '); setTimeout(() => loading = false, 1000)">
            //             <span x-show="!loading">Edit</span>
            //             <span x-show="loading" class="flex items-center justify-center">
            //                 <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            //                     <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            //                     <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
            //                 </svg>
            //             </span>
            //         </span>
            //     ')
            //     ->id('edit-btn-' . $row->id)
            //     ->class('inline-flex items-center px-4 py-2 bg-primary-md dark:bg-primary-md border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 hover:bg-primary-lt dark:hover:bg-primary-lt focus:bg-primary-dk dark:focus:bg-primary-dk active:bg-primary-dk dark:active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 dark:focus:ring-offset-gray-800')
            //     ->dispatch('edit-picking', ['rowId' => $row->id]) // Dispatch the edit-supplier event
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
