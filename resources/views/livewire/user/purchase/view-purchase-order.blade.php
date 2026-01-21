<!-- Grid Layout -->
<div class="min-w-7xl grid grid-cols-8 gap-3">
    <!-- Left Section (List & Search) -->
    <div class="col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg p-4 overflow-auto max-h-[500px] overflow-hidden">
        <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                </svg>
            </div>
            <input type="search" wire:model.live.debounce.300ms="searchPurchaseOrder"
                class="block w-full p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="Search ..." />
        </div>
        <ul class="mt-4 max-h-[500px] overflow-auto">
            @foreach ($purchaseOrderList as $po)
                @php
                    $status = $po?->status;
                    $statusClasses = match ($status) {
                        'pending' => 'text-yellow-600 dark:text-yellow-400',
                        'ordered' => 'text-blue-600 dark:text-blue-400',
                        'partial' => 'text-orange-600 dark:text-orange-400',
                        'completed' => 'text-green-600 dark:text-green-400',
                        'cancel' => 'text-red-600 dark:text-red-400',
                        default => 'text-gray-600 dark:text-gray-400',
                    };
                @endphp
                <li wire:click="selectPo({{ $po->id }})"
                    class="{{ $statusClasses }} p-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 border-b border-slate-300 dark:border-slate-700 {{ $purchaseOrder?->id == $po->id ? 'bg-primary-md !text-white rounded' : '' }}">
                    {{ $po->purchase_order_number }}
                </li>
            @endforeach
        </ul>
    </div>
    <!-- Right Section (PO Details) -->
    <div class="col-span-6 bg-white dark:bg-gray-800 shadow rounded-lg p-4 h-full transition-all duration-300">
        <div class="flex flex-end justify-end">
            <button wire:click="$set('viewPurchaseOrder', false)"
                class="text-black text-2xl font-bold hover:text-gray-300 text-end">
                &times;
            </button>
        </div>
        <div class="flex justify-between items-center mt-3 rounded bg-gray-100 dark:bg-primary-dk p-3">
            <h3 class="text-3xl font-semibold text-primary-dk dark:text-gray-200">
                {{ $purchaseOrder->merge_id ? $purchaseOrder->merge_id : $purchaseOrder->purchase_order_number }}
            </h3>
            @php
                $status = $purchaseOrder?->status;
                $statusClasses = match ($status) {
                    'pending'
                        => 'bg-yellow-100 border-2 border-yellow-800 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 py-2 px-3',
                    'ordered'
                        => 'bg-blue-100 border-2 border-blue-800 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                    'partial'
                        => 'bg-orange-100 border-2 border-orange-800 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                    'completed'
                        => 'bg-green-100 border-2 border-green-800 text-green-800 dark:bg-green-900 dark:text-green-300',
                    'cancel' => 'bg-red-100 border-2 border-red-800 text-red-800 dark:bg-red-900 dark:text-red-300',
                    default
                        => 'bg-gray-100 border-2 border-gray-800 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                };
            @endphp
            <span class="text-sm font-medium me-2 px-2.5 py-0.5 rounded-full border {{ $statusClasses }}">
                {{ ucfirst($status) ?? 'Unknown' }}
            </span>
        </div>
        <div class="flex items-center justify-between p-3 text-primary-dk font-semibold mt-2">

            <!-- Left side (Purchase + Order Acknowledgement) -->
            <div class="flex items-center gap-4">
                <div wire:click="$set('selectedTab', 'purchase')"
                    class="cursor-pointer px-2 py-1 border-b-2 transition-all duration-300 
            {{ $selectedTab === 'purchase'
                ? 'border-primary-dk dark:text-white'
                : 'dark:text-white text-black border-transparent' }}">
                    Purchase
                </div>

                @php
                    $edi855 = App\Models\Edi855::where(
                        'purchase_order',
                        $purchaseOrder->purchase_order_number,
                    )->exists();
                @endphp

                @if ($edi855)
                    <button wire:click="previewEdi855('{{ $purchaseOrder->purchase_order_number }}')"
                        class="flex items-center px-4 py-2 hover:bg-gray-100 text-blue-600 hover:text-blue-800 rounded-md transition duration-200">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ __('Order Acknowledgement') }}
                    </button>
                @endif
            </div>

            <!-- Right side (Cancel) -->
            <div wire:click="cancelOrderConfirmation({{ $purchaseOrder->id }})"
                class="cursor-pointer px-3 py-1 border-b-2 transition-all duration-300 
        {{ $selectedTab === 'cancel'
            ? 'text-red-600 border-red-600'
            : 'text-black dark:text-white border-transparent hover:text-red-600 hover:border-red-600' }}">
                Cancel
            </div>
        </div>
        <div class="grid grid-cols-6 p-3">
            <div class="col-span-2 space-y-2 p-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-900 dark:text-white">Date & Time :</span>
                    {{ \Carbon\Carbon::parse($purchaseOrder?->created_at)->format('m-d-Y H:i') }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-900 dark:text-white">Location :</span>
                    {{ $purchaseOrder?->purchaseLocation->name }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-900 dark:text-white">Created by :</span>
                    {{ $purchaseOrder?->createdUser->name }}
                </p>
            </div>
            <div class="col-span-2 space-y-2 p-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-900 dark:text-white">Total Products :</span>
                    {{ $purchaseOrder?->purchasedProducts->count() }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-gray-900 dark:text-white">Grand Total :</span>
                    {{ session('currency', '$') }}{{ number_format($purchaseOrder?->total, 2) }}
                </p>
            </div>
        </div>
        @include('livewire.user.purchase.partials.billing-shippng-info')
        <div class="p-3">
            @include('livewire.user.purchase.partials.product-details-table')

            <!-- Receipt Notes Section -->
            @include('livewire.user.purchase.partials.receipt_notes_section')

            <!-- Receipt Images Section -->
            @include('livewire.user.purchase.partials.receipt_images_section')
        </div>
    </div>
</div>
