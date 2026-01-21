<div class="px-2 py-1 rounded flex items-center space-x-1 justify-end bg-gray-50" style="position: absolute; top: 20px; right: 20px;">
    <span id="recentOrdersLocationDisplay" class="text-sm font-medium text-gray-500">All Locations</span>
</div>
<div class="recent-purchase-orders-list w-full">
    <!-- Header Row -->
    <div class="grid grid-cols-5 sm:grid-cols-6 gap-4 px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100 dark:border-gray-700">
        <span>Purchase order</span>
        {{-- <span>Status</span> --}}
        <span>Location</span>
        <span class="text-right">Amount</span>
        <span class="text-right">Date</span>
        <span class="text-center"></span>
    </div>

    @foreach($recent_purchase_orders_list as $orders)
        @php
            $statusClasses = match ($orders->status) {
                'pending' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400 border-yellow-200',
                'ordered' => 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400 border-blue-200',
                'partial' => 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400 border-orange-200',
                'completed' => 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400 border-green-200',
                'cancel' => 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400 border-red-200',
                default => 'bg-gray-100 text-gray-600 dark:bg-gray-900 dark:text-gray-400 border-gray-200',
            };
        @endphp

        <div
            class="grid grid-cols-5 sm:grid-cols-6 gap-4 items-center px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 rounded-lg">
            
            <!-- PO Number & Supplier -->
            <div class="flex flex-col truncate">
                <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $orders->purchase_order_number }}
                </p>
                <p class="text-xs text-gray-600 truncate">
                    {{ $orders->purchaseSupplier->supplier_name ?? 'Supplier Name' }}
                </p>
            </div>

            <!-- Status Badge -->
            {{-- <div>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusClasses }}">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{
                        match ($orders->status) {
                            'pending' => 'bg-yellow-500',
                            'ordered' => 'bg-blue-500',
                            'partial' => 'bg-orange-500',
                            'completed' => 'bg-green-500',
                            'cancel' => 'bg-red-500',
                            default => 'bg-gray-500',
                        }
                    }}"></span>
                    {{ ucfirst($orders->status ?? 'pending') }}
                </span>
            </div> --}}

            <!-- Location -->
            <div class="text-sm text-gray-700 dark:text-gray-300 truncate">
                {{ ucfirst($orders->purchaseLocation->name) }}
            </div>

            <!-- Amount -->
            <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">${{ number_format($orders->total ?? 0.00, 2) }}</p>
            </div>

            <!-- Date -->
            <div class="text-right text-xs text-gray-500">
                {{ $orders->created_at ? $orders->created_at->format('M d, Y') : 'N/A' }}
            </div>

            <!-- Reorder Button -->
            {{-- <div class="flex justify-center">
                <button onclick="openReorderModal({{ $orders->id }})"
                    class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reorder
                </button>
            </div> --}}
            <!-- Supplier Progress -->
