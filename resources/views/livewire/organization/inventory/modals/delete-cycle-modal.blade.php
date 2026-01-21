<x-modal name="delete_cycle_modal" maxWidth="md">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Delete Cycle
        </h3>
        <button wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="space-y-4">
        <p class="text-sm text-gray-700 dark:text-gray-300">
            This action cannot be undone. All cycle count data will be permanently deleted.
        </p>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Type <strong>"{{ $cycle->cycle_name ?? '' }}"</strong> to confirm:
            </label>
            <input type="text" wire:model.defer="confirmCycleName"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Enter cycle name">
            @error('confirmCycleName')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <button wire:click="closeDeleteModal"
                class="px-4 py-2 rounded bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors">
                Cancel
            </button>
            <button wire:click="deleteCycle"
                class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition-colors">
                Delete Cycle
            </button>
        </div>
    </div>
</x-modal>
