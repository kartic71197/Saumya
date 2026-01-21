<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">

    {{-- Loading --}}
    <div id="loadingState" class="hidden p-10 text-center">
        <svg class="animate-spin h-10 w-10 mx-auto text-blue-500"></svg>
        <p class="mt-2 text-gray-500">Loading data...</p>
    </div>

    {{-- Content --}}
    <div id="contentArea" class="space-y-6">

        {{-- Default billing --}}
        <div class="border rounded-lg p-6 dark:border-gray-700">
            <h3 class="text-lg font-semibold mb-4">
                <span class="supplierTitle">
                    {{ $suppliers->first()->supplier_name ?? 'Select Supplier' }}
                </span>
                - Billing Information
            </h3>


            @include('billing_and_shipping.partials.default-bill-to')
        </div>


        {{-- Billing --}}
        <div class="border rounded-lg p-6 dark:border-gray-700">
            <h3 class="text-lg font-semibold mb-4">
                <span class="supplierTitle">
                    {{ $suppliers->first()->supplier_name ?? 'Select Supplier' }}
                </span>
                - Billing Information
            </h3>

            <form id="billingForm" method="post"
                action="{{ route('billing.update', ['organization_id' => $organization_id]) }}">
                @csrf
                <input type="hidden" id="billingSupplierId" value="{{ $suppliers->first()->id ?? '' }}">

                <div id="billingLocations" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                </div>

                @if ($user->role_id == 1)
                    <div class="flex justify-end mt-6">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg">
                            Update Billing
                        </button>
                    </div>
                @endif
            </form>
        </div>

        {{-- Shipping --}}
        <div class="border rounded-lg p-6 dark:border-gray-700">
            <h3 class="text-lg font-semibold mb-4">
                <span class="supplierTitle">
                    {{ $suppliers->first()->supplier_name ?? 'Select Supplier' }}
                </span>
                - Shipping Information
            </h3>

            <form id="shippingForm" method="post"
                action="{{ route('shipping.update', ['organization_id' => $organization_id]) }}">
                @csrf
                <input type="hidden" id="shippingSupplierId" value="{{ $suppliers->first()->id ?? '' }}">

                <div id="shippingLocations" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                </div>

                @if ($user->role_id == 1)
                    <div class="flex justify-end mt-6">
                        <button class="px-6 py-2 bg-green-600 text-white rounded-lg">
                            Update Shipping
                        </button>
                    </div>
                @endif
            </form>
        </div>

    </div>
</div>