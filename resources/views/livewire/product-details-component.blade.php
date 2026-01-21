<div>
    @if($showModal)
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeModal">

            <!-- Modal Content -->
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden"
                wire:click.stop>

                <!-- Modal Header -->
                <div
                    class="bg-gradient-to-r from-[var(--color-primary-md)] to-[var(--color-primary-dk)] px-6 py-4 text-white">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold">{{ $product->product_name ?? 'Product Details' }}</h2>
                                <p class="text-[var(--color-primary-xl)] opacity-90 text-sm">
                                    {{ $product->product_code ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        @if ($context === 'catalog')
                            <button
                                class="w-24 flex justify-center items-center relative px-4 py-2 bg-red-800 hover:bg-red-700 text-white font-medium rounded-md shadow"
                                x-data="{ loading: false }"
                                x-on:click="loading = true;  $wire.closeModal(); $dispatch('edit-product', { rowId: {{ $productId ?? 0 }} }); setTimeout(() => loading = false, 1000)">
                                <!-- Normal state -->
                                <span class="flex items-center gap-2" :class="{ 'invisible': loading }">
                                    <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                        <path fill="currentColor"
                                            d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z" />
                                    </svg>
                                    Edit
                                </span>

                                <!-- Loading state -->
                                <span x-show="loading" class="absolute inset-0 flex items-center justify-center">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
                                    </svg>
                                </span>
                            </button>
                        @else
                            <button wire:click="closeModal"
                                class="w-8 h-8 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg flex items-center justify-center transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Modal Body -->
                {{-- <div class="overflow-y-auto max-h-[calc(90vh-80px)] scrollbar-hidden"> --}}
                    <div class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-hidden">
                        @if($product)
                            <div class="p-6 space-y-6">

                                <!-- Product Image and Details -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Product Image -->
                                    <div>
                                        <div
                                            class="bg-gradient-to-br from-[var(--color-primary-xl)] to-white rounded-xl p-4 h-64 flex items-center justify-center border border-[var(--color-primary-lt)] border-opacity-30">
                                            @php
                                                if (str_starts_with($product->image, 'http')) {
                                                    $fullImageUrl = $product->image;
                                                } else {
                                                    $images = json_decode($product->image, true);
                                                    $imagePath = is_array($images) && !empty($images) ? $images[0] : $product->image;
                                                    $fullImageUrl = asset('storage/' . $imagePath);
                                                }
                                            @endphp
                                            @if($product->image)
                                                <img src="{{ $fullImageUrl }}" alt="{{ $product->product_name }}"
                                                    class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                                            @else
                                                <div class="text-center text-[var(--color-primary-md)]">
                                                    <svg class="w-16 h-16 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <p class="text-sm opacity-60">No Image Available</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="space-y-4">
                                        <!-- Status and Active -->
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                class="px-3 py-1 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($product->has_expiry_date)
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-medium bg-[var(--color-primary-xl)] text-[var(--color-primary-dk)] border border-[var(--color-primary-lt)]">
                                                    Has Expiry Date
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Description -->
                                        @if($product->product_description)
                                            <div class="bg-gray-50 rounded-lg p-3">
                                                <p class="text-sm text-gray-600 leading-relaxed">{{ $product->product_description }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Product Information -->
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="font-semibold text-gray-800 mb-3 text-sm">Product Information</h4>
                                            <div class="grid grid-cols-1 gap-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Manufacture Code:</span>
                                                    <span
                                                        class="font-medium text-gray-800">{{ $product->manufacture_code ?? 'N/A' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Category:</span>
                                                    <span
                                                        class="font-medium text-gray-800">{{ ucfirst($product->categories?->category_name) ?? 'N/A' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Brand:</span>
                                                    <span
                                                        class="font-medium text-gray-800">{{ ucfirst($product->brand?->brand_name) ?? 'N/A' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Supplier:</span>
                                                    <span
                                                        class="font-medium text-gray-800">{{ ucfirst($product->supplier?->supplier_name) ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dimensions -->
                                        {{-- @if($product->weight || $product->length || $product->width || $product->height)
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <h4 class="font-semibold text-gray-800 mb-2 text-sm">Dimensions & Weight</h4>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                @if($product->weight)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Weight:</span>
                                                    <span class="font-medium">{{ $product->weight }}</span>
                                                </div>
                                                @endif
                                                @if($product->length)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Length:</span>
                                                    <span class="font-medium">{{ $product->length }}</span>
                                                </div>
                                                @endif
                                                @if($product->width)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Width:</span>
                                                    <span class="font-medium">{{ $product->width }}</span>
                                                </div>
                                                @endif
                                                @if($product->height)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Height:</span>
                                                    <span class="font-medium">{{ $product->height }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif --}}
                                    </div>
                                </div>


                                @if ($context === 'inventory' && isset($batchDetails) && count($batchDetails) > 0)
                                    <div class="border-t border-gray-200 pt-6">
                                        <h3 class="text-base font-semibold text-gray-800 mb-3">Inventory Batch Details</h3>

                                        <div class="space-y-1">
                                            @foreach ($batchDetails as $batch)
                                                <div
                                                    class="bg-white border border-gray-200 rounded px-3 py-2 flex items-center justify-between text-xs hover:shadow-sm transition-shadow">
                                                    {{-- Left content --}}
                                                    <div class="flex items-center gap-4 flex-wrap">
                                                        <span class="text-gray-600">Batch:
                                                            {{ $batch['batch_number'] ?? 'N/A' }}</span>
                                                        <span class="text-gray-600">|</span>
                                                        <span class="text-gray-600">Exp:
                                                            {{ $batch['expiry_date'] ?? 'N/A' }}</span>
                                                        @if ($batch['quantity'] > 0)
                                                            <span class="text-gray-600">|</span>
                                                            <span class="font-medium text-gray-600">Qty:
                                                                {{ $batch['quantity'] }}</span>
                                                        @endif
                                                    </div>

                                                    {{-- Status --}}
                                                    <span class="px-2 py-0.5 rounded-full border text-xs
                                                                @if ($batch['status'] === 'Expired') bg-red-100 text-red-600 border-red-300
                                                                @elseif($batch['status'] === 'Critical') bg-purple-100 text-purple-600 border-purple-300
                                                                @elseif($batch['status'] === 'Expiring Soon') bg-yellow-100 text-yellow-600 border-yellow-300
                                                                @elseif($batch['status'] === 'Good') bg-green-100 text-green-600 border-green-300
                                                                @else bg-gray-100 text-gray-600 border-gray-300 @endif">
                                                        {{ ucfirst($batch['status']) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($context === 'inventory')
                                    <div class="border-t border-gray-200 pt-6">
                                        <h3 class="text-base font-semibold text-gray-800 mb-3">Inventory Batch Details</h3>
                                        <div class="text-center py-6 text-gray-500">
                                            <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            @if (!$selectedLocationId)
                                                <p class="text-sm">Please select location to see the details</p>
                                            @else
                                                <p class="text-sm">No batch details available for this product</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <!-- Recent Purchase Orders -->
                                <div class="border-t border-gray-200 pt-6">
                                    @if ($context === 'top_pickups')
                                        <h3 class="font-semibold text-gray-800 mb-2">Recent Pickups</h3>
                                        @if($latestPickups->count() > 0)
                                            <div class="space-y-1">
                                                @foreach($latestPickups as $pickup)
                                                    <div
                                                        class="bg-white border border-gray-200 rounded px-3 py-2 flex items-center justify-between text-xs hover:shadow-sm transition-shadow">
                                                        <div class="flex items-center gap-4 flex-wrap">
                                                            <span class="font-medium text-gray-800">{{ $pickup->picking_number }}</span>
                                                            <span class="text-gray-600">|</span>
                                                            <span class="text-gray-600">Date: {{ $pickup->date }}</span>
                                                            <span class="text-gray-600">|</span>
                                                            <span class="text-gray-600 font-medium">Quantity: {{ $pickup->quantity }} /
                                                                {{ $pickup->unit }}</span>
                                                        </div>
                                                        <span
                                                            class="px-2 py-0.5 rounded-full border text-xs bg-green-100 text-green-700 border-green-300">
                                                            Picked
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-6 text-gray-500">
                                                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm">No pickups found for this product</p>
                                            </div>
                                        @endif
                                    @else
                                        <h3 class="text-base font-semibold text-gray-800 mb-3">Recent Purchase Orders</h3>
                                        @if(count($latestPurchaseOrders) > 0)
                                            <div class="space-y-1">
                                                @foreach($latestPurchaseOrders as $po)
                                                    <div
                                                        class="bg-white border border-gray-200 rounded px-3 py-2 flex items-center justify-between text-xs hover:shadow-sm transition-shadow">
                                                        <div class="flex items-center gap-4 flex-wrap">
                                                            <span class="font-medium text-gray-800">{{ $po['po_number'] }}</span>
                                                            <span class="text-gray-600">|</span>
                                                            <span class="text-gray-600">Date :
                                                                {{ date(session('date_format', 'd M Y'), strtotime($po['order_date'])) }}
                                                            </span>
                                                            <span class="text-gray-600">|</span>
                                                            <span class="text-gray-600 font-medium">Quantity: {{ $po['ordered_quantity'] }}/
                                                                {{ $po['ordered_unit'] }}</span>
                                                        </div>
                                                        <span
                                                            class="px-2 py-0.5 rounded-full border text-xs {{ $po['status_badge_class'] }}">
                                                            {{ ucfirst($po['status']) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-6 text-gray-500">
                                                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm">No purchase orders found for this product</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <!-- Audit Information -->
                                {{-- <div class="bg-gray-50 rounded-lg p-3">
                                    <h4 class="font-semibold text-gray-800 mb-2 text-sm">Audit Information</h4>
                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Created By:</span>
                                            <span class="font-medium">{{ $product->creator->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Updated By:</span>
                                            <span class="font-medium">{{ $product->updater->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Created:</span>
                                            <span class="font-medium">{{ $product->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Updated:</span>
                                            <span class="font-medium">{{ $product->updated_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div> --}}
                                @if ($context === 'inventory')
                                <div class="w-full flex justify-end items-center">
                                    @if($msg)
                                    <div class="text-red-500 text-sm mr-4">
                                        {{$msg}}
                                    </div>
                                    @endif
                                    <button 
    wire:click="removeFromInventory" 
    class="mt-4 px-4 py-2 rounded text-white 
           {{ $selectedLocationId ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed' }}">
    Remove from Inventory
</button>

                                </div>  
                                @endif
                            </div>
                        @else
                            <div class="p-8 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p>Product not found</p>
                            </div>
                        @endif
                    </div>
                    @if($context === 'low-stock')
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                            <button wire:click="closeModal"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                                Cancel
                            </button>

                            @if ($this->canAddToCart())
                                <button wire:click="addToCart"
                                    class="px-4 py-2 rounded-lg bg-[var(--color-primary-md)] text-white hover:bg-[var(--color-primary-dk)] transition">
                                    Add to Cart
                                </button>
                            @else
                                <div
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-100 text-red-700 border border-red-300 text-sm font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-9-3a1 1 0 112 0v3a1 1 0 11-2 0V7zm1 8a1.25 1.25 0 100-2.5A1.25 1.25 0 0010 15z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Product is already in your cart.</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            {{-- @endif --}}
            <x-modal name="add-product-to-cart" width="w-100" height="h-auto" maxWidth="4xl" class="z-9999 fixed">
                <header class="p-3 border-b border-gray-300 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Add to Cart') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Review the details below before adding to the cart.') }}
                    </p>
                </header>
                <form wire:submit.prevent="addProductToCart" class="bg-gray-50 dark:bg-gray-800">
                    <div class="py-3">
                        <div class="overflow-hidden bg-white dark:bg-gray-900 shadow rounded-lg">
                            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                                <thead class="text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="p-3">{{ __('Product Name') }}</th>
                                        <th class="p-3">{{ __('Clinic') }}</th>
                                        <th class="p-3 hidden">{{ __('Base Price') }}</th>
                                        <th class="p-3">{{ __('Unit') }}</th>
                                        <th class="p-3">{{ __('Quantity') }}</th>
                                        <th class="p-3 hidden">{{ __('Final Price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <td class="p-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $product_name }}
                                        </td>
                                        <td class="p-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $location_name }}
                                        </td>
                                        <td class="p-3 hidden">
                                            <span>${{ number_format($product_cost, 2) }}</span>
                                        </td>
                                        <td class="p-3">
                                            <select id="unit" wire:model.live="unit_id" wire:change="updateFinalPrice"
                                                class="block w-full border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600">
                                                @foreach ($units as $unit)
                                                    <option value="{{ $unit['unit_id'] }}" {{ $unit['is_base_unit'] ? 'selected' : '' }}>
                                                        {{ $unit['unit_name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-3">
                                            <div class="flex items-center rounded-md dark:border-gray-600 overflow-hidden">
                                                <!-- Minus Button -->
                                                <button type="button" x-on:click="
                                                            $wire.set('quantity', Math.max(1, Number($wire.quantity) - 1), false);
                                                            clearTimeout(window.quantityTimer);
                                                            window.quantityTimer = setTimeout(() => $wire.updateFinalPrice(), 500);
                                                        "
                                                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>

                                                <!-- Input without arrows -->
                                                <input type="number" min="1" max="100" wire:model.defer="quantity"
                                                    x-on:change="
                                                            clearTimeout(window.quantityTimer);
                                                            window.quantityTimer = setTimeout(() => $wire.updateFinalPrice(), 500);
                                                        "
                                                    class="w-12 text-center border-0 focus:ring-0 dark:bg-gray-800 dark:text-gray-300 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

                                                <!-- Plus Button -->
                                                <button type="button" x-on:click="
                                                            $wire.set('quantity', Math.min(100, Number($wire.quantity) + 1), false);
                                                            clearTimeout(window.quantityTimer);
                                                            window.quantityTimer = setTimeout(() => $wire.updateFinalPrice(), 500);
                                                        "
                                                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class=" hidden p-3 font-bold text-lg text-green-600 dark:text-green-400">
                                            <span>{{ number_format($total, 2) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4 p-4 border-t border-gray-300 dark:border-gray-700">
                        <x-primary-button class="px-6 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ __('Add to Cart') }}</span>
                            <span wire:loading>{{ __('Processing...') }}</span>
                        </x-primary-button>
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'add-product-to-cart')"
                            class="px-6 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                    </div>
                </form>
            </x-modal>
    @endif
    </div>