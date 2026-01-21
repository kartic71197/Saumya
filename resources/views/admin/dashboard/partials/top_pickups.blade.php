<div class="w-full p-3 bg-white rounded-lg text-sm">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <h3 class="font-semibold text-gray-800 text-sm">Top Pickups</h3>
        </div>
        <!-- <div class="flex items-center gap-2">
            <select onchange="getFilteredTopPickList(this)" id="sort-option-time"
                class="text-xs border border-gray-200 rounded-lg px-4 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="week">This Week</option>
                <option value="last_week">Last Week</option>
                <option value="month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="year">This Year</option>
                <option value="last_year">Last Year</option>
            </select>
            {{-- <span class="text-xs text-gray-500 bg-gray-50 px-2 py-0.5 rounded">
                Top {{ count($top_picks ?? []) }} items
            </span> --}}
        </div> -->
    </div>

    <!-- Table Header -->
    <div class="grid grid-cols-6 gap-2 px-2 py-1.5 bg-gray-50 rounded-md text-xs font-medium text-gray-600 border-b border-gray-200">
        <div class="col-span-2">Product</div>
        <div>Supplier</div>
        <div>Practice</div>
        <div>Unit</div>
        <div class="text-right">Picked Qty</div>
    </div>

    <!-- Products Table -->
    <div class="top-pickups-list space-y-0.5 mt-2">
        @foreach ($top_picks ?? [] as $index => $pickup)
            <div
                class="grid grid-cols-6 gap-2 p-2 py-3 border border-transparent hover:border-green-100 hover:bg-green-50/30 rounded-md transition cursor-pointer">

                <!-- Product Info (spans 2 columns) -->
                <div class="col-span-2 product-clickable" data-product-id="{{ $pickup->product_id }}">
                    <div class="flex items-center gap-2">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-gray-900 font-medium leading-tight truncate hover:text-green-700 text-xs">
                                {{ \Illuminate\Support\Str::limit($pickup->product_name, 40) }}
                            </h4>
                            <p class="text-xs text-gray-500">{{ $pickup->product_code }}</p>
                        </div>
                    </div>
                </div>

                <!-- Supplier -->
                <div class="flex flex-1 items-center">
                    <span class="text-xs text-gray-700 truncate"
                        title="{{ $pickup->supplier_name ?? 'N/A' }}">
                    {{ $pickup->supplier_name ?? 'N/A' }}
                    </span>
                </div>

                <!-- Practice -->
                <div class="flex items-center">
                    <span
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-xs truncate">
                        <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="truncate">{{$pickup->organization_name ?? 'N/A' }}</span>
                    </span>
                </div>

                <!-- Unit -->
                <div class="flex items-center">
                    <span class="text-xs text-gray-600 px-1.5 py-0.5 bg-gray-100 rounded">
                        {{ $pickup->picking_unit ?? 'N/A' }}
                    </span>
                </div>

                <!-- Picked Quantity -->
                <div class="flex items-center justify-end">
                    <div class="text-right">
                        <div class="text-sm font-semibold text-green-700">
                            {{ number_format($pickup->total_picked_qty) }}
                        </div>
                        {{-- <div class="text-xs text-gray-500">picked</div> --}}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if (count($top_picks ?? []) === 0)
        <div class="text-center py-6 text-sm text-gray-600">
            <div class="w-10 h-10 mx-auto mb-2 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            No pickup data available
        </div>
    @endif
</div>

