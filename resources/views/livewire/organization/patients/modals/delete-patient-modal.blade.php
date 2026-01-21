<x-modal name="delete-patient-modal" width="w-100" height="h-auto" maxWidth="3xl">
    <header class="p-3">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{  __('Are you sure want to delete chart number ' . $chartnumber . ' ?') }}
        </h2>
    </header>
    <div class="mt-6 flex items-center justify-end gap-x-6 px-6 pb-4">
        <x-secondary-button x-on:click="$dispatch('close-modal', 'patient-modal')"
            class="text-sm/6 font-semibold text-gray-900">{{ __('Cancel') }}
        </x-secondary-button>

        <x-primary-button class="min-w-24 flex justify-center items-center text-sm/6 font-semibold text-gray-900"
            wire:click="confirmdeletePatient">
            {{ __('Delete') }}
        </x-primary-button>
    </div>
</x-modal>