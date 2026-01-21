<?php

namespace App\Livewire\Tables\Inventory;

use App\Models\Cycle;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class CycleList extends PowerGridComponent
{
    use WithExport;
    public string $tableName = 'cycle-list-ybwibg-table';
    public $selectedLocation = '';
    protected $listeners = ['cycleCountLocationChanged' => 'updateLocation'];

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
        $query = Cycle::query()
            ->select([
                'cycles.*',
                'creator.name as created_by_name',
                'locations.name as location_name',
            ])
            ->leftJoin('users as creator', 'cycles.created_by', '=', 'creator.id')
            ->leftJoin('locations', 'cycles.location_id', '=', 'locations.id')
            ->where('cycles.organization_id', auth()->user()->organization_id);
        if ($this->selectedLocation) {
            $query->where('cycles.location_id', $this->selectedLocation);
        }
        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'location' => ['name'],
            'creator' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('cycle_code')
            ->add('cycle_name')
            ->add('created_by_name')
            ->add('location_name')
            ->add('schedule_date')
            ->add('schedule_date_formatted', fn(Cycle $cycle) =>
                $cycle->schedule_date ? Carbon::parse($cycle->schedule_date)->format('m-d-Y') : '-')
            // ->add('started_at')
            // ->add('started_at_formatted', fn (Cycle $cycle) => 
            //     $cycle->created_at ? Carbon::parse($cycle->started_at)->format('d-m-Y') : '-')
            ->add('ended_at')
            ->add('ended_at_formatted', fn (Cycle $cycle) =>
                $cycle->ended_at ? Carbon::parse($cycle->ended_at)->format('m-d-Y') : '-')
            ->add('status')
            ->add('status_export', function ($row) {
                return ucfirst($row->status);
            })
            ->add('status', function ($row) {
                $status = $row->status;
                if ($status == 'pending') {
                    return '<span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-yellow-800">Pending</span>';
                } elseif ($status == 'completed') {
                    return '<span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-blue-800">Completed</span>';
                } else {
                    return '<span class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 py-0.5 px-1.5 text-xs rounded-full border-2 border-gray-800">' . ucfirst($status) . '</span>';
                }
            });
    }

    public function columns(): array
    {
        return [

            Column::make('Cycle Code', 'cycle_code')
                
                ->searchable(),

            Column::make('Cycle Name', 'cycle_name')

                ->searchable(),

            Column::make('Created By', 'created_by_name', 'created_by')

                ->searchable(),
            Column::make('Location', 'location_name')

                ->searchable(),

            // Column::make('Started At', 'started_at_formatted', 'started_at')
            //     ,

            Column::make('Schedule Date', 'schedule_date_formatted', 'schedule_date')

                ->searchable(),

            Column::make('Ended At', 'ended_at_formatted', 'ended_at')
            ,

            Column::make('Status', 'status')

                ->searchable(),

            Column::make('Status', 'status_export')
                ->visibleInExport(true)
                ->hidden(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('cycle_name')->operators(['contains']),
            // Filter::inputText('location_name')->operators(['contains']),
            Filter::datetimepicker('schedule_date', 'schedule_date')->params(['time' => true]),
            Filter::datetimepicker('ended_at', 'ended_at')->params(['time' => true]),
            Filter::select('status', 'status')
                ->dataSource(
                    collect([
                        ['value' => 'pending', 'label' => 'Pending'],
                        ['value' => 'closed', 'label' => 'Closed'],
                    ])
                        ->sortBy('label')
                        ->values()
                        ->toArray()
                )
                ->optionValue('value')
                ->optionLabel('label'),
      
        ];
    }


    public function actions($row): array
    {
        return [
            Button::make('details', 'Details')
                ->slot('Details')
                ->class('bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded text-xs')
                ->route('organization.cycle-counts.details', ['cycle' => $row->id])
        ];
    }


}