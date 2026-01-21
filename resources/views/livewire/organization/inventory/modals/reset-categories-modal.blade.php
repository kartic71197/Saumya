<x-modal name="reset_categories_modal" maxWidth="md">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
            Reset Categories
        </h3>
        <button wire:click="closeRejectModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Select categories to reset assignments in this cycle. Users will need to recount these items.
        </p>

        <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2">
            @foreach ($categories as $category)
                <label class="flex items-center space-x-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded">
                    <input type="checkbox" wire:model="selectedCategories" value="{{ $category['id'] }}"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-600">
                    <span class="text-gray-700 dark:text-gray-300">{{ $category['name'] ?? '-' }}</span>
                </label>
            @endforeach
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <button wire:click="closeRejectModal"
                class="px-4 py-2 rounded bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors">
                Cancel
            </button>
            <button wire:click="rejectSelected"
                class="px-4 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-700 transition-colors">
                Reset Selected
            </button>

        </div>
    </div>
</x-modal>
