{{-- Main Invoice Layout --}}
<div class="max-w-6xl mx-auto bg-gray-50 min-h-screen">
    @forelse($po->edi810s as $index => $invoice)
        @if ($index === 0)
            {{-- Invoice Summary Card --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6 overflow-hidden">
                {{-- Invoice Header --}}
                <div class="bg-gray-50 border-b border-gray-200 p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ $po->purchase_order_number }}</p>
                                        <p class="text-indigo-500 text-lg font-semibold">Invoice #{{ $invoice->invoice_number }}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Issued on {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}
                                    </p>
                                    {{-- Added to clearly show who issued the invoice (Supplier)
                                    and who is billed for it (Organization)--}}
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Issued by:</span> {{ $supplier->supplier_name ?? '' }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Billed to:</span> {{ $organization->name ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="lg:text-right">
                            <p class="text-sm text-gray-500 mb-1">Total Amount</p>
                            <div class="flex items-center gap-2">
                                <span class="text-3xl font-bold text-emerald-600">
                                    ${{ number_format($invoice->total_amount_due / 100, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Line Items Section --}}
                <div class="p-6">

                    {{-- Desktop Table View --}}
                    <div class="hidden lg:block overflow-hidden border border-gray-200 rounded-lg">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Product</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Description</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Unit</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Qty</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Price</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Tax</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
        @endif

                            {{-- Desktop Table Row --}}
                            <tr class="hidden lg:table-row hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-indigo-600 font-semibold text-xs">
                                                {{ substr($invoice->product_code, 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $invoice->product_code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-900">{{ $invoice->product_description }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                        {{ $invoice->unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-semibold text-gray-900">{{ $invoice->qty }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="font-semibold text-gray-900">${{ number_format($invoice->price, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">${{ number_format($invoice->tax, 2) }}</p>
                                        <p class="text-xs text-gray-500">({{ $invoice->taxPercent }}%)</p>
                                    </div>
                                </td>
                            </tr>

                            {{-- Mobile Card View --}}
                            <div class="lg:hidden bg-white border border-gray-200 rounded-lg p-4 mb-4 shadow-sm">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-indigo-600 font-semibold text-sm">
                                                {{ substr($invoice->product_code, 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $invoice->product_code }}</p>
                                            <p class="text-sm text-gray-500">{{ $invoice->unit }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">${{ number_format($invoice->price, 2) }}</p>
                                        <p class="text-xs text-gray-500">Qty: {{ $invoice->qty }}</p>
                                    </div>
                                </div>

                                <p class="text-gray-700 text-sm mb-3">{{ $invoice->product_description }}</p>

                                <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                    <span class="text-sm text-gray-500">Tax ({{ $invoice->taxPercent }}%)</span>
                                    <span class="font-semibold text-gray-900">${{ number_format($invoice->tax, 2) }}</span>
                                </div>
                            </div>

                            @if ($loop->last)
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Invoice Footer --}}
                                    <div class="bg-gray-50 border-t border-gray-200 p-6">
                                        <div class="flex flex-col sm:flex-row sm:justify-between items-center gap-4">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Invoice generated automatically
                                            </div>
                                            <div class="flex gap-3">
                                                <a href="{{ route('invoice.download', $po->id) }}"
                                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Download PDF
                                                </a>

                                                {{-- <button
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors duration-150">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                    Send Email
                                                </button> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

    @empty
        {{-- Empty State --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Invoices Found</h3>
            <p class="text-gray-500 mb-6">There are no invoices associated with this Purchase Order yet.</p>
            <button
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Invoice
            </button>
        </div>
    @endforelse
</div>