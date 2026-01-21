<?php

namespace App\Livewire\Organization\Pos;

use App\Models\Pos;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class PosList extends PowerGridComponent
{
    public string $tableName = 'pos-list-fxysnk-table';
    public $organizationId = null;
    public $locationId = null;

    // Add filter listeners
    protected $listeners = [
        'posOrganizationFilterChanged' => 'updateOrganization',
        'posLocationFilterChanged' => 'updateLocation'
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
            PowerGrid::header()->showToggleColumns(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    /**
     * Main datasource for POS listing
     * - Replaced Eloquent-only relationship access with SQL JOINs.
     * - Selected related table fields using SQL aliases
     *   (e.g. organizations.name AS organization_name).
     *
     * WHY:
     * - PowerGrid search and filters work at SQL level.
     * - Computed fields (like $row->organization->name) do NOT exist in SQL,
     *   which caused "Unknown column" errors during search.
     * - Using JOIN + alias makes the columns real and searchable.
     */
    public function datasource(): Builder
    {
        $query = Pos::query()
            ->leftJoin('organizations', 'organizations.id', '=', 'pos.organization_id')
            ->leftJoin('locations', 'locations.id', '=', 'pos.location_id')
            ->leftJoin('customers', 'customers.id', '=', 'pos.customer_id')
            ->leftJoin('users', 'users.id', '=', 'pos.created_by')
            ->select(
                'pos.*',
                'organizations.name as organization_name',
                'locations.name as location_name',
                'customers.customer_name as customer_name',
                'users.name as created_by_name'
            );

        // Apply role-based filtering
        $user = auth()->user();

        if ($user->role_id == 1) {
            // Super Admin: can see all, filter by organization if selected
            if ($this->organizationId) {
                $query->where('pos.organization_id', $this->organizationId);
            }
            // Apply location filter if selected (for super admin)
            if ($this->locationId) {
                $query->where('pos.location_id', $this->locationId);
            }
        } else {
            // Non-admin users: always filter by their organization
            $query->where('pos.organization_id', $user->organization_id);
            // Apply location filter if selected (for non-admin)
            if ($this->locationId) {
                $query->where('pos.location_id', $this->locationId);
            }
        }

        //arrange by latest sale date and if same sale date then by created at
        return $query->orderByDesc('pos.sale_date')->orderByDesc('pos.created_at');

    }

    /**
     * Relation search
     *
     * WHAT CHANGED:
     * - Disabled relationSearch().
     *
     * WHY:
     * - Related fields are already joined and available as SQL aliases.
     * - PowerGrid can search them directly without relationship-based queries.
     */
    public function relationSearch(): array
    {
        return [];
    }



    /**
     * Map fields for PowerGrid
     * - Removed closure-based fields for organization, location, customer, and creator.
     * - These values are now provided directly by SQL aliases from JOINs.
     * - This avoids computed-only fields that cannot be searched or filtered.
     */
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('organization_name')
            ->add('location_name')
            ->add('customer_name')
            ->add('payment_method')
            ->add('total_amount')
            ->add('paid_amount')
            ->add('change_amount')
            ->add('created_by_name')
            ->add(
                'sale_date_formatted',
                fn($row) =>
                Carbon::parse($row->sale_date)->format('d M Y')
            )
            ->add(
                'created_at_formatted',
                fn($row) =>
                Carbon::parse($row->created_at)->format('d M Y H:i')
            );
    }


    public function columns(): array
    {
        $user = auth()->user();
        $role = $user->role_id;

        $columns = [];

        /* -----------------------------------------
           SHOW Organization column ONLY if role >= 2
           (SuperAdmin role_id = 1 cannot see it)
        ------------------------------------------*/
        if ($role >= 2) {
            $columns[] = Column::make('Practices', 'organization_name')
                ->sortable()
                ->searchable()
                ->headerAttribute('', 'white-space: normal !important;')
                ->bodyAttribute('', 'white-space: normal !important;');
        }

        /* -----------------------------------------
           SHOW Location column ONLY if role = 1
           (Others cannot see it)
        ------------------------------------------*/
        if ($role == 1) {
            $columns[] = Column::make('Location', 'location_name')
                ->sortable()
                ->searchable()
                ->headerAttribute('', 'white-space: normal !important;')
                ->bodyAttribute('', 'white-space: normal !important;');
        }

        // Common columns visible to all roles
        $columns = array_merge($columns, [
            Column::make('Customer', 'customer_name')
                ->sortable()
                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Payment Method', 'payment_method')
                ->sortable()
                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Total Amount', 'total_amount')
                ->sortable()
                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Created By', 'created_by_name')
                ->sortable()
                ->searchable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::make('Sale Date', 'sale_date_formatted', 'sale_date')
                ->sortable()->headerAttribute('', ' white-space: normal !important;')
                ->bodyAttribute('', ' white-space: normal !important;'),

            Column::action('Action'),
        ]);

        return $columns;
    }

    public function filters(): array
    {
        return [
            Filter::inputText('organization_name', 'organizations.name')->operators(['contains']),
            Filter::inputText('location_name', 'locations.name')->operators(['contains']),
            Filter::inputText('customer_name', 'customers.customer_name')->operators(['contains']),
            Filter::inputText('payment_method', 'pos.payment_method')->operators(['contains']),
            Filter::inputText('total_amount', 'pos.total_amount')->operators(['contains']),
            Filter::inputText('paid_amount', 'pos.paid_amount')->operators(['contains']),
            Filter::inputText('change_amount', 'pos.change_amount')->operators(['contains']),
            Filter::inputText('created_by_name', 'users.name')->operators(['contains']),
        ];
    }


    public function actions(Pos $row): array
    {
        return [
            Button::add('view')
                ->slot('View')
                ->id()
                ->class('px-3 py-1 bg-blue-600 text-white rounded-md')
                ->dispatch('view', ['id' => $row->id])
        ];
    }

}