<script>
$(document).ready(function() {
    // -----------------------
    // Global filter variables
    // -----------------------
    let selectedOrganization = $('#orgDropdownBtn').data('id') || 0;
    let selectedFromDate = $('#fromDate').val() || '';
    let selectedToDate = $('#toDate').val() || '';

    // -----------------------
    // Event Delegation for Product Modal
    // -----------------------
    $(document).on('click', '.product-clickable', function(e) {
        e.stopPropagation();
        const productId = $(this).data('product-id');
        
        if (!productId) {
            console.warn('No product ID found');
            return;
        }
        
        // Check if Livewire is available
        if (typeof Livewire !== 'undefined') {
            console.log('Opening product modal for ID:', productId);
            Livewire.dispatch('openProductDetailBrowser', { 
                id: productId,
                context: 'top_pickups' 
            });
        } else {
            console.error('Livewire is not available!');
            // Fallback: Redirect to product page or show alert
            alert('Product details cannot be loaded at the moment.');
        }
    });

    // -----------------------
    // Update Top Pickups HTML
    // -----------------------
    function updateTopPickupsList(pickups) {
        const container = document.querySelector('.top-pickups-list');
        if (!container) return;
        
        container.innerHTML = '';

        if (!pickups || pickups.length === 0) {
            container.innerHTML = `
                <div class="text-center py-6 text-sm text-gray-600">
                    <div class="w-10 h-10 mx-auto mb-2 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    No pickup data available
                </div>`;
            return;
        }

        pickups.forEach((pickup, index) => {
            const rankClass = index === 0 ? 'bg-yellow-100 text-yellow-800' :
                (index === 1 ? 'bg-gray-100 text-gray-600' :
                    (index === 2 ? 'bg-orange-100 text-orange-600' :
                        'bg-green-100 text-green-600'));

            // Safely get product data (flat structure from query builder)
            const productName = pickup.product_name || 'Unknown Product';
            const productCode = pickup.product_code || 'N/A';
            const supplierName = pickup.supplier_name || 'N/A';
            const organizationName = pickup.organization_name || 'N/A';
            const unit = pickup.picking_unit || 'N/A';
            const totalPicked = pickup.total_picked_qty || 0;

            const pickupHTML = `
                <div class="grid grid-cols-6 gap-2 p-2 border border-transparent hover:border-green-100 hover:bg-green-50/30 rounded-md transition cursor-pointer">
                    
                    <!-- Product Info (spans 2 columns) -->
                    <div class="col-span-2 product-clickable" data-product-id="${pickup.product_id}">
                        <div class="flex items-center gap-2">
                            <!-- Rank Badge -->
                            <div class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold ${rankClass}">
                                ${index + 1}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-gray-900 font-medium leading-tight truncate hover:text-green-700 text-xs">
                                    ${productName.length > 30 ? productName.slice(0, 30) + '...' : productName}
                                </h4>
                                <p class="text-xs text-gray-500">${productCode}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier -->
                    <div class="flex items-center">
                        <span class="text-xs text-gray-700 truncate" title="${supplierName}">
                            ${supplierName.length > 12 ? supplierName.slice(0, 12) + '...' : supplierName}
                        </span>
                    </div>

                    <!-- Practice -->
                    <div class="flex items-center">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-xs truncate">
                            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="truncate">
    ${organizationName.length > 8 ? organizationName.slice(0, 8) + '...' : organizationName}
</span>

                    </div>

                    <!-- Unit -->
                    <div class="flex items-center">
                        <span class="text-xs text-gray-600 px-1.5 py-0.5 bg-gray-100 rounded">
                            ${unit}
                        </span>
                    </div>

                    <!-- Picked Quantity -->
                    <div class="flex items-center justify-end">
                        <div class="text-right">
                            <div class="text-sm font-semibold text-green-700">
                                ${totalPicked.toLocaleString()}
                            </div>
                            <div class="text-xs text-gray-500">picked</div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', pickupHTML);
        });
    }

    // -----------------------
    // Fetch filtered data (SIMPLIFIED - no time filter)
    // -----------------------
    function refreshTopPickups() {
        console.log('Refreshing top pickups...');
    console.log('Selected Organization:', selectedOrganization);
    console.log('From Date:', selectedFromDate, 'To Date:', selectedToDate);
        // Show loading state
        const container = document.querySelector('.top-pickups-list');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-4 text-sm text-gray-600">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600 mx-auto mb-2"></div>
                    Loading...
                </div>`;
        }

        const params = new URLSearchParams();
        if (selectedOrganization) params.append('organization_id', selectedOrganization);
        if (selectedFromDate) params.append('from_date', selectedFromDate);
        if (selectedToDate) params.append('to_date', selectedToDate);

        fetch(`/admin/top-pickups/filter?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                console.log('[Top Pickups] Fetch status:', res.status);
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('[Top Pickups] Response data:', data.top_picks);
                updateTopPickupsList(data.top_picks ?? []);
                
                // Update the "Top X items" counter if it exists
                const counter = document.querySelector('.top-pickups-count');
                if (counter && data.top_picks) {
                    counter.textContent = `Top ${data.top_picks.length} items`;
                }
            })
            .catch(err => {
                console.error('Error fetching top pickups:', err);
                if (container) {
                    container.innerHTML = `
                        <div class="text-center py-6 text-sm text-red-600">
                            <div class="w-10 h-10 mx-auto mb-2 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            Failed to load data. Please try again.
                        </div>`;
                }
            });
    }

    // -----------------------
    // Event listeners for filters
    // -----------------------
   
    $('.org-option').on('click', function() {
        selectedOrganization = $(this).data('id');
        const orgName = $(this).data('name') || 'All Organizations';
        $('#orgDropdownBtn div').text(orgName);
        refreshTopPickups();
    });

    $('#fromDate').on('change', function() {
        selectedFromDate = $(this).val();
        
        // Validate date range
        if (selectedToDate && selectedFromDate > selectedToDate) {
            alert('From date cannot be after To date');
            $(this).val('');
            selectedFromDate = '';
            return;
        }
        
        refreshTopPickups();
    });

    $('#toDate').on('change', function() {
        selectedToDate = $(this).val();
        
        // Validate date range
        if (selectedFromDate && selectedToDate < selectedFromDate) {
            alert('To date cannot be before From date');
            $(this).val('');
            selectedToDate = '';
            return;
        }
        
        refreshTopPickups();
    });

    // -----------------------
    // Reset filters
    // -----------------------
    $(document).on('click', '.reset-filters', function() {
        selectedOrganization = 0;
        selectedFromDate = '';
        selectedToDate = '';

        $('#orgDropdownBtn div').text('All Practices');
        $('#fromDate').val('');
        $('#toDate').val('');
        
        refreshTopPickups();
    });

    // -----------------------
    // Initialize
    // -----------------------
    // No initialization needed since we don't have a time filter dropdown
});
</script>
