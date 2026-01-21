<?php

namespace App\Livewire\Tables;

use App\Models\FieldRep;
use App\Models\Organization;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class FieldRepsList extends PowerGridComponent
{
    public string $tableName = 'field-reps-list-fftt-table';

    public bool $showFilters = true;

    use WithExport;

    // public function boot(): void
    // {
    //     config(['livewire-powergrid.filter' => 'outside']);
    // }

    /**
     * Configure table UI (export, header, footer)
     */
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

    /**
     * Main query for the table
     *
     * We JOIN suppliers & organizations so their names
     * are available directly for display and filtering.
     */
    public function datasource(): Builder
    {
    $query = FieldRep::query()
        ->leftJoin('suppliers', 'suppliers.id', '=', 'field_reps.supplier_id')
        ->leftJoin('organizations', 'organizations.id', '=', 'field_reps.organization_id')
        ->select(
            'field_reps.*',
            'suppliers.supplier_name',
            'organizations.name as organization_name'
        )
        ->where('field_reps.is_deleted', 0);

    //  Super Admin → all data
    if (auth()->user()->role_id == 1) {
        return $query;
    }

    //  Organization users → only their org
    return $query->where(
        'field_reps.organization_id',
        auth()->user()->organization_id
    );

    }


    /**
     * Enable searching on joined relationship fields
     */
    public function relationSearch(): array
    {
        return [
            'organization' => ['name'],
            'supplier' => ['supplier_name'],
        ];
    }


    /**
     * Map database fields for PowerGrid usage
     */
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('medrep_name')
            ->add('medrep_email')
            ->add('medrep_phone')
            ->add('supplier_id')
            ->add('organization_id')
            ->add('supplier_name')
            ->add('organization_name');
    }

    /**
     * Define visible table columns
     */
    public function columns(): array
{
    $columns = [
        Column::make('Name', 'medrep_name')->searchable(),
        Column::make('Email', 'medrep_email')->searchable(),
        Column::make('Phone', 'medrep_phone'),
        Column::make('Supplier', 'supplier_name'),
    ];

    //  Only Super Admin sees Practice column
    if (auth()->user()->role_id == 1) {
        $columns[] = Column::make('Practice', 'organization_name');
    }

    $columns[] = Column::action('Action');

    return $columns;
}


    /**
     * Table filters
     *
     * Dropdowns only show suppliers/organizations
     * that actually exist in field_reps table.
     */
    public function filters(): array
    {
        return [
            Filter::inputText('medrep_name')->operators(['contains']),
            Filter::inputText('medrep_email')->operators(['contains']),

            Filter::select('organization_name', 'field_reps.organization_id')
                ->dataSource(
                    Organization::query()
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('field_reps')
                                ->whereColumn('field_reps.organization_id', 'organizations.id')
                                ->where('field_reps.is_deleted', false);
                        })
                        ->orderBy('name', 'asc')
                        ->get()
                )
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('supplier_name', 'field_reps.supplier_id')
                ->dataSource(
                    Supplier::query()
                        ->where('suppliers.is_active', true)
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('field_reps')
                                ->whereColumn('field_reps.supplier_id', 'suppliers.id')
                                ->where('field_reps.is_deleted', false);
                        })
                        ->orderBy('supplier_name', 'asc')
                        ->get()
                )
                ->optionLabel('supplier_name')
                ->optionValue('id'),

        ];
    }

    public function actions(FieldRep $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->dispatch('edit-field-rep', ['rowId' => $row->id])
                ->class('bg-primary-md text-white px-3 py-1 rounded'),
        ];
    }
}

