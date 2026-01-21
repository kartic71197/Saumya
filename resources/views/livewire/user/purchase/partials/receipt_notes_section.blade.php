<div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700"
                x-data="{ notesOpen: true }">
                <div class="mb-3">
                    <!-- Accordion Button -->
                    <button type="button" @click="notesOpen = !notesOpen"
                        class="w-full flex items-center justify-between text-left">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            Receipt Notes
                            @if ($this->receiptNotes->count() > 0)
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                    {{ $this->receiptNotes->count() }}
                                    {{ Str::plural('note', $this->receiptNotes->count()) }}
                                </span>
                            @endif
                        </h3>

                        <!-- Chevron Icon -->
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': notesOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="notesOpen" x-collapse class="mt-3">
                        @if ($this->receiptNotes->count() > 0)
                            <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                                @foreach ($this->receiptNotes as $note)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-lg p-2 shadow-sm border border-gray-200 dark:border-gray-700">
                                        <!-- Comment Header -->
                                        <div class="flex items-start gap-2 mb-1">
                                            <!-- User Info and Timestamp -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $note['user'] ?? 'Unknown User' }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $note['datetime'] ? \Carbon\Carbon::parse($note['datetime'])->format('M d, Y • H:i') : 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Comment Content -->
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $note['notes'] ?? 'No note provided' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No notes available</p>
                        @endif
                    </div>
                </div>
            </div>