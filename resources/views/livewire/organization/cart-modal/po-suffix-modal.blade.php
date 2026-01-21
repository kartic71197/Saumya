<x-modal name="po_suffix_modal" width="w-100" height="h-auto" maxWidth="4xl" wire:model="showPoSuffixModal">
    <header class="p-3 border-b border-gray-300 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-lg text-gray-700 dark:text-gray-100">
            {{ __('Review & Edit Purchase Orders') }}
        </h2>
        <div class="text-sm text-gray-500">
            {{ __('Optionally add custom suffixes before saving.') }}
        </div>
    </header>

    <form wire:submit.prevent="savePurchaseOrdersWithSuffixes">
        <div class="dark:bg-gray-800 bg-white overflow-x-auto">
            @if (!empty($generatedPurchaseOrders))
                <table class="w-full table-fixed">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr class="text-left text-gray-700 dark:text-gray-300">
                            <!-- <th class="p-3 border-b text-center">Select</th> -->
                            <th class="p-3 border-b w-36">Supplier</th>
                            <th class="p-3 border-b w-36">Auto Generated PO</th>
                            <th class="p-3 border-b w-40">Suffix (optional)</th>
                            <th class="p-3 border-b w-52">Final PO Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($generatedPurchaseOrders as $index => $po)
                            <tr class="text-gray-900 dark:text-gray-200">
                                <!-- <td class="p-3 border-b text-center">
                                                            <input type="checkbox" wire:model="selectedPOs" value="{{ $index }}"
                                                                   class="w-5 h-5 rounded border-gray-400 dark:border-gray-600">
                                                        </td> -->
                                <td class="p-3 border-b font-medium">
                                    {{ $po['supplier_name'] ?? 'Unknown Supplier' }}
                                </td>
                                <td class="p-3 border-b font-semibold">
                                    {{ $po['auto_number'] }}
                                </td>
                                <td class="p-3 border-b flex items-center gap-2">
                                    <input type="text"  wire:model.live.debounce.150ms="generatedPurchaseOrders.{{ $index }}.suffix"
    wire:blur="sanitizeSuffix({{ $index }})"
                                        maxlength="6" placeholder="e.g. ASH003"
                                        class="w-32 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm uppercase focus:ring focus:ring-blue-400">

                                    <!-- <button type="button" wire:click="updatePoPreview({{ $index }})"
                                                        class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                                        Save
                                                    </button> -->
                                </td>

                                <td class="p-3 border-b font-medium">
                                   {{ $po['auto_number'] }}
    @if(!empty($po['suffix']))
        -{{ strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $po['suffix'])) }}
    @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-6 text-gray-500 dark:text-gray-300">
                    {{ __('No purchase orders generated yet.') }}
                </div>
            @endif
        </div>

        {{-- FOOTER BUTTONS --}}
        <div class="flex justify-end gap-4 p-4 border-t border-gray-200 dark:border-gray-700">
            <x-secondary-button type="button" class="px-6 py-2 text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg"
                wire:click="cancelPoSuffixModal">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button class="..." wire:loading.attr="disabled" wire:target="savePurchaseOrdersWithSuffixes">
    <span wire:loading.remove wire:target="savePurchaseOrdersWithSuffixes">Save PO(s)</span>
    <span wire:loading wire:target="savePurchaseOrdersWithSuffixes">Processing...</span>
            </x-primary-button>
        </div>
    </form>
</x-modal>