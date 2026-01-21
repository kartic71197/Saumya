 <div class="lg:hidden divide-y divide-gray-200">
            <template x-for="item in filteredProducts" :key="item.id">
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 flex-1">
                            <div
                                class="flex-shrink-0 h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate" x-text="item.product_name">
                                </div>
                                <div class="text-xs text-gray-500" x-text="item.sku"></div>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap ml-2"
                            :class="getStatusClass(item)" x-text="getStatusText(item)"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3 text-sm">
                        <div>
                            <span class="text-gray-500">Batch:</span>
                            <span class="font-medium text-gray-900 ml-1" x-text="item.batch_number || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Qty:</span>
                            <span class="font-semibold text-gray-900 ml-1" x-text="item.on_hand_quantity"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500">Expiry:</span>
                            <span class="ml-1" :class="getExpiryClass(item.expiry_date)"
                                x-text="formatDate(item.expiry_date)"></span>
                        </div>
                    </div>

                    <button @click="showQuantityModal(item)" :disabled="item.on_hand_quantity <= 0"
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Add to POS
                    </button>
                </div>
            </template>
        </div>