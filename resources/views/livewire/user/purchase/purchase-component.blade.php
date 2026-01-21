<div>
    <div>
        @if (!$viewPurchaseOrder)
            <!-- Loading Overlay -->
            {{-- <div wire:loading wire:target="setOrderType,selectedLocation" class="fixed inset-0 bg-gray-500 bg-opacity-25 flex items-center justify-center z-100">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-md mx-auto"></div>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 text-center">Loading...</p>
                </div>
            </div> --}}

            <div class="py-1">
                <div class="max-w-5xl mx-auto">
                    <!-- Header Card -->
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm mb-4">
                        <!-- Tab Navigation and Location Dropdown -->
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 pb-4">
                            <!-- Tab Navigation -->
                            <div class="flex flex-wrap gap-2 sm:gap-6 items-center">
                                <button wire:click="setOrderType('organization')" 
                                    class="tab-btn flex items-center justify-center pb-3 -mb-px text-gray-600 dark:text-gray-300 border-b-2 hover:border-primary-md transition-colors duration-200 whitespace-nowrap
                                    {{ $orderType == 'organization' ? 'text-primary-md font-medium border-primary-md' : 'border-transparent' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h10M9 12h10M9 16h10M5 8H5m0 4h.01m0 4H5" />
                                    </svg>
                                    Practice's  Orders
                                </button>
                                <button wire:click="setOrderType('rep')"
                                    class="tab-btn flex items-center justify-center pb-3 -mb-px text-gray-600 dark:text-gray-300 border-b-2 hover:border-primary-md transition-colors duration-200 whitespace-nowrap
                                    {{ $orderType == 'rep' ? 'text-primary-md font-medium border-primary-md' : 'border-transparent' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 12v1h4v-1m4 7H6a1 1 0 0 1-1-1V9h14v9a1 1 0 0 1-1 1ZM4 5h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                                    </svg>
                                    Sample Orders
                                </button>
                            </div>
                            
                            <!-- Location Dropdown -->
                            <div class="flex items-center gap-3 w-full lg:w-auto">
                                @php
                                    $user = auth()->user();
                                    $role = $user->role;
                                @endphp
                                @if (!$role?->hasPermission('all_purchase') && $user->role_id > 2)
                                    <!-- User doesn't have permission -->
                                @else
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Location:</label>
                                    <div class="relative w-full lg:w-64">
                                        <select wire:model.live="selectedLocation"
                                            class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-md focus:border-primary-md dark:bg-gray-800 dark:text-gray-100 transition-colors duration-200">
                                                <option value="0">All Locations</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                        <div wire:loading wire:target="selectedLocation" class="absolute inset-y-0 right-8 flex items-center pr-3">
                                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Page Header -->
                        <div class="pt-4">
                            <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                                <div>
                                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ __('Purchase Orders') }}
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('Manage purchase orders for your Practice\'s') }}
                                    </p>
                                </div>
                                
                                <!-- Action Buttons (if needed) -->
                                <div class="flex items-center gap-2">
                                    <!-- Add your action buttons here -->
                                </div>
                            </header>
                        </div>
                    </div>
                    <!-- Search and Filters -->
                    <div>
                        <div class="flex flex-col flex-row items-stretch items-center gap-4 mb-2">
                            <!-- Search Input -->
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                    id="purchaseOrderSearch"
                                    placeholder="Search by order number, supplier, or reference..."
                                    wire:model.live.debounce.300ms="searchPurchaseOrder" 
                                    autocomplete="off"
                                    class="pl-10 pr-10 w-full px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-2 focus:ring-primary-md focus:border-primary-md dark:bg-gray-800 dark:text-white placeholder-gray-400 transition-colors duration-200" />
                                
                                <!-- Loading Indicator -->
                                <div wire:loading wire:target="searchPurchaseOrder" class="absolute inset-y-0 right-8 flex items-center pr-3">
                                    <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                
                                <!-- Clear Search Button -->
                                @if($searchPurchaseOrder)
                                    <button wire:click="clearSearch" 
                                        type="button"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <!-- Additional Filters (if needed) -->
                            <div class="flex items-center gap-2">
                                <!-- Status Filter -->
                                {{-- <select wire:model.live="statusFilter" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-md focus:border-primary-md dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="completed">Completed</option>
                                </select> --}}
                                
                                <!-- Date Range Filter -->
                                {{-- <input type="date" wire:model.live="dateFrom" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-md focus:border-primary-md dark:bg-gray-800 dark:text-gray-100"> --}}
                            </div>
                        </div>

                        <!-- Active Filters Display -->
                        @if($searchPurchaseOrder)
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                        Search: "{{ $searchPurchaseOrder }}"
                                        <button wire:click="clearSearch" class="ml-1.5 inline-flex items-center justify-center w-4 h-4 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Purchase Orders List -->
            <div class="max-w-5xl mx-auto">
                <div wire:loading.delay wire:target="searchPurchaseOrder,setOrderType,selectedLocation" class="mb-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <div class="animate-pulse">
                            <div class="flex items-center justify-between mb-4">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
                                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                            </div>
                            <div class="space-y-3">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div wire:loading.remove wire:target="searchPurchaseOrder,setOrderType,selectedLocation">
                    @if ($purchaseOrderList && $purchaseOrderList->count() > 0)

                        <!-- Purchase Orders Grid -->
                        <div class="space-y-4">
                            @foreach($purchaseOrderList->sortByDesc('created_at') as $order)
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                                    @include('livewire.user.purchase.purchase-order-card')
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination (if needed) -->
                        {{-- <div class="mt-6">
                            {{ $purchaseOrderList->links() }}
                        </div> --}}
                    @else
                        <!-- Empty State -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col items-center justify-center py-12 px-4">
                                @if (!empty($searchPurchaseOrder))
                                    <!-- Search Results Empty State -->
                                    <div class="text-center">
                                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No results found</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4">
                                            We couldn't find any purchase orders matching "<span class="font-medium">{{ $searchPurchaseOrder }}</span>"
                                        </p>
                                        <button wire:click="clearSearch" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Clear search
                                        </button>
                                    </div>
                                @else
                                    <!-- No Orders Empty State -->
                                    <div class="text-center">
                                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No purchase orders yet</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4">
                                            No purchase orders have been created yet for the selected filters.
                                        </p>
                                        <!-- Add create button if needed -->
                                        {{-- <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Create Purchase Order
                                        </button> --}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
                @include('livewire.user.purchase.view-purchase-order')
            @endif
        </div>
        <!-- Modal for receiving products -->
        @include('livewire.user.purchase.modals.receive-product-model')
        @include('livewire.user.purchase.modals.preview_edi855_modal')
        @include('livewire.user.purchase.modals.preview_edi856_modal')
        @include('livewire.admin.purchase.modals.preview-modal')
        @include('livewire.user.purchase.modals.cancel-po-confirmation')

    </div>


    {{-- Your existing HTML and Livewire directives --}}

<script>
    window.addEventListener('livewire:load', () => {
        console.log('✅ Livewire loaded');
    });

    document.addEventListener('livewire:update', (e) => {
        console.log('🔄 Livewire updated', e);
    });

    document.addEventListener('livewire:request', (e) => {
        console.log('📤 Livewire request sent:', e.detail);
    });

    document.addEventListener('livewire:response', (e) => {
        console.log('📥 Livewire response received:', e.detail);
    });
</script>

