<?php

namespace App\Livewire\User\Settings;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;

use App\Models\Category;
use App\Models\Organization;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CategoriesComponent extends Component
{

    public $categoryId = '';

    public $category_name;
    public $category_description;
    public $organizations; // Holds the list of organizations

    public $is_active = false;

    public $subcategories = [];
    public $newSubcategory = '';

    public $showSubcategoryInput = false;



    // Validation rules
    protected $rules = [
        'category_name' => 'required|min:3|max:25|unique:categories,category_name',
        'category_description' => 'required|min:3|max:250',
        'is_active' => 'nullable|boolean',
    ];

    // Custom validation messages (optional)
    protected $messages = [
        'category_name.required' => 'The category name is required.',
        'category_name.unique' => 'This category name already exists.',
        'category_organization.required' => 'Please select an organization.',
    ];

     public function resetForm()
{
    log::info('Reset form called.');
    $this->reset([
        'category_name',
        'category_description',
        'subcategories',
        'newSubcategory',
        'categoryId',
        'is_active',
        'showSubcategoryInput',
    ]);
    $this->resetErrorBag();
    $this->resetValidation();

}

    public function mount()
    {
        // Fetch organizations from the database
        $this->organizations = Organization::pluck('name', 'id');

        $this->subcategories = [];
    }

    
    public function createCategory()
    {
        Log::info('Create Category clicked', [
        'category_name' => $this->category_name,
        'is_active' => $this->is_active,
    ]);
    
        // Validate the input
        $this->validate([
            'category_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $this->resetErrorBag('category_name');

        // Fetch the organization_id from the authenticated user
        $organizationId = Auth::user()->organization_id;

        //  Check for duplicate category in same org
        $exists = Category::where('organization_id', $organizationId)
        ->whereRaw('LOWER(category_name) = ?', [strtolower($this->category_name)])
        ->exists();

        if ($exists) {
        $this->addError('category_name', 'This category already exists.');
        return;
        }

        // Create the category
        $category = Category::create([
            'organization_id' => $organizationId,
            'category_name' => $this->category_name,
            'category_description' => $this->category_description,
            'is_active' => true,
        ]);

        // $auditService = app(\App\Services\CategoryAuditing::class);
        // $auditService->logDeletion(
        //     $category,
        //     'Added',
        //     'Category is added.'
        // );

        foreach ($this->subcategories as $sub) {
            Subcategory::create([
                'subcategory' => $sub['subcategory'],
                'category_id' => $category->id,
                'is_active' => true,
            ]);
        }

        // Reset the form fields
        $this->reset(['category_name', 'category_description', 'is_active', 'subcategories', 'newSubcategory']);

        // Close the modal
        
        $this->dispatch('pg:eventRefresh-category-list-a1ujvr-table');
        $this->dispatch('close-modal', 'add-category-modal');
        $this->showSubcategoryInput = false;

    }

    #[On('edit')]

    public function edit($rowId)
    {
        $this->showSubcategoryInput = false;
        $this->resetErrorBag();
        $this->resetValidation();

        // $this->editing = true;
        $this->categoryId = $rowId;


        $category = Category::findOrFail($rowId); // Fetch category object

        $this->category_name = $category->category_name;
        // $this->category_organization = $category->organization_id;
        $this->category_description = $category->category_description;
        $this->is_active = $category->is_active;

        $this->subcategories = $category->subcategories()
        ->get()
        ->map(function ($sub) {
            return [
                'id' => $sub->id, 
                'subcategory' => $sub->subcategory,
                'is_new' => false 
            ];
        })
        ->toArray();

        $this->dispatch('open-modal', 'edit-category-modal');
    }

    public function updateCategory()
    {
        $this->validate([
            'category_name' => 'required|string|max:255',
            'category_description' => 'nullable|string',
        ]);

        $organizationId = Auth::user()->organization_id;

         $category = Category::findOrFail($this->categoryId);
         $oldCategory = clone $category; 

    //  Prevent duplicate category name (excluding itself)
    if (strtolower($category->category_name) !== strtolower($this->category_name)) {
    $exists = Category::where('organization_id', $organizationId)
        ->whereRaw('LOWER(category_name) = ?', [strtolower($this->category_name)])
        ->where('id', '!=', $this->categoryId)
        ->exists();

    if ($exists) {
        $this->addError('category_name', 'This category already exists.');
        return;
    }
}

        $category->update([
            'category_name' => $this->category_name,
            'category_description' => $this->category_description,
        ]);

        // Get current subcategories from database
        $existingDbSubcategories = Subcategory::where('category_id', $category->id)->get();

        // Get subcategories that should remain (from UI)
        $uiSubcategories = collect($this->subcategories)->pluck('subcategory')->toArray();

        // DELETE: Remove subcategories that are in DB but not in UI
    foreach ($existingDbSubcategories as $dbSubcategory) {
        if (!in_array($dbSubcategory->subcategory, $uiSubcategories)) {
            $dbSubcategory->delete();
            
            // Clean up product references
            \App\Models\Product::where('subcategory_id', $dbSubcategory->id)
                ->update(['subcategory_id' => null]);
        }
    }
        
        //  Add new subcategories from UI
        foreach ($this->subcategories as $sub) {
        if (!empty($sub['subcategory'])) {
            $exists = Subcategory::where('category_id', $category->id)
                ->whereRaw('LOWER(subcategory) = ?', [strtolower($sub['subcategory'])])
                ->exists();

            if (!$exists) {
                Subcategory::create([
                    'subcategory' => $sub['subcategory'],
                    'category_id' => $category->id,
                    'is_active' => true,
                ]);
            }
        }
    }

        $this->dispatch('pg:eventRefresh-category-list-a1ujvr-table');
        $this->dispatch('close-modal', 'edit-category-modal');
        $this->showSubcategoryInput = false;

        $auditService = app(\App\Services\CategoryAuditing::class);
        $auditService->logUpdate($oldCategory, $category, 'updated');
        
    }


    public function deleteCategory()
    {
        $category = Category::findOrFail($this->categoryId);
        $category->is_active = false;
        $category->save();

        $this->dispatch('pg:eventRefresh-category-list-a1ujvr-table');
        $this->dispatch('close-modal', 'edit-category-modal');

        $auditService = app(\App\Services\CategoryAuditing::class);
        $auditService->logDeletion(
            $category,
            'Removed',
            'Category marked as inactive.'
        );
        $this->showSubcategoryInput = false;
    }

    public function addSubcategory()
{
    $this->validate([
        'newSubcategory' => 'required|string|max:255',
    ]);
    $newSub = trim($this->newSubcategory);

    //  Check duplicate inside current subcategories array
    $duplicate = collect($this->subcategories)
        ->pluck('subcategory')
        ->map(fn($s) => strtolower(trim($s)))
        ->contains(strtolower($newSub));

    if ($duplicate) {
        $this->addError('newSubcategory', "Subcategory '{$this->newSubcategory}' already exists in this category.");
        return;
    }

    // Add as a new subcategory (no ID yet, will be created on save)
    $this->subcategories[] = [
        'id' => null, 
        'subcategory' => $newSub,
        'is_new' => true
    ];

    $this->newSubcategory = '';
    $this->showSubcategoryInput = false;
}

public function removeSubcategory($index)
{
    unset($this->subcategories[$index]);
    $this->subcategories = array_values($this->subcategories);
}

    public function render()
    {
        return view('livewire.user.settings.categories-component');
    }
}
