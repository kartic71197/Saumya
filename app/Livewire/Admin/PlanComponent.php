<?php

namespace App\Livewire\Admin;

use App\Models\Plan;
use App\Services\Stripe\SubscriptionStripeService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PlanComponent extends Component
{
    public $planId = '';
    public $name = '';
    public $price = '';
    public $max_users = '';
    public $max_locations = '';
    public $description = '';
    public $is_active = false;
    public $duration = '3';
    public $editing = false;


    /**
     * Opens Add Plan modal with a clean form.
     * This method ensures Add Plan always starts fresh.
     */

    public function openAddPlanModal()
    {
        $this->resetForm();
        $this->is_active = true;
        $this->editing = false;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('open-modal', 'add-plan-modal');
    }


    public function createPlan()
    {
        $this->validate([
            'name' => 'required|min:3|max:25|unique:plans,name',
            'price' => 'required|numeric|min:0',
            'max_users' => 'required|numeric|min:1',
            'max_locations' => 'required|numeric|min:1',
            'description' => 'required|min:3|max:250',
            'is_active' => 'nullable|boolean',
            'duration' => 'required',
        ]);
        // Handle checkbox 'is_active' as a boolean value
        // if ($this->is_active == 'true') {
        //     $is_active = true;
        // } else {
        //     $is_active = false;
        // }
        $plan = Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'max_users' => $this->max_users,
            'description' => $this->description,
            'max_locations' => $this->max_locations,
            'is_active' => $this->is_active,
            'duration' => $this->duration,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id
        ]);

        app( SubscriptionStripeService::class)->sync($plan);
        $this->dispatch('pg:eventRefresh-plans-list-mlwfa0-table');
        $this->dispatch('close-modal', 'add-plan-modal');
    }

    #[On('edit')]
    public function startEdit($rowId)
    {
        $this->reset();
        $this->editing = true;
        $this->planId = $rowId;

        $plan = Plan::findOrFail($rowId);
        $this->name = $plan->name;
        $this->price = $plan->price;
        $this->max_users = $plan->max_users;
        $this->max_locations = $plan->max_locations;
        $this->description = $plan->description;
        $this->is_active = (bool) $plan->is_active;
        $this->duration = (string) $plan->duration;
        $this->dispatch('open-modal', 'edit-plan-modal');

    }

    public function updatePlan()
    {

        $this->price = str_replace(',', '', $this->price);
        $this->price = (float) $this->price;
        $this->validate([
            'name' => 'required|min:3|max:25',
            'price' => 'required|numeric|min:0',
            'max_users' => 'required|numeric|min:1',
            'max_locations' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
            'description' => 'required|min:3|max:250',
            'duration' => 'required',
        ]);
        if (
            Plan::where('name', $this->name)
                ->where('is_active', true)
                ->where('id', '!=', $this->planId)
                ->exists()
        ) {
            $this->addError('name', 'The name must be unique.');
            return;
        }

        $plan = Plan::findOrFail($this->planId);
        $plan->update([
            'name' => $this->name,
            'price' => $this->price,
            'max_users' => $this->max_users,
            'max_locations' => $this->max_locations,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'updated_by' => auth()->user()->id
        ]);

        app(SubscriptionStripeService::class)->sync($plan);

        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('plan-updated');
        $this->dispatch('pg:eventRefresh-plans-list-mlwfa0-table');
        $this->reset();
        $this->dispatch('close-modal', 'edit-plan-modal');
    }

    /**
     * Reset all plan form fields.
     *
     * This is required because Livewire preserves component state.
     * Without resetting, data from Edit Plan leaks into
     * Add Plan modal.
     */
    public function resetForm()
    {
        $this->reset([
            'planId',
            'name',
            'price',
            'max_users',
            'max_locations',
            'description',
            'is_active',
            'duration',
        ]);

        // Set default duration explicitly
        $this->duration = '3';
    }

    public function render()
    {
        // Fetching plans from the database
        $plans = Plan::all();

        // Rendering the view and passing the plans data
        return view('livewire.admin.plan-component', compact('plans'));
    }
}
