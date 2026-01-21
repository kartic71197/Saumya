<?php

namespace App\Livewire\Tables\Admin\Users;

use App\Models\PotentialClient;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PotentialUser extends PowerGridComponent
{
    public string $tableName = 'potential-user-wozssd-table';
    public bool $showFilters = false;

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

            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return PotentialClient::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('email')
            ->add('otp')
            ->add('otp_expires_at_formatted', fn(PotentialClient $model) => Carbon::parse($model->otp_expires_at)->format('d/m/Y H:i:s'))
            ->add('otp_verified', function ($item) {
                if ($item->otp_verified == 1) {
                    return '<div class="text-green-500 text-xs">Verified</div>';
                } else if ($item->otp_verified == 0) {
                    return '<div class="text-red-500 text-xs">Not Verified</div>';
                }
            })
            ->add('otp_verified_export', fn($item) => $item->otp_verified ? 'Verified' : 'Not Verified')
            ->add('created_at')
             ->add('created_at_export', fn($model) =>
            Carbon::parse($model->created_at)->format('Y-m-d H:i:s')
        );
    }

    public function columns(): array
    {
        return [
            Column::make('Email', 'email')
                
                ->searchable(),

            Column::make('Verified', 'otp_verified')
                ->visibleInExport(false)
                ->searchable(),
            
            Column::make('Verified', 'otp_verified_export')
            ->visibleInExport(true)
            ->hidden(),

            Column::make('Created at', 'created_at')
                ->visibleInExport(false)
                ->searchable(),

        // Created At (plain formatted for export)
        Column::make('Created At', 'created_at_export')
            ->visibleInExport(true)
            ->hidden(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('otp_expires_at'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

#[\Livewire\Attributes\On('remove')]
public function remove($rowId): void
{    
    $this->dispatch('remove-potential-user', rowId: $rowId);
}

    #[\Livewire\Attributes\On('execute-delete')]
    public function executeDelete($userId)
    {        
        if (!$userId) {
            Log::warning('No user ID provided to executeDelete');
            return;
        }

        try {
            DB::beginTransaction();
            
            $potentialClient = PotentialClient::find($userId);
            if ($potentialClient) {
            
                $potentialClient->delete();
                $this->refresh();
            $this->dispatch(
                'show-notification',
                 'User deleted successfully!', 
                'success'
            );
            } else {
            $this->dispatch(
                'show-notification',
                'User not found!',
                'error'
            );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        $this->dispatch(
            'show-notification',
            'Error while deleting user: ' . $e->getMessage(),
            'error'
        );
        }
    }

    public function actions(PotentialClient $row): array
{
    return [
        Button::add('remove')
            ->slot('Remove')
            ->id()
            ->class('inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-red-600 border border-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200 shadow-sm hover:shadow-md')           
            ->dispatch('remove', ['rowId' => $row->id])
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