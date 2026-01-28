<x-app-layout>
    <div class="py-2">
        <div class="mx-auto max-w-7xl">
            <div class="shadow-sm">
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
