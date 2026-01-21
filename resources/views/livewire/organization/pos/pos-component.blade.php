<div class="max-w-10xl mx-auto">
    <div class="px-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <section class="w-full border-b-2 mb-2">
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3 mb-2">
                <div>
                    {{-- <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Sales') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Review your sales and track all previous ones.') }}
                    </p> --}}
                </div>
                {{-- SUPER ADMIN (role_id == 1) → PRACTICE FILTER --}}
                @if(auth()->user()->role_id == 1)
                    <div class="flex items-center gap-3">
                        <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Practices:') }}
                        </label>
                        <select wire:model.live="selectedOrganization"
                            class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="">All Practices</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                {{-- ROLE >= 2 → LOCATION FILTER --}}
                @if(auth()->user()->role_id >= 2)
                    <div class="flex items-center gap-3">
                        <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Location:') }}
                        </label>
                        <select wire:model.live="selectedLocation"
                            class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-sm">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                {{-- <x-primary-button>
                    <a href="{{ route('pos.sales.create') }}">+ New Sale</a>
                </x-primary-button> --}}
            </header>
        </section>
        <div class="text-xs">
            @livewire('organization.pos.pos-list', [
                'organizationId' => $selectedOrganization,
                'locationId' => $selectedLocation
            ])
        </div>
    </div>


    <x-modal name="pos-view-modal" maxWidth="4xl">
        <div class="bg-white dark:bg-gray-800 text-xs">
            <!-- Header -->
            <div
                class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 px-4 py-3 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-white flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Sale Receipt
                        </h2>

                        @if ($this->selectedPos)
                            <p class="text-blue-100 text-[10px] mt-0.5">
                                Invoice #{{ str_pad($this->selectedPos->id, 6, '0', STR_PAD_LEFT) }}
                            </p>
                        @endif
                    </div>

                    {{-- <button x-on:click="$dispatch('close-modal', { name: 'pos-view-modal' })"
                        class="text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button> --}}
                </div>
            </div>

            <!-- Content -->
            <div class="p-4 max-h-[70vh] overflow-y-auto">
                @if ($this->selectedPos)

                    <!-- Business & Customer Info -->
                    <div class="grid md:grid-cols-2 gap-4 mb-4">

                        <!-- Business Details -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">
                                Business Information
                            </h3>

                            <div class="space-y-1">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-gray-400 mr-2 mt-[2px]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Organization</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->selectedPos->organization->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-gray-400 mr-2 mt-[2px]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Location</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->selectedPos->location->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer & Payment -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">
                                Customer & Payment
                            </h3>

                            <div class="space-y-1">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-gray-400 mr-2 mt-[2px]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Customer</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->selectedPos->customer->customer_name ?? 'Walk-in Customer' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-gray-400 mr-2 mt-[2px]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Payment Method</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ ucfirst($this->selectedPos->payment_method) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-gray-400 mr-2 mt-[2px]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Sale Date</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($this->selectedPos->sale_date)->format('d M Y, h:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Items Table -->
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Purchased Items
                        </h3>

                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th class="px-3 py-2 text-left font-semibold">Product</th>
                                        <th class="px-3 py-2 text-center font-semibold">Qty</th>
                                        <th class="px-3 py-2 text-right font-semibold">Unit Price</th>
                                        <th class="px-3 py-2 text-right font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach ($this->selectedItems as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-3 py-2">{{ $item->product->product_name ?? '-' }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="px-2 py-[2px] text-[10px] bg-blue-100 dark:bg-blue-900/50 rounded">
                                                    {{ $item->qty }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                ${{ number_format($item->price, 2) }}
                                            </td>
                                            <td class="px-3 py-2 font-semibold text-right">
                                                ${{ number_format($item->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                        <h3 class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">
                            Payment Summary
                        </h3>

                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                <span class="font-semibold">${{ number_format($this->selectedPos->total_amount, 2) }}</span>
                            </div>

                            <div class="border-t dark:border-gray-600 pt-1 mt-1">
                                <div class="flex justify-between text-sm font-semibold">
                                    <span>Total Amount</span>
                                    <span class="text-blue-600 dark:text-blue-400">
                                        ${{ number_format($this->selectedPos->total_amount, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-600 dark:text-gray-400">Amount Paid</span>
                                <span class="text-green-600 dark:text-green-400 font-semibold">
                                    ${{ number_format($this->selectedPos->paid_amount, 2) }}
                                </span>
                            </div>

                            @if ($this->selectedPos->change_amount > 0)
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="text-gray-600 dark:text-gray-400">Change</span>
                                    <span class="font-bold text-yellow-700 dark:text-yellow-400">
                                        ${{ number_format($this->selectedPos->change_amount, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                @endif
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-b-lg border-t">
                <div class="flex justify-between items-center">
                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                        @if ($this->selectedPos)
                            Created by {{ $this->selectedPos->creator->name ?? 'System' }}
                        @endif
                    </div>

                    {{-- <x-secondary-button class="!text-xs !px-3 !py-1"
                        x-on:click="$dispatch('close-modal', { name: 'pos-view-modal' })">
                        Close
                    </x-secondary-button> --}}
                </div>
            </div>

        </div>
    </x-modal>


</div>