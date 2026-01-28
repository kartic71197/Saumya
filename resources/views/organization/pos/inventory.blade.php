<div x-data="inventoryManager()" 
    data-locations='@json($locations)' 
    data-role-id="{{ auth()->user()->role_id }}"
    data-user-location="{{ auth()->user()->location_id ?? '' }}"
    x-init="init()" 
    class="space-y-4">

    <!-- Search and Filter Bar -->
    <div class="flex flex-col md:flex-row gap-3">
        <!-- Search -->
        <div class="flex-1">
            <div class="relative">
                <input type="text" x-model="searchQuery" @input="filterProducts()"
                    placeholder="Search products, SKU, or batch..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Location Filter -->
        <select x-model="selectedLocationId" @change="onLocationChange()"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            <template x-for="location in locations" :key="location.id">
                <option :value="location.id" x-text="location.name"></option>
            </template>
        </select>

        <!-- Status Filter -->
        <select x-model="filterStatus" @change="filterProducts()"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            <option value="all">All Status</option>
            <option value="low">Low Stock</option>
            <option value="expiring">Expiring Soon</option>
            <option value="expired">Expired</option>
        </select>

        <!-- Refresh Button -->
        <button @click="refreshInventory()"
            title="Refresh Inventory"
            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>

    <!-- Inventory Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
            <div class="text-xs text-gray-500 font-medium">Total Products</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5" x-text="stats.total"></div>
        </div>
        <div class="bg-orange-50 rounded-lg p-3 border border-orange-200 shadow-sm">
            <div class="text-xs text-orange-700 font-medium">Low Stock</div>
            <div class="text-xl font-bold text-orange-900 mt-0.5" x-text="stats.lowStock"></div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200 shadow-sm">
            <div class="text-xs text-yellow-700 font-medium">Expiring Soon</div>
            <div class="text-xl font-bold text-yellow-900 mt-0.5" x-text="stats.expiringSoon"></div>
        </div>
        <div class="bg-red-50 rounded-lg p-3 border border-red-200 shadow-sm">
            <div class="text-xs text-red-700 font-medium">Expired</div>
            <div class="text-xl font-bold text-red-900 mt-0.5" x-text="stats.expired"></div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-200">
            <template x-for="item in paginatedProducts" :key="item.id">
                <div class="p-3 hover:bg-blue-50 transition-colors">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-gray-900 truncate" x-text="item.product_name"></div>
                                <div class="text-xs text-gray-500" x-text="item.sku"></div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full flex-shrink-0" :class="getStatusClass(item)"
                            x-text="getStatusText(item)"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs mb-2">
                        <div>
                            <span class="text-gray-500">Batch:</span>
                            <span class="font-medium text-gray-900 ml-1" x-text="item.batch_number || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Stock:</span>
                            <span class="font-bold text-gray-900 ml-1" x-text="item.on_hand_quantity"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Expiry:</span>
                            <span class="font-medium ml-1 text-xs" :class="getExpiryClass(item.expiry_date)"
                                x-text="formatDate(item.expiry_date)"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Price:</span>
                            <span class="font-bold text-green-700 ml-1">$<span x-text="item.price"></span></span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div x-show="getItemQuantity(item) === 0">
                            <button @click="incrementQuantity(item)" :disabled="item.on_hand_quantity <= 0"
                                class="w-9 h-9 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-md disabled:opacity-50">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <div x-show="getItemQuantity(item) > 0" class="flex items-center gap-1.5">
                            <button @click="decrementQuantity(item)"
                                class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                </svg>
                            </button>
                            <div class="w-12 h-8 bg-blue-50 rounded-lg flex items-center justify-center border border-blue-200">
                                <span class="text-sm font-bold text-blue-900" x-text="getItemQuantity(item)"></span>
                            </div>
                            <button @click="incrementQuantity(item)"
                                :disabled="getItemQuantity(item) >= item.on_hand_quantity"
                                class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center disabled:opacity-50">
                                <svg class="w-3.5 h-3.5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div x-show="filteredProducts.length > perPage" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span> 
                    to <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredProducts.length)"></span> 
                    of <span class="font-medium" x-text="filteredProducts.length"></span> products
                </div>
                <div class="flex gap-2">
                    <button @click="currentPage--" :disabled="currentPage === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button @click="currentPage++" :disabled="currentPage >= totalPages"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="filteredProducts.length === 0 && !isLoading" class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
            <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filter criteria</p>
        </div>

        <!-- Loader -->
        <div x-show="isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
            <p class="mt-4 text-sm text-gray-600">Loading inventory...</p>
        </div>
    </div>

    <!-- Success Toast -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 transform translate-y-2 scale-95"
        class="fixed bottom-4 right-4 bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-50 border border-green-500">
        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <span class="font-medium" x-text="toastMessage"></span>
    </div>
