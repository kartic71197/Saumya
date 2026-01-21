<x-modal name="cart-product-modal" width="w-100" height="h-auto" maxWidth="6xl">
    <!-- 
        Add to Cart Modal
        -----------------
        - Displays all eligible locations for adding the selected product.
        - Excludes locations where the product already exists in Cart or Mycatalog.
        - Allows adjusting unit and quantity per location.
        - Submits all at once using Livewire method `addToCartBulk`.
    -->
    <form wire:submit.prevent="addToCartBulk" class="bg-gray-50 dark:bg-gray-800">

        <!-- Main Table -->
        <div class="overflow-hidden bg-white dark:bg-gray-900 shadow rounded-lg">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead
                    class="text-xs uppercase text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 sticky top-0 z-10">
                    <tr>
                        <th class="p-3 w-1/4">{{ __('Location') }}</th>
                        <th class="p-3 w-1/4">{{ __('Unit') }}</th>
                        <th class="p-3 w-1/4">{{ __('Quantity') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @if(!empty($locations))
                        @foreach ($locations as $location)
                            @php
                                $locationId = $location['id'];
                            @endphp

                            <!-- Each row has a unique Livewire key to prevent shared DOM state -->
                            <tr wire:key="location-row-{{ $locationId }}"
                                class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition">

                                <!-- Location Name -->
                                <td class="p-3 font-medium text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $location['name'] }}
                                    </div>
                                </td>

                                <!-- Unit Selection -->
                                <td class="p-3">
                                    <select wire:model="locationUnits.{{ $locationId }}"
                                        wire:key="unit-select-{{ $locationId }}"
                                        class="w-full px-3 py-2 text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                        @foreach ($units ?? [] as $unit)
                                            <option value="{{ $unit['unit_id'] }}">
                                                {{ $unit['unit_name'] ?? 'Unknown Unit' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Quantity Input -->
                                <td class="p-3">
                                    <div
                                        class="flex items-center rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden w-32">
                                        <!-- Minus Button -->
                                        <button type="button"
                                            wire:click="$set('locationQuantities.{{ $locationId }}', Math.max(0, ({{ $locationQuantities[$locationId] ?? 1 }} - 1)))"
                                            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 12H4" />
                                            </svg>
                                        </button>

                                        <!-- Input -->
                                        <input type="number" min="0" max="1000"
                                            wire:model.defer="locationQuantities.{{ $locationId }}"
                                            wire:key="quantity-input-{{ $locationId }}"
                                            class="w-full text-center border-0 focus:ring-0 dark:bg-gray-800  dark:text-gray-300 [appearance:textfield]  [&::-webkit-outer-spin-button]:appearance-none  [&::-webkit-inner-spin-button]:appearance-none text-sm py-1">

                                        <!-- Plus Button -->
                                        <button type="button"
                                                    wire:click="$set('locationQuantities.{{ $locationId }}', {{ min(1000, ($locationQuantities[$locationId] ?? 1) + 1) }})"
                                                    class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <!-- No Locations Available -->
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-500 dark:text-gray-400">
                                {{ __('No locations available.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Info Box -->
        @if (!empty($locations))
            <div
                class="mt-4 mb-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        {{ __('Set quantity to 0 to skip a location. Items already in cart or Mycatalog are excluded automatically.') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Footer Actions -->
        <div
            class="flex justify-end gap-3 p-4 bg-gray-100 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
            <x-primary-button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700"
                wire:loading.attr="disabled">
                <!-- Loading Spinner -->
                <svg wire:loading wire:target="addToCartBulk" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0
                           c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <!-- Button Text -->
                <span wire:loading.remove wire:target="addToCartBulk">{{ __('Add to Cart') }}</span>
                <span wire:loading wire:target="addToCartBulk">{{ __('Processing...') }}</span>
            </x-primary-button>
        </div>
    </form>
</x-modal>