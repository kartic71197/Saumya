<x-modal name="product_report_details_modal" maxWidth="4xl">

    {{-- HEADER --}}
    <header
        class="rounded-t-lg border-b border-gray-200 dark:border-gray-700">

        <div class="px-6 py-4 flex justify-center">
            <x-application-logo
                class="h-12 w-auto fill-current text-gray-700 dark:text-gray-300" />
        </div>
    </header>

    {{-- TITLE SECTION --}}
    <div
        class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b
               border-gray-200 dark:border-gray-700">

        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
            Product Order Details
        </h2>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Purchase orders containing
        <span class="font-semibold text-gray-800 dark:text-gray-100">
            {{ $productName ?? 'this product' }}
        </span>
    </p>
    </div>
    

    {{-- BODY --}}
    <div class="px-6 py-6">

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">
                            Order Date
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">
                            PO Number
                        </th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                            Qty Ordered
                        </th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                            Cost
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                            </td>

                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">
                                {{ $order->purchase_order_number }}
                            </td>

                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-200">
                                {{ $order->quantity }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-gray-100">
                                {{ session('currency', '$') }}
                                {{ number_format($order->sub_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                No orders found for this product.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-modal>
