<div class="p-3">
    @if (!$viewPurchaseOrder)
        <div class="bg-white py-3 px-6 rounded">
            <section class="w-full border-b-2 pb-4 mb-6 bg-white">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Manage Purchase orders') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Manage Purchase orders and update the invoices.') }}
                        </p>
                    </div>
                    
                    <!-- Organization Filter (Only for role id 1) -->
                    @if(auth()->user()->role_id == 1 && count($organizations) > 0)
                    <div class="flex items-center gap-4">
                        <div class="w-64">
                            <label for="organization-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Practices:') }}
                            </label>
                            <select 
                                id="organization-filter" 
                                wire:model.live="selectedOrganization"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring focus:ring-blue-400 bg-white dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">All Practices</option>
                                @foreach($organizations as $organization)
                                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </header>
            </section>
            <div class="text-xs">
                <livewire:tables.admin.purchase-orders-list/>
            </div>
        </div>
    @else
        <!-- View purchase order partial -->
        @include('livewire.admin.purchase.view-purchase-order')
    @endif
    @include('livewire.admin.purchase.modals.preview-modal')
    @include('livewire.admin.purchase.modals.upload-invoice-modal')
    @include('livewire.admin.purchase.modals.upload-ack-modal')
    @include('livewire.admin.purchase.modals.tracking-link-modal')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-url-in-new-tab', (event) => {
                window.open(event.url, '_blank');
            });
        });
    </script>
</div>