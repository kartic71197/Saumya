<?php

namespace App\Livewire\Tables\Inventory;

use App\Models\CycleCount;
use App\Models\Category;
use App\Models\Location;
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
use Illuminate\Support\Facades\Log;

final class CycleCountList extends PowerGridComponent
{
    public string $tableName = 'cycle-count-list-ybwibg-table';
    public $selectedLocation = '';
    use WithExport;
    public $cycle_id;
    protected $listeners = [
        'cycleCountLocationChanged' => 'updateLocation',
        'cycleCountListUpdated' => '$refresh',
    ];

    public function updateLocation($locationId)
    {
        $this->selectedLocation = $locationId;
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
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $cycleId = $this->cycle_id;

        $query = CycleCount::query()
            ->select([
                'cycle_counts.*',
                'products.product_name as product_name',
                'products.product_code as product_code',
                'products.category_id as product_category_id',
                'products.subcategory_id as product_subcategory_id',
                'users.name as user_name',
                'cycles.organization_id',
                'cycles.location_id',
                'cycles.cycle_name',
            ])
            ->join('products', 'cycle_counts.product_id', '=', 'products.id')
            ->join('cycles', 'cycle_counts.cycle_id', '=', 'cycles.id')
            ->leftJoin('users', 'cycle_counts.user_id', '=', 'users.id')
            ->where('cycle_counts.cycle_id', $cycleId)
            ->where('products.is_active', true);

        if ($this->selectedLocation) {
            $query->where('cycles.location_id', $this->selectedLocation);
        }

        return $query;
    }


    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('product_code')
            ->add('product_name', function ($row) {
            return '<div class="break-words whitespace-normal min-w-0 max-w-sm">' . ($row->product_name ?? '—') . '</div>';
        })
            ->add('user_name')
            ->add('category_name', function ($row) {
                $categoryName = '—';
                if ($row->product->category) {
                    $categoryName = $row->product->category->category_name;
                }
                return '<span class="bg-indigo-100 text-indigo-800 dark:text-blue-300 py-0.5 px-1.5 text-xs font-medium rounded-full border">' . $categoryName . '</span>';
            })
            ->add('subcategory_name', function ($row) {
                if ($row->product->subcategory) {
                    return $row->product->subcategory->subcategory;
                }
                return '-';
            })
            ->add('expected_qty')
            ->add('counted_qty')
            ->add('location_id')
            ->add('variance_count_export', function ($row) {
            if ($row->counted_qty === null)
                return '—';
            if ($row->expected_qty <= 0)
                return (string) $row->counted_qty . '%';

            $percentage = (($row->counted_qty - $row->expected_qty) / $row->expected_qty) * 100;
            $roundedPercentage = round($percentage, 2);
            return abs($roundedPercentage) . '%';
        })

            ->add('variance_count', function ($row) {
                if ($row->counted_qty === null)
                    return '—';
                if ($row->expected_qty <= 0)        
                return '<span class="text-green-600 dark:text-green-400 font-semibold">' . (string) $row->counted_qty . '%</span>';

                $percentage = (($row->counted_qty - $row->expected_qty) / $row->expected_qty) * 100;
                $roundedPercentage = round($percentage, 2);
                $displayPercentage = abs($roundedPercentage) . '%';

                // Color based on positive or negative variance
                if ($percentage < 0) {
                    return '<span class="text-red-600 dark:text-red-400 font-semibold">' . $displayPercentage . '</span>';
                } else {
                    return '<span class="text-green-600 dark:text-green-400 font-semibold">' . $displayPercentage . '</span>';
                }
            })
            ->add('status_export', function ($row) {
                return ucfirst($row->status);
            })
            ->add('status', function ($row) {
                $status = $row->status;

                if ($status == 'completed') {
                    return '<span class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-green-800">Completed</span>';
                } elseif ($status == 'pending') {
                    return '<span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-yellow-800">Pending</span>';
                } else {
                    return '<span class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-gray-800">' . ucfirst($status) . '</span>';
                }
            })
            ->add('reassign_dropdown', function ($row) {
                // Only show dropdown if the task is still pending
                if ($row->status !== 'pending') {
                    return '<span class="text-gray-400 text-xs italic">—</span>';
                }

                // Get users for this organization
                $users = User::where('organization_id', $row->organization_id)
                    ->where('is_active', true)
                    ->get();

                $options = "<option value=''>Select User</option>";
                foreach ($users as $user) {
                    $selected = $user->id == $row->user_id ? 'selected' : '';
                    $options .= "<option value='{$user->id}' {$selected}>{$user->name}</option>";
                }

                return <<<HTML
                    <select wire:change="\$parent.reassignTask({$row->id}, \$event.target.value)"
                            class="text-xs border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded px-2 py-1 w-full">
                        {$options}
                        </select>
                HTML;
            })

