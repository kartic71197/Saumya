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

    {{-- Practices Dropdown --}}
     @if (auth()->user()->role_id == 1)
        <div>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button id="orgDropdownBtn"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                        <div>All Practices</div>
                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="org-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-id="0"
                        data-name="All Practices">
                        All Practices
                    </div>
                    @foreach ($org_list as $org)
                        <div class="org-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            data-id="{{ $org->id }}" data-name="{{ $org->name }}">
                            {{ $org->name }}
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

    const lowStockEl = document.getElementById('low_on_stock');
    const availableEl = document.getElementById('product_avialable');
    const notAvailableEl = document.getElementById('product_not_avialable');
    const totalEl = document.getElementById('total_products');
    const organizationOl = document.getElementById('currentOrganziationDisplay');
    const activePercentEl = document.getElementById('active_percent');
    const activeBarEl = document.getElementById('active_bar');

    // 🔐 HARD GUARD — STOP if UI not present
    if (!lowStockEl || !availableEl || !notAvailableEl || !totalEl) {
        console.warn('⚠️ Products overview section not rendered. Skipping update.');
        return;
    }

    lowStockEl.textContent = productsOverviewData?.low_on_stock ?? 0;
    availableEl.textContent = productsOverviewData?.product_avialable ?? 0;
    notAvailableEl.textContent = productsOverviewData?.product_not_avialable ?? 0;
    totalEl.textContent = productsOverviewData?.total_products ?? 0;

    if (organizationOl) {
        organizationOl.textContent = productsOverviewData.orgName ?? '';
    }

    let activePercent = 0;
    if (productsOverviewData.product_avialable && productsOverviewData.total_products) {
        activePercent = (
            productsOverviewData.product_avialable /
            productsOverviewData.total_products * 100
        ).toFixed(2);
    }

    if (activePercentEl) activePercentEl.textContent = `${activePercent}%`;
    if (activeBarEl) activeBarEl.style.width = `${activePercent}%`;
}

</script>
<script>
    let selectedOrganization = 0;
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

        $('.org-option').on('click', function () {
            var selectedOrgId = $(this).data('id');
            var selectedOrgName = $(this).data('name');
            $('#orgDropdownBtn div').text(selectedOrgName);
            updateDashboard(selectedOrgId, selectedOrgName);
        });

        $('#fromDate, #toDate').on('change', function () {
            selectedFromDate = $('#fromDate').val();
            selectedToDate = $('#toDate').val();
            console.log('Date filters changed:', {
                fromDate: selectedFromDate,
                toDate: selectedToDate
            });

            updateDashboard(selectedOrganization);
        });

        function updateDashboard(orgId = selectedOrganization, orgName = 'All Practices') {
            if (orgId !== null && orgId !== undefined) {
                selectedOrganization = parseInt(orgId);
            }

            console.log('Updating dashboard with:', {
                organization_id: selectedOrganization,
                from_date: selectedFromDate,
                to_date: selectedToDate
            });

            if (typeof initBarGraph === 'function') {
                initBarGraph();
            }

            const url = '/admin/update_dashboard/' + selectedOrganization;
            
            $.get(url, {
                from_date: selectedFromDate,
                to_date: selectedToDate
            }, function (data) {
                console.log('Dashboard data received:', data);

                if (typeof updateBarGraph === 'function') {
                    updateBarGraph(selectedOrganization);
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
                    updateRecentPurchaseOrders(data.recent_purchase_orders_list || [], orgName);
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
                    orgName: orgName
                });

                if (typeof loadSupplierChart === 'function') {
                    loadSupplierChart();
                }
            }).fail(function(xhr, status, error) {
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
            link.addEventListener('click', function (e) {
                e.preventDefault();
                let url = '/purchase';
                // Your existing code...
            });
        }
        
        // Initial load of dashboard
        updateDashboard(selectedOrganization, 'All Practices');
    });
</script>