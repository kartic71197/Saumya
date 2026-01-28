<div x-data="pos()" data-role-id="{{ auth()->user()->role_id }}" data-locations='@json($locations)'
    class="flex flex-col">

    <div x-show="selectedLocation" x-transition.duration.300ms class="flex-1 flex flex-col space-y-4">

        <!-- Customer Section (Moved to Top) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex-shrink-0">
            <h3 class="text-sm font-bold text-gray-700 mb-3">👤 Customer</h3>

            <div class="relative" x-data="{ focused: false }">
                <input type="text" x-model="customerInput" @input="searchCustomer()"
                    @focus="focused = true; searchCustomer()" @blur="setTimeout(() => focused = false, 200)"
                    placeholder="Search by name, phone, or email..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                <!-- Customer Search Dropdown -->
                <div x-show="showDropdown && focused"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="customer in customerSearchResults" :key="customer.id">
                        <div @click="selectCustomer(customer)"
                            class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <p class="text-sm font-semibold text-gray-900" x-text="customer.name"></p>
                            <p class="text-xs text-gray-500" x-text="customer.phone || customer.email"></p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Selected Customer -->
            <div x-show="customerExists" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-green-900" x-text="customerName"></p>
                        <p class="text-xs text-green-700" x-text="customerPhone || customerEmail"></p>
                    </div>
                    <button @click="clearCustomer()" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Cart Header with Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    🛒 Cart
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full"
                        x-text="locationCart.length"></span>
                </h2>
                <button @click="clearCart()" x-show="locationCart.length > 0"
                    class="text-xs text-red-600 hover:text-red-800 font-semibold hover:underline">
                    Clear All
                </button>
            </div>

            <!-- Search in Cart -->
            <div class="relative" x-show="locationCart.length > 0">
                <input type="text" x-model="searchQuery" @input="filterCart()" placeholder="Search in cart..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Cart Items - Fixed Height with Scroll -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 max-h-[500px]">
            <template x-if="locationCart.length === 0">
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-500 font-medium">Your cart is empty</p>
                    <p class="text-xs text-gray-400 mt-1">Add products from inventory</p>
                </div>
            </template>

            <template x-for="(item, index) in filteredCart" :key="index">
                <div
                    class="bg-gradient-to-r from-gray-50 to-white rounded-lg p-3 border border-gray-200 hover:border-blue-300 transition-all shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-gray-900" x-text="item.product_name"></h3>
                            <p class="text-xs text-gray-500" x-text="item.sku"></p>
                            <p class="text-xs text-gray-400">Batch: <span x-text="item.batch_number || 'N/A'"></span>
                            </p>
                        </div>
                        <button @click="removeItem(item.originalIndex)" class="text-red-500 hover:text-red-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button @click="decrementQty(item.originalIndex)"
                                class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M20 12H4" />
                                </svg>
                            </button>

                            <input type="number" :value="item.qty"
                                @input="updateQty(item.originalIndex, $event.target.value)"
                                class="w-14 text-center border border-gray-300 rounded-lg py-1 text-sm font-bold focus:ring-2 focus:ring-blue-500">

                            <button @click="incrementQty(item.originalIndex)"
                                :disabled="item.qty >= item.on_hand_quantity"
                                class="w-7 h-7 bg-green-500 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>

                            <span class="text-xs text-gray-500 ml-1">/ <span
                                    x-text="item.on_hand_quantity"></span></span>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-gray-500">@ $<span x-text="item.price"></span></p>
                            <p class="text-sm font-bold text-green-700">$<span
                                    x-text="(item.qty * item.price).toFixed(2)"></span></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Payment Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex-shrink-0">
            <h3 class="text-sm font-bold text-gray-700 mb-3">💳 Payment</h3>

            <div class="grid grid-cols-2 gap-2 mb-3">
                <button @click="paymentMethod = 'cash'"
                    :class="paymentMethod === 'cash' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors">
                    Cash
                </button>
                <button @click="paymentMethod = 'card'"
                    :class="paymentMethod === 'card' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors">
                    Card
                </button>
            </div>

            <!-- Cash Payment -->
            <div x-show="paymentMethod === 'cash'" class="space-y-2">
                <input type="number" x-model.number="paidAmount" @input="calculateChange()"
                    placeholder="Amount paid"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                <div x-show="paidAmount > 0" class="p-3 rounded-lg"
                    :class="changeAmount >= 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                    <p class="text-xs font-semibold mb-1"
                        :class="changeAmount >= 0 ? 'text-green-700' : 'text-red-700'">
                        Change:
                    </p>
                    <p class="text-2xl font-bold" :class="changeAmount >= 0 ? 'text-green-900' : 'text-red-900'">
                        $<span x-text="Math.abs(changeAmount).toFixed(2)"></span>
                    </p>
                </div>
            </div>

            <!-- Card Payment -->
            <div x-show="paymentMethod === 'card'" class="space-y-2">
                <select x-model="cardType" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="credit">Credit Card</option>
                    <option value="debit">Debit Card</option>
                </select>
                <input type="text" x-model="cardLast4" placeholder="Last 4 digits"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" maxlength="4">
                <input type="text" x-model="transactionId" placeholder="Transaction ID"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <!-- Total -->
        <div class="bg-white rounded-lg border border-gray-300 p-4 flex-shrink-0">
            <div class="flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    Total Amount
                </span>

                <span class="text-2xl font-bold text-gray-900">
                    $<span x-text="total.toFixed(2)"></span>
                </span>
            </div>
        </div>


        <!-- Complete Sale Button -->
        <button @click="submitSale"
            :disabled="locationCart.length === 0 || (paymentMethod === 'cash' && changeAmount < 0)"
            class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg text-base transition-all transform hover:scale-105 active:scale-95 flex-shrink-0">
            <span x-show="locationCart.length === 0">Complete Sales</span>
            <span x-show="locationCart.length > 0 && paymentMethod === 'cash' && changeAmount < 0">
                Insufficient Payment
            </span>
            <span x-show="locationCart.length > 0 && (paymentMethod !== 'cash' || changeAmount >= 0)">
                Complete Sale • $<span x-text="total.toFixed(2)"></span>
            </span>
        </button>
    </div>

    <!-- Loader -->
    <div x-show="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 shadow-2xl">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mx-auto">
            </div>
            <p class="mt-4 text-sm text-gray-600 font-medium">Processing...</p>
        </div>
    </div>
