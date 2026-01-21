{{-- Add Tag Modal --}}
    <x-modal name="add-tag-modal" maxWidth="md">
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 p-6 border-b border-indigo-100 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create New Tag</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Add a custom tag to organize your services</p>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tag Name</label>
                <x-text-input wire:model="newTag" placeholder="e.g., Premium, Popular, New" class="w-full" />
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-secondary-button
                    wire:click="$dispatch('close-modal', 'add-tag-modal')">
                    Cancel
                </x-secondary-button>

                <x-primary-button wire:click="createTag" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Tag
                </x-primary-button>
            </div>
        </div>
    </x-modal>