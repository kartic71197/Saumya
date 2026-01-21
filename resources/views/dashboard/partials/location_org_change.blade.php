<div class="flex justify-end items-center mb-3 gap-3">
    <!-- <div class="flex items-center gap-2">
        {{-- Date Range Labels and Inputs --}}
        <div class="flex items-center gap-2">
            {{-- Start Date Label --}}
            <label for="fromDate" class="text-xs text-gray-600 font-medium whitespace-nowrap">
                Start date:
            </label>

            {{-- From Date --}}
            <input type="date" id="fromDate" class="h-9 px-3 text-xs border border-gray-300 rounded-md
                   bg-white text-gray-700
                   focus:outline-none focus:ring-1 focus:ring-primary" />
        </div>
        <div class="flex items-center gap-2">
            {{-- End Date Label --}}
            <label for="toDate" class="text-xs text-gray-600 font-medium whitespace-nowrap">
                End date:
            </label>

            {{-- To Date --}}
            <input type="date" id="toDate" class="h-9 px-3 text-xs border border-gray-300 rounded-md
                   bg-white text-gray-700
                   focus:outline-none focus:ring-1 focus:ring-primary" />
        </div>
    </div> -->
    {{--removed Organization Dropdowdown from here and added that on superadmin side --}}
     
    {{-- Location Dropdown --}}
    @if (auth()->user()->role_id > 1)
        <div>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button id="locDropdownBtn"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                        <div>All Locations</div>
                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="loc-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-id="0"
                        data-name="All Locations">
                        All Locations
                    </div>
                    @foreach ($locations_list as $location)
                        <div class="loc-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            data-id="{{ $location->id }}" data-name="{{ $location->name }}">
                            {{ $location->name }}
                        </div>
                    @endforeach
                </x-slot>
            </x-dropdown>
        </div>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateProductsOverview(productsOverviewData) {
        console.log("product OverView data -->", productsOverviewData);

        //  Guard clause - Check if elements exist BEFORE trying to set textContent
        const lowStockEl = document.getElementById('low_on_stock');
        const availableEl = document.getElementById('product_avialable');
        const notAvailableEl = document.getElementById('product_not_avialable');
        const totalEl = document.getElementById('total_products');
        const locationDisplayEl = document.getElementById('currentLocationDisplay');

        // If any element is missing, log a warning and exit the function
        if (!lowStockEl || !availableEl || !notAvailableEl || !totalEl) {
            console.warn(' Products overview section not rendered. Skipping update.');
            return;
        }

        // Now safely update the elements
        lowStockEl.textContent = productsOverviewData?.low_on_stock ?? 0;
        availableEl.textContent = productsOverviewData?.product_avialable ?? 0;
        notAvailableEl.textContent = productsOverviewData?.product_not_avialable ?? 0;
        totalEl.textContent = productsOverviewData?.total_products ?? 0;
        
        if (locationDisplayEl) {
            locationDisplayEl.textContent = productsOverviewData?.locName ?? 'All Locations';
        }

        let activePercent = 0;

        if (productsOverviewData.product_avialable && productsOverviewData.total_products) {
            // Update percentage bar
            activePercent = (
                productsOverviewData.product_avialable /
                productsOverviewData.total_products * 100
            ).toFixed(2);

        }


        document.getElementById('active_percent').textContent = `${activePercent}%`;
        document.getElementById('active_bar').style.width = `${activePercent}%`;

        // Update chart
        if (typeof productsChart !== 'undefined' && productsChart) {
            productsChart.data.datasets[0].data = getProductData();
            productsChart.update();
        }
    }

    // function updateRecentPurchaseOrders(orders, locationName) {
    //     document.getElementById('recentOrdersLocationDisplay').textContent = locationName ?? 'All Locations';
    //     const container = document.querySelector('.recent-purchase-orders-list');

    //     container.innerHTML = '';
    //     if (Array.isArray(orders) && orders.length > 0) {

    //         orders.forEach(order => {
    //             const statusClasses = {
    //                     'pending': 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400 border-yellow-200',
    //                     'ordered': 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400 border-blue-200',
    //                     'partial': 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400 border-orange-200',
    //                     'completed': 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400 border-green-200',
    //                     'cancel': 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400 border-red-200',
    //                 } [order.status] ||
    //                 'bg-gray-100 text-gray-600 dark:bg-gray-900 dark:text-gray-400 border-gray-200';

    //             const dotColor = {
    //                 'pending': 'bg-yellow-500',
    //                 'ordered': 'bg-blue-500',
    //                 'partial': 'bg-orange-500',
    //                 'completed': 'bg-green-500',
    //                 'cancel': 'bg-red-500',
    //             } [order.status] || 'bg-gray-500';

    //             const locationName = order.purchase_location ? order.purchase_location.name :
    //                 'Unknown Location';
    //             const supplierName = order.purchase_supplier ? order.purchase_supplier.supplier_name :
    //                 'Supplier Name';
    //             const amount = parseFloat(order.total ?? 0).toFixed(2);
    //             const createdAt = order.created_at ? new Date(order.created_at).toLocaleString('default', {
    //                 month: 'short',
    //                 day: 'numeric'
    //             }) : 'Dec 15';

    //             const orderElement = document.createElement('div');
    //             orderElement.className =
    //                 `flex items-center justify-between gap-3 p-3 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200`;
    //             orderElement.innerHTML = `
    //             <div class="flex min-w-0 flex-col">
    //                 <p class="text-sm font-semibold text-gray-900 truncate">${order.purchase_order_number}</p>
    //                 <div class="flex items-center gap-1 mt-0.5">
    //                     <p class="text-xs text-gray-600 truncate">${supplierName}</p>
    //                 </div>
    //             </div>
    //             <div class="flex-shrink-0">
    //                 <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border ${statusClasses}">
    //                     <div class="w-1.5 h-1.5 rounded-full mr-1.5 ${dotColor}"></div>
    //                     ${order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Pending'}
    //                 </span>
    //             </div>
    //             <div class="flex-shrink-0">
    //                 <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium">
    //                     ${locationName}
    //                 </span>
    //             </div>
    //             <div class="flex-shrink-0 text-right min-w-0">
    //                 <p class="text-sm font-semibold text-gray-900">$${amount}</p>
    //                 <p class="text-xs text-gray-500">${createdAt}</p>
    //             </div>
    //             <div class="flex-shrink-0">
    //                 <button
    //                     onclick="openReorderModal(${order.id})"
    //                     class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors duration-200"
    //                     title="Reorder this purchase order">
    //                     <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    //                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    //                             d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
    //                     </svg>
    //                     Reorder
    //                 </button>
    //             </div>
    //         `;

    //             container.appendChild(orderElement);
    //         });
    //     } else {
    //         container.innerHTML = `
    //         <div class="p-4 text-center text-gray-500 text-sm">
    //             No recent purchase orders found.
    //         </div>
    //     `;
    //     }
    // }
