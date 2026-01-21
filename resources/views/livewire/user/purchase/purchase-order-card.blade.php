<div class="dark:bg-gray-800 dark:border-gray-700 bg-white rounded-lg p-5 mb-2 order-item"
    data-order="{{ $order->purchase_order_number }}">
    <div class="flex flex-col md:flex-row md:justify-between items-center mb-4">
        <div>
            <p class="dark:text-gray-200 text-gray-600">Purchase Order:
                <span
                    class="font-semibold text-black dark:text-gray-100">{{ $order->merge_id ? $order->merge_id : $order->purchase_order_number }}</span>
                <span class="ml-2 inline-block py-0.5 px-1.5 text-xs rounded-full border-2
                    @if($order->status == 'ordered') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border-blue-800
                    @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 border-yellow-800
                    @elseif($order->status == 'partial') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 border-orange-800
                    @elseif($order->status == 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 border-green-800
                    @endif
                ">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
            @include('livewire.user.purchase.partials.invoice-buttons')
        </div>
        <div class="flex gap-3">
            <!-- View Order Button -->
            <x-primary-button wire:click="fetchPoModal({{ $order->id }})"
                class="inline-flex items-center justify-center w-42 px-4 py-2 bg-primary-md dark:bg-primary-md border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dk dark:hover:bg-primary-dk focus:bg-primary-dk dark:focus:bg-primary-dk active:bg-primary-dk dark:active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 whitespace-nowrap">
                <span x-data="{ loading: false }" x-on:click="loading = true; setTimeout(() => loading = false, 1000)"
                    class="flex items-center justify-center w-full">
                    <span :class="{ 'invisible': loading }">
                        View Order Status
                    </span>
                    <span x-show="loading" class="absolute flex items-center justify-center">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
                        </svg>
                    </span>
                </span>
            </x-primary-button>
            <!-- Receive Order Button (Secondary) -->
            <x-primary-button wire:click="receiveProduct({{ $order->id }})"
                class="bg-green-500 py-1 px-3 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2">
                <span x-data="{ loading: false }" x-on:click="loading = true; setTimeout(() => loading = false, 1000)"
                    class="flex items-center justify-center w-full">
                    <span :class="{ 'invisible': loading }">
                        Receive Order
                    </span>
                    <span x-show="loading" class="absolute flex items-center justify-center">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
                        </svg>
                    </span>
                </span>
            </x-primary-button>
        </div>
    </div>
    <div class="flex flex-col md:flex-row justify-between mb-2 text-xs">
        <div class="flex flex-col md:flex-row gap-3 md:gap-8">
            <div class="flex items-center gap-x-2">
                <p class="text-gray-600">Order Date:</p>
                <p class="dark:text-gray-100 font-medium whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($order?->created_at)->format('m-d-Y') }}
                </p>
            </div>
            <div class=" flex items-center gap-x-2">
                <p class="text-gray-600">Email:</p>
                <p class="font-medium dark:text-gray-100">{{ $order->purchaseSupplier->supplier_email ?? 'N/A' }}</p>
            </div>
            <div class=" flex items-center gap-x-2">
                <p class="text-gray-600">Supplier:</p>
                <p class="font-medium dark:text-gray-100">{{ $order->purchaseSupplier->supplier_name ?? 'N/A' }}</p>
            </div>
            <div class="flex items-center gap-x-2">
                <p class="text-gray-600">Location:</p>
                <p class="dark:text-gray-100 font-medium">{{ $order->purchaseLocation->name ?? 'N/A' }}</p>
            </div>
            <!-- <div class="flex items-center gap-x-2">
                <p class="text-gray-600">Order Time:</p>
                <p class="dark:text-gray-100 font-medium">
                    {{ \Carbon\Carbon::parse($order?->created_at)->format('h:i A T') }}</p>
            </div> -->
        </div>
    </div>

    @if (!empty($order->tracking_link))
        <div class="mt-4 dark:bg-gray-800 bg-green-50 p-2 rounded-md border border-green-100">
            <p class="flex items-center text-green-600 dark:text-green-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-700" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ $order->note . ' '    }}
                <a href="{{ $order->tracking_link }}" target="_blank"
                    class="text-blue-500 text-sm dark:text-blue-400 underline">
                    {{__(' Track order') }}
                </a>
            </p>
        </div>
    @else
        <div class="mt-4 dark:bg-gray-800 bg-orange-50 p-2 rounded-md border border-orange-100">
            <p class="flex items-center text-orange-800 dark:text-orange-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-orange-700" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ $order->note ?? 'No Update available' }}
            </p>
        </div>
    @endif
</div>