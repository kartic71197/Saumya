<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class OrganizationList extends PowerGridComponent
{
    public string $tableName = 'organization-list-eyipxp-table';

    public bool $showFilters = true;

    use WithExport;
    public function boot(): void
    {
        // config(['livewire-powergrid.filter' => 'outside']);
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
            //     ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),

            //   PowerGrid::responsive()
            // ->fixedColumns('organization_code', 'name','email'),
        ];
    }

    public function datasource(): Builder
    {
        $query = Organization::query()
            ->where('organizations.is_deleted', false)
            ->whereHas('users', function ($q) {
            $q->where('system_locked', false);
            })
            ->with('plan');


        return $query;
    }



    public function relationSearch(): array
    {
        return [
            'plan' => [
                'plan.name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('organization_code')
            ->add('name', function ($row) {
                return '
                <span 
                    class="underline cursor-pointer text-blue-600 hover:text-blue-800"
                    wire:click="$dispatch(\'showOrgDetails\', { orgId: ' . $row->id . ' })"
                >
                    ' . e($row->name) . '
                </span>
            ';
            })
            ->add('plan.name') // Directly return the plan_name
            ->add('email')
            ->add('phone')
            ->add('open-tickets', function ($model) {
                $count = $model->openTickets()->count();

                if ($count > 0) {
                    return '
            <span class="inline-flex items-center justify-center 
                         text-red-500 text-xs font-semibold px-2.5 py-0.5 
                         rounded-full shadow-sm border border-red-400">
                ' . $count . '
            </span>
        ';
                }

                return '
        <span class="inline-flex items-center justify-center  text-xs font-medium 
                     px-2.5 py-0.5 rounded-full border border-gray-300">
            ' . $count . '
        </span>
    ';
            })

            ->add('open-orders', fn($model) => $model->openOrders()->count())
            // ->add('city')
            // ->add('state')
            // ->add('country')
            // ->add('pin')
            ->add('full_address', fn($model) =>
                "{$model->address},{$model->city}, {$model->state}, {$model->country} ({$model->pin})")
            ->add('is_active', fn($item) => $item->is_active ? '<div class="text-green-400">Active</div>' : '<div class="text-red-400">Inactive</div>')

            ->add('plan_valid')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Code', 'id')
            //     ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
            //     ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
            //     ->searchable()
            //     ->hidden(),
            Column::make('Practice ID   ', 'organization_code') // Display organization_code
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->searchable(),
            Column::make('Practice Name', 'name')
                
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Practice Email', 'email')
                
                ->searchable(),
            Column::make('Practice Phone', 'phone')
                
                ->searchable(),

            // Column::make('Practice Address', 'full_address')
            //     
            //     ->searchable()
            //     ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
            //     ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Plan', 'plan.name') // Display plan name
                
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),
            Column::make('Open tickets', 'open-tickets') // Display open tickets count
                
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),

            Column::make('Open orders', 'open-orders') // Display open orders count
                
                ->searchable()
                ->headerAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;')
                ->bodyAttribute('max-w-xl', 'min-width: 0; white-space: normal !important;'),



            // Column::make('City', 'city')
            //     
            //     ->searchable(),

            // Column::make('State', 'state')
            //     
            //     ->searchable(),

            // Column::make('Country', 'country')
            //     
            //     ->searchable(),

            // Column::make('Pin', 'pin')
            //     
            //     ->searchable(),


            // Column::make('Address', 'address')
            //     
            //     ->searchable(),
            // Column::make('Is active', 'is_active')
            //     
            //     ->searchable(),

            // Column::make('Plan valid', 'plan_valid')
            //     
            //     ->searchable(),

            // Column::make('Created at', 'created_at')
            //     
            //     ->searchable(),

            // Column::action('Action')->hidden()
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText(column: 'organization_code')->placeholder('Code')->operators(['contains']),
            Filter::inputText(column: 'name')->placeholder('Name')->operators(['contains']),
            Filter::select('plan.name', 'plan_id')
                ->dataSource(Plan::all())
                ->optionLabel('name')
                ->optionValue('id'),
            Filter::inputText('email')->placeholder('Email')->operators(['contains']),
            Filter::boolean('is_active')->label('Active', 'Inactive'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    #[\Livewire\Attributes\On('toggle-org-status')]
    public function toggleOrganizationStatus($orgId): void
    {
        $organization = Organization::find($orgId);
        if ($organization) {
            $organization->is_active = !$organization->is_active; // Toggle the status
            $organization->save();
        }
    }

    // public function actions(Organization $row): array
    // {
    //     return [

    //         // Button::add('edit')
    //         // ->slot('
    //         //     <span class="w-24 flex justify-center items-center relative"
    //         //           x-data="{ loading: false }"
    //         //           x-on:click="loading = true; $dispatch(\'edit-user\', { rowId: ' . $row->id . ' }); setTimeout(() => loading = false, 1000)">

    //         //         <!-- Normal state: Icon + Edit text -->
    //         //         <span class="flex items-center gap-2" :class="{\'invisible\': loading}">
    //         //             <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
    //         //                 <path fill="currentColor" d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z"/>
    //         //             </svg>
    //         //             Edit
    //         //         </span>

    //         //         <!-- Loading state: Spinner -->
    //         //         <span x-show="loading" class="absolute inset-0 flex items-center justify-center">
    //         //             <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    //         //                 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    //         //                 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
    //         //             </svg>
    //         //         </span>
    //         //     </span>
    //         // ')
    //         // ->id('edit-btn-' . $row->id)
    //         // ->class('inline-flex items-center justify-center w-24 px-4 py-2 bg-primary-md dark:bg-primary-md border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150')
    //         // ->class('hover:bg-primary-dk dark:hover:bg-primary-dk focus:bg-primary-dk dark:focus:bg-primary-dk active:bg-primary-dk dark:active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 dark:focus:ring-offset-gray-800')
    //         // ->dispatch('edit-organization', ['rowId' => $row->id])

    //         Button::add('edit')
    //             ->slot('
    //             <span class="w-24 flex justify-center items-center relative"
    //                   x-data="{ loading: false }"
    //                   x-on:click="loading = true; $dispatch(\'edit-user\', { rowId: ' . $row->id . ' }); setTimeout(() => loading = false, 1000)">

    //                 <!-- Normal state: Icon + Edit text -->
    //                 <span class="flex items-center gap-2" :class="{\'invisible\': loading}">
    //                  View
    //                     <svg class="w-4 h-4 ml-2 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none"
    //                             viewBox="0 0 14 10">
    //                             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    //                                 d="M1 5h12m0 0L9 1m4 4L9 9" />
    //                         </svg>

    //                 </span>

    //                 <!-- Loading state: Spinner -->
    //                 <span x-show="loading" class="absolute inset-0 flex items-center justify-center">
    //                     <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    //                         <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    //                         <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
    //                     </svg>
    //                 </span>
    //             </span>
    //         ')
    //             ->id('edit-btn-' . $row->id)
    //             ->class('inline-flex items-center justify-center w-24 px-4 py-2 bg-primary-md dark:bg-primary-md border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150')
    //             ->class('hover:bg-primary-dk dark:hover:bg-primary-dk focus:bg-primary-dk dark:focus:bg-primary-dk active:bg-primary-dk dark:active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 dark:focus:ring-offset-gray-800')
    //             ->dispatch('showOrgDetails', ['orgId' => $row->id])


    //     ];
    // }

}
