<div class="flex justify-between gap-4 mb-3">
        <div href="#" class="flex-1 items-stretch p-4 bg-white border border-gray-200 rounded-lg shadow-sm  
            dark:bg-gray-800 dark:border-gray-700">
            <h3 class="mb-1 text-medium font-bold tracking-tight text-gray-900 dark:text-white">Select Location</h3>
            <p class="text-xs font-normal text-gray-700 dark:text-gray-400">Please select a location for which you want
                to view cart.</p>
            <select id="location-dropdown" onclick="handleLocationClick()" onchange="handleLocationSelect()"
                wire:model.live="selectedLocation" wire:change="updateLocation"
                class="mt-1 block w-full border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                <option value="">Select Location</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}">
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 items-stretch p-4 bg-white border border-gray-200 rounded-lg shadow-sm  
            dark:bg-gray-800 dark:border-gray-700">

            @if ($shippingLocations->count() > 0 && $default_shipping_location == null)
                <h3 class="mb-2 text-medium font-bold tracking-tight text-gray-900 dark:text-white">Shipping Information
                </h3>
                <p class="font-normal text-gray-700 dark:text-gray-400 text-xs"> View your billing data.<a
                        class="dark:text-white underline text-primary-dk font-semibold"
                        href="{{ route('billing.index', ['organization_id' => auth()->user()->organization_id]) }}">shipping
                        data</a></p>
                <select id="selectedShippingLocation" onchange="handleShippingSelect()" onclick="handleShippingClick()"
                    wire:model.live="selectedShippingLocation"
                    class="dark:text-gray-300 mt-2 block w-full border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600">
                    <option value="0">Select Shipping Location</option>
                    @foreach ($shippingLocations as $shippingLocation)
                        <option value="{{ $shippingLocation->id }}" {{ $shippingLocation->id == $selectedShippingLocation ? 'selected' : '' }}>
                            {{ $shippingLocation->name }}
                        </option>
                    @endforeach
                </select>
            @elseif ($shippingLocations->count() > 0 && $default_shipping_location != null)
                <h3 class="mb-1 text-medium font-bold tracking-tight text-gray-900 dark:text-white">Default Shipping
                    Information
                </h3>
                <p class="font-normal text-gray-700 dark:text-gray-400 text-xs"> View your billing data.<a
                        class="dark:text-white underline text-primary-dk font-semibold"
                        href="{{ route('billing.index', ['organization_id' => auth()->user()->organization_id]) }}">shipping
                        data</a></p>
                <div class="mt-1 text-semibold border-2 rounded border-gray-200 px-3 py-2">
                    {{ $default_shipping_location->name}}</div>
            @else
                <h3 class="mb-2 text-medium font-bold tracking-tight text-gray-900 dark:text-white">Shipping Information
                </h3>
                <p class="font-normal text-gray-700 dark:text-gray-400">You have no shipping information. Please add
                    shipping
                    information.</p>
                <a href="{{ route('billing.index', ['organization_id' => auth()->user()->organization_id]) }}"
                    class="mt-3 inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-primary-md rounded-lg hover:bg-primary-dk focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-primary-md dark:hover:bg-primary-dk dark:focus:ring-blue-800">
                    Add Shipping details
                    <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </a>
            @endif
        </div>

        {{-- <div
            class="flex-1 p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <h3 class="mb-2 text-medium font-semibold text-gray-900 dark:text-white">Billing Information</h3>
            @if ($billingLocations->count() > 0)
                <p class="font-normal text-gray-700 dark:text-gray-400 text-xs">
                    View your billing data details.
                    <a href="{{ route('billing.index', ['organization_id' => auth()->user()->organization_id]) }}"
                        class="font-semibold text-primary-dk underline dark:text-white hover:text-primary-md">
                        Billing Data
                    </a>
                </p>
                @if ($selectedBillingLocation)
                    <div
                        class="py-1 px-2 mt-4 bg-gray-100 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <p class="text-medium font-medium text-gray-900 dark:text-gray-200">
                            {{ $selectedBillingLocation->name }}
                        </p>
                    </div>
                @else
                    <div
                        class="py-1 px-2 mt-4 bg-gray-100 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <p class="text-medium font-medium text-gray-900 dark:text-gray-200">
                            {{ __('Default Billing Location is not avialable ') }}
                        </p>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400">No billing information found. Please add your
                    billing
                    details.</p>
                <a href="{{ route('billing.index', ['organization_id' => auth()->user()->organization_id]) }}"
                    class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-md rounded-lg hover:bg-primary-dk focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-primary-md dark:hover:bg-primary-dk dark:focus:ring-blue-800  transition-all">
                    <span>Add Billing Details</span>
                    <svg class="w-4 h-4 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </a>
            @endif
        </div> --}}
    </div>