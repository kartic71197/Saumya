<x-modal name="picking_product_modal" width="w-100" height="h-auto" maxWidth="6xl" wire:model="showModal">
    <header class="p-3 border-b border-gray-300 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-lg font-medium text-gray-600 dark:text-gray-100">
            {{ __('Pickings') }}
        </h2>
        <div class="p-3 font-semibold dark:text-gray-100">
            {{ $pickingNumber ?? '' }}
        </div>
    </header>

    <form wire:submit.prevent="updatePicking">
        <div class="dark:bg-gray-800 bg-white">
            @if ($selectedProduct && $selectedProduct->product)
                {{-- PRODUCT DETAILS --}}
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr class="text-left text-gray-700 dark:text-gray-300">
                                    <th class="p-3 border-b">Product Name</th>
                                    <th class="p-3 border-b">Unit</th>
                                    @if($selectedProduct->product->has_expiry_date && $selectedProduct->batch_number && $selectedProduct->expiry_date)
                                        <th class="p-3 border-b">Batch</th>
                                        <th class="p-3 border-b">Expiration</th>
                                    @endif
                                    <th class="p-3 border-b">{{ __('Available') }}</th>
                                    <th class="p-3 border-b">Pick Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-gray-900 dark:text-gray-200">
                                    <td class="p-3 border-b font-semibold text-wrap">
                                        {{ $selectedProduct->product->product_name }}
                                        ({{ $selectedProduct->product->product_code }})
                                    </td>
                                    <td class="p-3 border-b">
                                        {{ $selectedProduct->product->units[0]->unit->unit_name }}
                                    </td>
                                    @if($selectedProduct->product->has_expiry_date && $selectedProduct->batch_number && $selectedProduct->expiry_date)
                                        <td class="p-3 border-b text-nowrap">
                                            {{ $selectedProduct->batch_number ?? 'N/A' }}
                                        </td>
                                        <td class="p-3 border-b text-nowrap">
                                            {{ $selectedProduct->expiry_date ? \Carbon\Carbon::parse($selectedProduct->expiry_date)->format('m/Y') : 'N/A' }}
                                        </td>
                                    @endif
                                    <td class="p-3 border-b font-medium">
                                        {{ $selectedProduct->on_hand_quantity }}
                                    </td>

                                    <td class="p-3 border-b">
                                        <div class="flex items-center">
                                            <button type="button" wire:click="decrementQuantity"
                                                class="flex items-center justify-center w-8 h-8 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-l-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20 12H4" />
                                                </svg>
                                            </button>

                                            <input type="number" min="0" wire:model.live="pickQuantity"
                                                class="w-16 px-2 py-1 text-center border-t border-b border-gray-300 dark:border-gray-600 focus:ring focus:ring-blue-400 bg-gray-100 dark:bg-gray-700 dark:text-white [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

                                            <button type="button" wire:click="incrementQuantity"
                                                class="flex items-center justify-center w-8 h-8 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                        @if ($pickError)
                                            <div class="text-red-600 text-sm mt-1">{!! $pickError !!}</div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            @else
                <div class="text-red-500 text-center font-medium p-3">Product not found.</div>
            @endif
            {{-- BIOLOGICAL PRODUCT WARNING --}}
            @if ($isBiologicalProduct)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 mt-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Biological Product - Patient Level Picking Required
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>
                                    Picking will be handled at the patient's level for proper tracking and administration.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ALTERNATIVE BATCH NOTICE (works for all products now) --}}
            @if ($selectedProduct?->product && $alternativeBatch?->product)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-3 my-3 rounded flex justify-between items-center">
                    <p>
                        Another batch of {{ $selectedProduct->product->product_name }}
                        expires sooner on
                        <strong><u>{{ \Carbon\Carbon::parse($alternativeBatch->expiry_date)->format('d-M-Y') }}</u></strong>.
                    </p>
                    <button type="button" wire:click="switchBatch({{ $alternativeBatch->id }})"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-1.5 rounded-full text-sm shadow-md transition-all">
                        Pick this instead
                    </button>
                </div>
            @endif

            {{-- FOOTER BUTTONS --}}
            <div class="flex justify-end gap-4 p-4">
                <x-secondary-button type="button"
                    class="px-6 py-2 text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg transition-all"
                    wire:click="cancelPicking">
                    {{ __('Cancel') }}
                </x-secondary-button>

                @if ($isBiologicalProduct)
                    <!-- Biological product - redirect directly -->
                    <a href="{{ route('patient.index') }}"
                       class="px-6 py-2 text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-all font-medium inline-block text-center">
                        {{ __('Pick') }}
                    </a>
                @else
                    <!-- Regular product - normal picking -->
                    <x-primary-button class="px-6 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Pick') }}</span>
                        <span wire:loading>{{ __('Processing...') }}</span>
                    </x-primary-button>
                @endif
            </div>
        </div>
    </form>
</x-modal>