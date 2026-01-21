<x-modal name="cancel-po-confirmation" maxWidth="4xl" wire:model="showCancelOrderModal">
    <div class="p-6">
        <!-- Modal Header -->
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Cancel Order') }}
        </h2>

        <!-- Modal Body -->
        <div class="mt-4 text-gray-600 dark:text-gray-400">
            {{ __('Are you sure you want to cancel this order? This action cannot be undone.') }}
        </div>

        <!-- Modal Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <x-secondary-button wire:click="$dispatch('close-modal', 'cancel-po-confirmation')">
                {{ __('No, Keep Order') }}
            </x-secondary-button>

            <x-danger-button wire:click="cancelOrder">
                {{ __('Yes, Cancel Order') }}
            </x-danger-button>
        </div>
    </div>
</x-modal>
