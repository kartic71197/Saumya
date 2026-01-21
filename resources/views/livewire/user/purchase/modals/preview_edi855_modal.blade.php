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

        @if($edi855data->isNotEmpty())
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
                        <th class="px-2 py-1 border border-black text-center">Status</th>
                        <th class="px-2 py-1 border border-black text-center">Tracking</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($edi855data as $item)
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
                                    $trackingUrl = 'https://www.ups.com/track?tracknum=' . $tracking->invoiceNumber;
                                } elseif (str_contains($carrierLower, 'fedex') || $scacLower === 'fdxe' || $scacLower === 'fxfe') {
                                    $trackingUrl = 'https://www.fedex.com/fedextrack/?trknbr=' . $tracking->invoiceNumber;
                                } elseif (str_contains($carrierLower, 'usps') || $scacLower === 'usps') {
                                    $trackingUrl = 'https://tools.usps.com/go/TrackConfirmAction?tLabels=' . $tracking->invoiceNumber;
                                } elseif (str_contains($carrierLower, 'dhl') || $scacLower === 'dhlw') {
                                    $trackingUrl = 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=' . $tracking->invoiceNumber;
                                } elseif (str_contains($carrierLower, 'amazon') || $scacLower === 'amzl') {
                                    $trackingUrl = 'https://track.amazon.com/tracking/' . $tracking->invoiceNumber;
                                }
                            }
                        @endphp
                        <tr>
                            <td class="px-2 py-1 border border-black">{{ $item->product_code }}</td>
                            <td class="px-2 py-1 border border-black">{{ $product?->product_name ?? $item->product_name }}</td>
                            {{-- <td class="px-2 py-1 border border-black text-center">{{ number_format($item->ordered_qty) }}
                            </td> --}}
                            <td class="px-2 py-1 border border-black text-center">{{ number_format($item->ack_qty ?? 0) }}
                                {{ $item->ack_unit ?? '' }}
                            </td>
                            <td class="px-2 py-1 border border-black text-center">
                                ${{ number_format($item->unit_price ?? 0, 2) }}</td>
                            <td class="px-2 py-1 border border-black text-center">{{ $item->ack_type ?? '' }}
                            </td>

                            @if($tracking && $tracking->invoiceNumber)
                                <td class="px-2 py-1 border border-black text-center">
                                    <a href="{{ $trackingUrl }}" target="_blank"
                                        class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 font-medium"> <svg
                                            class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                    ${{ number_format($edi855data->sum(fn($i) => ($i->unit_price ?? 0) * ($i->ack_qty ?? 0)), 2) }}</p>
            </div>
        @else
            <div class="text-center py-10 text-lg">No EDI 855 acknowledgement data found</div>
        @endif
    </div>

    <!-- Footer -->
    <div class="px-8 py-4 border-t border-black bg-white flex justify-between items-center print:hidden text-xs">
        <div>Document ID: ACK-{{ now()->format('Ymd') }}-{{ substr(md5(uniqid()), 0, 6) }}</div>
        <div class="flex space-x-2">
            {{-- <button onclick="window.print()" class="px-3 py-1 border border-black">Print</button> --}}
            <x-secondary-button x-on:click="$dispatch('close')" class="border border-black">Close</x-secondary-button>
        </div>
    </div>
</x-modal>