<div class="flex flex-col text-xs text-gray-600">
    @php $step = $orders->supplier_progress_step; @endphp

    <div class="flex items-center space-x-2">
        
        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full {{ $step >= 1 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
            <span class="ml-1 {{ $step >= 1 ? 'text-blue-600 font-semibold' : '' }}">Ordered</span>
        </div>

        <div class="flex-1 h-0.5 {{ $step >= 2 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>

        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full {{ $step >= 2 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
            <span class="ml-1 {{ $step >= 2 ? 'text-blue-600 font-semibold' : '' }}">Ack</span>
        </div>

        <div class="flex-1 h-0.5 {{ $step >= 3 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>

        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full {{ $step >= 3 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
            <span class="ml-1 {{ $step >= 3 ? 'text-blue-600 font-semibold' : '' }}">Shipped</span>
        </div>

        <div class="flex-1 h-0.5 {{ $step >= 4 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>

        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full {{ $step >= 4 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
            <span class="ml-1 {{ $step >= 4 ? 'text-blue-600 font-semibold' : '' }}">Delivered</span>
        </div>
    </div>
</div>

        </div>
    @endforeach
</div>

<!-- Confirmation Modal -->
<div id="reorder-confirm-modal"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] p-6 pb-0 flex flex-col"
        style="height:80vh;">
        <div class="overflow-y-auto flex-1 p-6">
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h3 class="text-2xl font-bold text-primary-dk dark:text-primary-md">
                    <span id="modalOrderNumber">Loading...</span>
                </h3>
            </div>
            <div class="px-4 py-4">
                <div class="flex justify-between text-sm mb-6">
                    <div class="space-y-2 w-1/2 pr-4">
                        <p>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">Created Date:</span>
                        <span id="modalOrderDate" class="font-medium text-gray-900 dark:text-white">Loading...</span>
                        </p>
                        <p>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">Location:</span>
                            <span id="modalLocation" class="font-medium text-gray-900 dark:text-white">Loading...</span>
                        </p>
                        <p>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">Created By:</span>
                        <span id="modalCreatedBy" class="font-medium text-gray-900 dark:text-white">Loading...</span>
                        </p>
                    </div>
                    <div class="space-y-2 w-1/2 pl-4">
                        <p>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">Total Products:</span>
                            <span id="modalTotalProducts" class="font-medium text-gray-900 dark:text-white">0</span>
                        </p>
                        <p>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">Grand Total:</span>
                            <span id="modalGrandTotal" class="font-medium text-gray-900 dark:text-white">$0.00</span>
                        </p>
                    </div>
                </div>


            </div>

            <div class="grid grid-cols-2 gap-1 mb-6 ">
                <!-- Shipping -->
                <div class="col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-white mb-3">Shipping Information</h4>
                    <p class="text-sm"><span class="font-medium text-gray-600 dark:text-gray-400">Location:</span>
                        <span id="modalShippingLocation" class="text-gray-900 dark:text-white">Loading...</span>
                    </p>
                    <p class="text-sm"><span class="font-medium text-gray-600 dark:text-gray-400">Contact:</span>
                        <span id="modalShippingEmail" class="text-gray-900 dark:text-white">Loading...</span>
                    </p>
                    <p class="text-sm"><span class="font-medium text-gray-600 dark:text-gray-400">Ship to:</span>
                        <span id="modalShippingNumber" class="text-gray-900 dark:text-white">Loading...</span>
                    </p>
                </div>

                <!-- Supplier -->
                <div
                    class="col-span-1 max-w-lg p-6 rounded-lg bg-primary-md p-6 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700">
                    <h4 class="text-base font-semibold text-white dark:text-white mb-3">Supplier Details</h4>
                    <h5 class="text-lg font-bold text-white dark:text-white mb-1" id="modalSupplierName"></h5>
                    <p class="text-sm text-white dark:text-gray-300 mb-1" id="modalSupplierEmail"></p>
                    <p class="text-sm text-white dark:text-gray-300 mb-1" id="modalSupplierPhone"></p>
                    <p class="text-xs text-white dark:text-gray-400" id="modalSupplierAddress"></p>
                </div>
            </div>

            <div class="mb-4">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white mb-3">Products</h4>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <table class="min-w-full table-fixed text-sm text-left divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th style="width: 50%" class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
                                    Product</th>
                            <th style="width: 12.5%" class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Unit
                                </th>
                                <th style="width: 12.5%" class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
                                    Quantity</th>
                                <th style="width: 12.5%" class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
                                    Received</th>
                                <th style="width: 12.5%" class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
                                    Total Price</th>
                            </tr>
                        </thead>
                    </table>

                    <div class="max-h-64 overflow-y-auto">
                        <table class="min-w-full text-sm text-left divide-y divide-gray-200 dark:divide-gray-700">
                            <tbody id="modalProductsTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-center text-gray-500">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="p-6 w-full flex justify-between space-x-2 sticky bottom-0 bg-white">
                <button type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500 rounded-lg transition-colors"
                    onclick="closeReorderModal()">
                    Cancel
                </button>
                <!-- Yes, reorder button - right -->
                <button id="addAllToCartBtn"
                    class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg 
               hover:bg-green-700 transition-colors"
                    onclick="openCartReviewModalPlain()">
                    Yes, Reorder
                </button>
            </div>
        </div>
    </div>
</div>

 <div id="poCartReviewModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-80 flex items-center justify-center">
    
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl 
                w-full max-w-3xl max-h-[90vh] flex flex-col -translate-y-10">

            <!-- Header -->
            <header class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Review Products Before Adding to Cart
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Adjust quantities if needed, then confirm.
                </p>
            </header>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2 text-left">Product</th>
                                <th class="px-4 py-2">Unit</th>
                                <th class="px-4 py-2">Quantity</th>
                                <th class="px-4 py-2 text-center">Action</th>

                            </tr>
                        </thead>
                        <tbody id="po-cart-review-body" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- JS fills here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700">
                <button id="closePoCartReviewPlain"
                    class="px-4 py-2 border border-green-700 bg-gray-100 hover:bg-gray-200 text-green-700 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="confirmPoCartReviewPlain"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    Add To Cart
                </button>
            </div>
        </div>
</div> 


<script>
    // let reorderId = null;
    // let currentOrder = null;
    window.reorderId = null;
    window.currentOrder = null;
    window.loadedOrder = window.loadedOrder || null;

    // function openReorderModal(id) {
    //     reorderId = id;
    //     loadPurchaseOrderDetails(id);
    // }
    // Expose functions globally so inline onclick can find them
    window.openReorderModal = function (id) {
        window.reorderId = id;
        window.loadPurchaseOrderDetails(id);
    };

    // Fetch details from your backend
    // async function loadPurchaseOrderDetails(id) {
         window.loadPurchaseOrderDetails = async function (id) {
        try {
            const res = await fetch(`/purchase-orders/${id}/details`);
            const data = await res.json();

            if (data.success) {
                const order = data.data;
                window.loadedOrder = order;

                renderReorderModal(order);
                // Always close cart review modal before opening PO modal
                const cartModal = document.getElementById('poCartReviewModal');
                if (cartModal) {
                    cartModal.classList.add('hidden');
                    cartModal.style.display = "none"; // extra safeguard

                }
                document.getElementById('reorder-confirm-modal').classList.remove('hidden');
            } else {
                alert('Failed to load purchase order details.');
            }
        } catch (error) {

            alert('Error fetching purchase order details.');

        }
    }

    // Fill in the modal with data
    function renderReorderModal(order) {
        document.getElementById('modalOrderNumber').innerText = order.purchase_order_number ?? '';
        document.getElementById('modalOrderDate').innerText = order.created_at ? new Date(order.created_at)
            .toLocaleDateString() : '';
        document.getElementById('modalLocation').innerText = order.purchase_location?.name ?? '';
        document.getElementById('modalCreatedBy').innerText = order.created_user?.name ?? '';
        document.getElementById('modalTotalProducts').innerText = order.purchased_products?.length ?? '0';
        document.getElementById('modalGrandTotal').innerText = '$' + (Number(order.total ?? 0)).toFixed(2);

        // Supplier info
        document.getElementById('modalSupplierName').innerText = order.purchase_supplier?.supplier_name ?? 'Supplier';
        document.getElementById('modalSupplierEmail').innerText = order.purchase_supplier?.supplier_email ?? 'Email';
        document.getElementById('modalSupplierPhone').innerText = order.purchase_supplier?.supplier_phone ?? 'Phone';

        // Shipping info
        document.getElementById('modalShippingLocation').innerText = order.shipping_location?.name ?? '';
        document.getElementById('modalShippingEmail').innerText = order.shipping_location?.email ?? '';
        document.getElementById('modalShippingNumber').innerText = order.bill_to ?? '';

        // Products table


        const tbody = document.getElementById('modalProductsTableBody');
        tbody.innerHTML = '';
        if (order.purchased_products?.length) {
            order.purchased_products.forEach(item => {
                // console.log('Purchased product ID:', item.id, 'Product object ID:', item.product?.id);

                const row = document.createElement('tr');
                row.innerHTML = `
            <td style="width: 50%" class="px-4 py-3 text-gray-500 whitespace-normal break-words">${item.product?.product_name ?? ''} (${item.product?.product_code ?? ''})</td>
            <td style="width: 12.5%" class="px-4 py-3  text-gray-500">${item.unit?.unit_name ?? ''}</td>
            <td style="width: 12.5%" class="px-4 py-3  text-gray-500">${item.quantity}</td>
            <td style="width: 12.5%" class="px-4 py-3  text-gray-500">${(parseFloat(item.received_quantity) || 0)}</td>
            <td style="width: 12.5%" class="px-4 py-3  text-gray-500">$${(parseFloat(item.sub_total) || 0).toFixed(2)}</td>
        `;
                tbody.appendChild(row);

            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">No products found.</td></tr>';
        }
    }
    // Closes modal
    function closeReorderModal() {
        reorderId = null;
        document.getElementById('reorder-confirm-modal').classList.add('hidden');
    }



    // When user confirms reorder
    function confirmReOrder() {
        if (!reorderId) return;

        fetch(`/purchase-orders/${reorderId}/reorder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                closeReorderModal();
                if (data.success) {

                    alert('Order placed successfully !');

                } else {
                    alert(data.message || 'Failed to reorder. Please try again.');
                }
            })
            .catch(err => {
                closeReorderModal();
                alert('An error occurred while reordering.');
            });
    };

    // Close modal if user clicks overlay
    document.getElementById('reorder-confirm-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReorderModal();
        }
    });

    // === NEW REVIEW CART MODAL ===
    window.poReviewProducts = [];

    function openCartReviewModalPlain() {

        const po = window.loadedOrder;
        if (!po || !po.purchased_products) {
            alert('No products found.');
            return;
        }


        // Close PO modal
        const reorderModal = document.getElementById('reorder-confirm-modal');
        if (reorderModal) reorderModal.classList.add('hidden');

        // Reset cart modal state
        document.getElementById('poCartReviewModal')?.classList.add('hidden');


        // Clone products into working array
        window.poReviewProducts = po.purchased_products.map(item => ({
            product_id: item.product?.id,
            product_name: item.product?.product_name,
            unit_name: item.unit?.unit_name,
            unit_id: item.unit?.id ?? null,
            quantity: item.quantity,
            price: item.sub_total ?? 0,
            organization_id: po.organization_id ?? null,
            location_id: po.location_id ?? null,
            added_by: po.created_user?.id ?? null
        }));


        renderPoCartReviewRowsPlain();
        // Show Cart Review Modal
        const cartModal = document.getElementById('poCartReviewModal');
        if (cartModal) {
            cartModal.classList.remove('hidden');
            cartModal.style.display = 'flex'; // ensure flex layout
            cartModal.style.position = 'fixed'; // in case inherited CSS changed it
            cartModal.style.top = '0';
            cartModal.style.left = '0';
            cartModal.style.width = '100%';
            cartModal.style.height = '100%';
            cartModal.style.zIndex = '99999'; // bring to front
            cartModal.scrollIntoView({
                behavior: 'smooth'
            });
            console.log("poCartReviewModal should now be visible", cartModal);
        } else {
            console.log(" poCartReviewModal element NOT found");
        }

    }


    function renderPoCartReviewRowsPlain() {
        const tbody = document.getElementById('po-cart-review-body');
        tbody.innerHTML = '';

        window.poReviewProducts.forEach((p, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
    <td class="p-3 font-medium text-left text-gray-900 dark:text-gray-100">
        ${p.product_name ?? ''}
    </td>
    <td class="p-3 text-center">
        ${p.unit_name ?? ''}
    </td>
    <td class="p-3 text-center">
    <div class="flex items-center justify-center rounded-md dark:border-gray-600 overflow-hidden">
        <!-- Minus -->
        <button type="button" data-index="${index}" 
            class="dec-btn px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 
                   dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 12H4" />
            </svg>
        </button>

        <!-- Input -->
        <input type="number" min="1" 
            class="w-12 text-center border-0 focus:ring-0 
                   dark:bg-gray-800 dark:text-gray-300 
                   [appearance:textfield] 
                   [&::-webkit-outer-spin-button]:appearance-none 
                   [&::-webkit-inner-spin-button]:appearance-none 
                   qty-input"
            data-index="${index}" value="${p.quantity}">

        <!-- Plus -->
        <button type="button" data-index="${index}" 
            class="inc-btn px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 
                   dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>
</td>
<td class="p-3 text-center">
                <!-- Remove Button -->
                <button type="button" data-index="${index}" class="remove-btn text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
</td>


`;

            tbody.appendChild(row);
        });

        // Attach remove button logic
        tbody.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = e.currentTarget.dataset.index;
                window.poReviewProducts.splice(index, 1); // remove from array
                renderPoCartReviewRowsPlain(); // re-render table
            });
        });
        // Attach events
        tbody.querySelectorAll('.dec-btn').forEach(btn =>
            btn.addEventListener('click', e => {
                const idx = e.target.dataset.index;
                if (window.poReviewProducts[idx].quantity > 1) {
                    window.poReviewProducts[idx].quantity--;
                    renderPoCartReviewRowsPlain();
                }
            })
        );

        tbody.querySelectorAll('.inc-btn').forEach(btn =>
            btn.addEventListener('click', e => {
                const idx = e.target.dataset.index;
                window.poReviewProducts[idx].quantity++;
                renderPoCartReviewRowsPlain();
            })
        );

        tbody.querySelectorAll('.qty-input').forEach(inp =>
            inp.addEventListener('change', e => {
                const idx = e.target.dataset.index;
                let val = parseInt(e.target.value) || 1;
                if (val < 1) val = 1;
                window.poReviewProducts[idx].quantity = val;
            })
        );
    }


    //     console.log('Adding products to cart:', products);

    function confirmReviewedAddToCart() {
        console.log("confirmReviewedAddToCart() called");
        const products = window.poReviewProducts;
        fetch(`/cart/check-existing`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    products
                })
            })
            .then(res => {

                return res.json();
            })
            .then(checkRes => {


                if (!checkRes.success) {
                    console.warn("Cart check failed:", checkRes.message);
                    alert(checkRes.message || 'Error checking cart.');
                    return;
                }

                const existing_ids = checkRes.existing_ids || [];
                const existing_names = checkRes.existing_names || [];


                const newProducts = products.filter(p => !existing_ids.includes(p.product_id));

                if (newProducts.length === 0) {
                    let msg;

                    if (existing_names.length === 1) {
                        msg = `${existing_names[0]} is already in the cart.`;
                    } else {
                        msg = `Products are already in the cart.`;
                    }

                    Livewire.dispatch('show-notification', {
                        message: msg,
                        type: 'error'
                    });
                    closeCartReviewModal();
                    return;
                }

                // Step 2: Add only non-duplicate products

                fetch(`/cart/add-multiple`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            po_id: window.loadedOrder?.id ?? null,
                            products: newProducts
                        })
                    })
                    // .then(res => res.json())
                    .then(res => {

                        return res ? res.json() : null;
                    })
                    .then(data => {

                        //                     console.log('Add to cart response:', data);
                        //                     if (data.success) {
                        //                         alert(data.message);
                        //                         closeReorderModal();

                        //                         let msg = `Cart updated successfully! ${newProducts.length} added.`;
                        //                         if (existing_ids.length > 0) {
                        //                             msg =
                        //                                 ` ${existing_names.join(', ')}:Skipped (already in cart).\n${newProducts.length} added.`;
                        //                         }

                        //                         Livewire.dispatch('show-notification', {
                        //                             message: msg,
                        //                             type: 'success'
                        //                         });


                        //                         // Trigger Livewire event to update CartIcon count
                        //                         Livewire.dispatch('cartUpdated');
                        //                     } else {
                        //                         alert(data.message || 'Failed to add products to cart.');
                        //                     }
                        //                 })
                        //                 .catch(err => {
                        //                     console.error('Error in addAllToCart:', err);
                        //                     Livewire.dispatch('show-notification', {
                        //                         message: 'Something went wrong while adding products.',
                        //                         type: 'error'
                        //                     });
                        //                 });
                        //         })
                        //         .catch(err => {
                        //             console.error('Error in cart/check-existing:', err);
                        //             Livewire.dispatch('show-notification', {
                        //                 message: 'Something went wrong while checking duplicates.',
                        //                 type: 'error'
                        //             });
                        //         });
                        // }
                        if (data && data.success) {
                            let msg = `Cart updated successfully! ${newProducts.length} added.`;
                            if (existing_ids.length > 0) {
                                if (existing_names.length === 1) {
            msg = `${existing_names[0]} was already in the cart. ${newProducts.length} added.`;
                                } else {
            msg = `${existing_names.length} products were already in the cart. ${newProducts.length} added.`;
                                }
                            }

                            Livewire.dispatch('show-notification', {
                                message: msg,
                                type: 'success'
                            });
                            Livewire.dispatch('cartUpdated');

                            // document.getElementById('closePoCartReview')?.click();
                            closeCartReviewModal();
                        } else if (data) {
                            console.warn("Failed to add products:", data.message);
                            Livewire.dispatch('show-notification', {
                                message: data.message || 'Failed to add products.',
                                type: 'error'
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Error in add-multiple:", err);
                        Livewire.dispatch('show-notification', {
                            message: 'Something went wrong while adding products.',
                            type: 'error'
                        });
                    });

            })
            .catch(err => {
                console.error(err);
                Livewire.dispatch('show-notification', {
                    message: 'Something went wrong while checking duplicates.',
                    type: 'error'
                });
            });
    }

    const poCartModal = document.getElementById('poCartReviewModal');
    const closeBtn = document.getElementById('closePoCartReviewPlain');

    function closeCartReviewModal() {
        poCartModal.classList.add('hidden');
        poCartModal.style.display = "none";
    }
    // Cancel button closes modal
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCartReviewModal);
    }

    // Outside click closes modal
    poCartModal.addEventListener('click', function(e) {
        if (e.target === poCartModal) {
            closeCartReviewModal();
        }
    });


    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCartReviewModal();
            closeReorderModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {


        // Confirm button triggers add to cart
        const confirmBtn = document.getElementById('confirmPoCartReviewPlain');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', confirmReviewedAddToCart);
        }


    });


    // Close modal if user clicks on the black background (overlay)
    document.getElementById('reorder-confirm-modal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeReorderModal();
        }
    });
</script>
