<div>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
            <section class="w-full border-b-2 pb-4 mb-6">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Auditing report') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Review your audit trail and track all activities below.') }}
                        </p>
                    </div>

                    <!-- Organization Filter -->
                    <div class="flex items-center gap-3 mb-4">
                        @if(auth()->user()->role_id == 1)
                            <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                Practices:
                            </label>

                            <select wire:model.live="selectedOrganization"
                                    class="border border-gray-300 dark:border-gray-600 rounded-md p-2
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                <option value="">All Practices</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>

                        {{-- NORMAL USER → Show ONLY Locations --}}
                        @else
                            <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                Location:
                            </label>

                            <select wire:model.live="selectedLocation"
                                    class="border border-gray-300 dark:border-gray-600 rounded-md p-2
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Date Range Filters (optional - you can uncomment if needed) -->
                    {{-- <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <div class="flex flex-col">
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('From Date') }}
                            </label>
                            <input type="text" id="fromDate" wire:model.live="fromDate" placeholder="MM/DD/YYYY"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-md" />
                        </div>

                        <div class="flex flex-col">
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('To Date') }}
                            </label>
                            <input type="text" id="toDate" wire:model.live="toDate" placeholder="MM/DD/YYYY"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-md" />
                        </div>

                        <div class="flex items-end">
                            <button wire:click="resetDateFilters"
                                class="px-4 py-2 text-xs font-semibold text-white rounded-md border border-transparent bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition duration-150 ease-in-out dark:focus:ring-offset-gray-800">
                                {{ __('Reset') }}
                            </button>
                        </div>
                    </div> --}}
                </header>
            </section>
            <div class="text-xs">
                <livewire:tables.reports.audit-report-list
                    :organization-id="$selectedOrganization"
                    :location-id="$selectedLocation"
                    :wire:key="'audit-report-'.($selectedOrganization ?? $selectedLocation ?? 'all')"
                />
            </div>
        </div>
    </div>
</div>