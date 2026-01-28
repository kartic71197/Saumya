<div x-data="inventoryManager()" data-locations='@json($locations)' data-role-id="{{ auth()->user()->role_id }}"
    data-user-location="{{ auth()->user()->location_id ?? '' }}" x-init="init()" class="space-y-4">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-20" x-data="checkoutCounter()">
        <div class="px-6 py-3">
            <div class="flex items-center justify-between">
                <!-- Left: Title -->
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="text-lg font-bold text-gray-900 leading-tight">
                            Point of Sale
                        </h1>
                    </div>
                </div>
                <!-- Right: Date & Time -->
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-700"
                            x-text="new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })">
                        </div>
                        <div class="text-xs text-gray-500"
                            x-text="new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </nav>

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
        <button @click="refreshInventory()" title="Refresh Inventory"
            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>

    <!-- Product Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <template x-for="item in paginatedProducts" :key="item.id">
            <div @click="toggleCartItem(item)" :disabled="item.on_hand_quantity <= 0"
                class="bg-white rounded-xl shadow-sm border-2 transition-all cursor-pointer hover:shadow-md"
                :class="isInCart(item) ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-blue-300'"
                style="height: 180px; position: relative;">

                <!-- Out of Stock Overlay -->
                <div x-show="item.on_hand_quantity <= 0"
                    class="absolute inset-0 bg-gray-900 bg-opacity-50 rounded-xl flex items-center justify-center z-10">
                    <span class="text-white font-bold text-sm">OUT OF STOCK</span>
                </div>

                <!-- Card Content -->
                <div class="p-2 h-full flex flex-col">
                    <!-- Product Icon & Status Badge -->
                    <div class="flex items-start justify-between mb-1">
                        <div>

                        </div>
                        {{-- <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div> --}}
                        <span class="px-2 py-0.5 text-[8px] font-bold rounded-full" :class="getStatusClass(item)"
                            x-text="getStatusText(item)"></span>
                    </div>

                    <!-- Product Name -->
                    <div class="mb-1 flex-grow">
                        <h3 class="text-xs font-semibold text-gray-900 line-clamp-2" :title="item.product_name"
                            x-text="item.product_name"></h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="item.sku"></p>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Stock:</span>
                            <span class="font-bold text-gray-900" x-text="item.on_hand_quantity"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Price:</span>
                            <span class="font-bold text-green-700">$<span x-text="item.price"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Batch:</span>
                            <span class="font-medium text-gray-700 truncate max-w-[80px]"
                                :title="item.batch_number || 'N/A'" x-text="item.batch_number || 'N/A'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Expiry:</span>
                            <span class="font-medium text-gray-700 truncate max-w-[80px]"
                                :title="item.expiry_date || 'N/A'" x-text="item.expiry_date || 'N/A'"></span>
                        </div>
                    </div>

                    <!-- Added to Cart Indicator -->
                    <div x-show="isInCart(item)" class="absolute bottom-2 right-2 bg-green-500 rounded-full p-1">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Pagination -->
    <div x-show="filteredProducts.length > perPage" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
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
    <div x-show="filteredProducts.length === 0 && !isLoading"
        class="bg-white rounded-xl shadow-sm border border-gray-200 text-center py-12">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
        <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filter criteria</p>
    </div>

    <!-- Loader -->
    <div x-show="isLoading" class="bg-white rounded-xl shadow-sm border border-gray-200 text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent">
        </div>
        <p class="mt-4 text-sm text-gray-600">Loading inventory...</p>
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

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

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
            perPage: 20,
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
                        detail: {
                            locationId: this.selectedLocationId
                        }
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
                    detail: {
                        locationId: this.selectedLocationId
                    }
                }));
            },

            isInCart(item) {
                return this.cart.some(i =>
                    i.id === item.id && i.location_id == this.selectedLocationId
                );
            },

            toggleCartItem(item) {
                if (item.on_hand_quantity <= 0) return;

                const existingIndex = this.cart.findIndex(i =>
                    i.id === item.id && i.location_id == this.selectedLocationId
                );

                const newCart = [...this.cart];

                if (existingIndex >= 0) {
                    // Remove from cart
                    newCart.splice(existingIndex, 1);
                    this.showToastMessage(`${item.product_name} removed from cart`);
                } else {
                    // Add to cart with qty 1
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
                    this.showToastMessage(`${item.product_name} added to cart`);
                }

                this.cart = newCart;

                window.dispatchEvent(new CustomEvent('cart-updated', {
                    detail: {
                        cart: this.cart
                    }
                }));
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
                                return this.isExpiringSoon(item.expiry_date) && !this.isExpired(item
                                    .expiry_date);
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
                this.stats.expiringSoon = productsForStats.filter(p => this.isExpiringSoon(p.expiry_date) && !this
                    .isExpired(p.expiry_date)).length;
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
