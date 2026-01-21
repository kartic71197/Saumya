<!-- Patient Modal (used for both add and edit) -->
<x-modal name="edit-payment-modal" width="w-100" height="h-auto" maxWidth="4xl">
    <div>
        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                    </path>
                </svg>
                {{ __('Edit Payment Information') }} - {{ $chartnumber }}
            </h2>
        </header>

        <form wire:submit.prevent="updatePayment">
            <div class="p-6">
                <!-- Payment Information Section -->
                <div class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Patient Copay -->
                        <div class="space-y-2">
                            <x-input-label for="pt-copay" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="text-red-500">*</span> Patient Copay
                            </x-input-label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <x-text-input id="pt-copay" wire:model.live="pt_copay" type="number" step="0.01"
                                    class="pl-7 mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00" required />
                            </div>
                            @error('pt_copay')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Insurance Paid -->
                        <div class="space-y-2">
                            <x-input-label for="paid" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="text-red-500">*</span> Insurance Paid
                            </x-input-label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <x-text-input id="paid" wire:model.live="paid" type="number" step="0.01"
                                    class="pl-7 mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00" required />
                            </div>
                            @error('paid')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Our Cost -->
                        <div class="space-y-2">
                            <x-input-label for="our_cost" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="text-red-500">*</span> Our Cost
                            </x-input-label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <x-text-input id="our_cost" wire:model.live="our_cost" type="number" step="0.01"
                                    class="pl-7 mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00" required />
                            </div>
                            @error('our_cost')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Profit Calculation Section -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Revenue -->
                        <div
                            class="bg-white dark:bg-gray-600 rounded-lg p-4 border border-gray-200 dark:border-gray-500">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Revenue</span>
                                <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                    ${{ number_format((float)($pt_copay ?? 0) + (float)($paid ?? 0), 2) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Patient Copay + Insurance Paid
                            </div>
                        </div>

                        <!-- Net Profit -->
                        <div
                            class="bg-white dark:bg-gray-600 rounded-lg p-4 border border-gray-200 dark:border-gray-500">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Net Profit</span>
                                @php
                                    $pt_copay = (float) ($pt_copay ?? 0);
                                    $paid = (float) ($paid ?? 0);
                                    $our_cost = (float) ($our_cost ?? 0);

                                    $profit = ($pt_copay + $paid) - $our_cost;
                                @endphp
                                <span
                                    class="text-lg font-semibold {{ $profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Total Revenue - Our Cost
                            </div>
                        </div>
                    </div>

                    <!-- Profit Margin -->
                    @if(($our_cost ?? 0) > 0)
                        <div
                            class="mt-4 bg-white dark:bg-gray-600 rounded-lg p-4 border border-gray-200 dark:border-gray-500">
                            @php
                                $denominator = (float)($pt_copay ?? 0) + (float)($paid ?? 0);
                                $profitMargin = $denominator > 0
                                    ? ($profit / $denominator) * 100
                                    : 0;
                            @endphp

                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Profit Margin</span>
                                <span
                                    class="text-lg font-semibold {{ $profitMargin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($profitMargin, 1) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-gradient-to-r {{ $profitMargin >= 0 ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} h-2 rounded-full transition-all duration-300"
                                    style="width: {{ min(abs($profitMargin), 100) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 rounded-b-lg flex items-center justify-end gap-4">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-payment-modal')"
                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button
                    class="px-6 py-2 min-w-32 flex justify-center items-center text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                    x-data="{ loading: false }"
                    x-on:click="loading = true; $wire.updatePayment().then(() => { loading = false; })"
                    x-bind:disabled="loading">
                    <!-- Button Text -->
                    <span x-show="!loading" class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        {{ __('Update Payment') }}
                    </span>

                    <!-- Loader (Spinner) -->
                    <span x-show="loading" class="flex justify-center items-center">
                        <svg class="animate-spin h-4 w-4 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
                        </svg>
                        Updating...
                    </span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>