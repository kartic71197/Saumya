<x-app-layout>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
            <section class="w-full border-b-2 pb-4 mb-6">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Product Report') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Manage products inventory and update the alert and par quantity.') }}
                        </p>
                    </div>
                    
                    <!-- Filters - Reorganized structure -->
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-3 w-full md:w-auto">
                        <!-- Date filters in a row -->
                        <div class="flex flex-col sm:flex-row gap-2 w-full">
                            <div class="w-full sm:w-auto">
                                <label for="start_date" class="text-sm text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input type="date" id="start_date"
                                    class="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    onchange="dispatchFilterChanged()">
                            </div>

                            <div class="w-full sm:w-auto">
                                <label for="end_date" class="text-sm text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                <input type="date" id="end_date"
                                    class="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    onchange="dispatchFilterChanged()">
                            </div>
                        </div>
                        
                        <!-- Practice/Location dropdown -->
                        @if (auth()->user()->role_id == 1)
                            <div class="w-full sm:w-auto">
                                <label for="organization" class="text-sm text-gray-700 dark:text-gray-300 mb-1">Practice</label>
                                <select id="organization"
                                    class="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    onchange="dispatchFilterChanged()">
                                    <option value="">All Practices</option>
                                    @foreach($organizations as $organization)
                                        <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($locations)
                            <div class="w-full sm:w-auto">
                                <label for="location" class="text-sm text-gray-700 dark:text-gray-300 mb-1">Location</label>
                                <select id="location"
                                    class="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    @if(auth()->user()->role_id == 3) disabled @endif onchange="dispatchFilterChanged()">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location?->id }}">{{ $location?->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </header>
            </section>

            <div class="text-xs">
                <livewire:tables.reports.product-report />
            </div>
            {{-- Including the modal component to show purchase order details --}}
            <livewire:reports.product-report-details-modal />

        </div>

        <script>
            function dispatchFilterChanged() {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const location = document.getElementById('location')?.value || '';
                const organization = document.getElementById('organization')?.value || '';

                
                Livewire.dispatch('productLocationChanged', {
                    location: location,
                    start_date: startDate,
                    end_date: endDate,
                    organization: organization
                });
            }
        </script>
    </div>
</x-app-layout>