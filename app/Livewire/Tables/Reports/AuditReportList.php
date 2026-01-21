<?php

namespace App\Livewire\Tables\Reports;

use App\Models\AuditModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\Organization;
use App\Models\PickingModel;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;


final class AuditReportList extends PowerGridComponent
{
    public string $tableName = 'audit-report-list-zdy6gu-table';
    public $organizationId = null; // Add this
    public $locationId = null;


    // Add organization filter listener
    protected $listeners = [
        'auditOrganizationFilterChanged' => 'updateOrganization',
        'auditLocationFilterChanged' => 'updateLocation',
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
            //     ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = AuditModel::query()
            ->with(['user', 'user.location', 'organization'])
            ->orderBy('created_at', 'desc');

        // For all non-admins: filter organization
        if (auth()->user()->role_id != 1) {
            $query->where('organization_id', auth()->user()->organization_id);
        } else {
            // Admin selected organization
            if ($this->organizationId) {
                $query->where('organization_id', $this->organizationId);
            }
        }

        // ✅ For all users — apply location filter if selected
        if ($this->locationId) {
            $query->whereHas('user', function ($q) {
                $q->where('location_id', $this->locationId);
            });
        }
        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'user' => [
                'name',
            ],
            'user.location' => [
                'name',
            ],
            'organization' => [
                'name',
            ],
        ];
    }
