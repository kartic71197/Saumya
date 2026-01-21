<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Products
                            <span x-show="filteredCart.length > 0"
                                class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full"
                                x-text="filteredCart.length"></span>
                        </h2>
                    </div>

                    <div class="p-4">
                        <template x-if="cart.length === 0">
                            <div class="text-center py-12 text-gray-400">
                                <svg class="mx-auto h-16 w-16 mb-3 opacity-50" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <p class="text-sm font-medium">Your cart is empty</p>
                                <p class="text-xs mt-1">No items available for this location</p>
                            </div>
                        </template>

                        <template x-if="cart.length > 0 && filteredCart.length === 0">
                            <div class="text-center py-12 text-gray-400">
                                <svg class="mx-auto h-16 w-16 mb-3 opacity-50" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <p class="text-sm font-medium">No matching items found</p>
                                <p class="text-xs mt-1">Try a different search term</p>
                            </div>
                        </template>

                        <div x-show="filteredCart.length > 0" class="space-y-3">
                            <template x-for="(item, index) in filteredCart" :key="item.id">
                                <div
                                    class="flex items-center gap-3 p-3 bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-200 hover:border-blue-300 transition-all">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm text-gray-900 truncate"
                                            x-text="item.product_name">
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span class="font-medium text-gray-700">$<span
                                                    x-text="item.price"></span></span>
                                            <span class="mx-1">•</span>
                                            <span class="text-gray-600">Batch: <span
                                                    x-text="item.batch_number"></span></span>
                                            <span class="mx-1">•</span>
                                            <span
                                                :class="item.on_hand_quantity < 10 ? 'text-red-600' : 'text-green-600'">
                                                Stock: <span x-text="item.on_hand_quantity"></span>
                                            </span>
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-1 bg-white rounded-lg border border-gray-200 p-1">
                                        <button @click="decrementQty(item.originalIndex)"
                                            class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition-colors">−</button>
                                        <input type="number" :value="item.qty"
                                            @input="updateQty(item.originalIndex, $event.target.value)" min="1"
                                            :max="item.on_hand_quantity"
                                            class="w-14 text-center text-sm border-0 bg-transparent font-semibold py-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button @click="incrementQty(item.originalIndex)"
                                            :disabled="item.qty >= item.on_hand_quantity"
                                            class="w-7 h-7 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed text-gray-700 font-bold transition-colors">+</button>
                                    </div>

                                    <div class="text-right w-20">
                                        <p class="font-bold text-base text-gray-900">$<span
                                                x-text="(item.qty * item.price).toFixed(2)"></span></p>
                                    </div>

                                    <button @click="removeItem(item.originalIndex)"
                                        class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-all">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            {{-- Total below products --}}
                            <div
                                class="border-gray-200 pt-4 mt-4 bg-gradient-to-r from-green-50 to-emerald-50 -mx-4 -mb-4 px-4 pb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-gray-800">Total Amount</span>
                                    <span
                                        class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">$<span
                                            x-text="total.toFixed(2)"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>