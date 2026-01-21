<!-- Location Display -->
<div class="px-2 py-1 rounded flex items-center space-x-1 justify-end bg-gray-50"
    style="position: absolute; top: 20px; right: 20px;">
    <span id="recentOrdersLocationDisplay" class="text-xs font-medium text-gray-500">All Locations</span>
</div>

<!-- Orders Container -->
<div class="recent-purchase-orders-list w-full">
    <!-- Desktop Header Row - Hidden on mobile -->
    <div class="hidden lg:grid gap-3 px-3 py-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100 dark:border-gray-700"
        style="grid-template-columns: 140px 100px 90px 90px 1fr;">
        <span>Purchase order</span>
        <span>Location</span>
        <span class="text-left">Amount</span>
        <span class="text-left">Ordered Date</span>
        <span class="text-center">Status</span>
    </div>

    <!-- JS will render all rows here -->
    <div id="recentOrdersList"></div>

</div>

<!-- JS Rendering Function -->
<script>
function updateRecentPurchaseOrders(orders, locationName) {
    const locationEl = document.getElementById('recentOrdersLocationDisplay');
    if (locationEl) locationEl.textContent = locationName ?? 'All Locations';

    const container = document.getElementById('recentOrdersList');
    if (!container) return;

    // Clear old rows
    container.innerHTML = '';

    if (!Array.isArray(orders) || orders.length === 0) {
        container.innerHTML = `<div class="p-4 text-center text-gray-500 text-sm">No recent purchase orders found.</div>`;
        return;
    }

    orders.forEach(order => {
        const step = Number(order.supplier_progress_step ?? order.supplierProgressStep ?? order.step) || 0;
        const location = order.purchase_location?.name ?? order.purchaseLocation?.name ?? 'Unknown';
        const supplier = order.purchase_supplier?.supplier_name ?? order.purchaseSupplier?.supplier_name ?? 'Supplier';
        const amount = parseFloat(order.total ?? 0).toFixed(2);
        const date = order.created_at
            ? new Date(order.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
            : 'N/A';

        const stepLabels = ['Ordered', "Acknowledged", 'Shipped']; // 4 steps if needed

        // Render steps for desktop
        let desktopStepsHTML = '';
        for (let i = 1; i <= 3; i++) { // Change to 4 if Delivered is required
            desktopStepsHTML += `
                <div class="flex flex-col items-center relative group">
                    <div class="relative">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300 ${step >= i ? 'bg-green-600 shadow-lg shadow-green-200' : 'bg-gray-200'}">
                            ${step >= i
                                ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>'
                                : '<div class="w-2 h-2 rounded-full bg-gray-400"></div>'}
                        </div>
                        ${step === i ? '<div class="absolute inset-0 rounded-full bg-green-600 opacity-20 animate-ping"></div>' : ''}
                    </div>
                    <span class="text-[9px] mt-1 ${step >= i ? 'text-green-600' : 'text-gray-500'} whitespace-nowrap">${stepLabels[i-1]}</span>
                </div>
                ${i < 3 ? `
                    <div class="flex-1 flex items-center" style="margin-bottom: 16px;">
                        <div class="h-[1px] w-full bg-gray-200 rounded-full relative overflow-hidden">
                            <div class="absolute inset-0 rounded-full transition-all duration-500 ${step >= i+1 ? 'bg-gradient-to-r from-green-600 to-green-500 w-full' : 'w-0'}"></div>
                        </div>
                    </div>` : ''}
            `;
        }

        // Render steps for mobile
        let mobileStepsHTML = '';
        for (let i = 1; i <= 3; i++) {
            mobileStepsHTML += `
                <div class="flex flex-col items-center">
                    <div class="relative">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center transition-all duration-300 ${step >= i ? 'bg-green-600 shadow-md' : 'bg-gray-200'}">
                            ${step >= i
                                ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>'
                                : '<div class="w-2 h-2 rounded-full bg-gray-400"></div>'}
                        </div>
                        ${step === i ? '<div class="absolute inset-0 rounded-full bg-green-600 opacity-20 animate-ping"></div>' : ''}
                    </div>
                    <span class="text-[9px] mt-0.5 ${step >= i ? 'text-green-600 font-medium' : 'text-gray-500'}">${stepLabels[i-1]}</span>
                </div>
                ${i < 3 ? `
                    <div class="flex-1 h-[2px] bg-gray-200 mx-1 relative overflow-hidden" style="margin-bottom: 12px;">
                        <div class="absolute inset-0 transition-all duration-500 ${step >= i+1 ? 'bg-green-600 w-full' : 'w-0'}"></div>
                    </div>` : ''}
            `;
        }

        container.innerHTML += `
            <!-- Desktop -->
            <div class="hidden lg:grid gap-3 items-center px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 rounded-lg border-b border-gray-100 dark:border-gray-700" style="grid-template-columns: 140px 100px 90px 90px 1fr;">
                <div class="flex flex-col truncate">
                    <p class="text-xs font-semibold text-gray-900 truncate">${order.purchase_order_number}</p>
                    <p class="text-[10px] text-gray-600 truncate">${supplier}</p>
                </div>
                <div class="text-xs text-gray-700 dark:text-gray-300 truncate">${location}</div>
                <div class="text-left"><p class="text-xs font-semibold text-gray-900">$${amount}</p></div>
                <div class="text-left text-[10px] text-gray-500">${date}</div>
                <div class="flex items-center justify-center px-4">
                    <div class="flex items-center w-full max-w-2xl">${desktopStepsHTML}</div>
                </div>
            </div>

            <!-- Mobile -->
            <div class="lg:hidden bg-white dark:bg-gray-800 rounded-lg p-3 mb-2 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-900 dark:text-white">${order.purchase_order_number}</p>
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-0.5">${supplier}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">$${amount}</p>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-3 text-[10px]">
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">${location}</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-gray-500">${date}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between px-2">${mobileStepsHTML}</div>
            </div>
        `;
    });
}

// On page load, render Blade data
document.addEventListener('DOMContentLoaded', function() {
    updateRecentPurchaseOrders(@json($recent_purchase_orders_list), 'All Locations');
});
</script>
