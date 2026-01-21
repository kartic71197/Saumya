<div class="hidden lg:block overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch
                    #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Expiry Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">On
                    Hand</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <template x-for="item in filteredProducts" :key="item.id">
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-4 max-w-[220px]">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 break-words leading-snug"
                                    x-text="item.product_name"></div>
                                <div class="text-xs text-gray-500" x-text="item.sku"></div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900" x-text="item.batch_number || 'N/A'"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm" :class="getExpiryClass(item.expiry_date)"
                            x-text="formatDate(item.expiry_date)"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-semibold text-gray-900" x-text="item.on_hand_quantity"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(item)"
                            x-text="getStatusText(item)"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <!-- Show only plus button when quantity is 0 -->
                        <div x-show="getItemQuantity(item) === 0">
                            <button @click="incrementQuantity(item)" 
                                :disabled="item.on_hand_quantity <= 0"
                                class="w-9 h-9 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed border-2 border-green-600">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Show full controls when quantity > 0 -->
                        <div x-show="getItemQuantity(item) > 0" class="flex items-center gap-2">
                            <button @click="decrementQuantity(item)" 
                                class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            
                            <div class="w-12 text-center">
                                <span class="text-sm font-semibold text-gray-900" x-text="getItemQuantity(item)"></span>
                            </div>
                            
                            <button @click="incrementQuantity(item)" 
                                :disabled="getItemQuantity(item) >= item.on_hand_quantity"
                                class="w-8 h-8 bg-green-100 hover:bg-green-200 rounded-lg flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>