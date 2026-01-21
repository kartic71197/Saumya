<div>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
            <section class="w-full border-b-2 pb-4 mb-6">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Cycle Counts report') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('View your Cycle Counts list below.') }}
                        </p>
                    </div>

                    <!-- PRACTICES FOR SUPERADMIN -->
                    @if(auth()->user()->role_id == 1)
                        <div class="flex items-center gap-3 mb-4">
                            <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Practices:') }}
                            </label>
                            <select wire:model.live="selectedOrganization"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none">
                                <option value="">{{ __('All Practices') }}</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- LOCATION FILTER FOR ALL NON-ADMIN USERS -->
                    @if(auth()->user()->role_id >= 2)
                        <div class="flex items-center gap-3 mb-4">
                            <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Locations:') }}
                            </label>
                            <select wire:model.live="selectedLocation"
                                class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none">
                                <option value="">{{ __('All Locations') }}</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                </header>
            </section>
            <div class="text-xs">
                <livewire:tables.reports.cycle-counts-report 
    :organization-id="$selectedOrganization"
    :location-id="$selectedLocation"
    :wire:key="'cycle-counts-report-'.$selectedOrganization.'-'.$selectedLocation" />

            </div>
        </div>
    </div>
</div>