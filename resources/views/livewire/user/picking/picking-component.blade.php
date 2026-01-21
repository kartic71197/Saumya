<div class="ml-3 px-6 py-2 bg-white dark:bg-gray-800 rounded-lg border">
    <div class="bg-white dark:bg-gray-800 pb-3">
        <!-- Tab Navigation - Improved spacing and visual feedback -->
        <div class="flex justify-between items-center dark:border-gray-700 px-2">
            <nav class="flex space-x-8 py-3">
                <button id="picking-btn" wire:click="switchTab('picking')"
                    class="tab-btn flex items-center justify-center pb-3 -mb-px text-gray-600 @if($isPicking) text-primary-md font-medium border-primary-md @endif border-b-2 hover:border-primary-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 8h10M9 12h10M9 16h10M5 8H5m0 4h.01m0 4H5" />
                    </svg>
                    Picking
                </button>
                {{-- <button id="batch-btn" wire:click="switchTab('batch')"
                    class="tab-btn flex items-center justify-center pb-3 -mb-px text-gray-600 @if(!$isPicking) text-primary-md font-medium border-primary-md @endif  border-b-2 hover:border-primary-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 12v1h4v-1m4 7H6a1 1 0 0 1-1-1V9h14v9a1 1 0 0 1-1 1ZM4 5h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                    </svg>
                    Batch (LOT#) Picking
                </button>--}}
            </nav> 
            <div class="flex items-center justify-center gap-3">
                <div class="flex items-center justify-center gap-2 border-r-2 border-gray-300 pr-3">
                    <input type="checkbox" wire:model.live="showSampleProducts">
                    <div class="dark:text-gray-100 text-nowrap">Show only Samples</div>  
                </div>
                @php
                    $user = auth()->user();
                    $role = $user->role;
                @endphp
                @if ($role?->hasPermission('all_picking') || $user->role_id <= 2)
                    <div class="dark:text-gray-100">Location</div>
                    <select wire:model.live="selectedLocation"
                        class="dark:text-gray-100 dark:bg-gray-800 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
    </div>
    <div class="pt-3 text-xs">
            <div class="{{ $isPicking ? '' : 'hidden' }}">
                <livewire:tables.organization.picking.picking-inventory-list wire:key="picking-list" />
            </div>
            <div class="{{ $isPicking ? 'hidden' : '' }}">
                <livewire:tables.organization.picking.batch-picking-list wire:key="batch-list" />
            </div>
    </div>
    @include('livewire.user.picking.modals.picking-product-modal')
    @include('livewire.user.picking.modals.biological-product-modal')
    @include('livewire.user.picking.modals.picking-batch-modal')
    <script>
        
        function openProductModal(id) {
            //console.log('openProductModal called with ID:', id);

            // Check if Livewire is available
            if (typeof Livewire !== 'undefined') {
                //console.log('Livewire is available, dispatching event...');
                Livewire.dispatch('openProductDetailBrowser', { id: id });
                //console.log('Event dispatched');
            } else {
                console.error('Livewire is not available!');
            }
        }
        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
                closeImageModal();
            }
        });
    </script>
</div>