public function formatJsonValues($values): string
{
    if (empty($values)) {
        return '';
    }

    $data = is_array($values) ? $values : json_decode($values, true);
    if (!is_array($data)) {
        return e((string) $values);
    }

    $formatted = [];

        foreach ($data as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $value = $value ?: 'N/A';


            // Handle unit changes in structured form
            if ($key === 'unit_changes' && is_array($value)) {
                $formatted[] = '<strong>Unit Changes:</strong>';
                foreach ($value as $unitChange) {
                    $unitId = $unitChange['unit_id'] ?? 'N/A';
                    $action = ucfirst($unitChange['action'] ?? 'Unknown');
                    $formatted[] = "<div style='margin-left:10px;'>• <strong>Unit ID:</strong> {$unitId} <strong>Action:</strong> {$action}</div>";

                    if (!empty($unitChange['changes'])) {
                        foreach ($unitChange['changes'] as $field => $diff) {
                            $formatted[] = "<div style='margin-left:20px;'>↳ <strong>{$field}</strong>: <span style='color:red;'>Old: {$diff['old']}</span> → <span style='color:green;'>New: {$diff['new']}</span></div>";
                    }
                }

                    if (!empty($unitChange['old']) && $action === 'Deleted') {
                        $formatted[] = "<div style='margin-left:20px; color:red;'>↳ Deleted Unit: " . json_encode($unitChange['old'], JSON_UNESCAPED_UNICODE) . "</div>";
                    }

                    if (!empty($unitChange['new']) && $action === 'Added') {
                        $formatted[] = "<div style='margin-left:20px; color:green;'>↳ Added Unit: " . json_encode($unitChange['new'], JSON_UNESCAPED_UNICODE) . "</div>";
                    }
                }
                continue;
            }

            // Product, location, and user references
            try {
                if ($key == 'product_id') {
                    $product = Product::find($value);
                    $formatted[] = $product
                        ? "<strong>Code:</strong> {$product->product_code}<br/><strong>Product:</strong> {$product->product_name}"
                        : "<strong>Product ID:</strong> {$value} (not found)";
            continue;
        }

                if ($key == 'location_id') {
                $location = Location::find($value);
                    $formatted[] = $location
                        ? "<strong>Location:</strong> {$location->name}"
                        : "<strong>Location ID:</strong> {$value} (not found)";
                continue;
            }
                if ($key == 'category_id') {
                $category = Category::find($value);
                    $formatted[] = $category
                        ? "<strong>Category:</strong> {$category->category_name}"
                        : "<strong>Category ID:</strong> {$value} (not found)";
                continue;
            }

                if ($key == 'user_id') {
                $user = User::find($value);
                    $formatted[] = $user
                        ? "<strong>User:</strong> {$user->name}"
                        : "<strong>User ID:</strong> {$value} (not found)";
                continue;
            }
        } catch (\Exception $e) {
            $formatted[] = "<strong>{$formattedKey}:</strong> " . e($value);
            continue;
        }

            // Default handling for all other keys
        if (is_array($value)) {
                $pretty = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $formatted[] = "<strong>{$formattedKey}:</strong><pre style='margin-left:10px;background:#f8f9fa;padding:5px;border-radius:4px;display:inline-block;white-space:pre-wrap;'>{$pretty}</pre>";
        } else {
                $formatted[] = "<strong>{$formattedKey}:</strong> " . e($value);
            }
        }

        return implode('<br>', $formatted);
}


    // For export (plain text)
    public function formatJsonValuesExport($values): string
    {
        if (empty($values)) {
            return '';
        }

        $data = is_array($values) ? $values : json_decode($values, true);
        if (!is_array($data)) {
            return (string) $values;
        }

        $formatted = [];

        foreach ($data as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $value = $value ?: 'N/A';

            // === Handle unit_changes (added/updated/deleted) ===
            if ($key === 'unit_changes' && is_array($value)) {
                $formatted[] = "Unit Changes:";
                foreach ($value as $unitChange) {
                    $unitId = $unitChange['unit_id'] ?? 'N/A';
                    $action = ucfirst($unitChange['action'] ?? 'Unknown');
                    $formatted[] = " - Unit ID: {$unitId}, Action: {$action}";

                    if (!empty($unitChange['changes'])) {
                        foreach ($unitChange['changes'] as $field => $diff) {
                            $formatted[] = "   ↳ {$field}: Old={$diff['old']} → New={$diff['new']}";
                        }
                    }

                    if (!empty($unitChange['old']) && $action === 'Deleted') {
                        $formatted[] = "   ↳ Deleted Unit: " . json_encode($unitChange['old'], JSON_UNESCAPED_UNICODE);
                    }

                    if (!empty($unitChange['new']) && $action === 'Added') {
                        $formatted[] = "   ↳ Added Unit: " . json_encode($unitChange['new'], JSON_UNESCAPED_UNICODE);
                    }
                }
                continue;
            }

            // === Handle product/location/user references ===
            try {
                if ($key == 'product_id') {
                    $product = Product::find($value);
                    $formatted[] = $product
                        ? "Code: {$product->product_code}, Product: {$product->product_name}"
                        : "Product ID: {$value} (not found)";
                    continue;
                }

                if ($key == 'location_id') {
                    $location = Location::find($value);
                    $formatted[] = $location
                        ? "Location: {$location->name}"
                        : "Location ID: {$value} (not found)";
                    continue;
                }

                if ($key == 'user_id') {
                    $user = User::find($value);
                    $formatted[] = $user
                        ? "User: {$user->name}"
                        : "User ID: {$value} (not found)";
                    continue;
                }
            } catch (\Exception $e) {
                $formatted[] = "{$formattedKey}: {$value}";
                continue;
            }

            // === Default: handle scalar or nested array safely ===
            if (is_array($value)) {
                $formatted[] = "{$formattedKey}: " . json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $formatted[] = "{$formattedKey}: {$value}";
            }
        }

        return implode(' | ', $formatted);
    }


    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('user_name', fn($item) => e($item->user->name ?? 'N/A'))
            ->add('user_location_name', fn($item) => e($item->user->location->name ?? 'N/A'))
            ->add('organization_name', fn($item) => e($item->organization->name ?? 'N/A'))
            ->add('event')
            ->add('auditable_type')
            ->add('auditable_id', function ($item) {
                try {
                    switch ($item->auditable_type) {
                        case 'User':
                            $user = User::find($item->auditable_id);
                            return $user ? $user->name : 'User Not Found';

                        case 'Products':
                            $product = Product::find($item->auditable_id);
                            return $product ? "{$product->product_name} ({$product->product_code})" : 'Product Not Found';

                        case 'Inventory':
                            $product = Product::where('product_code', $item->auditable_id)->first();
                            return $product ? $product->product_name : 'Product Not Found';

                        default:
                            return $item->auditable_id; // fallback
                    }
                } catch (\Exception $e) {
                    return $item->auditable_id;
                }
            })
            ->add('old_values_display', fn($model) => $this->formatJsonValues($model->old_values))
            ->add('new_values_display', fn($model) => $this->formatJsonValues($model->new_values))
            ->add('old_values_export', fn($model) => $this->formatJsonValuesExport($model->old_values))
            ->add('new_values_export', fn($model) => $this->formatJsonValuesExport($model->new_values))
            ->add('created_at_formatted', function ($model) {
                return $model->created_at
                    ? \Carbon\Carbon::parse($model->created_at)->format('m-d-Y') : '';
            })
            ->add('created_at_export', function ($model) {
                return $model->created_at
                    ? \Carbon\Carbon::parse($model->created_at)->format('m-d-Y') : null;
            });
    }
    // private function formatRelatedModelData($model)
    // {
    //     if ($model->auditable_type == 'Picking') {
    //         return PickingModel::find($model->auditable_id)->pluck('picking_number')->first();
    //     }
    //     if ($model->auditable_type == 'Purchase Order') {
    //         return PurchaseOrder::find($model->auditable_id)->pluck('purchase_order_number')->first();
    //     }
    //     $className = class_basename(get_class($model));
    //     return "{$className} #{$model->id}";
    // }

    public function columns(): array
    {
        $columns = [
            Column::make('Created at', 'created_at_formatted')

                ->searchable()
                ->visibleInExport(false),

            Column::make('Created at', 'created_at_export')

                ->searchable()
                ->visibleInExport(true)
                ->hidden(),

            Column::make('User', 'user_name')

                ->searchable(),

            Column::make('Location', 'user_location_name')

                ->searchable()
                ->hidden(),

            Column::make('Event', 'event')

                ->searchable(),

            Column::make('Module', 'auditable_type')

                ->searchable(),

            Column::make('Reference', 'auditable_id')

                ->searchable(),

            Column::make('Old values', 'old_values_display')

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('New values', 'new_values_display')

                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->visibleInExport(false),

            Column::make('Old values', 'old_values_export')

                ->searchable()
                ->visibleInExport(true)
                ->hidden(),

            Column::make('New values', 'new_values_export')

                ->searchable()
                ->visibleInExport(true)
                ->hidden(),

            Column::action('Action')->hidden(),
        ];

        // if (auth()->user()->role_id == 1) {
        //     array_splice($columns, 2, 0, [
        //         Column::make('Practices', 'organization_name')

        //             ->searchable()
        //             ->bodyAttribute('class', 'w-12 text-xs'),
        //     ]);
        // }

        return $columns;
    }

    public function filters(): array
{
    $auth = auth()->user();

    // Base audit query
    $auditQuery = AuditModel::query();

    // Non-admin → restrict to user's org
    if ($auth->role_id != 1) {
        $auditQuery->where('organization_id', $auth->organization_id);
    }
    // In case of Admin AND organization selected, filter by org
    elseif ($this->organizationId) {
        $auditQuery->where('organization_id', $this->organizationId);
    }

    // Location filter
    if ($this->locationId) {
        $auditQuery->whereHas('user', function ($q) {
            $q->where('location_id', $this->locationId);
        });
    }

    // Get user IDs present in this audit query
    $userIdsInAudit = $auditQuery->pluck('user_id')->unique();

    // Get users based on those IDs
    $allUsers = User::whereIn('id', $userIdsInAudit)
        ->orderBy('name')
        ->get()
        ->unique(fn($u) => strtolower(trim($u->name))) // unique names
        ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
        ->values();

    return [
        Filter::datepicker('created_at_formatted', 'created_at'),
        Filter::select('user_name', 'user_id')
            ->dataSource($allUsers)
            ->optionLabel('name')
            ->optionValue('id'),

        Filter::inputText('auditable_id')->placeholder('Reference')->operators([
            'contains',
        ]),

        Filter::select('organization_name', 'organization_id')
            ->dataSource(Organization::orderBy('name', 'asc')->get())
            ->optionLabel('name')
            ->optionValue('id'),

        Filter::inputText('event')->placeholder('Event')->operators([
            'contains',
        ]),
        Filter::inputText('auditable_type')->placeholder('Module')->operators([
            'contains',
        ]),
    ];
}

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(AuditModel $row): array
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
