<div class="max-w-10xl mx-auto px-4">
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
        <section class="w-full border-b-2 pb-4 mb-6">
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">

                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Price History Report') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('View your price history report below.') }}
                    </p>
                </div>

                {{-- SUPER ADMIN → PRACTICE FILTER --}}
                @if (auth()->user()->role_id == 1)
                    <div class="flex items-center gap-3 mb-4">
                        <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Practices:') }}
                        </label>
                        <select wire:model.live="selectedOrganization"
                            class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="">All Practices</option>
                            @foreach ($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

            </header>
        </section>
        <div class="text-xs">
            <livewire:tables.reports.price-history-table :organization-id="$selectedOrganization" :location-id="$selectedLocation"
                :wire:key="'priceHistory-report-'.$selectedOrganization.'-'.$selectedLocation" />
        </div>
    </div>
    <x-modal name="open-price-history-modal" width="w-100" height="h-auto" maxWidth="4xl" class="z-9999 fixed">

        {{-- Header --}}
        <header class="p-3 border-b border-gray-300 dark:border-gray-700 flex justify-between">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Cost History
            </h2>

            <button wire:click="closeHistoryModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                ✕
            </button>
        </header>

        {{-- Body --}}
        <div class="p-4 overflow-x-auto text-sm">
            <table class="min-w-full border">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        {{-- <th class="px-3 py-2 text-left">Price</th> --}}
                        <th class="px-3 py-2 text-left">Changed On</th>
                        <th class="px-3 py-2 text-left">Cost</th>
                        <th class="px-3 py-2 text-left">Changed By</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($historyRows as $row)
                        <tr class="border-t">
                            {{-- <td class="px-3 py-2">
                                {{ $row->price !== null ? '$' . number_format($row->price, 2) : '—' }}
                            </td> --}}

                            <td class="px-3 py-2">
                                {{ \Carbon\Carbon::parse($row->created_at)->format('m/d/Y H:i') }}
                            </td>

                            <td class="px-3 py-2 font-semibold">
                                {{ '$' . number_format($row->cost, 2) }}
                            </td>

                            <td class="px-3 py-2">
                                {{ $row->changed_by == 0 ? 'System' : $row->user->name ?? 'Unknown' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">
                                No history found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-modal>

</div>
