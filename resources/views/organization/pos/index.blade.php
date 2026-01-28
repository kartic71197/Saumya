<x-app-layout>
    <div class="py-2">
        <div class="mx-auto max-w-7xl">
            <div class="shadow-sm">
                <nav class="bg-white border-b border-gray-200 sticky top-0 z-20" x-data="checkoutCounter()">
                    <div class="px-6 py-3">
                        <div class="flex items-center justify-between">
                            <!-- Left: Title -->
                            <div class="flex items-center gap-3">
                                <div>
                                    <h1 class="text-lg font-bold text-gray-900 leading-tight">
                                        Point of Sale
                                    </h1>
                                </div>
                            </div>
                            <!-- Right: Date & Time -->
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-700"
                                        x-text="new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })">
                                    </div>
                                    <div class="text-xs text-gray-500"
                                        x-text="new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </nav>

                <!-- Side by side layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 py-3 items-start">

                    <!-- Inventory (Left - wider) -->
                    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-4 overflow-auto">
                        @include('organization.pos.inventory')
                    </div>

                    <!-- Checkout (Right - narrower) -->
                    <div class="lg:col-span-1 bg-white rounded-lg shadow-sm p-4">
                        @include('organization.pos.checkout')
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
