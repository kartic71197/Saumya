<div>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
            <section class="w-full border-b-2 pb-4 mb-6">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Inventory adjust report') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('View your Inventory adjustments list below.') }}
                        </p>
                    </div>

                    <!-- Dynamic Filter -->
                    <div class="flex items-center gap-3 mb-4">
                        @if(auth()->user()->role_id == 1)
                            <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Practices:') }}
                            </label>
                            <select wire:model.live="selectedOrganization" class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-md">
                                <option value="">{{ __('All Practices') }}</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Location:') }}
                            </label>
                            <select wire:model.live="selectedLocation" class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-md">
                                <option value="">{{ __('All Locations') }}</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </header>
            </section>

            <div class="text-xs">
                <livewire:tables.reports.inventory-adjust-report 
                    :organization-id="$selectedOrganization" 
                    :location-id="$selectedLocation ?? null"
                    :wire:key="'inventory-adjust-report-'.($selectedOrganization ?? $selectedLocation ?? 'all')" />
            </div>
        </div>
    </div>
</div>
