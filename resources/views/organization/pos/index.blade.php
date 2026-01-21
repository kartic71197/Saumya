<x-app-layout>
    <div class="min-h-screen py-2">
        <div class="mx-auto max-w-7xl">
            <div class=" shadow-sm overflow-hidden" x-data="{ activeTab: 'inventory' }"
                @open-customers-tab.window="activeTab = 'customers'">
                @include('organization.pos.partials.top-navigation')
                <!-- Content Area with improved spacing -->
                <div class="py-3 min-h-[600px]">

                    <div x-show="activeTab === 'inventory'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
                        @include('organization.pos.inventory')
                    </div>

                    <div x-show="activeTab === 'customers'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
                        @include('organization.pos.customers')
                    </div>


                    <div x-show="activeTab === 'checkout'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
                        @include('organization.pos.checkout')
                    </div>

                    <div x-show="activeTab === 'sales'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
                        @include('organization.pos.sale-history')
                    </div>

                </div>

            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</x-app-layout>
