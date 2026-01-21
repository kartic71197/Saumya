<div>
    <!-- PowerGrid Table -->
    <livewire:tables.admin.users.potential-user />
    
    <!-- Delete Confirmation Modal -->
    <x-modal name="delete-user-modal" width="w-120" height="h-auto" maxWidth="md">
        <div class="p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Remove Potential User
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to remove this user? This action cannot be undone.
                </p>
                <div class="flex space-x-3">
                    <button 
                        wire:click="closeDeleteModal"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button 
                        wire:click="confirmDelete"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</div>