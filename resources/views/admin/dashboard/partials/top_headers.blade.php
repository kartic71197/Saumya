<div class="max-w-10xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Card 1 -->
    <div
        class=" bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-500 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="py-6 px-3 text-white">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-white/30 rounded-lg backdrop-blur-sm flex-shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-xl font-bold mb-1" id="stock_onhand">
                        {{ $stock_onhand ?? 0 }}
                    </div>
                    <div class="text-emerald-50 text-xs font-medium uppercase tracking-wide">
                        Unique Products On Hand
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div
        class="bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-500 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="py-6 px-3 text-white">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-white/30 rounded-lg backdrop-blur-sm flex-shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <div class="flex-1 relative group">
                    <div class="text-xl font-bold mb-1 cursor-pointer" id="value_onhand">
                        $<span data-full-value="{{ $value_onhand ? number_format($value_onhand, 2, '.', '') : 0 }}">
                            {{ $value_onhand ? (
    $value_onhand >= 1000000000 ? number_format($value_onhand / 1000000000, 2) . 'B' :
    ($value_onhand >= 1000000 ? number_format($value_onhand / 1000000, 2) . 'M' :
        number_format($value_onhand, 2))
) : 0 }}
                        </span>

                    </div>

                    <!-- Tooltip Popup -->
                    <div
                        class="absolute left-0 top-full mt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2 z-50">
                        <div
                            class="bg-white text-gray-800 px-4 py-3 rounded-lg shadow-2xl border border-blue-200 backdrop-blur-sm min-w-max animate-bounce-in">
                            <div class="text-sm font-semibold text-blue-600 mb-1"> Value On Hand</div>
                            <div class="text-lg font-bold">
                                $<span
                                    class="tooltip-value">{{ $value_onhand ? number_format($value_onhand, 2) : 0 }}</span>
                            </div>
                            <!-- Arrow pointing up -->
                            <div
                                class="absolute -top-2 left-6 w-4 h-4 bg-white border-l border-t border-blue-200 transform rotate-45">
                            </div>
                        </div>
                    </div>

                    <div class="text-blue-50 text-xs font-medium uppercase tracking-wide">
                        Value On Hand
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes bounce-in {
            0% {
                transform: translateY(8px) scale(0.95);
                opacity: 0;
            }

            50% {
                transform: translateY(-2px) scale(1.02);
            }

            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .animate-bounce-in {
            animation: bounce-in 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .group:hover .tooltip-value {
            animation: number-pop 0.5s ease-out;
        }

        @keyframes number-pop {

            0%,
            100% {
                transform: scale(1);
            }

            25% {
                transform: scale(1.1);
            }

            75% {
                transform: scale(0.95);
            }
        }
    </style>

    <!-- Card 3 -->
    <div
        class="bg-gradient-to-br from-amber-400 via-orange-500 to-orange-600 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="py-6 px-3 text-white">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-white/30 rounded-lg backdrop-blur-sm flex-shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="flex-1 cursor-pointer"
                    onclick="window.location.href='{{ auth()->user()->role_id == 1 ? route('admin.purchase.index') : route('purchase.index') }}'">
                    <div class="text-xl font-bold mb-1" id="stock_to_receive">
                        {{ $stock_to_receive ?? 0 }}
                    </div>
                    <div
                        class="text-amber-50 text-xs font-medium uppercase tracking-wide hover:text-white transition-colors">
                        Stock To Be Received
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Card 4 -->
    <div
        class="bg-gradient-to-br from-purple-400 via-purple-500 to-pink-500 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="py-6 px-3 text-white">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-white/30 rounded-lg backdrop-blur-sm flex-shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-xl font-bold mb-1" id="pendingValue">
                        ${{ $pending_value ? number_format($pending_value, 2) : 0 }}
                    </div>
                    <div class="text-purple-50 text-xs font-medium uppercase tracking-wide">
                        Value To Be Received
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedLocation = @json($selectedLocation ?? '');
        const link = document.getElementById('stock-to-receive-link');

        link.addEventListener('click', function (e) {
            e.preventDefault();
            let url = '/purchase';

            if (selectedLocation) {
                url += '?location_id=' + encodeURIComponent(selectedLocation);
            } else {
                console.error('No location found, using base URL:', url);
            }
            window.location.href = url;
        });

    });
</script>