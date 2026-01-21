<x-modal name="add-prescription-modal" width="w-100" height="h-auto" maxWidth="6xl">
    <!-- Wrapper that catches outside clicks -->
    <div
        class="relative"
        x-on:click.outside="$dispatch('close-modal', 'add-prescription-modal'); $wire.resetForm()"
    >
    <header class="p-3">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Assign medication') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add information regarding patient\'s medication') }}
        </p>
    </header>

    <form wire:submit.prevent="createPrescription">
        <div class="space-y-3">
            <div class="border-b border-gray-900/10 pb-12 px-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <!-- Hidden location field (if needed) -->
                    <input type="hidden" wire:model="location" />
                    <div class="sm:col-span-1">
                        <x-input-label for="date_given" :value="__('Date given')" />
                        <x-text-input id="date_given" wire:model="date_given" class="mt-1 block w-full" type="date" />
                        @error('date_given') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="drug" :value="__('Drug')" />
                        <div class="p-2 relative">
                            <input type="text" wire:model.live="product_search" wire:focus="showDropdown"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('product_search') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror"
                                autocomplete="off" placeholder="Search product..." />

                            <input type="hidden" wire:model="product_id" />

                            @error('product_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            @if($show_dropdown && $products && count($products) > 0)
                                <div
                                    class="absolute z-[999] w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    @foreach($products as $product)
                                        <div wire:click="selectProduct({{ $product->id }})"
                                            class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                                            {{ $product->product_name . ' (' . $product->product_code . ')' }}
                                            @if(isset($product->batch_number))
                                                - {{ 'LOT# ' . $product->batch_number }}
                                            @endif
                                            <div class="text-xs text-gray-500">{{ $product->location_name ?? '' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($show_dropdown && !empty($product_search) && (!$products || count($products) == 0))
                                <div
                                    class="absolute z-[999] w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg">
                                    <div class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                        No products found
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="batch_number" :value="__('Batch Number')" />
                        <x-text-input id="batch_number" wire:model.live="batch_number" type="text"
                            class="mt-1 block w-full" readonly />
                        @error('batch_number') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="expiry_date" :value="__('Expiry Date')" />
                        <x-text-input id="expiry_date" wire:model.live="expiry_date" type="date"
                            class="mt-1 block w-full" readonly />
                        @error('expiry_date') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <div class="flex items-center rounded-md dark:border-gray-600 overflow-hidden mt-1">
                            <!-- Minus Button -->
                            <button type="button" wire:click="decrementQuantity"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4" />
                                </svg>
                            </button>

                            <!-- Input without arrows -->
                            <input type="number" min="1" max="100" wire:model.live="quantity"
                                class="w-12 text-center border-0 focus:ring-0 dark:bg-gray-800 dark:text-gray-300 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

                            <!-- Plus Button -->
                            <button type="button" wire:click="incrementQuantity"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                        @error('quantity') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="dose" :value="__('Dose')" />
                        <x-text-input id="dose" wire:model.live="dose" type="text" class="mt-1 block w-full" readonly />
                        @error('dose') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="frequency" :value="__('Frequency')" />
                        {{-- <x-text-input id="frequency" wire:model="frequency" type="text" class="mt-1 block w-full"
                            required /> --}}
                        <select id="frequency" wire:model.live="frequency" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
        <option value="">-- Select Frequency --</option>
        <option value="Every Two Weeks">Every Two Weeks</option>
        <option value="Every Four Weeks">Every Four Weeks</option>
        <option value="Every Eight Weeks">Every Eight Weeks</option>
        <option value="Custom">Custom</option>
    </select>

    @if($frequency === 'Custom')
        <x-text-input id="custom_frequency" wire:model="custom_frequency" placeholder="Enter custom frequency" class="mt-2 block w-full" required />
        @error('custom_frequency') <span class="text-red-500">{{ $message }}</span> @enderror
    @endif
                        @error('frequency') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <x-input-label for="paid" :value="__('INS Paid')" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">{{ $organization?->currency }}</span>
                            </div>
                            <x-text-input id="paid" wire:model.live="paid" type="number" step="0.01"
                                class="mt-1 block w-full pl-8" />
                        </div>
                        @error('paid') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <x-input-label for="our_cost" :value="__('Our Cost')" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">{{ $organization?->currency }}</span>
                            </div>
                            <x-text-input id="our_cost" wire:model.live="our_cost" type="number" step="0.01"
                                class="mt-1 block w-full pl-8" readonly />
                        </div>
                        @error('our_cost') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                     <div class="sm:col-span-1">
                        <x-input-label for="price" :value="__('Price')" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">{{ $organization?->currency }}</span>
                            </div>
                            <x-text-input id="price" wire:model.live="price" type="number" step="0.01"
                                class="mt-1 block w-full pl-8"  />
                        </div>
                        @error('price') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    

                    <div class="sm:col-span-1">
                        <x-input-label for="pt_copay" :value="__('PT Paid')" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">{{ $organization?->currency }}</span>
                            </div>
                            <x-text-input id="pt_copay" wire:model.live="pt_copay" type="number" step="0.01"
                                class="mt-1 block w-full pl-8" />
                        </div>
                        @error('pt_copay') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <x-input-label for="profit" :value="__('Profit')" />
                        <div class="mt-1 flex items-center">
                            <div
                                class="bg-gray-100 px-3 py-2 rounded-md w-full text-gray-700 dark:text-gray-300 relative">
                                {{ $organization?->currency }}{{ number_format($profit, 2) }}
                                <div wire:loading wire:target="paid, our_cost, pt_copay"
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                    <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="mt-6 flex items-center justify-end gap-x-6 px-6 pb-4">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'add-prescription-modal')"
                    class="text-sm font-semibold text-gray-900">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit"
                    class="min-w-24 flex justify-center items-center text-sm font-semibold text-gray-900">
                    {{ __('Submit') }}
                </x-primary-button>
            </div>
        </div>
    </form>
      </div>
</x-modal>