            ->add('notes', function ($row) {
                $note = $row->notes ?? '—';
                $displayNote = strlen($note) > 50 ? substr($note, 0, 25) . '...' : $note;
                return '<div class="whitespace-normal break-words max-w-xs text-xs text-gray-700 dark:text-gray-300">'
                . e($displayNote) . '</div>';
                })
            ->add('notes_export', fn ($row) => $row->notes ?? '—');

    }
    public function columns(): array
    {
        return [

            Column::make('Product Code', 'product_code')
                
                ->searchable(),

            Column::make('Product Name', 'product_name')
                
                ->searchable(),

            Column::make('User', 'user_name')
                
                ->searchable(),
            
            Column::make('Notes', 'notes')
                
                 ->searchable()
                 ->bodyAttribute('whitespace-normal break-words align-top'),

            Column::make('Notes', 'notes_export')
                ->visibleInExport(true)
                ->hidden(),



            // Column::make('Category', 'category_name')
            //     
            //     ->searchable(),

            // Column::make('Subcategory', 'subcategory_name')
            //     
            //     ->searchable(),



            // Column::make('Location', 'location_name')
            //     
            //     ->searchable()
            //     ->hidden(),

            Column::make('Expected Qty', 'expected_qty')
                
                ->searchable(),

            Column::make('Counted Qty', 'counted_qty')
                
                ->searchable(),

            // Column::make('Variance', 'variance')
            //     
            //     ->hidden(),

            Column::make('Variance %', 'variance_count')
                
                ->searchable(),

            Column::make('Variance %', 'variance_count_export')
            ->visibleInExport(true)
            ->hidden(),

            Column::make('Status', 'status')
                
                ->searchable(),

            Column::make('Status', 'status_export')
            ->visibleInExport(true)
            ->hidden(),


            // Column::make('Created At', 'created_at_formatted', 'created_at')
            //     ->hidden(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('product_name')
                ->placeholder('Product Name')
                ->operators(['contains']),
            Filter::inputText('product_code')
                ->placeholder('Code')
                ->operators(['contains']),
            // Filter::inputText('count_id')
            //     ->placeholder('Cycle code')
            //     ->operators(['contains']),
            // Filter::datetimepicker('counted_at'),
            // Filter::select('location_name', 'cycle_counts.location_id')
            //     ->dataSource(Location::where('org_id', auth()->user()->organization_id)
            //         ->where('is_active', true)->get())
            //     ->optionLabel('name')
            //     ->optionValue('id'),
            Filter::select('user_name', 'cycle_counts.user_id')
                ->dataSource(
                    User::whereIn('id', function ($query) {
                        $query->select('user_id')
                            ->from('cycle_counts')
                            ->where('cycle_id', $this->cycle_id)
                            ->whereNotNull('user_id');
                    })
                        ->where('organization_id', auth()->user()->organization_id)
                        ->where('is_active', true)
                        ->get()
                )
                ->optionLabel('name')
                ->optionValue('id'),
            Filter::select('category_name', 'products.category_id')
                ->dataSource(
                    Category::whereIn('id', function ($query) {
                        $query->select('products.category_id')
                            ->from('cycle_counts')
                            ->join('products', 'cycle_counts.product_id', '=', 'products.id')
                            ->where('cycle_counts.cycle_id', $this->cycle_id)
                            ->where('products.is_active', true);
                    })
                    ->where('organization_id', auth()->user()->organization_id)
                    ->where('is_active', true)
                    ->get()
                )
                ->optionLabel('category_name')
                ->optionValue('id'),
            // Filter::inputText('subcategory_name')
            //     ->placeholder('Subcategory')
            //     ->operators(['contains']),
        ];
    }

public function actions($row): array
{
    return [
        Button::add('action')
            ->slot('Action')
            ->class('bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-xs transition')
           ->dispatch('openCycleActionModal', ['cycleCountId' => $row->id]),
    ];
}


    // public function actionRules($row): array
    // {
    //     return [
    //     ];
    // }
}