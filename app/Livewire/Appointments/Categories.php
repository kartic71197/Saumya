<?php

namespace App\Livewire\Appointments;

use App\Models\AppointmentService;
use Livewire\Component;
use App\Models\AppointmentCategory;
use App\Models\AppointmentTag;

class Categories extends Component
{
    /** ========================
     *  DATA
     *  ======================== */
    public $categories;
    public $tags;

    // Category
    public $categoryId;
    public $category_name;
    public $category_description;

    // Services
    public $subcategories = [];

    public $newServiceName;
    public $newServiceDescription;
    public $newServiceDuration;
    public $newServicePrice;
    public $newServiceTags = [];

    public $showSubcategoryInput = false;
    public $isEditMode = false;

    // Edit Service Properties
    public $editingServiceId;
    public $editServiceName;
    public $editServiceDuration;
    public $editServicePrice;
    public $editServiceDescription;
    public $editServiceTags = [];

    // Tags
    public $newTag;

    /** ========================
     *  LIFECYCLE
     *  ======================== */
    public function mount()
    {
        $this->loadAll();
    }

    public function render()
    {
        return view('livewire.appointments.categories');
    }

    /** ========================
     *  LOADERS
     *  ======================== */
    public function loadAll()
    {
        $orgId = auth()->user()->organization_id;

        $this->categories = AppointmentCategory::with(['services.tags'])
            ->where('organization_id', $orgId)
            ->latest()
            ->get();

        $this->tags = AppointmentTag::where('organization_id', $orgId)->get();
    }

    /** ========================
     *  RESET
     *  ======================== */
    public function resetForm()
    {
        $this->reset([
            'categoryId',
            'category_name',
            'category_description',
            'subcategories',
            'newServiceName',
            'newServiceDescription',
            'newServiceDuration',
            'newServicePrice',
            'newServiceTags',
            'showSubcategoryInput',
            'isEditMode',
        ]);
    }



    /** ========================
     *  CATEGORY CRUD
     *  ======================== */

    public function createCategory()
    {
        $this->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'required|string',
        ]);

        $orgId = auth()->user()->organization_id;

        $category = AppointmentCategory::create([
            'organization_id' => $orgId,
            'name' => $this->category_name,
            'description' => $this->category_description,
        ]);

        $this->saveServices($category);

        $this->afterSave();
    }

    public function editCategory($id)
    {
        $category = AppointmentCategory::with('services.tags')->findOrFail($id);

        $this->categoryId = $category->id;
        $this->category_name = $category->name;
        $this->category_description = $category->description;
        $this->isEditMode = true;

        $this->subcategories = $category->services->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'description' => $s->description,
            'duration' => $s->duration,
            'price' => $s->price,
            'tags' => $s->tags->pluck('id')->toArray(),
        ])->toArray();

        $this->dispatch('open-modal', 'add-category-modal');
    }

    public function updateCategory()
    {
        $this->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'required|string',
        ]);

        $category = AppointmentCategory::findOrFail($this->categoryId);

        $category->update([
            'name' => $this->category_name,
            'description' => $this->category_description,
        ]);

        // Remove existing services (simple & safe for phase 1)
        AppointmentService::where('appointment_category_id', $category->id)->delete();

        $this->saveServices($category);

        $this->afterSave();
    }

    private function saveServices($category)
    {
        $orgId = auth()->user()->organization_id;

        foreach ($this->subcategories as $service) {
            $svc = AppointmentService::create([
                'organization_id' => $orgId,
                'appointment_category_id' => $category->id,
                'name' => $service['name'],
                'description' => $service['description'],
                'duration' => $service['duration'],
                'price' => $service['price'],
            ]);

            if (!empty($service['tags'])) {
                $svc->tags()->sync($service['tags']);
            }
        }
    }

    private function afterSave()
    {
        $this->loadAll();
        $this->dispatch('close-modal', 'add-category-modal');
        $this->resetForm();
    }

    /** ========================
     *  SERVICES (TEMP STATE)
     *  ======================== */

    public function addSubcategory()
    {
        $this->validate([
            'newServiceName' => 'required|string|max:255',
            'newServiceDescription' => 'nullable|string',
            'newServiceDuration' => 'required|integer|min:5',
            'newServicePrice' => 'required|numeric|min:0',
        ]);

        $this->subcategories[] = [
            'name' => $this->newServiceName,
            'description' => $this->newServiceDescription,
            'duration' => $this->newServiceDuration,
            'price' => $this->newServicePrice,
            'tags' => $this->newServiceTags,
        ];

        $this->reset([
            'newServiceName',
            'newServiceDescription',
            'newServiceDuration',
            'newServicePrice',
            'newServiceTags',
            'showSubcategoryInput',
        ]);
    }

    public function removeSubcategory($index)
    {
        unset($this->subcategories[$index]);
        $this->subcategories = array_values($this->subcategories);
    }

    /** ========================
     *  TAGS
     *  ======================== */

    public function createTag()
    {
        $this->validate([
            'newTag' => 'required|string|max:100',
        ]);

        AppointmentTag::create([
            'organization_id' => auth()->user()->organization_id,
            'name' => $this->newTag,
        ]);

        $this->newTag = null;

        $this->loadAll();
        $this->dispatch('close-modal', 'add-tag-modal');
    }

    // ==================== EDIT SERVICE FUNCTIONS ====================

    /**
     * Load service data for editing
     */
    public function editService($serviceId)
    {
        $service = AppointmentService::with('tags')->findOrFail($serviceId);

        $this->editingServiceId = $service->id;
        $this->editServiceName = $service->name;
        $this->editServiceDuration = $service->duration;
        $this->editServicePrice = $service->price;
        $this->editServiceDescription = $service->description;
        $this->editServiceTags = $service->tags->pluck('id')->toArray();

        $this->dispatch('open-modal', 'edit-service-modal');
    }

    /**
     * Update the service
     */
    public function updateService()
    {
        $this->validate([
            'editServiceName' => 'required|string|max:255',
            'editServiceDuration' => 'required|integer|min:5',
            'editServicePrice' => 'required|numeric|min:0',
            'editServiceDescription' => 'nullable|string|max:500',
            'editServiceTags' => 'array',
        ], [
            'editServiceName.required' => 'Service name is required.',
            'editServiceDuration.required' => 'Duration is required.',
            'editServiceDuration.min' => 'Duration must be at least 5 minutes.',
            'editServicePrice.required' => 'Price is required.',
            'editServicePrice.min' => 'Price must be 0 or greater.',
        ]);

        $service = AppointmentService::where('id', $this->editingServiceId)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();

        $service->update([
            'name' => $this->editServiceName,
            'duration' => $this->editServiceDuration,
            'price' => $this->editServicePrice,
            'description' => $this->editServiceDescription,
        ]);

        // Sync tags (handles empty automatically)
        $service->tags()->sync($this->editServiceTags ?? []);

        $this->resetServiceForm();
        $this->loadAll();

        $this->dispatch('close-modal', 'edit-service-modal');
        session()->flash('message', 'Service updated successfully!');
    }


    /**
     * Reset service form fields
     */
    public function resetServiceForm()
    {
        $this->editingServiceId = null;
        $this->editServiceName = '';
        $this->editServiceDuration = '';
        $this->editServicePrice = '';
        $this->editServiceDescription = '';
        $this->editServiceTags = [];
        $this->resetValidation([
            'editServiceName',
            'editServiceDuration',
            'editServicePrice',
            'editServiceDescription',
            'editServiceTags',
        ]);
    }

}
