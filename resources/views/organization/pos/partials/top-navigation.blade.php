<!-- Navigation with reactive checkout count -->
<nav class="bg-white border-b border-gray-200" x-data="checkoutCounter()">
    <div class="px-6 py-2">
        <div class="flex items-center justify-between">
            <div class="flex gap-1">

                <button @click="activeTab = 'inventory'"
                    :class="activeTab === 'inventory' ? 'bg-blue-50 text-blue-700 border-blue-600' :
                        'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-transparent'"
                    class="px-4 py-3 font-medium text-sm rounded-lg transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Inventory</span>
                    </div>
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-500">
                    <span class="font-medium text-gray-700"
                        x-text="new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></span>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Alpine.js reactive checkout counter component
    function checkoutCounter() {
        return {
            count: 0,

            init() {
                // Initialize count
                this.updateCount();

                // Listen for checkout updates
                window.addEventListener('checkout-updated', () => {
                    this.updateCount();
                });

                // Listen for location changes (in case triggered from storage events)
                window.addEventListener('storage', (e) => {
                    if (e.key === 'pos_selected_location' || e.key === 'pos_checkout_items_by_location') {
                        this.updateCount();
                    }
                });
            },

            updateCount() {
                const selectedLocationId = localStorage.getItem('pos_selected_location') || '';

                if (!selectedLocationId) {
                    this.count = 0;
                    return;
                }

                const allLocationCheckouts = JSON.parse(localStorage.getItem('pos_checkout_items_by_location') || '{}');
                const locationItems = allLocationCheckouts[selectedLocationId] || [];

                this.count = locationItems.reduce((total, item) => total + item.quantity, 0);

                window.dispatchEvent(
                    new CustomEvent('checkout-page-updated', {
                        detail: {
                            locationId: selectedLocationId,
                            count: this.count
                        }
                    })
                );
            }
        }
    }
</script>
