<table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 p-3">
    <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-6 py-3">Product</th>
            <th scope="col" class="px-6 py-3">Unit</th>
            <th scope="col" class="px-6 py-3">Quantity</th>
            <th scope="col" class="px-6 py-3">Received</th>
            <th scope="col" class="px-6 py-3">Status</th>
            <th scope="col" class="px-6 py-3"></th>
            <th scope="col" class="px-6 py-3">Total Price</th>
            <th scope="col" class="px-6 py-3">Action</th>
        </tr>
    </thead>
    <tbody>
        @if ($purchaseOrder?->purchasedProducts)
            @foreach ($purchaseOrder->purchasedProducts as $index => $product)
                <tr
                    class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200 {{ $product->quantity == $product->received_quantity ? 'opacity-50 pointer-events-none' : '' }}">

                    <th scope="row"
                        class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-normal">
                        ({{ $product->product->product_code }})
                        {{ $product->product->product_name }}
                    </th>
                    <td class="px-6 py-4">
                        {{ $product->unit->unit_name }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $product->quantity }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $product->received_quantity }}
                    </td>
                    <td class="px-6 py-4 text-xs">
                        @if($product->product_status === 'canceled')
                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 rounded-full">
                                Canceled
                            </span>
                        @else
                            {{ $product->product_status }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if ($product->tracking_link)
                            <a href="{{ $product->tracking_link }}" target="_blank"
                                class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                Track
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 3h7v7m0-7L10 14"></path>
                                </svg>
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        {{ session('currency', '$') }}{{ number_format($product->sub_total, 2) }}
                    </td>
                    
                    <td class="px-6 py-4">
                        @if($product->product_status !== 'canceled')
                            <button 
                                wire:click="openCancelProductModal({{ $product->id }}, '{{ $product->product->product_code }}')"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                                title="Cancel Product">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>

                <!-- History Row - Conditionally visible -->
                @if ($this->isHistoryVisible($index))
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="8" class="px-6 py-2">
                            <div class="dark:bg-gray-900 rounded-lg dark:border-gray-700 p-2">
                                @php
                                    $receipts = App\Models\PoReceipt::where(
                                        'purchase_order_id',
                                        $purchaseOrder->id,
                                    )
                                        ->where('product_id', $product->product_id)
                                        ->with('receivedBy')
                                        ->orderBy('date_received', 'desc')
                                        ->get();
                                @endphp

                                @if ($receipts->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs border-collapse">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-700">
                                                    <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                        Date Received</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                        Received Qty</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                        Batch Number</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                        Expiry Date</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
                                                        Received By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($receipts as $receipt)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                                        <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                            {{ $receipt->date_received ? \Carbon\Carbon::parse($receipt->date_received)->format('M d, Y') : '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                            {{ $receipt->received_qty ?? '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                            {{ $receipt->batch_number ?? '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                            {{ $receipt->expiry_date ? \Carbon\Carbon::parse($receipt->expiry_date)->format('M d, Y') : '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-300">
                                                            {{ $receipt->receivedBy->name ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">No receipt history found for this product.</p>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
    </tbody>
</table>

<!-- Cancel Product Modal -->
@if($showCancelProductModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeCancelProductModal"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Cancel Product
                        </h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Are you sure you want to cancel this product from the purchase order?
                            </p>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Enter Product Code to Confirm <span class="text-red-600">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="confirmProductCode"
                                    placeholder="Enter product code"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                >
                                @error('confirmProductCode')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cancel Note <span class="text-red-600">*</span>
                                </label>
                                <textarea 
                                    wire:model="cancelNote"
                                    rows="3"
                                    placeholder="Please provide a reason for cancellation..."
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white sm:text-sm resize-none"
                                ></textarea>
                                @error('cancelNote')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($cancelProductError)
                                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                                    <p class="text-sm text-red-800 dark:text-red-400">{{ $cancelProductError }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button 
                    type="button" 
                    wire:click="confirmCancelProduct"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Cancel
                </button>
                <button 
                    type="button" 
                    wire:click="closeCancelProductModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif