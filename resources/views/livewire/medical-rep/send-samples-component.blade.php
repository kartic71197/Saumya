<div class="p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <!-- Header -->
        <div class="px-4 pt-4 pb-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Send Samples
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">
                        Select products to send as samples to {{ $organization->name ?? 'the organization' }}
                    </p>
                </div>

                @if($location)
                    <div class="bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg">
                        <div class="text-xs font-medium text-blue-900 dark:text-blue-100">
                            {{ $location->name }}
                        </div>
                        <div class="text-xs text-blue-700 dark:text-blue-300">
                            ({{ $organization->name ?? 'N/A' }})
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sale Number -->
            <div class="mt-2">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Sample Number:</span>
                <span class="ml-1 text-xs font-semibold text-gray-900 dark:text-white">{{ $sale_number }}</span>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="searchTerm"
                    placeholder="Search products by name or code..."
                    class="w-full px-3 py-1.5 pl-8 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                <i class="fas fa-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
        </div>

        <!-- Products List -->
        <div class="px-4 py-3">
            @if(count($products) > 0)
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($products as $product)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-start gap-3">
                                <!-- Checkbox -->
                                <div class="flex-shrink-0 pt-0.5">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleProduct({{ $product['id'] }})"
                                        @checked($selectedProducts[$product['id']]['is_selected'] ?? false)
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                </div>

                                <!-- Product Info -->
                                <div class="flex-grow min-w-0 flex items-center gap-2">
                                    <!-- Product Details -->
                                    <div class="flex-grow min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $product['name'] }}
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            Code: {{ $product['code'] }} | Brand: {{ $product['brand'] }}
                                        </p>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="w-24 flex-shrink-0">
                                        <input 
                                            type="number" 
                                            wire:model.blur="selectedProducts.{{ $product['id'] }}.quantity"
                                            min="0.01"
                                            step="0.01"
                                            placeholder="Qty"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        >
                                        @error("selectedProducts.{$product['id']}.quantity")
                                            <span class="text-xs text-red-600 dark:text-red-400 block mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Unit Select -->
                                    <div class="w-32 flex-shrink-0">
                                        <select 
                                            wire:model.change="selectedProducts.{{ $product['id'] }}.unit_id"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        >
                                            @foreach($product['units'] as $unit)
                                                <option value="{{ $unit['id'] }}">
                                                    {{ $unit['name'] }} ({{ $unit['code'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("selectedProducts.{$product['id']}.unit_id")
                                            <span class="text-xs text-red-600 dark:text-red-400 block mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-box-open text-gray-400 dark:text-gray-600 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $searchTerm ? 'No products found matching your search.' : 'No sample products available.' }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Selected Summary & Actions -->
        @if(count($this->getSelectedProductsOnly()) > 0)
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-between items-center">
                    <div class="space-y-0.5">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Selected Products: 
                            <span class="font-semibold text-gray-900 dark:text-white">{{ count($this->getSelectedProductsOnly()) }}</span>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Total Quantity: 
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $this->getTotalQuantity() }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button 
                            wire:click="saveSamples"
                            wire:loading.attr="disabled"
                            class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="saveSamples">
                                <i class="fas fa-paper-plane mr-1.5"></i>
                                Send Samples
                            </span>
                            <span wire:loading wire:target="saveSamples">
                                <i class="fas fa-spinner fa-spin mr-1.5"></i>
                                Sending...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>