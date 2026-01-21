<div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700"
                x-data="{ imagesOpen: true }">
                <div class="mb-3">
                    <!-- Accordion Button -->
                    <button type="button" @click="imagesOpen = !imagesOpen"
                        class="w-full flex items-center justify-between text-left">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Receipt Images
                            @if ($this->receiptImages->count() > 0)
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                    {{ $this->receiptImages->count() }}
                                    {{ Str::plural('receipt', $this->receiptImages->count()) }}
                                </span>
                            @endif
                        </h3>

                        <!-- Chevron Icon -->
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': imagesOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="imagesOpen" x-collapse class="mt-3">
                        @if ($this->receiptImages->count() > 0)
                            <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
                                @foreach ($this->receiptImages as $imageEntry)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700">
                                        <!-- Image Entry Header -->
                                        <div class="flex items-start gap-2 mb-2">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $imageEntry['user'] ?? 'Unknown User' }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $imageEntry['datetime'] ? \Carbon\Carbon::parse($imageEntry['datetime'])->format('M d, Y • H:i') : 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Images Grid -->
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            @foreach ($imageEntry['images'] as $imagePath)
                                                <div class="relative group">
                                                    <img src="{{ asset('storage/' . $imagePath) }}"
                                                        alt="Packing slip image"
                                                        class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow duration-200">
                                                    <a href="{{ asset('storage/' . $imagePath) }}" target="_blank"
                                                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity duration-200 rounded-lg">
                                                        <span
                                                            class="text-white text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m0 0l3-3m-3 3l-3-3" />
                                                            </svg>
                                                            View
                                                        </span>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No image available</p>
                        @endif
                    </div>
                </div>
            </div>