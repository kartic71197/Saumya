<x-modal name="bulk-mycatalog-product-modal" width="w-100" maxWidth="6xl">
    <div class="bg-white dark:bg-gray-800">
        <div class="bg-gray-50 dark:bg-gray-800">
            <!-- Modal Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Bulk Add Products to Locations') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Select locations to add the selected products into your inventory') }}
                </p>
            </div>

            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-gray-500">
                        {{ __('Applies to all selected products') }}
                    </span>
                    <!-- Select All Locations checkbox -->
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="selectAll" wire:click="toggleSelectAll"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                        {{ __('Select All Locations') }}
                    </label>
                </div>

                <!-- Locations table:
                     Shows all available locations with checkboxes -->
                <div class="overflow-hidden bg-white dark:bg-gray-900 shadow rounded-lg">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                        <!-- Table header -->
                        <thead
                            class="text-xs uppercase text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 w-2/5">{{ __('Location') }}</th>
                                <th class="p-3 w-1/12 text-center">{{ __('Select') }}</th>
                            </tr>
                        </thead>
                        <!-- Table body:
                             Each row represents one location -->
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if(!empty($locations))
                                @foreach ($locations as $location)
                                    @php
                                        $locationId = $location->id;
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                        <!-- Location Name Column -->
                                        <td class="p-3 font-medium text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $location->name }}
                                            </div>
                                        </td>

                                        <!-- Checkbox Column -->
                                        <td class="p-3 text-center">
                                            <input type="checkbox" wire:model="selectedLocations.{{ $locationId }}"
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="2" class="p-6 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No locations available.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>

                @if (!empty($locations))
                    <div
                        class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-medium">{{ __('Important Information:') }}</p>
                                <ul class="mt-1 list-disc list-inside space-y-1">
                                    <li>{{ __('Check locations to add them to your catalog') }}</li>
                                    <li>{{ __('Locations already added will be skipped automatically') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- Modal Footer:
                 Cancel or confirm bulk add -->

            <div
                class="flex justify-end gap-3 p-4 bg-gray-100 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                <button type="button" wire:click="$dispatch('close-modal', 'bulk-mycatalog-product-modal')"
                    class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    {{ __('Cancel') }}
                </button>

                <x-primary-button type="button" wire:click="updateBulkMyCatalogLocations"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="updateBulkMyCatalogLocations"
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span wire:loading.remove wire:target="updateBulkMyCatalogLocations">{{ __('+ Add') }}</span>
                    <span wire:loading wire:target="updateBulkMyCatalogLocations">{{ __('Processing...') }}</span>
                </x-primary-button>
            </div>
        </div>
    </div>
</x-modal>