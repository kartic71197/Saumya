<div>
    <section class="w-full border-b-2 pb-4 mb-6">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Inventory
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    View inventory across all Practices.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div>
                    <label class="text-gray-700 dark:text-gray-300">Practice:</label>
                    <select wire:model.live="selectedOrganization"
                        class="dark:bg-gray-800 dark:text-gray-100 mt-1 block w-full pl-3 pr-10 py-2 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Practices</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </header>
    </section>

    <div class="text-xs">
        <livewire:tables.admin.inventory-list
            :selectedOrganization="$selectedOrganization" />
    </div>
</div>
