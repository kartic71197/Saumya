<div class="w-full p-3 bg-white rounded-lg text-sm">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
            <h3 class="font-semibold text-gray-800 text-sm">Low Stock Alert</h3>
        </div>
        <!-- Pagination Controls -->
        <div class="flex justify-between items-center mt-4 gap-2">
            <button id="prevBtn" onclick="previousPage()"
                class="px-2 py-1 bg-gray-200 text-gray-600 rounded disabled:opacity-50" disabled>
                ←
            </button>
            <span id="pageInfo" class="text-sm text-gray-600">1/1</span>
            <button id="nextBtn" onclick="nextPage()"
                class="px-2 py-1 bg-gray-200 text-gray-600 rounded disabled:opacity-50">
                →
            </button>
        </div>

    </div>

    <!-- Products -->
    <div class="low-product-list space-y-1.5">
        @foreach ($low_stock_products_list as $product)
            <div class="product-item flex items-start gap-2 p-2 border border-transparent hover:border-red-100 hover:bg-red-50/30 rounded-md transition cursor-pointer"
                data-product-id="{{ $product->product?->id }}" data-context="low-stock"
                data-location-id="{{ $product->location->id ?? $product->product?->location->id ?? 0 }}"
                data-par-quantity="{{ $product->par_quantity ?? 0 }}">
                <!-- Info -->
                <div class="flex-1">

                    <div class="flex justify-between items-start">
                        <div>
                            <h4
                                class="text-gray-900 font-medium leading-tight break-words whitespace-normal hover:text-red-700">
                                {{ \Illuminate\Support\Str::limit($product->product?->product_name, 40) }}
                            </h4>

                            <p class="text-xs text-gray-500">Code: {{ $product->product?->product_code }}</p>
                            <div class="flex flex-wrap items-center gap-1 mt-1 text-xs">
                                @if (isset($product->location) || isset($product->product?->location))
                                    <span
                                        class="flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $product->location->name ?? $product->product->location->name ?? 'N/A' }}
                                    </span>
                                @endif

                                @if (isset($product->supplier) || isset($product->product->supplier))
                                    <span
                                        class="flex items-center gap-1 px-1.5 py-0.5 bg-green-50 text-green-700 border border-green-200 rounded">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M9 7h6m-6 4h6m-6 4h6">
                                            </path>
                                        </svg>
                                        {{ $product->supplier->name ?? $product->product?->supplier->supplier_name ?? 'N/A' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Stock Count -->
                        <div
                            class="text-xs px-2 py-0.5 bg-red-100 text-red-800 rounded-full font-medium whitespace-nowrap">
                            {{ isset($product->total_quantity) ? (int) $product->total_quantity : 'Low' }}
                        </div>
                    </div>

                    <!-- Progress -->
                    @if (isset($product->on_hand_quantity) && isset($product->alert_quantity))
                        <div class="mt-1">
                            @php
                                $percentage = min(
                                    ($product->on_hand_quantity / max($product->alert_quantity, 1)) * 100,
                                    100,
                                );
                            @endphp
                            <div class="h-1 w-full bg-gray-200 rounded-full">
                                <div class="h-1 bg-red-500 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State -->
     @if (count($low_stock_products_list) === 0)
        <div class="text-center py-6 text-sm text-gray-600">
            <div class="w-10 h-10 mx-auto mb-2 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            All products are well stocked!
        </div>
    @endif
</div>


<script>
let currentPage = 1;
const itemsPerPage = 5;
let allProducts = [];

// Wait for Livewire to finish rendering
document.addEventListener('livewire:init', function() {
    initPagination();
});

// Also listen for Livewire updates
document.addEventListener('livewire:update', function() {
    setTimeout(initPagination, 100); // Small delay to ensure DOM is updated
});

function initPagination() {
    allProducts = Array.from(document.querySelectorAll('.product-item'));
    
    if (allProducts.length === 0) {
        // Hide pagination controls completely
        const paginationDiv = document.querySelector('.flex.justify-between.items-center.mt-4.gap-2');
        if (paginationDiv) {
            paginationDiv.style.display = 'none';
        }
        return;
    }
    
    // Show pagination controls if they were hidden
    const paginationDiv = document.querySelector('.flex.justify-between.items-center.mt-4.gap-2');
    if (paginationDiv) {
        paginationDiv.style.display = 'flex';
    }
    
    // Hide all products initially
    allProducts.forEach(product => {
        product.style.display = 'none';
    });
    
    // Show first page
    showPage(1);
}

function showPage(page) {
    if (allProducts.length === 0) return;
    
    const totalPages = Math.ceil(allProducts.length / itemsPerPage);
    
    // Ensure page is within bounds
    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;
    
    // Hide all products
    allProducts.forEach(product => {
        product.style.display = 'none';
    });
    
    // Show products for current page
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    
    for (let i = startIndex; i < endIndex && i < allProducts.length; i++) {
        allProducts[i].style.display = 'block';
    }
    
    // Update button states
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (prevBtn && nextBtn) {
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages;
        
        // Update button opacity
        prevBtn.classList.toggle('opacity-50', page === 1);
        nextBtn.classList.toggle('opacity-50', page === totalPages);
    }
    
    // Update page info
    const pageInfo = document.getElementById('pageInfo');
    if (pageInfo) {
        pageInfo.textContent = `${page}/${totalPages}`;
    }
    
    currentPage = page;
}

function nextPage() {
    const totalPages = Math.ceil(allProducts.length / itemsPerPage);
    if (currentPage < totalPages) {
        showPage(currentPage + 1);
    }
}

function previousPage() {
    if (currentPage > 1) {
        showPage(currentPage - 1);
    }
}

    // Handle product click to open modal
    $(document).on('click', '.product-item', function(e) {
        e.stopPropagation();
        const productElement = $(this);
        const productId = productElement.data('product-id');
        const context = productElement.data('context') || 'low-stock';
        const locationId = productElement.data('location-id') || 0;
        const parQuantity = productElement.data('par-quantity') || 0;

        if (!productId) {
            console.warn('No product ID found');
            return;
        }

        // Check if Livewire is available
        if (typeof Livewire !== 'undefined') {
            console.log('Opening product modal for ID:', productId);
            Livewire.dispatch('openProductDetailBrowser', {
                id: parseInt(productId),
                context: context,
                location_id: parseInt(locationId),
                par_quantity: parseFloat(parQuantity),
            });
        } else {
            console.error('Livewire is not available!');
            alert('Product details cannot be loaded at the moment.');
        }
    });

    // Optional: Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            previousPage();
        } else if (e.key === 'ArrowRight') {
            nextPage();
        }
    });

    function updateLowProductList(products) {

        console.log('Updating low product list with products:', products);
        const container = document.querySelector('.low-product-list');
        container.innerHTML = '';

        if (products.length === 0) {
            container.innerHTML = `
                <div class="text-center py-6 text-sm text-gray-600">
                    <div class="w-10 h-10 mx-auto mb-2 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    All products are well stocked!
                </div>`;
            return;
        }

        products.forEach(product => {
            const percentage = Math.min((product.total_quantity / Math.max(product.alert_quantity, 1)) * 100,
                100);
            const productHTML = `
                <div class="product-item flex items-start gap-2 p-2 border border-transparent hover:border-red-100 hover:bg-red-50/30 rounded-md transition cursor-pointer"
                data-product-id="${product.product?.id ?? product.id}"
                data-context="low-stock"
                data-location-id="${product.location?.id || product.product?.location_id || 0}"
                data-par-quantity="${product.par_quantity || 0}">
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-gray-900 font-medium leading-tight truncate hover:text-red-700">
                                     ${product.product.product_name.length > 40 ? product.product.product_name.slice(0, 40) + '...' : product.product.product_name}</h4>
                                <p class="text-xs text-gray-500">Code: ${product.product?.product_code ?? 'N/A'}</p>
                                <div class="flex flex-wrap items-center gap-1 mt-1 text-xs">
                                    <span class="flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        ${product.location?.name ?? product.product?.location?.name ?? 'N/A'}
                                    </span>
                                    <span class="flex items-center gap-1 px-1.5 py-0.5 bg-green-50 text-green-700 border border-green-200 rounded">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M9 7h6m-6 4h6m-6 4h6">
                                            </path>
                                        </svg>
                                        ${product.supplier?.name ?? product.product?.supplier?.supplier_name ?? 'N/A'}
                                    </span>
                                </div>
                            </div>
                            <div class="text-xs px-2 py-0.5 bg-red-100 text-red-800 rounded-full font-medium whitespace-nowrap">
                                ${product.total_quantity!== undefined ? Math.floor(product.total_quantity) : 'Low'} left
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', productHTML);
        });
        // Reinitialize pagination
        allProducts = Array.from(document.querySelectorAll('.product-item'));
        showPage(1);
    }
</script>
