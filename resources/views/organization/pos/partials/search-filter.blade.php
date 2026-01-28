<div>
    <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
        <div class="flex-1">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" x-model="searchQuery" @input="filterProducts()"
                    placeholder="Search products by name, batch number..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Location Dropdown - Only show if role_id < 3 -->
        <template x-if="userRoleId < 3">
            <select x-model="selectedLocationId" @change="onLocationChange()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <template x-for="location in locations" :key="location.id">
                    <option :value="location.id.toString()" x-text="location.name"
                        :selected="location.id.toString() === selectedLocationId"></option>
                </template>
            </select>
        </template>

        <select x-model="filterStatus" @change="filterProducts()"
            class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="all">All Products</option>
            <option value="low">Low Stock</option>
            <option value="expired">Expired</option>
            <option value="expiring">Expiring Soon</option>
        </select>

        <button @click="refreshInventory()"  title="Refresh"
            class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>
</div>
