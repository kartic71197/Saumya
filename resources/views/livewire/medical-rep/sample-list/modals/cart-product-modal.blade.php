<x-modal name="cart-product-modal" width="w-100" height="h-auto" maxWidth="6xl">
    <div class="bg-white dark:bg-gray-800">
        <form wire:submit.prevent="addToCartBulk" class="bg-gray-50 dark:bg-gray-800">
            <div class="p-4 max-h-[500px] overflow-y-auto">
                <div class="overflow-hidden bg-white dark:bg-gray-900 shadow rounded-lg">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 w-1/4">{{ __('Location') }}</th>
                                <th class="p-3 w-1/4">{{ __('Unit') }}</th>
                                <th class="p-3 w-1/4">{{ __('Quantity') }}</th>
                                <th class="p-3 w-1/4">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if(!empty($locations))
                                @foreach ($locations as $location)
                                    @php
                                        $locationId = $location['id'];
                                        $existingInCart = \App\Models\Cart::where('product_id', $selectedProductId)
                                            ->where('location_id', $locationId)
                                            ->exists();
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                        <td class="p-3 font-medium text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $location['name'] }}
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <select wire:model="locationUnits.{{ $locationId }}"
                                                class="w-full px-3 py-2 text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">{{ __('Select Unit') }}</option>
                                                @if(!empty($units))
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit['unit_id'] ?? 'N/A' }}">
                                                            @if(isset($unit['unit_name']))
                                                            {{ $unit['unit_name'] }}
                                                                @else
                                                                    Unit ID: {{ $unit['unit_id'] ?? 'Unknown' }} (Name Missing)
                                                                @endif
                                                            </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td class="p-3">
                                            <div class="flex items-center rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden w-32">
                                                <!-- Minus Button -->
                                                <button type="button"
                                                    wire:click="$set('locationQuantities.{{ $locationId }}', {{ max(0, ($locationQuantities[$locationId] ?? 1) - 1) }})"
                                                    class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>

                                                <!-- Input -->
                                                <input type="number" min="0" max="1000"
                                                    wire:model.defer="locationQuantities.{{ $locationId }}"
                                                    class="w-full text-center border-0 focus:ring-0 dark:bg-gray-800 dark:text-gray-300 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none text-sm py-1">

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
                                        <td class="p-3">
                                            @if ($existingInCart)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    In Cart
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Not in Cart
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
                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-medium">{{ __('Tip:') }}</p>
                                <p>{{ __('Set quantity to 0 to skip a location. Items already in cart will be updated with new quantities.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 p-4 bg-gray-100 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                <x-primary-button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="addToCartBulk" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="addToCartBulk">{{ __('Add to Cart') }}</span>
                    <span wire:loading wire:target="addToCartBulk">{{ __('Processing...') }}</span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>