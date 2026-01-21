<x-modal name="mycatalog-product-modal" width="w-100" maxWidth="6xl">
    <div class="bg-white dark:bg-gray-800">
        <div class="bg-gray-50 dark:bg-gray-800">
            <!-- Modal Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Manage Product') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Select locations to add products into your inventory') }}
                </p>
            </div>

            <div class="p-4">
                <div class="overflow-hidden bg-white dark:bg-gray-900 shadow rounded-lg">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                        <thead
                            class="text-xs uppercase text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 w-1/12 text-center"> </th>
                                <th class="p-3 w-2/5">{{ __('Location') }}</th>
                                <th class="p-3 w-1/5 text-center">{{ __('On-Hand Quantity') }}</th>
                                {{-- <th class="p-3 w-1/5 text-center">{{ __('Status') }}</th> --}}
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if(!empty($locations))
                                @foreach ($locations as $location)
                                                    @php
                                                        $locationId = $location->id;
                                                        $isChecked = isset($selectedLocations[$locationId]) && $selectedLocations[$locationId];
                                                        $inMyCatalog = \App\Models\Mycatalog::where('location_id', $locationId)
                                                            ->where('product_id', $this->selectedProductId)
                                                            ->first();

                                                        $onHandQty = $location->on_hand_quantity ?? 0;
                                                        $hasInventory = $onHandQty > 0;
                                                    @endphp
                                                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                                        <!-- Checkbox Column -->
                                                        <td class="p-3 text-center">
                                                            @if(!$inMyCatalog)
                                                                <input type="checkbox" @checked(isset($selectedLocations[$locationId]) && $selectedLocations[$locationId]) wire:click="toggleLocation({{ $locationId }})"
                                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded">
                                                            @endif

                                                        </td>

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

                                                        <!-- On-Hand Quantity Column -->
                                                        {{-- <td class="p-3 text-center">
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium  {{ $hasInventory       ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'  : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                                {{ number_format($onHandQty, 2) }}
                                                            </span>
                                                        </td> --}}

                                                        <!-- Status Column -->
                                                        <td class="p-3 text-center">
                                                            @if ($inMyCatalog)
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                In Inventory
                                                            </span>
                                                            @else
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                                Not in Inventory
                                                            </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-gray-500 dark:text-gray-400">
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
                                    {{-- <li>{{ __('Products already added will be removed through Invebtory only.') }}</li>
                                    <li>{{ __('Locations with inventory cannot be removed until cleared') }}</li> --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div
                class="flex justify-end gap-3 p-4 bg-gray-100 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                <button type="button" wire:click="$dispatch('close-modal', 'mycatalog-product-modal')"
                    class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    {{ __('Cancel') }}
                </button>

                <x-primary-button type="button" wire:click="updateMyCatalogLocations"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="updateMyCatalogLocations"
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span wire:loading.remove wire:target="updateMyCatalogLocations">{{ __('+ Add') }}</span>
                    <span wire:loading wire:target="updateMyCatalogLocations">{{ __('Processing...') }}</span>
                </x-primary-button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal for Inventory Warning -->
    <x-modal name="mycatalog-inventory-warning" width="w-100" height="h-auto" maxWidth="md">
        <div class="bg-white dark:bg-gray-800 p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Cannot Remove Location') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('The following locations have inventory and cannot be removed from your catalog:') }}
                    </p>
                    @if(!empty($locationsWithInventory))
                        <ul class="mt-3 space-y-2">
                            @foreach($locationsWithInventory as $location)
                                <li
                                    class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $location['name'] }}
                                    </span>
                                    <span class="text-sm text-yellow-800 dark:text-yellow-200">
                                        {{ __('Qty:') }} {{ number_format($location['quantity'], 2) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Please clear the inventory before removing these locations from your catalog.') }}
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="$dispatch('close-modal', 'mycatalog-inventory-warning')"
                    class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    {{ __('Understood') }}
                </button>
            </div>
        </div>
    </x-modal>
</x-modal>