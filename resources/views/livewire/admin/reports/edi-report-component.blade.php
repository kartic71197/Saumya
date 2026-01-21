<div>
    <section class="w-full border-b-2 pb-4 mb-6">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    EDI Data Report
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    View your EDI data report below.
                </p>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <label class="font-semibold text-sm text-gray-700 dark:text-gray-300">Practice:</label>
                <select wire:model.live="selectedOrganization"
                    class="border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary-md">
                    <option value="">All Practices</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
        </header>
    </section>

    <div class="text-xs">
        <livewire:admin.reports.edi-report-list :selected-organization="$selectedOrganization"
            :wire:key="'edi-report-'.$selectedOrganization" />
    </div>
</div>