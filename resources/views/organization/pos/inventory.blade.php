<div x-data="inventoryManager()" data-locations='@json($locations)' data-role-id="{{ auth()->user()->role_id }}"
    x-init="init()" class="space-y-4">

    <!-- Search and Filter Bar -->
    @include('organization.pos.partials.search-filter')

    <!-- Inventory Stats -->
    @include('organization.pos.partials.inventory-stats')

    <!-- Inventory Table - Responsive -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Desktop View -->
        @include('organization.pos.partials.inventroy-table-desktop-view')

        <!-- Mobile View - Card Layout -->
        @include('organization.pos.partials.inventory-table-mobile-view')

        <!-- Empty State -->
        @include('organization.pos.partials.empty-table-stats')

        @include('organization.pos.partials.loader')
    </div>

    <!-- Quantity Modal -->
    {{-- @include('organization.pos.partials.add-inventory-modal') --}}

    <!-- Success Toast -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="toastMessage"></span>
    </div>

</div>

<script>
    function inventoryManager() {
        return {
            products: [],
            filteredProducts: [],
            searchQuery: '',
            filterStatus: 'all',
            showToast: false,
            toastMessage: '',
            checkoutItems: [],
            userRoleId: null,
            selectedLocationId: '',
            locations: [],
            isLoading: false,
            stats: {
                total: 0,
                lowStock: 0,
                expiringSoon: 0,
                expired: 0
            },

            init() {
                const container = document.querySelector('[x-data="inventoryManager()"]');
                this.userRoleId = parseInt(container?.dataset?.roleId || '999');

                if (container?.dataset?.locations) {
                    try {
                        this.locations = JSON.parse(container.dataset.locations);
                    } catch (error) {
                        console.error('Error parsing locations:', error);
                        this.locations = [];
                    }
                }

                this.selectedLocationId = localStorage.getItem('pos_selected_location') || '';

                this.loadFromCache();
                this.fetchInventory();
                this.loadCheckoutItems();

                window.addEventListener('checkout-updated', () => {
                    this.loadCheckoutItems();
                });
            },

            onLocationChange() {
                this.isLoading = true;
                localStorage.setItem('pos_selected_location', this.selectedLocationId);

                // Load checkout items for the new location
                this.loadCheckoutItems();

                // Refresh inventory and filter
                this.fetchInventory();
                this.showToastMessage('Location changed');
            },

            loadFromCache() {
                const cached = localStorage.getItem('pos_inventory');
                if (cached) {
                    this.products = JSON.parse(cached);
                    this.filterProducts();
                    this.calculateStats();
                }
            },

            loadCheckoutItems() {
                // Load all location-wise checkout items
                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') || '{}');

                // Get items for current location
                if (this.selectedLocationId) {
                    this.checkoutItems = allLocationCheckouts[this.selectedLocationId] || [];
                } else {
                    this.checkoutItems = [];
                }

            },

            saveCheckoutItems(items) {
                // Load all location-wise checkout items
                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') || '{}');

                // Update items for current location
                if (this.selectedLocationId) {
                    allLocationCheckouts[this.selectedLocationId] = items;
                }

                // Save back to localStorage
                localStorage.setItem('pos_checkout_items_by_location', JSON.stringify(allLocationCheckouts));

            },

            async fetchInventory() {
                this.isLoading = true;
                // console.log('fetch inventory');
                try {
                    const response = await fetch('pos/inventory/stock-counts');
                    const data = await response.json();
                    // console.log(data);

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

                    localStorage.setItem('pos_inventory', JSON.stringify(this.products));
                    this.filterProducts();
                    this.calculateStats();
                } catch (error) {
                    console.error('Error fetching inventory:', error);
                    this.showToastMessage('Failed to load inventory');
                } finally {
                    this.isLoading = false; // Hide loader
                }
                window.dispatchEvent(new CustomEvent('checkout-updated'));
            },

            getItemQuantity(item) {
                const checkoutItem = this.checkoutItems.find(i =>
                    i.id === item.id && i.location_id == item.location_id
                );
                return checkoutItem ? checkoutItem.quantity : 0;
            },

            incrementQuantity(item) {
                if (item.on_hand_quantity <= 0) return;

                const currentQty = this.getItemQuantity(item);
                if (currentQty >= item.on_hand_quantity) return;

                // Load current location's checkout items
                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') || '{}');
                let checkoutItems = allLocationCheckouts[this.selectedLocationId] || [];

                const existingIndex = checkoutItems.findIndex(i =>
                    i.id === item.id && i.location_id == item.location_id
                );

                // console.log('item on invenotry too add to checkout',item.price);

                if (existingIndex >= 0) {
                    checkoutItems[existingIndex].quantity += 1;
                } else {
                    checkoutItems.push({
                        id: item.id,
                        product_id: item.product_id,
                        product_name: item.product_name,
                        sku: item.sku,
                        price: item.price,
                        on_hand_quantity: item.on_hand_quantity,
                        quantity: 1,
                        batch_number: item.batch_number,
                        expiry_date: item.expiry_date,
                        location_id: item.location_id
                    });
                }

                this.saveCheckoutItems(checkoutItems);
                this.loadCheckoutItems();
                window.dispatchEvent(new CustomEvent('checkout-updated'));

                this.showToastMessage(`${item.product_name} added to checkout`);
            },

            decrementQuantity(item) {
                const currentQty = this.getItemQuantity(item);
                if (currentQty <= 0) return;

                // Load current location's checkout items
                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') || '{}');
                let checkoutItems = allLocationCheckouts[this.selectedLocationId] || [];

                const existingIndex = checkoutItems.findIndex(i =>
                    i.id === item.id && i.location_id == item.location_id
                );

                if (existingIndex >= 0) {
                    if (checkoutItems[existingIndex].quantity > 1) {
                        checkoutItems[existingIndex].quantity -= 1;
                    } else {
                        checkoutItems.splice(existingIndex, 1);
                    }
                }

                this.saveCheckoutItems(checkoutItems);
                this.loadCheckoutItems();
                window.dispatchEvent(new CustomEvent('checkout-updated'));

                this.showToastMessage(`${item.product_name} removed from checkout`);
            },

            filterProducts() {
                let filtered = [...this.products];

                if (this.selectedLocationId) {
                    filtered = filtered.filter(item => item.location_id == this.selectedLocationId);
                }

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
            },

            calculateStats() {
                let productsForStats = this.products;
                if (this.selectedLocationId) {
                    productsForStats = this.products.filter(p => p.location_id == this.selectedLocationId);
                }

                this.stats.total = productsForStats.length;
                this.stats.lowStock = productsForStats.filter(p => p.on_hand_quantity <= p.alert_quantity).length;
                this.stats.expiringSoon = productsForStats.filter(p => this.isExpiringSoon(p.expiry_date) && !this
                    .isExpired(p.expiry_date)).length;
                this.stats.expired = productsForStats.filter(p => this.isExpired(p.expiry_date)).length;
            },

            refreshInventory() {
                this.fetchInventory();
                this.loadCheckoutItems();
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
                    return 'bg-red-100 text-red-800';
                }
                if (item.on_hand_quantity <= item.alert_quantity) {
                    return 'bg-orange-100 text-orange-800';
                }
                if (this.isExpiringSoon(item.expiry_date)) {
                    return 'bg-yellow-100 text-yellow-800';
                }
                return 'bg-green-100 text-green-800';
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
