<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4
            max-h-[calc(100vh-8rem)] overflow-y-auto">

    {{-- Search --}}
    <div class="mb-4">
        <input type="text" id="supplierSearch" placeholder="Search suppliers..." class="w-full px-3 py-2 text-sm border rounded-lg
                   focus:ring-2 focus:ring-blue-500
                   dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
    </div>

    {{-- Supplier List --}}
    <div id="supplierList" class="space-y-1">
        @foreach ($suppliers as $index => $supplier)
            <button type="button" class="supplier-nav-item w-full text-left px-4 py-3 rounded-lg
                           transition hover:bg-gray-100 dark:hover:bg-gray-700
                           {{ $index === 0 ? 'bg-blue-100 dark:bg-blue-900 text-blue-700' : '' }}"
                data-supplier-id="{{ $supplier->id }}" data-supplier-name="{{ $supplier->supplier_name }}"
                onclick="selectSupplier(event, {{ $supplier->id }}, '{{ $supplier->supplier_name }}')">
                <div class="flex justify-between items-center">
                    <span class="font-medium">{{ $supplier->supplier_name }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>
        @endforeach
    </div>
</div>
