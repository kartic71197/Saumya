<div>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
            <section class="w-full border-b-2 pb-4 mb-6">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Invoice report') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Review your audit trail and track all activities below.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        @if(auth()->user()->role_id == 1)
                            <div class="flex items-center gap-2">
                                <label class="font-semibold">Practices:</label>
                                <select wire:model.live="selectedOrganization"
                                        class="border border-gray-300 rounded-md p-2">
                                    <option value="">All Practices</option>
                                    @foreach($organizations as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- ROLE >= 2 → LOCATION DROPDOWN --}}
                        @if(auth()->user()->role_id >= 2)
                            <div class="flex items-center gap-2">
                                <label class="font-semibold">Location:</label>
                                <select wire:model.live="selectedLocation"
                                        class="border border-gray-300 rounded-md p-2">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </header>
            </section>
            <div class="text-xs">
                <livewire:tables.reports.invoice-list :organization-id="$selectedOrganization" />
            </div>
        </div>
    </div>
</div>