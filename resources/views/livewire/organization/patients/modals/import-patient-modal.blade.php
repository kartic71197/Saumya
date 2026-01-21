<x-modal name="import-patient-modal" width="w-100" height="h-auto" maxWidth="4xl">
    <header class="p-3">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Import Patients list') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Upload a CSV file to import multiple patient\'s data at once.') }}
        </p>
    </header>
    <form action="{{ route('import.patients') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="space-y-6 p-3">
            <div class="border-b border-gray-900/10 pb-6">
                <div class="grid grid-cols-6">
                    <!-- CSV Template Example -->
                    <div class="col-span-3 p-3">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('CSV Format') }}
                        </h3>
                        <div class="mt-2">
                            <x-secondary-button type="button" wire:click="downloadSampleCsv">
                                {{ __('Download Sample CSV') }}
                            </x-secondary-button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSV File Upload -->
            <div class="mt-4">
                <x-input-label for="csv_file" :value="__('*CSV File')" />
                <input type="file" name="csv_file" id="csv_file"
                    class="mt-1 block w-full border rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    accept=".csv">
                @error('csv_file')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex justify-end gap-4 mt-6 p-3" x-data="{ loading: false }">
            <x-primary-button typr="submit" class="min-w-24 flex justify-center items-center">Import
            </x-primary-button>
            <x-secondary-button
                x-on:click="$dispatch('close-modal', 'import-patient-modal')">{{ __('Cancel') }}</x-secondary-button>
        </div>
    </form>
</x-modal>