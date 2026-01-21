<div x-data="pos()" data-role-id="{{ auth()->user()->role_id }}" data-locations='@json($locations)'
    class="min-h-screen pt-1">
    <div class="max-w-7xl mx-auto">
        {{-- Location Selection and search --}}
        @include('organization.pos.partials.checkout-search')

        {{-- Main Interface (Only show after location selected) --}}
        <div x-show="selectedLocation" x-transition.duration.300ms class="grid grid-cols-1 lg:grid-cols-3 gap-4 py-3">

            {{-- Left: Search & Cart --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Cart --}}
                @include('organization.pos.partials.checkout-products')

                {{-- Loader --}}
                @include('organization.pos.partials.loader')
            </div>

            {{-- Right: Customer, Payment & Total --}}
            <div class="space-y-4">
                {{-- Customer Section --}}
                @include('organization.pos.partials.checkout-customer')

                {{-- Payment Section --}}
                <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                            Payment
                        </h2>
                    </div>

                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Payment Method</label>
                            <select x-model="paymentMethod"
                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                            </select>
                        </div>

                        {{-- Cash Payment Fields --}}
                        <template x-if="paymentMethod === 'cash'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Amount Paid</label>
                                    <input type="number" placeholder="0.00" x-model.number="paidAmount"
                                        @input="calculateChange" step="0.01"
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>

                                <div class="flex justify-between items-center p-3 rounded-lg"
                                    :class="changeAmount < 0 ? 'bg-red-50 border border-red-200' :
                                        'bg-green-50 border border-green-200'">
                                    <span class="text-sm font-semibold text-gray-700">Change</span>
                                    <span class="text-lg font-bold"
                                        :class="changeAmount < 0 ? 'text-red-600' : 'text-green-600'">
                                        $<span x-text="Math.abs(changeAmount).toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        {{-- Card Payment Fields --}}
                        <template x-if="paymentMethod === 'card'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Card Type</label>
                                    <select x-model="cardType"
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                        <option value="credit">Credit Card</option>
                                        <option value="debit">Debit Card</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Last 4 Digits
                                        (Optional)</label>
                                    <input type="text" placeholder="1234" maxlength="4" x-model="cardLast4"
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Transaction ID
                                        (Optional)</label>
                                    <input type="text" placeholder="TXN123456" x-model="transactionId"
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Total Section --}}
                <div
                    class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-5 border-2 border-green-400">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-white">Total Amount</span>
                        <span class="text-4xl font-bold text-white drop-shadow-lg">$<span
                                x-text="total.toFixed(2)"></span></span>
                    </div>
                </div>

                {{-- Complete Sale Button --}}
                <button @click="submitSale"
                    :disabled="cart.length === 0 || (paymentMethod === 'cash' && changeAmount < 0)"
                    class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg text-base transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    <span x-show="cart.length === 0">Add Products to Cart</span>
                    <span x-show="cart.length > 0 && paymentMethod === 'cash' && changeAmount < 0">Insufficient
                        Payment</span>
                    <span x-show="cart.length > 0 && (paymentMethod !== 'cash' || changeAmount >= 0)">Complete Sale •
                        $<span x-text="total.toFixed(2)"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function pos() {
        return {
            roleId: parseInt(document.querySelector('[x-data="pos()"]')?.dataset?.roleId || '999'),
            userLocationId: document.querySelector('[x-data="pos()"]')?.dataset?.userLocationId || '',
            locations: [],
            searchQuery: "",
            cart: [],
            filteredCart: [],
            total: 0,
            selectedLocation: "",

            // Customer search properties
            customerInput: "",
            customerExists: false,
            customerName: "",
            customerId: null,
            customerEmail: "",
            customerPhone: "",
            isSearchingCustomer: false,
            hasSearched: false,
            customerSearchResults: [],
            showDropdown: false,

            // Payment properties
            paymentMethod: "cash",
            paidAmount: 0,
            changeAmount: 0,
            cardType: "credit",
            cardLast4: "",
            transactionId: "",
            isLoading: false,

            init() {
                const container = document.querySelector('[x-data="pos()"]');
                this.roleId = parseInt(container?.dataset?.roleId || '999');

                if (container?.dataset?.locations) {
                    try {
                        this.locations = JSON.parse(container.dataset.locations);
                    } catch (error) {
                        console.error('Error parsing locations:', error);
                        this.locations = [];
                    }
                }

                this.selectedLocation = localStorage.getItem('pos_selected_location') || '';

                window.addEventListener('checkout-page-updated', (e) => {
                    const {
                        locationId
                    } = e.detail;
                    this.selectedLocation = locationId;
                    this.loadCartFromCheckout();
                });

                window.addEventListener('customer-added', (e) => {
                    if (e.detail && e.detail.customer) {
                        this.selectCustomerFromEvent(e.detail.customer);
                    }
                });

                this.loadCartFromCheckout();
            },

            locationChanged() {
                this.isLoading = true;
                localStorage.setItem('pos_selected_location', this.selectedLocation);
                this.loadCartFromCheckout();
            },

            loadCartFromCheckout() {
                if (!this.selectedLocation) return;

                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') ||
                    '{}');
                const locationItems = allLocationCheckouts[this.selectedLocation] || [];

                this.cart = locationItems.map(item => ({
                    id: item.id,
                    product_id: item.product_id,
                    product_name: item.product_name,
                    sku: item.product_code || item.sku,
                    batch_number: item.batch_number,
                    expiry_date: item.expiry_date,
                    price: item.price,
                    on_hand_quantity: item.on_hand_quantity || 0,
                    qty: item.quantity,
                    location_id: item.location_id
                }));

                this.filterCart();
                this.updateTotals();
                window.dispatchEvent(new CustomEvent('checkout-updated'));
                this.isLoading = false;
            },

            filterCart() {
                if (!this.searchQuery) {
                    this.filteredCart = this.cart.map((item, index) => ({
                        ...item,
                        originalIndex: index
                    }));
                } else {
                    const query = this.searchQuery.toLowerCase();
                    this.filteredCart = this.cart
                        .map((item, index) => ({
                            ...item,
                            originalIndex: index
                        }))
                        .filter(item =>
                            item.product_name.toLowerCase().includes(query) ||
                            (item.sku && item.sku.toLowerCase().includes(query)) ||
                            (item.batch_number && item.batch_number.toLowerCase().includes(query))
                        );
                }
            },

            incrementQty(index) {
                if (this.cart[index].qty < this.cart[index].on_hand_quantity) {
                    this.cart[index].qty++;
                    this.updateCheckoutStorage();
                    this.filterCart();
                    this.updateTotals();
                }
            },

            decrementQty(index) {
                if (this.cart[index].qty > 1) {
                    this.cart[index].qty--;
                    this.updateCheckoutStorage();
                    this.filterCart();
                    this.updateTotals();
                }
            },

            updateQty(index, value) {
                const qty = parseInt(value) || 1;
                const item = this.cart[index];

                if (qty < 1) {
                    item.qty = 1;
                } else if (qty > item.on_hand_quantity) {
                    item.qty = item.on_hand_quantity;
                    alert(`Maximum available quantity is ${item.on_hand_quantity}`);
                } else {
                    item.qty = qty;
                }

                this.updateCheckoutStorage();
                this.filterCart();
                this.updateTotals();
            },

            removeItem(index) {
                this.cart.splice(index, 1);
                this.updateCheckoutStorage();
                this.filterCart();
                this.updateTotals();
            },

            updateCheckoutStorage() {
                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') ||
                    '{}');

                const checkoutItems = this.cart.map(item => ({
                    id: item.id,
                    product_id: item.product_id,
                    product_name: item.product_name,
                    product_code: item.sku,
                    price: item.price,
                    quantity: item.qty,
                    batch_number: item.batch_number,
                    expiry_date: item.expiry_date,
                    location_id: item.location_id,
                    on_hand_quantity: item.on_hand_quantity
                }));

                allLocationCheckouts[this.selectedLocation] = checkoutItems;
                localStorage.setItem('pos_checkout_items_by_location', JSON.stringify(allLocationCheckouts));
                localStorage.setItem('pos_checkout_items', JSON.stringify(checkoutItems));
                window.dispatchEvent(new CustomEvent('checkout-updated'));
            },

            updateTotals() {
                this.total = this.cart.reduce((sum, item) => sum + (item.qty * item.price), 0);
                this.calculateChange();
            },

            calculateChange() {
                if (this.paymentMethod === 'cash') {
                    this.changeAmount = this.paidAmount - this.total;
                } else {
                    this.changeAmount = 0;
                }
            },

            // Customer Search Function
            async searchCustomer() {
                if (!this.customerInput || this.customerInput.trim().length < 3) {
                    this.customerSearchResults = [];
                    this.showDropdown = false;
                    this.customerExists = false;
                    this.customerName = "";
                    this.customerId = null;
                    this.customerEmail = "";
                    this.customerPhone = "";
                    this.hasSearched = false;
                    return;
                }

                this.isSearchingCustomer = true;
                this.hasSearched = false;
                this.showDropdown = false;

                try {
                    const response = await fetch(
                        `/pos/customers/search?search=${encodeURIComponent(this.customerInput.trim())}`
                    );
                    const data = await response.json();

                    console.log('Customer search results:', data);

                    if (data.customers && data.customers.length > 0) {
                        this.customerSearchResults = data.customers;
                        this.showDropdown = true;
                        this.customerExists = false;
                    } else {
                        this.customerSearchResults = [];
                        this.showDropdown = false;
                        this.customerExists = false;
                    }

                    this.hasSearched = true;
                } catch (error) {
                    console.error('Error searching customer:', error);
                    this.customerSearchResults = [];
                    this.showDropdown = false;
                    this.customerExists = false;
                    this.hasSearched = true;
                } finally {
                    this.isSearchingCustomer = false;
                }
            },

            // Select Customer from Dropdown
            selectCustomer(customer) {
                this.customerExists = true;
                this.customerId = customer.id;
                this.customerName = customer.name;
                this.customerEmail = customer.email || '';
                this.customerPhone = customer.phone || '';
                this.customerInput = customer.phone || customer.email || customer.name;
                this.showDropdown = false;
                this.customerSearchResults = [];
            },

            // Select Customer from Event (modal)
            selectCustomerFromEvent(customer) {
                this.customerExists = true;
                this.customerId = customer.id;
                this.customerName = customer.name;
                this.customerEmail = customer.email || '';
                this.customerPhone = customer.phone || '';
                this.customerInput = customer.phone || customer.email || customer.name;
                this.hasSearched = true;
                this.showDropdown = false;
                this.customerSearchResults = [];
            },

            // Clear Customer Selection
            clearCustomer() {
                this.customerInput = "";
                this.customerExists = false;
                this.customerName = "";
                this.customerId = null;
                this.customerEmail = "";
                this.customerPhone = "";
                this.hasSearched = false;
                this.customerSearchResults = [];
                this.showDropdown = false;
            },

            async submitSale() {
                if (this.cart.length === 0) {
                    alert('Cart is empty. Please add products to continue.');
                    return;
                }

                if (this.paymentMethod === 'cash' && this.changeAmount < 0) {
                    alert('Insufficient payment amount. Please enter the correct amount.');
                    return;
                }

                const saleData = {
                    location_id: this.selectedLocation,
                    items: this.cart.map(item => ({
                        product_id: item.product_id,
                        batch_id: item.id,
                        quantity: item.qty,
                        price: item.price,
                        on_hand_quantity: item.on_hand_quantity,
                        subtotal: item.qty * item.price
                    })),
                    total_amount: this.total,
                    payment_method: this.paymentMethod,
                    customer_id: this.customerId,
                };

                if (this.paymentMethod === 'cash') {
                    saleData.paid_amount = this.paidAmount;
                    saleData.change_amount = this.changeAmount;
                } else if (this.paymentMethod === 'card') {
                    saleData.card_type = this.cardType;
                    saleData.card_last4 = this.cardLast4;
                    saleData.transaction_id = this.transactionId;
                }

                try {
                    const submitButton = event.target;
                    const originalText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="animate-pulse">Processing...</span>';

                    const response = await fetch('/pos/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.content || ''
                        },
                        body: JSON.stringify(saleData)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        alert(`Sale completed successfully! Sale ID: ${result.sale_id || 'N/A'}`);

                        this.cart = [];
                        this.filteredCart = [];
                        this.total = 0;
                        this.paidAmount = 0;
                        this.changeAmount = 0;
                        this.clearCustomer();
                        this.cardLast4 = "";
                        this.transactionId = "";
                        this.searchQuery = "";

                        this.updateCheckoutStorage();

                        if (result.receipt_url) {
                            window.open(result.receipt_url, '_blank');
                        }
                    } else {
                        alert(result.message || 'Failed to complete sale. Please try again.');
                    }

                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;

                } catch (error) {
                    console.error('Error submitting sale:', error);
                    alert('An error occurred while processing the sale. Please try again.');

                    const submitButton = event.target;
                    submitButton.disabled = false;
                }
            }
        };
    }
</script>
