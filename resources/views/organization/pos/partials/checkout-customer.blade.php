{{-- Customer Section --}}
<div class="bg-white rounded-xl shadow-md border border-gray-100 ">
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Customer
        </h2>
    </div>

    <div class="p-4 space-y-3">
        {{-- Search Customer Input --}}
        <div class="relative">
            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Search Customer</label>
            <div class="relative">
                <input type="text" placeholder="Enter phone or email to search..." x-model="customerInput"
                    @input.debounce.300ms="searchCustomer" @focus="showDropdown = customerSearchResults.length > 0"
                    @keydown.escape="showDropdown = false" name="customer-search" autocomplete="new-password"
                    autocorrect="off" autocapitalize="off" spellcheck="false"
                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm
           focus:ring-2 focus:ring-blue-500 focus:border-transparent
           transition-all pl-10 pr-3 py-2">

                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            {{-- Dropdown Results --}}
            <div x-show="showDropdown && customerSearchResults.length > 0"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">

                <template x-for="(customer, index) in customerSearchResults" :key="customer.id">
                    <div @click="selectCustomer(customer)"
                        class="px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800" x-text="customer.name"></p>
                                <div class="mt-1 space-y-0.5">
                                    <template x-if="customer.email">
                                        <p class="text-xs text-gray-600 flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span x-text="customer.email"></span>
                                        </p>
                                    </template>
                                    <template x-if="customer.phone">
                                        <p class="text-xs text-gray-600 flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                </path>
                                            </svg>
                                            <span x-text="customer.phone"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-blue-500 flex-shrink-0 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Loading State --}}
        <template x-if="isSearchingCustomer">
            <div class="flex items-center justify-center py-4">
                <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C6.48 0 0 6.48 0 12h4z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Searching...</span>
            </div>
        </template>

        {{-- Customer Selected --}}
        <template x-if="customerExists && !isSearchingCustomer">
            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3 flex-1">
                        <svg class="h-6 w-6 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-green-800 mb-1" x-text="customerId"></p>
                            <p class="text-sm font-bold text-green-800 mb-1" x-text="customerName"></p>
                            <template x-if="customerEmail">
                                <p class="text-xs text-green-700 flex items-center gap-1 mb-0.5">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span x-text="customerEmail"></span>
                                </p>
                            </template>
                            <template x-if="customerPhone">
                                <p class="text-xs text-green-600 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                        </path>
                                    </svg>
                                    <span x-text="customerPhone"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                    <button @click="clearCustomer"
                        class="text-green-600 hover:text-green-800 hover:bg-green-100 p-1.5 rounded-lg transition-all flex-shrink-0 ml-2"
                        title="Clear customer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Customer Not Found --}}
        <template
            x-if="!customerExists && customerInput && !isSearchingCustomer && hasSearched && customerSearchResults.length === 0">
            <div class="p-4 bg-gradient-to-r from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-lg">
                <div class="flex items-start gap-3 mb-3">
                    <svg class="h-6 w-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800 mb-1">Customer Not Found</p>
                        <p class="text-xs text-amber-700">No customer found with this phone or email.</p>
                    </div>
                </div>

                {{-- Add New Customer Button --}}
                <x-primary-button class="w-full flex justify-center items-center"
                    @click="$dispatch('open-customers-tab')">
                    + Add New Customer
                </x-primary-button>
            </div>
        </template>

        {{-- Walk-in Customer (Default State) --}}
        <template x-if="!customerInput && !isSearchingCustomer && !customerExists">
            <div class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                <svg class="mx-auto h-12 w-12 mb-2 opacity-50" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <p class="text-sm font-medium text-gray-500">Walk-in Customer</p>
                <p class="text-xs text-gray-400 mt-1">Search to link a customer to this sale</p>
            </div>
        </template>
    </div>
</div>