</div>

<script>
    function pos() {
        return {
            roleId: parseInt(document.querySelector('[x-data="pos()"]')?.dataset?.roleId || '999'),
            locations: [],
            searchQuery: "",
            cart: [],
            locationCart: [],
            filteredCart: [],
            total: 0,
            selectedLocation: "",

            // Customer properties
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

                // Listen for cart updates from inventory
                window.addEventListener('cart-updated', (e) => {
                    this.cart = e.detail.cart || [];
                    this.updateLocationCart();
                });

                // Listen for location changes from inventory
                window.addEventListener('location-changed', (e) => {
                    this.selectedLocation = e.detail.locationId;
                    this.updateLocationCart();
                });
            },

            updateLocationCart() {
                // Filter cart to only show items for selected location
                this.locationCart = this.cart.filter(item => item.location_id == this.selectedLocation);
                this.filterCart();
                this.updateTotals();
            },

            filterCart() {
                if (!this.searchQuery) {
                    this.filteredCart = this.locationCart.map((item, index) => ({
                        ...item,
                        originalIndex: this.cart.findIndex(c => c.id === item.id && c.location_id == item
                            .location_id)
                    }));
                } else {
                    const query = this.searchQuery.toLowerCase();
                    this.filteredCart = this.locationCart
                        .map((item, index) => ({
                            ...item,
                            originalIndex: this.cart.findIndex(c => c.id === item.id && c.location_id == item
                                .location_id)
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
                    this.updateLocationCart();
                    this.notifyInventory();
                }
            },

            decrementQty(index) {
                if (this.cart[index].qty > 1) {
                    this.cart[index].qty--;
                    this.updateLocationCart();
                    this.notifyInventory();
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

                this.updateLocationCart();
                this.notifyInventory();
            },

            removeItem(index) {
                this.cart.splice(index, 1);
                this.updateLocationCart();
                this.notifyInventory();
            },

            clearCart() {
                if (confirm('Are you sure you want to clear the cart?')) {
                    // Remove only items from selected location
                    this.cart = this.cart.filter(item => item.location_id != this.selectedLocation);
                    this.updateLocationCart();
                    this.notifyInventory();
                }
            },

            notifyInventory() {
                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: {
                        cart: this.cart
                    }
                }));
            },

            updateTotals() {
                this.total = this.locationCart.reduce((sum, item) => sum + (item.qty * item.price), 0);
                this.calculateChange();
            },

            calculateChange() {
                if (this.paymentMethod === 'cash') {
                    this.changeAmount = this.paidAmount - this.total;
                } else {
                    this.changeAmount = 0;
                }
            },

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
                if (this.locationCart.length === 0) {
                    alert('Cart is empty. Please add products to continue.');
                    return;
                }

                if (this.paymentMethod === 'cash' && this.changeAmount < 0) {
                    alert('Insufficient payment amount. Please enter the correct amount.');
                    return;
                }

                const saleData = {
                    location_id: this.selectedLocation,
                    items: this.locationCart.map(item => ({
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
                    this.isLoading = true;

                    const response = await fetch('/pos/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify(saleData)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        alert(`Sale completed successfully! Sale ID: ${result.sale_id || 'N/A'}`);

                        // Remove only sold items from cart
                        this.cart = this.cart.filter(item => item.location_id != this.selectedLocation);
                        this.updateLocationCart();

                        this.paidAmount = 0;
                        this.changeAmount = 0;
                        this.clearCustomer();
                        this.cardLast4 = "";
                        this.transactionId = "";
                        this.searchQuery = "";

                        this.notifyInventory();

                        if (result.receipt_url) {
                            window.open(result.receipt_url, '_blank');
                        }
                    } else {
                        alert(result.message || 'Failed to complete sale. Please try again.');
                    }
                } catch (error) {
                    console.error('Error submitting sale:', error);
                    alert('An error occurred while processing the sale. Please try again.');
                } finally {
                    this.isLoading = false;
                }
            }
        };
    }
</script>
