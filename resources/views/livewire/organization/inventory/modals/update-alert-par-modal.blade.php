<x-modal name="update-alert-par-modal" maxWidth="4xl" class="z-[9999] fixed">
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Update Alert & Par Quantity
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage inventory threshold levels
                    </p>
                </div>
            </div>
            <button type="button"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
                wire:click="$dispatch('close-modal','update-alert-par-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Product & Location Info Card -->
        <div
            class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Product
                        </p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                            {{ $stockCount?->product->product_name }}
                        </p>
                        @if($stockCount?->product->product_code)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Code: {{ $stockCount?->product->product_code }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Location
                        </p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                            {{ $stockCount?->location?->name ?? 'N/A'  }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Stock Status -->
        @if($stockCount)
            <div class="mb-6">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Current Stock</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">On hand quantity</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p
                            class="text-2xl font-bold {{ $stockCount->total_quantity < $stockCount->alert_quantity ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($stockCount->total_quantity, 2) }}
                        </p>
                        @if($stockCount->total_quantity < $stockCount->alert_quantity)
                            <p class="text-xs text-red-500 font-medium">Below Alert Level</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Alert Quantity -->
            <div class="relative">
                <label for="alert_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Alert Quantity
                </label>
                <input type="number" id="alert_quantity" wire:model="alert_quantity" min="0" step="1"
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500 pl-3 pr-12 py-3 text-lg">
                @error('alert_quantity')
                    <p class="text-red-500 text-sm mt-1 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Par Quantity -->
            <div class="relative">
                <label for="par_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Par Quantity
                </label>
                <input type="number" id="par_quantity" wire:model="par_quantity" min="0" step="1"
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 pl-3 pr-12 py-3 text-lg">
                @error('par_quantity')
                    <p class="text-red-500 text-sm mt-1 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>
        </div>


        <!-- Footer -->
        <div
            class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" wire:click="$dispatch('close-modal','update-alert-par-modal')"
                class="w-full sm:w-auto px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200 flex items-center justify-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Cancel</span>
            </button>
            <button type="button" wire:click="updateAlertPar" wire:loading.attr="disabled"
                class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 flex items-center justify-center space-x-2">
                <span wire:loading.remove wire:target="updateAlertPar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </span>
                <span wire:loading wire:target="updateAlertPar">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="updateAlertPar">Update threshold</span>
                <span wire:loading wire:target="updateAlertPar">Updating...</span>
            </button>
        </div>
    </div>
</x-modal>