</div>

<script>
    function inventoryManager() {
        return {
            products: [],
            filteredProducts: [],
            paginatedProducts: [],
            searchQuery: '',
            filterStatus: 'all',
            showToast: false,
            toastMessage: '',
            cart: [],
            userRoleId: null,
            selectedLocationId: '',
            locations: [],
            isLoading: false,
            currentPage: 1,
            perPage: 10,
            totalPages: 1,
            stats: {
                total: 0,
                lowStock: 0,
                expiringSoon: 0,
                expired: 0
            },

            init() {
                const container = document.querySelector('[x-data="inventoryManager()"]');
                this.userRoleId = parseInt(container?.dataset?.roleId || '999');
                
                // Get user's location or first location
                const userLocation = container?.dataset?.userLocation;

                if (container?.dataset?.locations) {
                    try {
                        this.locations = JSON.parse(container.dataset.locations);
                        
                        // Set default location
                        if (userLocation && userLocation !== '') {
                            // this.selectedLocationId = userLocation;
                            this.selectedLocationId = this.locations[0].id;
                        } else if (this.locations.length > 0) {
                            this.selectedLocationId = this.locations[0].id;
                        }
                    } catch (error) {
                        console.error('Error parsing locations:', error);
                        this.locations = [];
                    }
                }

                this.fetchInventory();

                // Listen for cart updates from checkout
                window.addEventListener('cart-updated', (e) => {
                    this.cart = e.detail.cart || [];
                });

                // Watch for page changes
                this.$watch('currentPage', () => {
                    this.updatePagination();
                });
            },

            async fetchInventory() {
                this.isLoading = true;
                try {
                    const response = await fetch('pos/inventory/stock-counts');
                    const data = await response.json();

                    this.products = data.map(item => ({
                        id: item.id,
                        product_id: item.product_id,
                        product_name: item.product_name || 'Unknown Product',
                        sku: item.product_code || 'N/A',
                        batch_number: item.batch_number,
                        expiry_date: item.expiry_date,
                        on_hand_quantity: item.on_hand_quantity,
                        alert_quantity: item.alert_quantity,
                        par_quantity: item.par_quantity,
                        location_id: item.location_id,
                        price: item.price || 0
                    }));

                    console.log(this.products);

                    this.filterProducts();
                    this.calculateStats();
                    
                    // Notify checkout about location
                    window.dispatchEvent(new CustomEvent('location-changed', {
                        detail: { locationId: this.selectedLocationId }
                    }));
                } catch (error) {
                    console.error('Error fetching inventory:', error);
                    this.showToastMessage('Failed to load inventory');
                } finally {
                    this.isLoading = false;
                }
            },

            onLocationChange() {
                this.filterProducts();
                this.calculateStats();
                this.showToastMessage('Location changed');
                
                // Notify checkout about location change
                window.dispatchEvent(new CustomEvent('location-changed', {
                    detail: { locationId: this.selectedLocationId }
                }));
            },

            getItemQuantity(item) {
                const cartItem = this.cart.find(i =>
                    i.id === item.id && i.location_id == this.selectedLocationId
                );
                return cartItem ? cartItem.qty : 0;
            },

            incrementQuantity(item) {
                if (item.on_hand_quantity <= 0) return;

                const currentQty = this.getItemQuantity(item);
                if (currentQty >= item.on_hand_quantity) return;

                const newCart = [...this.cart];
                const existingIndex = newCart.findIndex(i =>
                    i.id === item.id && i.location_id == this.selectedLocationId
                );

                if (existingIndex >= 0) {
                    newCart[existingIndex].qty += 1;
                } else {
                    newCart.push({
                        id: item.id,
                        product_id: item.product_id,
                        product_name: item.product_name,
                        sku: item.sku,
                        price: item.price,
                        on_hand_quantity: item.on_hand_quantity,
                        qty: 1,
                        batch_number: item.batch_number,
                        expiry_date: item.expiry_date,
                        location_id: this.selectedLocationId
                    });
                }

                this.cart = newCart;
                
                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: { cart: this.cart }
                }));

                this.showToastMessage(`${item.product_name} added to cart`);
            },

            decrementQuantity(item) {
                const currentQty = this.getItemQuantity(item);
                if (currentQty <= 0) return;

                const newCart = [...this.cart];
                const existingIndex = newCart.findIndex(i =>
                    i.id === item.id && i.location_id == this.selectedLocationId
                );

                if (existingIndex >= 0) {
                    if (newCart[existingIndex].qty > 1) {
                        newCart[existingIndex].qty -= 1;
                    } else {
                        newCart.splice(existingIndex, 1);
                    }
                }

                this.cart = newCart;
                
                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: { cart: this.cart }
                }));

                this.showToastMessage(`${item.product_name} removed from cart`);
            },

            filterProducts() {
                let filtered = [...this.products];

                // Always filter by selected location
                filtered = filtered.filter(item => item.location_id == this.selectedLocationId);

                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    filtered = filtered.filter(item =>
                        item.product_name.toLowerCase().includes(query) ||
                        (item.sku && item.sku.toLowerCase().includes(query)) ||
                        (item.batch_number && item.batch_number.toLowerCase().includes(query))
                    );
                }

                if (this.filterStatus !== 'all') {
                    filtered = filtered.filter(item => {
                        switch (this.filterStatus) {
                            case 'low':
                                return item.on_hand_quantity <= item.alert_quantity;
                            case 'expired':
                                return this.isExpired(item.expiry_date);
                            case 'expiring':
                                return this.isExpiringSoon(item.expiry_date) && !this.isExpired(item.expiry_date);
                            default:
                                return true;
                        }
                    });
                }

                this.filteredProducts = filtered;
                this.currentPage = 1;
                this.updatePagination();
            },

            updatePagination() {
                this.totalPages = Math.ceil(this.filteredProducts.length / this.perPage);
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                this.paginatedProducts = this.filteredProducts.slice(start, end);
            },

            calculateStats() {
                const productsForStats = this.products.filter(p => p.location_id == this.selectedLocationId);

                this.stats.total = productsForStats.length;
                this.stats.lowStock = productsForStats.filter(p => p.on_hand_quantity <= p.alert_quantity).length;
                this.stats.expiringSoon = productsForStats.filter(p => this.isExpiringSoon(p.expiry_date) && !this.isExpired(p.expiry_date)).length;
                this.stats.expired = productsForStats.filter(p => this.isExpired(p.expiry_date)).length;
            },

            refreshInventory() {
                this.fetchInventory();
                this.showToastMessage('Inventory refreshed');
            },

            isExpired(date) {
                if (!date) return false;
                return new Date(date) < new Date();
            },

            isExpiringSoon(date) {
                if (!date) return false;
                const expiryDate = new Date(date);
                const today = new Date();
                const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
                return daysUntilExpiry <= 30 && daysUntilExpiry > 0;
            },

            getExpiryClass(date) {
                if (!date) return 'text-gray-500';
                if (this.isExpired(date)) return 'text-red-600 font-semibold';
                if (this.isExpiringSoon(date)) return 'text-yellow-600 font-semibold';
                return 'text-gray-900';
            },

            getStatusClass(item) {
                if (this.isExpired(item.expiry_date)) {
                    return 'bg-red-100 text-red-800 border border-red-300';
                }
                if (item.on_hand_quantity <= item.alert_quantity) {
                    return 'bg-orange-100 text-orange-800 border border-orange-300';
                }
                if (this.isExpiringSoon(item.expiry_date)) {
                    return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                }
                return 'bg-green-100 text-green-800 border border-green-300';
            },

            getStatusText(item) {
                if (this.isExpired(item.expiry_date)) return 'Expired';
                if (item.on_hand_quantity <= item.alert_quantity) return 'Low Stock';
                if (this.isExpiringSoon(item.expiry_date)) return 'Expiring Soon';
                return 'In Stock';
            },

            formatDate(date) {
                if (!date) return 'N/A';
                return new Date(date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            },

            showToastMessage(message) {
                this.toastMessage = message;
                this.showToast = true;
                setTimeout(() => {
                    this.showToast = false;
                }, 3000);
            }
        }
    }
</script>