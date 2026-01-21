<!-- EDI 856 Advanced Shipping Notice Modal -->
<x-modal name="preview_edi856_modal" maxWidth="6xl">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">
                Advanced Shipping Notice
            </h2>
            <div class="bg-green-500 bg-opacity-30 px-3 py-1 rounded-lg">
                @if(!empty($edi856data) && $edi856data->isNotEmpty() && $edi856data[0]?->poNumber)
                    <p class="text-green-100 text-sm mt-1">
                        PO #{{ $edi856data[0]->poNumber }}
                    </p>
                @else
                    <p class="text-green-100 text-sm mt-1">
                        No Purchase Order Number Available
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="p-6">
        @if(!empty($edi856data) && $edi856data->isNotEmpty())

            <!-- Shipping Header Information -->
            @php
                $firstRecord = $edi856data->first();
                $uniqueCarriers = $edi856data->whereNotNull('carrier')->unique('carrier');
                $uniqueInvoices = $edi856data->whereNotNull('invoiceNumber')->unique('invoiceNumber');
            @endphp

            @if($firstRecord)
                <div class="mb-6 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4 border border-green-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Shipping Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @if($firstRecord->date)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ship Date</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($firstRecord->date)->format('M j, Y') }}
                                    @if($firstRecord->time)
                                        at {{ $firstRecord->time }}
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if($firstRecord->carrier || $firstRecord->SCAC)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Carrier</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $firstRecord->carrier ?: 'N/A' }}
                                    @if($firstRecord->SCAC)
                                        <span class="text-xs text-gray-500">({{ $firstRecord->SCAC }})</span>
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if($firstRecord->internalRefNumber)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Internal Ref</p>
                                <p class="text-sm font-medium text-gray-900">{{ $firstRecord->internalRefNumber }}</p>
                            </div>
                        @endif

                        @if($uniqueInvoices->count() > 0)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Invoice(s)</p>
                                <p class="text-sm font-medium text-gray-900">
                                    @if($uniqueInvoices->count() === 1)
                                        {{ $uniqueInvoices->first()->invoiceNumber }}
                                    @else
                                        Multiple ({{ $uniqueInvoices->count() }})
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Line Items -->
            <div class="space-y-6">
                @foreach ($edi856data as $index => $data)
                    @php
                        // Fetch product name from database based on product_code and organization_id
                        $productName = null;
                        if ($data?->product_code) {
                            $product = \App\Models\Product::where('product_code', $data->product_code)
                                ->where('organization_id', auth()->user()->organization_id)
                                ->first();
                            $productName = $product?->name ?? $product?->product_name;
                        }
                    @endphp

                    <div
                        class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                        <!-- Item Header -->
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-md font-semibold text-gray-800">
                                    Item #{{ $index + 1 }}
                                    @if($productName)
                                        - {{ $productName }}
                                    @endif
                                </h4>
                                @if($data?->shippedDate)
                                    <span class="text-sm text-gray-500">
                                        Shipped: {{ \Carbon\Carbon::parse($data->shippedDate)->format('M j, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                <!-- Product Information -->
                                <div class="space-y-1">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Product Code
                                    </p>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $data?->product_code ?: 'N/A' }}
                                    </p>
                                    @if($productName)
                                        <p class="text-xs text-green-600">
                                            ✓ Found in database
                                        </p>
                                    @elseif($data?->product_code)
                                        <p class="text-xs text-amber-600">
                                            ⚠ Not found in Practice's database
                                        </p>
                                    @endif
                                </div>

                                <!-- Shipped Quantity -->
                                <div class="space-y-1">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Quantity Shipped
                                    </p>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($data?->unitShipped)
                                            <span class="inline-flex items-center">
                                                {{ number_format($data->unitShipped) }}
                                                {{ $data?->units ?: 'units' }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>

                                <!-- Status -->
                                <div class="space-y-1">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Status
                                    </p>
                                    <p class="text-sm font-medium">
                                        @if($data?->status)
                                                            @php
                                                                $status = strtolower($data->status);
                                                                $statusColors = [
                                                                    'shipped' => 'bg-green-100 text-green-800 border-green-200',
                                                                    'delivered' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                                    'in transit' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                                    'pending' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                                ];
                                                                $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                                            @endphp
                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colorClass }}">
                                                                {{ ucfirst($data->status) }}
                                                            </span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </p>
                                </div>

                                <!-- Carrier Info -->
                                <div class="space-y-1">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Carrier
                                    </p>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($data?->carrier)
                                            {{ $data->carrier }}
                                            @if($data?->SCAC)
                                                <br><span class="text-xs text-gray-500">SCAC: {{ $data->SCAC }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </p>
                                </div>

                                <!-- Tracking Information -->
                                <div class="space-y-1">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Tracking Number
                                    </p>
                                    @if($data?->invoiceNumber)
                                        @php
                                            // Generate tracking URL based on carrier
                                            $trackingUrl = null;
                                            $carrierLower = strtolower($data->carrier ?? '');
                                            $scacLower = strtolower($data->SCAC ?? '');

                                            if (str_contains($carrierLower, 'ups') || $scacLower === 'upsn') {
                                                $trackingUrl = 'https://www.ups.com/track?tracknum=' . $data->invoiceNumber;
                                            } elseif (str_contains($carrierLower, 'fedex') || $scacLower === 'fdxe' || $scacLower === 'fxfe') {
                                                $trackingUrl = 'https://www.fedex.com/fedextrack/?trknbr=' . $data->invoiceNumber;
                                            } elseif (str_contains($carrierLower, 'usps') || $scacLower === 'usps') {
                                                $trackingUrl = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $data->invoiceNumber;
                                            } elseif (str_contains($carrierLower, 'dhl') || $scacLower === 'dhlw') {
                                                $trackingUrl = 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=' . $data->invoiceNumber;
                                            } elseif (str_contains($carrierLower, 'amazon') || $scacLower === 'amzl') {
                                                $trackingUrl = 'https://track.amazon.com/tracking/' . $data->invoiceNumber;
                                            }
                                        @endphp

                                        <div class="space-y-1">
                                            @if($trackingUrl)
                                                <a href="{{ $trackingUrl }}" target="_blank"
                                                    class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                        </path>
                                                    </svg>
                                                    {{ $data->invoiceNumber }}
                                                </a>
                                            @else
                                                <p class="text-xs text-gray-500">
                                                    Manual tracking required
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Details -->
                            @if($data?->product_desc && !$productName)
                                <div class="mt-4 pt-3 border-t border-gray-100">
                                    <div class="flex items-start space-x-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                            Product Description:
                                        </p>
                                        <p class="text-sm text-gray-700">
                                            {{ $data->product_desc }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary -->
            @php
                $totalItems = $edi856data->count();
                $totalUnitsShipped = $edi856data->where('unitShipped', '!=', null)->sum('unitShipped');
                $shippedStatuses = $edi856data->where('status', '!=', null)->groupBy('status');
                $uniqueCarrierCount = $edi856data->whereNotNull('carrier')->unique('carrier')->count();
                $trackableItems = $edi856data->whereNotNull('invoiceNumber')->count();
            @endphp

            <div class="mt-8 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border border-green-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Shipment Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $totalItems }}</p>
                        <p class="text-sm text-gray-600">Line Items</p>
                    </div>

                    @if($totalUnitsShipped > 0)
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalUnitsShipped) }}</p>
                            <p class="text-sm text-gray-600">Total Units Shipped</p>
                        </div>
                    @endif

                    @if($uniqueCarrierCount > 0)
                        <div class="text-center">
                            <p class="text-2xl font-bold text-purple-600">{{ $uniqueCarrierCount }}</p>
                            <p class="text-sm text-gray-600">Carrier{{ $uniqueCarrierCount > 1 ? 's' : '' }}</p>
                        </div>
                    @endif

                    @if($trackableItems > 0)
                        <div class="text-center">
                            <p class="text-2xl font-bold text-indigo-600">{{ $trackableItems }}</p>
                            <p class="text-sm text-gray-600">Trackable Items</p>
                        </div>
                    @endif

                    @if($shippedStatuses->count() > 0)
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-700">{{ $shippedStatuses->count() }}</p>
                            <p class="text-sm text-gray-600">Status Types</p>
                        </div>
                    @endif
                </div>

                <!-- Status Breakdown -->
                @if($shippedStatuses->count() > 0)
                    <div class="mt-4 pt-4 border-t border-green-200">
                        <p class="text-sm font-medium text-gray-700 mb-2">Status Breakdown:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($shippedStatuses as $status => $items)
                                @php
                                    $statusLower = strtolower($status);
                                    $statusColors = [
                                        'shipped' => 'bg-green-100 text-green-800',
                                        'delivered' => 'bg-blue-100 text-blue-800',
                                        'in transit' => 'bg-yellow-100 text-yellow-800',
                                        'pending' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $colorClass = $statusColors[$statusLower] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                    {{ ucfirst($status) }} ({{ $items->count() }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        @else
            <!-- No Data State -->
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Shipping Data Available</h3>
                <p class="text-gray-500">
                    No EDI 856 Advanced Shipping Notice data found for this purchase order.
                </p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex justify-end space-x-3">
            <x-secondary-button x-on:click="$dispatch('close')" class="px-6">
                Close
            </x-secondary-button>
            @if(!empty($edi856data) && $edi856data->isNotEmpty())
                {{-- <button type="button"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export Shipping Details
                </button> --}}
            @endif
        </div>
    </div>
</x-modal>