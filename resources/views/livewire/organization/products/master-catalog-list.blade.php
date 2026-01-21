<div class="w-full">
    <!-- Clear Filters Button (only shown when filters are active) -->
    @if($filterSupplier || $filterBrand || $filterCategory || $filterSubcategory || $filterProductCode || $filterProductName || $filterUnit || $filterCatalogStatus)
        <div class="mb-4 flex justify-end">
            <button wire:click="clearFilters"
                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                Clear All Filters
            </button>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-800">
        <table class="w-full text-xs text-left">
            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">Image</th>

                    <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                        wire:click="sortBy('product_code')">
                        <div class="flex items-center gap-1">
                            Product Code
                            @if($sortField === 'product_code')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7">
                                        </path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>

                    <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                        wire:click="sortBy('product_name')">
                        <div class="flex items-center gap-1">
                            Product Name
                            @if($sortField === 'product_name')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7">
                                        </path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>

                    <th class="px-4 py-3">Manufacturer</th>
                    <th class="px-4 py-3">Units</th>

                    @if(!auth()->user()->is_medical_rep)
                        <th class="px-4 py-3">Cost</th>
                    @endif

                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Sub-category</th>

                    @if(!auth()->user()->is_medical_rep)
                        <th class="px-4 py-3 text-center">Action</th>
                    @endif
                </tr>

                <!-- Filter Row -->
                <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                    <!-- Image - No Filter -->
                    <th class="px-4 py-2"></th>

                    <!-- Product Code Filter -->
                    <th class="px-4 py-2">
                        <input type="text" wire:model.live.debounce.300ms="filterProductCode"
                            placeholder="Product code..."
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                    </th>

                    <!-- Product Name Filter -->
                    <th class="px-4 py-2">
                        <input type="text" wire:model.live.debounce.300ms="filterProductName"
                            placeholder="Product name..."
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                    </th>

                    <!-- Manufacturer Filter -->
                    <th class="px-4 py-2">
                        <select wire:model.live="filterBrand"
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                            @endforeach
                        </select>
                    </th>

                    <!-- Units Filter -->
                    <th class="px-4 py-2">
                        <input type="text" wire:model.live.debounce.300ms="filterUnit" placeholder="Unit..."
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                    </th>

                    <!-- Cost - No Filter (if visible) -->
                    @if(!auth()->user()->is_medical_rep)
                        <th class="px-4 py-2"></th>
                    @endif

                    <!-- Supplier Filter -->
                    <th class="px-4 py-2">
                        <select wire:model.live="filterSupplier"
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                            @endforeach
                        </select>
                    </th>

                    <!-- Category Filter -->
                    <th class="px-4 py-2">
                        <select wire:model.live="filterCategory"
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </th>

                    <!-- Sub-category Filter -->
                    <th class="px-4 py-2">
                        <select wire:model.live="filterSubcategory"
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}">{{ $subcategory->subcategory }}</option>
                            @endforeach
                        </select>
                    </th>



                    {{-- <!-- Catalog Status Filter (if not medical rep) -->
                    @if(!auth()->user()->is_medical_rep)
                    <th class="px-4 py-2">
                        <select wire:model.live="filterCatalogStatus"
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All</option>
                            <option value="in_catalog">In Catalog</option>
                            <option value="not_in_catalog">Not in Catalog</option>
                        </select>
                    </th>
                    @endif --}}
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <!-- Image -->
                        <td class="px-4 py-3">
                            @php
                                if (str_starts_with($product->image, 'http')) {
                                    $fullImageUrl = $product->image;
                                } else {
                                    $images = json_decode($product->image, true);
                                    $imagePath = is_array($images) && !empty($images) ? $images[0] : $product->image;
                                    $fullImageUrl = asset('storage/' . $imagePath);
                                }
                            @endphp
                            <div onclick="openImageModal('{{ $fullImageUrl }}')" class="cursor-pointer">
                                <img class="w-10 h-10 rounded-md object-cover" src="{{ $fullImageUrl }}"
                                    alt="{{ $product->product_name }}">
                            </div>
                        </td>

                        <!-- Product Code -->
                        <td class="px-4 py-3 font-medium">{{ $product->product_code }}</td>

                        <!-- Product Name -->
                        <td class="px-4 py-3">
                            <span class="underline cursor-pointer text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                onclick="openProductModal('{{ $product->id }}', 'catalog')">
                                {{ $product->product_name }}
                            </span>
                        </td>

                        <!-- Manufacturer -->
                        <td class="px-4 py-3">{{ $product->brand?->brand_name ?? '-' }}</td>

                        <!-- Units -->
                        <td class="px-4 py-3">{{ $product->unit->first()?->unit?->unit_name ?? '-' }}</td>

                        <!-- Cost -->
                        @if(!auth()->user()->is_medical_rep)
                            <td class="px-4 py-3">
                                {{ session('currency', '$') }}{{ number_format($product->cost, 2) }}
                            </td>
                        @endif

                        <!-- Supplier -->
                        <td class="px-4 py-3">{{ $product->supplier?->supplier_name ?? '-' }}</td>

                        <!-- Category -->
                        <td class="px-4 py-3">{{ $product->categories?->category_name ?? '-' }}</td>

                        <!-- Sub-category -->
                        <td class="px-4 py-3">{{ $product->subcategory?->subcategory ?? '-' }}</td>


                        <!-- Action -->
                        @if(!auth()->user()->is_medical_rep)
                            <td class="px-4 py-3 text-center">
                                @php
                                    $inCatalog = $this->isInMyCatalog($product->id);
                                @endphp
                                <button wire:click="toggleMyCatalog({{ $product->id }})" wire:loading.attr="disabled"
                                    wire:target="toggleMyCatalog({{ $product->id }})"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full transition ease-in-out duration-150 {{ $inCatalog ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-50 hover:bg-gray-100 border border-gray-500' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="{{ $inCatalog ? 'Already in Catalog' : 'Click to add to Catalog' }}">

                                    <!-- Loader (shown during loading) -->
                                    <svg wire:loading wire:target="toggleMyCatalog({{ $product->id }})"
                                        class="animate-spin w-5 h-5 {{ $inCatalog ? 'text-white' : 'text-gray-500' }}"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>

                                    <!-- Checkmark (shown when not loading) -->
                                    <svg wire:loading.remove wire:target="toggleMyCatalog({{ $product->id }})"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5 {{ $inCatalog ? 'text-white' : 'text-gray-500' }}" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No products found</p>
                                <p class="text-sm">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
            <label class=" text-gray-700 dark:text-gray-300">Show</label>
            <select wire:model.live="perPage"
                class="text-xs px-4 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option class="py-2" value="10">10</option>
                <option class="py-2" value="25">25</option>
                <option class="py-2" value="50">50</option>
                <option class="py-2" value="100">100</option>
            </select>
            <span class="text-sm text-gray-700 dark:text-gray-300">entries</span>
        </div>
        {{--
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
            results
        </div> --}}

        <div class="px-2">
            {{ $products->links() }}
        </div>
    </div>
</div>