<div class="max-w-10xl mx-auto px-4">
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
        <section class="w-full border-b-2 pb-4 mb-6">
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">

    <div>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Picking report') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('View your picking list below.') }}
        </p>
    </div>

    {{-- SUPER ADMIN → PRACTICE FILTER --}}
    @if(auth()->user()->role_id == 1)
        <div class="flex items-center gap-3 mb-4">
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
        <div class="flex items-center gap-3 mb-4">
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

</header>
        </section>
        <div class="text-xs">
           <livewire:tables.reports.picking-list 
    :organization-id="$selectedOrganization"
    :location-id="$selectedLocation"
    :wire:key="'picking-report-'.$selectedOrganization.'-'.$selectedLocation" 
/>

        </div>
    </div>
</div>