<div class="max-w-10xl mx-auto px-4">
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
        <section class="w-full border-b-2 pb-4 mb-6">
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Order History Report') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('View your order history report with advanced filters.') }}
                    </p>
                </div>

                <!-- Organization Filter (Super Admin only) -->
                @if (auth()->user()->role_id == 1)
                    <div class="flex items-center gap-3 mb-4">
                        <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Practices:') }}
                        </label>
                        <select wire:model.live="selectedOrganization"
                            class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-md">
                            <option value="">{{ __('All Practices') }}</option>
                            @foreach ($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Location Filter (Role ≥2 only) -->
                @if (auth()->user()->role_id >= 2)
                    <div class="flex items-center gap-3 mb-4">
                        <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Location:') }}
                        </label>
                        <select wire:model.live="selectedLocation"
                            class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-primary-md">
                            <option value="">{{ __('All Locations') }}</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif


                <!-- Date Range Filters -->
                {{-- <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="flex flex-col">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('From Date') }}
                        </label>
                        <input type="text" id="fromDate" wire:model.live="fromDate" placeholder="MM/DD/YYYY"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-md" />
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('To Date') }}
                        </label>
                        <input type="text" id="toDate" wire:model.live="toDate" placeholder="MM/DD/YYYY"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-md" />
                    </div>

                    <div class="flex items-end">
                        <button wire:click="resetDateFilters"
                            class="px-4 py-2 text-xs font-semibold text-white rounded-md border border-transparent bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition duration-150 ease-in-out dark:focus:ring-offset-gray-800">
                            {{ __('Reset') }}
                        </button>
                    </div>
                </div> --}}
            </header>
        </section>

        <div class="text-xs">
            <livewire:tables.reports.purchase-report-list :organization-id="$selectedOrganization" :location-id="$selectedLocation"
                :wire:key="'purchase-report-'.$selectedOrganization.'-'.$selectedLocation"
                fromDate="{{ $formattedFromDate }}" toDate="{{ $formattedToDate }}" />
        </div>
        {{-- <livewire:preview-edi855-modal /> --}}
        <x-modal name="preview_edi855_modal" maxWidth="6xl">
            <div class="text-black p-10 font-serif bg-white min-h-[600px] border border-black">

                @php
                    $edi855data = collect($edi855data);
                    $firstItem = $edi855data->first();
                @endphp

                <!-- Document Header -->
                <div class="border-b border-black pb-4 mb-6 flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold uppercase tracking-wide">
                            ACKNOWLEDGEMENT
                        </h1>
                        <p class="mt-1 text-sm">
                            Date: {{ $firstItem->ack_date ?? 'N/A' }}
                        </p>
                        <p class="text-xs">Generated on {{ now()->format('F j, Y, g:i A T') }}</p>
                    </div>
                    <div class="text-right px-4 py-3">
                        <p class="text-lg font-bold">
                            PO #: {{ $firstItem->purchase_order ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                @if ($edi855data->isNotEmpty())
                    <!-- Order Details Table -->
                    <h2 class="text-lg font-bold uppercase mb-3 pb-1">Order Details</h2>
                    <table class="table-auto w-full border border-black text-sm">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-2 py-1 border border-black">Product Code</th>
                                <th class="px-2 py-1 border border-black">Product Name</th>
                                {{-- <th class="px-2 py-1 border border-black text-center">Ordered Qty</th> --}}
                                <th class="px-2 py-1 border border-black text-center">Ack Qty</th>
                                <th class="px-2 py-1 border border-black text-center">Unit Price</th>
                                <th class="px-2 py-1 border border-black text-center">Tracking</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($edi855data as $item)
                                @php
                                    $tracking = null;
                                    $trackingUrl = null;

                                    if ($item->product_code) {
                                        $product = App\Models\Product::where('product_code', $item->product_code)
                                            ->where('organization_id', auth()->user()->organization_id)
                                            ->where('is_active', true)
                                            ->first();

                                        if ($product) {
                                            $tracking = App\Models\Edi856::where('product_code', $product->product_code)
                                                ->where('poNumber', $item->purchase_order)
                                                ->first();
                                        }
                                    }

                                    if ($tracking && $tracking->invoiceNumber) {
                                        $carrierLower = strtolower($tracking->carrier ?? '');
                                        $scacLower = strtolower($tracking->SCAC ?? '');

                                        if (str_contains($carrierLower, 'ups') || $scacLower === 'upsn') {
                                            $trackingUrl =
                                                'https://www.ups.com/track?tracknum=' . $tracking->invoiceNumber;
                                        } elseif (
                                            str_contains($carrierLower, 'fedex') ||
                                            $scacLower === 'fdxe' ||
                                            $scacLower === 'fxfe'
                                        ) {
                                            $trackingUrl =
                                                'https://www.fedex.com/fedextrack/?trknbr=' . $tracking->invoiceNumber;
                                        } elseif (str_contains($carrierLower, 'usps') || $scacLower === 'usps') {
                                            $trackingUrl =
                                                'https://tools.usps.com/go/TrackConfirmAction?tLabels=' .
                                                $tracking->invoiceNumber;
                                        } elseif (str_contains($carrierLower, 'dhl') || $scacLower === 'dhlw') {
                                            $trackingUrl =
                                                'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=' .
                                                $tracking->invoiceNumber;
                                        } elseif (str_contains($carrierLower, 'amazon') || $scacLower === 'amzl') {
                                            $trackingUrl =
                                                'https://track.amazon.com/tracking/' . $tracking->invoiceNumber;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="px-2 py-1 border border-black">{{ $item->product_code }}</td>
                                    <td class="px-2 py-1 border border-black">
                                        {{ $product?->product_name ?? $item->product_name }}</td>
                                    {{-- <td class="px-2 py-1 border border-black text-center">{{ number_format($item->ordered_qty) }}
                            </td> --}}
                                    <td class="px-2 py-1 border border-black text-center">
                                        {{ number_format($item->ack_qty ?? 0) }}
                                        {{ $item->ack_unit ?? '' }}
                                    </td>
                                    <td class="px-2 py-1 border border-black text-center">
                                        ${{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    @if ($tracking && $tracking->invoiceNumber)
                                        <td class="px-2 py-1 border border-black text-center">
                                            <a href="{{ $trackingUrl }}" target="_blank"
                                                class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg> {{ $tracking?->invoiceNumber }} </a>
                                        </td>
                                    @else
                                        <td class="px-2 py-1 border border-black text-center">

                                        </td>
                                    @endif

                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Summary -->
                    <div class="mt-6 border border-black p-4 w-1/3 ml-auto text-sm">
                        <p><strong>Total Items:</strong> {{ number_format($edi855data->count()) }}</p>
                        <p><strong>Total Ack Qty:</strong> {{ number_format($edi855data->sum('ack_qty')) }}</p>
                        <p><strong>Estimated Total Value:</strong>
                            ${{ number_format($edi855data->sum(fn($i) => ($i->unit_price ?? 0) * ($i->ack_qty ?? 0)), 2) }}
                        </p>
                    </div>
                @else
                    <div class="text-center py-10 text-lg">No EDI 855 acknowledgement data found</div>
                @endif
            </div>

            <!-- Footer -->
            <div
                class="px-8 py-4 border-t border-black bg-white flex justify-between items-center print:hidden text-xs">
                <div>Document ID: ACK-{{ now()->format('Ymd') }}-{{ substr(md5(uniqid()), 0, 6) }}</div>
                <div class="flex space-x-2">
                    {{-- <button onclick="window.print()" class="px-3 py-1 border border-black">Print</button> --}}
                    <x-secondary-button x-on:click="$dispatch('close')"
                        class="border border-black">Close</x-secondary-button>
                </div>
            </div>
        </x-modal>
    </div>
    <x-modal name="purchase_report_details_modal" width="w-full" height="h-auto" maxWidth="4xl" wire:model="showModal">
        <!-- Header with logo -->
        <header
            class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-center items-center bg-white dark:bg-gray-800 rounded-t-lg shadow-sm">
            <x-application-logo class="w-auto h-14 fill-current text-gray-700 dark:text-gray-300" />
        </header>

        <!-- Order info section -->
        <div
            class="flex items-center justify-between px-8 py-5 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    <span
                        class="text-blue-600 dark:text-blue-400">#{{ $purchase_order?->purchase_order_number }}</span>
                </h1>
            </div>
            <div class="text-right">
                <h2 class="text-md font-medium text-gray-600 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($purchase_order?->created_at)->format('m-d-Y ' . session('time_format', 'H:i A')) }}
                </h2>
            </div>
        </div>

        <!-- Details section -->
        <div class="bg-white dark:bg-gray-800 px-2 py-6">
            <!-- Order details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Left column -->
                <div class="bg-gray-50 dark:bg-gray-900 p-5 rounded-lg shadow-sm border">
                    <h3 class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Order Information
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Location </p>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $purchase_order?->purchaseLocation->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Shipping To</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $purchase_order?->shippingLocation->name }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">
                                @php
                                    $status = strtolower($purchase_order?->status);
                                    $displayStatus = $status === 'completed' ? 'received' : ucfirst($status);

                                    $statusClasses = match ($displayStatus) {
                                        'received'
                                            => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'canceled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'pending'
                                            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    };
                                @endphp

                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($displayStatus) }}
                                </span>
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Right column -->
                <div class="bg-gray-50 dark:bg-gray-900 p-5 rounded-lg shadow-sm border">
                    <h3 class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Supplier
                        Information</h3>
                    <div class="space-y-3">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Supplier </p>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $purchase_order?->purchaseSupplier->supplier_name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $purchase_order?->purchaseSupplier->supplier_email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                #</th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Product</th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Unit</th>
                            <th scope="col"
                                class="py-3.5 px-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Quantity</th>
                            <th scope="col"
                                class="py-3.5 px-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap ">
                                Sub Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($purchase_data as $index => $data)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="py-4 px-4 whitespace-wrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $index + 1 }}
                                </td>
                                <td
                                    class="py-4 px-4 whitespace-wrap text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $data?->product->product_name . ' (' . $data?->product->product_code . ')' }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $data?->unit->unit_name }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $data?->quantity }}
                                </td>
                                <td
                                    class="py-4 px-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200 text-right font-medium">
                                    {{ session('currency', '$') . $data?->sub_total }}
                                </td>
                            </tr>
                            <!-- History Row - Conditionally visible -->
                            @php
                                // Get receipt history for this product
                                $receipts = App\Models\PoReceipt::where('purchase_order_id', $purchase_order->id)
                                    ->where('product_id', $data->product_id)
                                    ->with('receivedBy')
                                    ->orderBy('date_received', 'desc')
                                    ->get();
                            @endphp
                            @if ($receipts->count() > 0)
                                <tr class="bg-white dark:bg-gray-800">
                                    <td colspan="6" class="px-6 py-2">
                                        <div class="dark:bg-gray-900 rounded-lg dark:border-gray-700 p-2">



                                            <div class="overflow-x-auto">
                                                <table class="w-full text-xs border-collapse">
                                                    <thead>
                                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Date Received</th>
                                                            {{-- <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Ordered Qty</th> --}}
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Received Qty</th>
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Batch Number</th>
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Expiry Date</th>
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Received By</th>
                                                            {{-- <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Note Added</th> --}}
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($receipts as $receipt)
                                                            <tr
                                                                class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                                                <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->date_received ? \Carbon\Carbon::parse($receipt->date_received)->format('M d, Y') : '-' }}
                                                                </td>
                                                                {{-- <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->ordered_qty ?? '-' }}
                                                                </td> --}}
                                                                <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->received_qty ?? '-' }}
                                                                </td>
                                                                <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->batch_number ?? '-' }}
                                                                </td>
                                                                <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->expiry_date ? \Carbon\Carbon::parse($receipt->expiry_date)->format('M d, Y') : '-' }}
                                                                </td>
                                                                <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->receivedBy->name ?? '-' }}
                                                                </td>
                                                                {{-- <td
                                                                    class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                    {{ $receipt->user_note ?
                                                                    \Illuminate\Support\Str::words($receipt->user_note, 7, '...') :
                                                                    '-' }}
                                                                </td> --}}
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @php
                                // Get receipt history for this product
                                $canceled_data = App\Models\PurchaseOrderDetail::where(
                                    'purchase_order_id',
                                    $purchase_order->id,
                                )
                                    ->where('product_id', $data->product_id)
                                    ->where('product_status', 'canceled')
                                    ->with('canceledByUser')
                                    ->get();
                            @endphp
                            @if ($canceled_data->count() > 0)
                                <!-- Cancel Row - Conditionally visible -->
                                <tr class="bg-white dark:bg-gray-800">
                                    <td colspan="6" class="px-6 py-2">
                                        <div class="dark:bg-gray-900 rounded-lg dark:border-gray-700 p-2">
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-xs border-collapse">
                                                    <thead>
                                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Canceled by</th>
                                                            <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Cancelation note</th>
                                                            {{-- <th
                                                                class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                                Note Added</th> --}}
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($canceled_data as $data)
                                                        @endforeach
                                                        <tr
                                                            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                                            <td
                                                                class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                {{ $data->canceledByUser->name ?? '-' }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                                {{ $data->cancelation_note ?? '-' }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <td colspan="4"
                                class="py-4 px-4 text-sm font-semibold text-gray-700 dark:text-gray-200 text-right">
                                Subtotal:</td>
                            <td class="py-4 px-4 text-sm font-semibold text-gray-700 dark:text-gray-200 text-right">
                                {{ session('currency', '$') . $purchase_order?->total }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"
                                class="py-4 px-4 text-base font-bold text-gray-800 dark:text-gray-100 text-right">
                                Total:
                            </td>
                            <td class="py-4 px-4 text-base font-bold text-gray-800 dark:text-gray-100 text-right">
                                {{ session('currency', '$') . $purchase_order?->total }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Footer with actions -->
        <footer
            class="px-8 py-5 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
            <!-- Notes Section with Accordion -->
            <div class="mb-4" x-data="{ open: true }">
                <!-- Accordion Button -->
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between text-left mb-3">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        Receipt Notes
                        @if ($this->receiptNotes->count() > 0)
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                ({{ $this->receiptNotes->count() }}
                                {{ Str::plural('note', $this->receiptNotes->count()) }})
                            </span>
                        @endif
                    </h4>

                    <!-- Chevron Icon -->
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Accordion Content -->
                <!-- Accordion Content -->
                <div x-show="open" x-collapse>
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
                                    <div class="text-sm text-gray-700 dark:text-gray-300 break-words">
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

            <!-- Receipt Images Section -->
            <div class="mb-4" x-data="{ imagesOpen: false }">
                <!-- Accordion Button -->
                <button type="button" @click="imagesOpen = !imagesOpen"
                    class="w-full flex items-center justify-between text-left mb-3">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Receipt Images
                        @if ($this->receiptImages->count() > 0)
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                ({{ $this->receiptImages->count() }}
                                {{ Str::plural('image', $this->receiptImages->count()) }})
                            </span>
                        @endif
                    </h4>

                    <!-- Chevron Icon -->
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': imagesOpen }" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Accordion Content -->
                <div x-show="imagesOpen" x-collapse>
                    @if ($this->receiptImages->count() > 0)
                        <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                            @foreach ($this->receiptImages as $imageEntry)
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-lg p-2 shadow-sm border border-gray-200 dark:border-gray-700">
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
                                                <img src="{{ asset('storage/' . $imagePath) }}" alt="Receipt image"
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
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No images available</p>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                    wire:click="closeModal">
                    Close
                </button>
            </div>
        </footer>
    </x-modal>
</div>
