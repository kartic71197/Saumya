<x-app-layout>
    <div class="max-w-5xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md rounded-lg mb-6">
            <section class="w-full">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Reports Dashboard') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Access various reports to monitor and analyze your inventory and orders efficiently.') }}
                        </p>
                    </div>
                    <div class="relative w-full md:w-64 mt-4 md:mt-0">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="searchReports" placeholder="Search reports..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all">
                    </div>
                </header>
            </section>
        </div>

        <!-- Reports Grid -->
        <div id="reportsList"
            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">

            @php
                $reports = [
                    [
                        'route' => 'report.audit',
                        'title' => 'Audit Report',
                        'desc' => 'Monitor audits and compliance status within your inventory.',
                        'icon' => 'M9 12h6m-3-3v6m9 4H3V5h18v14z',
                    ],
                    [
                        'route' => 'report.cycleCount',
                        'title' => 'Cycle Count Report',
                        'desc' => 'Check all Cycle Counts details so far.',
                        'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                    ],
                    [
                        'route' => 'report.inventoryAdjust',
                        'title' => 'Inventory Adjust Report',
                        'desc' => 'Monitor all the inventory adjustments from the past.',
                        'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    ],
                    [
                        'route' => 'report.inventoryTransfers',
                        'title' => 'Inventory Transfer Report',
                        'desc' => 'Check all Inventory transfers done so far.',
                        'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    ],
                    [
                        'route' => 'report.invoices',
                        'title' => 'Invoices Report',
                        'desc' => 'Check all invoices of the purchase orders.',
                        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    [
                        'route' => 'report.purchase_order',
                        'title' => 'Order History Report',
                        'desc' => 'View your order history report with advanced filters.',
                        'icon' =>
                            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                    ],
                    [
                        'route' => 'report.picking',
                        'title' => 'Picking Report',
                        'desc' => 'Analyze picking efficiency and order fulfillment progress.',
                        'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                    ],
                    // ['route' => 'report.lot_picking', 'title' => 'Batch(LOT#) Picking Report', 'desc' => 'Analyze Batch(LOT#) picking efficiency and order fulfillment progress.'],


                    [
                        'route' => 'report.priceHistory',
                        'title' => 'Price history Report',
                        'desc' => 'Check price history logs of all products.',
                        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    [
                        'route' => 'report.product',
                        'title' => 'Product Report',
                        'desc' => 'Check all Products purchased quantity and amount.',
                        'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                    ],
                    [
                        'route' => 'report.sales',
                        'title' => 'Sales Report',
                        'desc' => 'Check all Sales details so far.',
                        'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                    ],


                ];
                if (auth()->user()->role_id == 1) {
                    $reports[] = [
                        'route' => 'admin.report.edi-report',
                        'title' => 'EDI Report',
                        'desc' => 'Track and analyze all EDI purchase order transactions in one place.',
                        'icon' => 'M3 3h18v2H3V3zm2 4h14v2H5V7zm-2 4h18v2H3v-2zm2 4h14v2H5v-2zm-2 4h18v2H3v-2z',
                    ];
                }

            @endphp

            @foreach ($reports as $report)
                <div class="report-item group relative hover:bg-gradient-to-r hover:from-primary-lt/5 hover:to-primary-md/5 transition-all duration-300 cursor-pointer"
                    data-title="{{ strtolower($report['title']) }}">
                    <!-- Left border on hover -->
                    <div
                        class="absolute left-0 top-0 h-full w-1 bg-primary-md transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left">
                    </div>

                    <div class="px-4 py-4 ml-1">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1 min-w-0">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-primary-lt to-primary-md rounded-lg flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $report['icon'] }}" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-primary-dk transition-colors duration-200">
                                        {{ __($report['title']) }}
                                    </h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed pr-3">
                                        {{ __($report['desc']) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="flex-shrink-0 ml-3">
                                <a href="{{ route($report['route']) }}"
                                    class="inline-flex items-center px-4 py-2 text-xs font-semibold text-primary-dk bg-primary-lt/20 border border-primary-lt rounded-lg hover:bg-primary-md hover:text-white hover:border-primary-md focus:ring-2 focus:ring-primary-lt/50 focus:outline-none transition-all duration-200 group/btn shadow-sm hover:shadow-md">
                                    {{ __('View') }}
                                    <svg class="w-3 h-3 ml-1.5 rtl:rotate-180 group-hover/btn:translate-x-1 transition-transform duration-200"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- No Results Message -->
    <div id="noResults" class="hidden mt-6">
        <div
            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="text-center py-12">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No reports found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms.</p>
                <button
                    onclick="document.getElementById('searchReports').value=''; document.getElementById('searchReports').dispatchEvent(new Event('input'));"
                    class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-medium text-primary-dk bg-primary-lt/20 border border-primary-lt rounded-lg hover:bg-primary-lt/30 transition-colors duration-200">
                    Clear Search
                </button>
            </div>
        </div>
    </div>
    </div>

    <!-- JavaScript for Filtering -->
    <script>
        document.getElementById('searchReports').addEventListener('input', function () {
            const filter = this.value.toLowerCase().trim();
            const reportItems = document.querySelectorAll('.report-item');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            reportItems.forEach(item => {
                const title = item.getAttribute('data-title');
                if (title.includes(filter)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.classList.toggle('hidden', visibleCount > 0);
        });
    </script>
</x-app-layout>