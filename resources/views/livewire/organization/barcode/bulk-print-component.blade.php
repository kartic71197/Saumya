<div>
    <!-- Header with Search and Print Button -->
    <div class="flex justify-between items-center mb-4 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center space-x-4">
            <h2 class="text-lg font-semibold text-gray-800">Barcode Print Selection</h2>
        </div>

        <div class="flex items-center space-x-4">
            <div class="w-64">
                {{-- <label for="category" class="block text-sm font-medium text-gray-700">Category</label> --}}
                <select wire:model.live="selectedCategory" id="category"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['id'] }}">{{ $cat['category_name'] }}</option>
                    @endforeach
                </select>
            </div>
        
        <!-- Search Bar -->
        <div class="relative">
            <input type="text" wire:model.live="search" placeholder="Search products..."
                class="px-4 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg class="w-4 h-4 absolute right-2 top-2 text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

        {{-- <div class="flex items-center space-x-2">
            <button wire:click="printSelected"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
                @if(count($selectedProducts) === 0) disabled @endif>
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-7a2 2 0 00-2-2H9a2 2 0 00-2 2v7a2 2 0 002 2z" />
                </svg>
                Print ({{ count($selectedProducts) }})
            </button>
        </div> --}}
    </div>

    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Two Tables Side by Side -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Available Products Table -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-4 py-3 bg-gray-50 border-b rounded-t-lg flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-medium text-gray-700">Available Products</h3>
                    <p class="text-xs text-gray-500 mt-1">Click to add to selection</p>
                </div>
                @if(count($this->availableProducts) > 0)
                    <button wire:click="selectAll" 
                        class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                        Select All
                    </button>
                @endif
            </div>
            
            <div class="overflow-x-auto max-h-96">
                <table class="min-w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->availableProducts as $product)
                            <tr class="hover:bg-blue-50 cursor-pointer transition-colors duration-150" 
                                wire:click="addToSelected({{ $product['id'] }})">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product['code'] }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-sm text-gray-900 truncate max-w-xs" title="{{ $product['name'] }}">
                                        {{ $product['name'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center">
                                    <button wire:click.stop="addToSelected({{ $product['id'] }})"
                                        class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-sm">No products available</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Selected Products Table -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-4 py-3 bg-blue-50 border-b rounded-t-lg flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-medium text-gray-700">Selected for Print</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ count($selectedProducts) }} products selected</p>
                </div>
                @if(count($selectedProducts) > 0)
                    <button wire:click="clearSelected" 
                        class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                        Clear All
                    </button>
                @endif
            </div>
            
            <div class="overflow-x-auto max-h-96">
                <table class="min-w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                Remove
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($selectedProducts as $product)
                            <tr class="hover:bg-red-50 transition-colors duration-150">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product['code'] }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-sm text-gray-900 truncate max-w-xs" title="{{ $product['name'] }}">
                                        {{ $product['name'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center">
                                    <button wire:click="removeFromSelected({{ $product['id'] }})"
                                        class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="text-sm">No products selected</p>
                                        <p class="text-xs text-gray-400">Click products from left table to add</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Summary -->
    <div class="mt-6 flex flex-col sm:flex-row justify-between items-center p-4 bg-gray-50 rounded-lg">
        <div class="text-sm text-gray-600 mb-3 sm:mb-0">
            {{-- Total Products: <span class="font-bold">{{ count($products) }}</span> | --}}
            Total Products: <span class="font-bold text-green-600">{{ count($this->availableProducts) }}</span> |
            Selected: <span class="font-bold text-blue-600">{{ count($selectedProducts) }}</span>
        </div>

        <button wire:click="printSelected"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
            @if(count($selectedProducts) === 0) disabled @endif>
            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-7a2 2 0 00-2-2H9a2 2 0 00-2 2v7a2 2 0 002 2z" />
            </svg>
            Print Selected ({{ count($selectedProducts) }})
        </button>
    </div>

    <!-- Loading State -->
    <div wire:loading wire:target="printSelected"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l-3-2.647z">
                </path>
            </svg>
            <span class="text-gray-700">Preparing print...</span>
        </div>
    </div>
</div>