<x-modal name="delete-modal-open" height="h-auto" max-w-10xl mx-auto>
    <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="flex items-center justify-center h-14 w-14 rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-7 w-7 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 text-center mb-2">
                Delete Cycle
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                Are you sure you want to delete cycle <span
                    class="font-semibold text-gray-900 dark:text-white">"{{ $cycle->cycle_name }}"</span>?
            </p>
        </div>

        <!-- Warning Text -->
        <div
            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-8">
            <p class="text-xs text-yellow-800 dark:text-yellow-200">
                <span class="font-semibold">Warning:</span> This action cannot be undone. All cycle count data will be
                permanently deleted.
            </p>
        </div>

        <!-- Confirmation Input -->
        <div class="mb-8">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Type the cycle name to confirm
            </label>
            <input type="text" wire:model.defer="confirmCycleName"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm transition-colors"
                onpaste="return false" ondrop="return false" autocomplete="off" wire:keydown.enter="deleteCycle">
            @error('confirmCycleName')
                <span class="text-red-500 text-sm mt-1 block text-left">{{ $message }}</span>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 justify-end">
            <x-secondary-button wire:click="closeDeleteModal" class="flex-shrink-0">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-danger-button wire:click="deleteCycle" class="flex-shrink-0">
                {{ __('Delete Cycle') }}
            </x-danger-button>
        </div>
    </div>
</x-modal>

