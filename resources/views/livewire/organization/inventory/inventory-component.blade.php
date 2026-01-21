<div>
    <section class="w-full border-b-2 pb-4 mb-6">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Manage Inventory') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Manage products inventory and update the alert and par quantity.') }}
                </p>
            </div>
            <!-- location dropdown -->
            <div class="flex items-center justify-center gap-3">
                <div class="flex items-center justify-center gap-2 border-r-2 border-gray-300 pr-3">
                    <input type="checkbox" wire:model.live="showSampleProducts">
                    <div class="dark:text-gray-100 text-nowrap">Show only Samples</div>
                </div>

                <div class="flex items-center justify-center gap-2 border-r-2 border-gray-300 pr-3">
                    <input type="checkbox" wire:model.live="showEmptyProducts">
                    <div class="dark:text-gray-100 text-nowrap">Show Empty Products</div>
                </div>

                @php
                    $user = auth()->user();
                    $role = $user->role;
                @endphp
                @if (!$role?->hasPermission('all_inventory') && $user->role_id > 2)

                @else
                    <div class="dark:text-gray-100">Location:</div>
                    <select wire:model.live="selectedLocation"
                        class="dark:bg-gray-800 dark:text-gray-100 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="0">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </header>
    </section>
    <div class="text-xs">
        {{-- <livewire:tables.organization.inventory.inventory-list /> --}}
        <livewire:tables.organization.inventory.inventory-list :selectedLocation="$selectedLocation"
            :highlightProductId="$highlightProductId" />
    </div>
    @include('livewire.organization.inventory.modals.add-product-to-cart')
    @include('livewire.organization.inventory.modals.update-alert-par-modal')
    <script>
        function openAlertParModal(stockId) {
            console.log('Opening modal for stock ID:', stockId);

            // Call the Livewire method
            @this.openAlertParModal(stockId);
        }
    </script>

    @if ($highlightProductId)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const row = document.querySelector('tr.bg-yellow-300');
                if (row) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        </script>
    @endif
</div>