</script>
<script>
    let selectedLocation = 0;
    // Default values for date filters
    let selectedFromDate = null;
    let selectedToDate = null;
    let purchaseOrderStatPieChart;

    function updatePurchaseOrderStat(ordered_status_count, partial_status_count, in_cart_count) {
        $('#ordered_status_count').text(ordered_status_count);
        $('#partial_status_count').text(partial_status_count);
        $('#in_cart_count').text(in_cart_count);

        let total = Math.max(1, ordered_status_count + partial_status_count + in_cart_count);

        let ordered_status_count_percent = ((ordered_status_count / total) * 100).toFixed(1);
        let partial_status_count_percent = ((partial_status_count / total) * 100).toFixed(1);
        let in_cart_count_percent = ((in_cart_count / total) * 100).toFixed(1);

        $('#ordered_status_count_percent').text(ordered_status_count_percent + "%");
        $('#partial_status_count_percent').text(partial_status_count_percent + "%");
        $('#in_cart_count_percent').text(in_cart_count_percent + "%");

        if (typeof purchaseOrderStatPieChart !== 'undefined' && purchaseOrderStatPieChart) {
            purchaseOrderStatPieChart.data.datasets[0].data = [
                ordered_status_count,
                partial_status_count,
                in_cart_count
            ];
            purchaseOrderStatPieChart.update();
        }
    }

    $(document).ready(function () {
        console.log('Dashboard main script loaded');

        // Check if functions are available
        console.log('Available functions on load:', {
            updateLowProductList: typeof updateLowProductList,
            updateRecentPurchaseOrders: typeof updateRecentPurchaseOrders,
            updateSupplierStats: typeof updateSupplierStats
        });

        $('.loc-option').on('click', function () {
            var selectedLocId = $(this).data('id');
            var selectedLocName = $(this).data('name');
            $('#locDropdownBtn div').text(selectedLocName);
            updateDashboard(selectedLocId, selectedLocName);
        });

        $('#fromDate, #toDate').on('change', function () {
            selectedFromDate = $('#fromDate').val();
            selectedToDate = $('#toDate').val();
            console.log('Date filters changed:', {
                fromDate: selectedFromDate,
                toDate: selectedToDate
            });

            updateDashboard(selectedLocation);
        });

        // Function to update the dashboard data
        //workflow: when location or date changes, call this function to update all relevant sections
        // this function makes an AJAX call to fetch updated data based on selected filters
        // and then calls individual update functions for each section of the dashboard
        // modular approach to keep code organized and maintainable
        function updateDashboard(locId = selectedLocation, locName = 'All Locations') {
            if (locId !== null && locId !== undefined) {
                selectedLocation = parseInt(locId);
            }

            console.log('Updating dashboard with:', {
                location_id: selectedLocation,
                from_date: selectedFromDate,
                to_date: selectedToDate
            });

            if (typeof initBarGraph === 'function') {
                initBarGraph();
            }

            const url = '/update_dashboard/' + selectedLocation;

            $.get(url, {
                from_date: selectedFromDate,
                to_date: selectedToDate
            }, function (data) {
                console.log('Dashboard data received:', data);

                if (typeof updateBarGraph === 'function') {
                    updateBarGraph(selectedLocation);
                }

                updateTopHeaders(
                    data.stock_onhand || 0,
                    data.value_onhand || 0,
                    data.stock_to_receive || 0,
                    data.pending_value || 0
                );

                updatePurchaseOrderStat(
                    data.ordered_status_count || 0,
                    data.partial_status_count || 0,
                    data.in_cart_count || 0
                );

                if (typeof updateSupplierStats === 'function') {
                    updateSupplierStats(data.supplier_list || []);
                }

                if (typeof updateRecentPurchaseOrders === 'function') {
                    console.log('✅ Calling updateRecentPurchaseOrders...');
                    updateRecentPurchaseOrders(data.recent_purchase_orders_list || [], locName);
                } else {
                    console.error('❌ updateRecentPurchaseOrders function not found!');
                }

                if (typeof updateLowProductList === 'function') {
                    updateLowProductList(data.low_stock_products_list || []);
                }

                updateProductsOverview({
                    low_on_stock: data.low_on_stock || 0,
                    product_avialable: data.product_avialable || 0,
                    product_not_avialable: data.product_not_avialable || 0,
                    total_products: data.total_products || 0,
                    locName: locName
                });

                if (typeof loadSupplierChart === 'function') {
                    loadSupplierChart();
                }
            }).fail(function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                alert('Error loading dashboard data. Please try again.');
            });
        }

        function updateTopHeaders(stock_onhand, value_onhand, stock_to_receive, pendingValue) {
            $('#stock_onhand').text(stock_onhand);
            $('#value_onhand').text('$' + formatCurrency(value_onhand));
            $('#stock_to_receive').text(stock_to_receive);
            $('#pendingValue').text('$' + formatCurrency(pendingValue));
        }

        function formatCurrency(value) {
            return value ? parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '0.00';
        }
        const link = document.getElementById('stock-to-receive-link');
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let url = '/purchase';
                // if (selectedLocation && selectedLocation !== 0) {
                //     url += '?location_id=' + encodeURIComponent(selectedLocation);
                // } 
                // else {
                //     console.log('No location found, using base URL:', url);
                // }

                // window.location.href = url;
            });
        }

        // Initial load of dashboard
        updateDashboard(selectedLocation, 'All Locations');
    });
</script>
