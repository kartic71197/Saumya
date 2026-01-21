 <x-modal name="add-category-modal" maxWidth="4xl" wire:close="resetForm">

     <div
         class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 p-6 border-b border-indigo-100 dark:border-gray-700">
         <h2 class="text-xl font-bold text-gray-900 dark:text-white">
             {{ $isEditMode ? 'Edit Category' : 'Create New Category' }}
         </h2>
         <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
             {{ $isEditMode ? 'Update category details and services' : 'Add a new category with services and pricing' }}
         </p>
     </div>

     <form wire:submit.prevent="{{ $isEditMode ? 'updateCategory' : 'createCategory' }}">
         <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

             {{-- Category Details --}}
             <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 space-y-4">
                 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Category Details</h3>

                 <div>
                     <x-input-label value="Category Name" class="text-sm font-medium" />
                     <x-text-input wire:model="category_name" class="w-full mt-1.5" placeholder="e.g., Hair Services" />
                 </div>

                 <div>
                     <x-input-label value="Description" class="text-sm font-medium" />
                     <textarea wire:model="category_description"
                         class="w-full mt-1.5 rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                         rows="3" placeholder="Brief description of this category..."></textarea>
                 </div>
             </div>

             {{-- Services Section --}}
             <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                 <div class="flex justify-between items-center mb-4">
                     <div>
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Services</h3>
                         <p class="text-sm text-gray-500 dark:text-gray-400">Add services under this category</p>
                     </div>
                     <x-secondary-button type="button" wire:click="$set('showSubcategoryInput', true)"
                         class="inline-flex items-center px-4 py-2 rounded-lg">
                         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                         </svg>
                         Add Service
                     </x-secondary-button>
                 </div>

                 {{-- Added Services Display --}}
                 @if (count($subcategories) > 0)
                     <div class="flex flex-wrap gap-3 mb-6">
                         @foreach ($subcategories as $index => $s)
                             <span
                                 class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md hover:shadow-lg transition-shadow duration-200">
                                 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M5 13l4 4L19 7" />
                                 </svg>
                                 {{ $s['name'] }}
                                 <button type="button" wire:click="removeSubcategory({{ $index }})"
                                     class="ml-3 hover:bg-white/20 rounded-full p-1 transition-colors">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M6 18L18 6M6 6l12 12" />
                                     </svg>
                                 </button>
                             </span>
                         @endforeach
                     </div>
                 @endif

                 {{-- Add Service Form --}}
                 @if ($showSubcategoryInput)
                     <div
                         class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 space-y-4 border-2 border-dashed border-indigo-300 dark:border-indigo-700">
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                             <div>
                                 <label
                                     class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Service
                                     Name</label>
                                 <x-text-input wire:model="newServiceName" placeholder="e.g., Haircut" class="w-full" />
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                     Duration (min)
                                 </label>

                                 <select wire:model="newServiceDuration"
                                     class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700
               focus:border-indigo-500 focus:ring-indigo-500">
                                     <option value="">Select duration</option>

                                     @for ($i = 15; $i <= 240; $i += 15)
                                         <option value="{{ $i }}">{{ $i }} min</option>
                                     @endfor
                                 </select>
                             </div>

                             <div>
                                 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Price
                                     ($)</label>
                                 <x-text-input wire:model="newServicePrice" type="number" step="0.01"
                                     placeholder="500.00" class="w-full" />
                             </div>
                         </div>

                         <div>
                             <label
                                 class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                             <textarea wire:model="newServiceDescription" placeholder="Describe this service..." rows="2"
                                 class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                         </div>

                         <div>
                             <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tags (Hold
                                 Ctrl/Cmd for multiple)</label>
                             <select wire:model="newServiceTags" multiple
                                 class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                                 size="3">
                                 @foreach ($tags as $tag)
                                     <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                 @endforeach
                             </select>
                         </div>

                         <div class="flex gap-3">
                             <x-primary-button type="button" wire:click="addSubcategory"
                                 class="flex-1 justify-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">
                                 <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M12 4v16m8-8H4" />
                                 </svg>
                                 Add Service
                             </x-primary-button>
                             <x-secondary-button type="button" wire:click="$set('showSubcategoryInput', false)">
                                 Cancel
                             </x-secondary-button>
                         </div>
                     </div>
                 @endif
             </div>
         </div>

         <div
             class="flex justify-end gap-3 p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
             <x-secondary-button type="button" wire:click="$dispatch('close-modal', 'add-category-modal')">
                 Cancel
             </x-secondary-button>

             <x-primary-button type="submit"
                 class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">
                 {{ $isEditMode ? 'Update Category' : 'Create Category' }}
             </x-primary-button>
         </div>
     </form>
 </x-modal>
