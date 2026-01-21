<div x-data="cycleCountData()" x-init="init()">
    <div class="py-6">
        <div class="max-w-screen-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="w-full">
                    <section class="w-full border-b-2 py-2">
                        <header
                            class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Manage Cycle Counts') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Review and manage your current inventory through cycle counts.') }}
                                </p>
                            </div>
                            <!-- location dropdown -->
                            <div class="flex items-center justify-center gap-3">
                                <div class="dark:text-gray-100">Location:</div>
                                <select wire:model.live="selectedLocation"
                                    class="dark:bg-gray-800 dark:text-gray-100 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">All Locations</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                                <button @click="openModal()"
                                    class="min-w-52 flex justify-center items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    {{ __('+ New Cycle Count') }}
                                </button>
                            </div>
                        </header>
                    </section>
                </div>

                <!-- Flash Messages -->
                <div x-show="flashMessage.show" x-transition
                    x-bind:class="flashMessage.type === 'success' ? 'bg-green-100 border-green-400 text-green-700' :
                        'bg-red-100 border-red-400 text-red-700'"
                    class="mt-4 p-4 border rounded" style="display: none;">
                    <span x-text="flashMessage.message"></span>
                </div>

                @if (session()->has('success'))
                    <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Cycle Counts List -->
                <div class="text-xs mt-3">
                    <livewire:tables.inventory.cycle-list :selectedLocation="$selectedLocation" />
                </div>
            </div>
        </div>
    </div>

    <!-- Cycle Count Modal -->
    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div @click.away="closeModal()" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-4xl"
                style="max-height: 80vh;">

                <div class="p-6">
                    <div class="flex justify-between items-center mb-1">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Create Cycle Counts') }}
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Select a location first, then manage users and categories.
                            </p>
                        </div>
                        <!-- Location Selection -->
                        <div class="min-w-64">
                            {{-- <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Location</label> --}}
                            <select @change="handleLocationChange($event.target.value)" x-model="selectedLocation"
                                wire:model="location_id"
                                class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">{{ __('Select a location...') }}</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <div class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- ADD THIS ERROR MESSAGE HERE -->
                @error('assignments')
                    <div class="bg-red-50 border border-red-200 rounded-md p-2 mb-2">
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    </div>
                @enderror

                    <div class="p-6 overflow-y-auto flex-1" style="max-height: 60vh;">
                        <template x-if="selectedLocation">
                            <div>

                                <!-- Cycle Name and Description Section -->
                                <div class="mb-4 space-y-2">
                                    <!-- Cycle Code -->
                                    <div>
    <label class="font-medium text-sm text-gray-700 dark:text-gray-300">Cycle Code:</label> 
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $cycle_code }}
                                        </span>
                                    </div>

                                    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Cycle Name (optional)
        </label>
        <input type="text"
               wire:model="cycle_name"
               placeholder="Enter cycle name (e.g., Jan Cycle)"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 p-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>


                                    <!-- Description -->
                                    {{-- <div class="relative mb-4">
                                        <textarea x-data="{ focused: false }" x-on:focus="focused = true" x-on:blur="focused = $el.value !== ''"
                                            x-bind:placeholder="focused ? '' : 'Enter description...'" wire:model="description"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 p-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 resize-none h-12"
                                            rows="3"></textarea>
                                    </div> --}}
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Schedule Date
                                        </label>
    <input type="date"
           wire:model="schedule_date"
                                            min="{{ now()->toDateString() }}"
                                            class="mt-1 block w-52 border border-gray-300 dark:border-gray-700 
                  dark:bg-gray-900 dark:text-gray-300 
                  focus:border-indigo-500 focus:ring-indigo-500 
                  rounded-lg shadow-sm text-sm px-3 py-2" />
                                        @error('schedule_date')
        <div class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> 
                                        @enderror
                                    </div>



                                </div>


                                {{-- <div class="flex justify-between items-center gap-4">
                                    <div>
                                        <input type="text"
                                            @input.debounce.300ms="handleSearchChange($event.target.value)"
                                            x-model="search" placeholder="Search products..."
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                                    </div>
                                    <div>
                                        <select @change="handleCategoryChange($event.target.value)"
                                            x-model="selectedCategory"
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->category_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div> --}}

                                <div class="space-y-3">


                                    <!-- User Assignment Section -->
                                    <div class="bg-blue-50 dark:bg-gray-800 rounded-xl p-6 mt-6">
                                        <div class="flex justify-between items-center mb-6">
                                            <h3
                                                class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                {{ __('Manage Assignments') }}
                                            </h3>
                                            <button type="button" wire:click="addRow"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                {{ __('Add More Users') }}
                                            </button>
                                        </div>

                                        <!-- Assignment Rows -->
                                        <div class="space-y-4">
                                            @foreach ($assignments as $index => $row)
                                                <div
                                                    class="bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-center gap-4">
                                                        <!-- User -->
                                                        <div class="flex-1">
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                                {{ __('User*') }}
                                                            </label>
                                                            <div class="relative">
                                                                <select
                                                                    wire:model="assignments.{{ $index }}.user_id"
                                                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
               dark:bg-gray-600 dark:text-white 
               focus:border-blue-500 focus:ring-blue-500
               relative z-50"
                                                                    required>
                                                                    <option value="">{{ __('Select User') }}
                                                                    </option>
                                                                    @foreach ($users as $user)
                                                                        <option value="{{ $user->id }}">
                                                                            {{ $user->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="min-h-[20px]">
                                                                @error("assignments.$index.user_id")
                                                                    <span
                                                                        class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                                                @enderror
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Category -->
                                                        <div class="flex-1">
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                                {{ __('Category*') }}
                                                            </label>
                                                            <select
                                                                wire:model.lazy="assignments.{{ $index }}.category_id"
                                                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
               dark:bg-gray-600 dark:text-white 
               focus:border-blue-500 focus:ring-blue-500
               relative z-50"
                                                                required>
                                                                <option value="">
                                                                    {{ __('Select Category') }}
                                                                </option>
                                                                @foreach ($this->getAvailableCategories($index) as $category)
                                                                    <option value="{{ $category->id }}">
                                                                        {{ $category->category_name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="min-h-[20px]">
                                                            @error("assignments.$index.category_id")
                                                                <span
                                                                    class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                                            @enderror
                                                            </div>
                                                        </div>
                                                        <!-- Subcategory (reactive) -->
{{-- @if(!empty($assignments[$index]['category_id'])) --}}
    <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('Subcategory*') }}
        </label>
        <select
            wire:model.lazy="assignments.{{ $index }}.subcategory_id"
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 
                   dark:bg-gray-600 dark:text-white 
                   focus:border-blue-500 focus:ring-blue-500
                   relative z-50">
            <option value="">{{ __('Select Subcategory') }}</option>
            @foreach ($this->getSubcategories($assignments[$index]['category_id'], $index) as $sub)
                <option value="{{ $sub->id }}">{{ $sub->subcategory }}</option>
            @endforeach
        </select>
        <div class="min-h-[20px]">
        @error("assignments.$index.subcategory_id")
            <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
        @enderror
        </div>
    </div>
{{-- @endif --}}



                                                        <!-- Remove Button -->
                                                        @if ($index > 0)
                                                            <button type="button"
                                                                wire:click="removeRow({{ $index }})"
                                                                class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors mt-6">
                                                                <svg class="w-5 h-5" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>



                                    <!-- Existing Cycles Table -->
                                    {{-- <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                    Existing Cycles for
                                                    {{ $locations->firstWhere('id', $selectedLocation)->name ?? 'Selected Location' }}
                                                </h3>
                                            </div>

                                            <div class="overflow-x-auto">
                                                <table class="w-full text-sm">
                                                    <thead class="bg-gray-50 dark:bg-gray-600">
                                                        <tr>
                                                            <th
                                                                class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">
                                                                Cycle Name</th>

                                                            <th
                                                                class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">
                                                                Created By</th>
                                                            <th
                                                                class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">
                                                                Status</th>
                                                            <th
                                                                class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">
                                                                Created On</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                                        @forelse ($existingCycles as $cycle)
                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                                                     {{ $cycle['cycle_name'] ?? 'N/A' }}
                                                                </td>
                                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ $cycle['user']['name'] ?? 'N/A' }}
                        </td>
                                                                <td class="px-4 py-3">
                                                                    <span
                                                                        class="px-2 py-1 text-xs rounded-full 
                    @if ($cycle['status'] === 'completed') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                    @elseif($cycle['status'] === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200
                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 @endif">
                                                                        {{ ucfirst($cycle['status']) }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                                                    {{ \Carbon\Carbon::parse($cycle['created_at'])->format('d-m-Y') }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5"
                                                                    class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                                                    No existing cycles found for this location.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>
                               
                            </div> --}}
                                    {{-- </div> --}}
                        </template>
                        <template x-if="!selectedLocation">
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 48 48">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12l-1.586-1.586A2 2 0 0017 10H7a2 2 0 00-2 2v20a2 2 0 002 2h10a2 2 0 001.414-.586L20 32m0-20v20m0-20l1.586-1.586A2 2 0 0123 10h10a2 2 0 012 2v20a2 2 0 01-2 2H23a2 2 0 01-1.414-.586L20 32" />
                                </svg>
                                <p class="mt-2 text-lg font-medium">Select a location to view products
                                </p>
                                <p class="mt-1 text-sm">Choose a location from the dropdown above to
                                    start creating cycle
                                    counts.</p>
                            </div>
                        </template>
                    </div>



                    <!-- Replace the entire template x-if="selectedLocation" footer section -->
                    <template x-if="selectedLocation">
    <div class="flex justify-end items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-4 sticky bottom-0">
                            <div class="flex gap-2 w-full justify-end">
                                <button @click="closeModal()"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                    Cancel
                                </button>
            <button type="button" wire:click="submitCycle"
                wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50">
                                    <span wire:loading.remove>Create Cycle</span>
                                    <span wire:loading>Processing...</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>



                {{-- <template x-if="!selectedLocation">
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 48 48">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 12l-1.586-1.586A2 2 0 0017 10H7a2 2 0 00-2 2v20a2 2 0 002 2h10a2 2 0 001.414-.586L20 32m0-20v20m0-20l1.586-1.586A2 2 0 0123 10h10a2 2 0 012 2v20a2 2 0 01-2 2H23a2 2 0 01-1.414-.586L20 32" />
                            </svg>
                            <p class="mt-2 text-lg font-medium">Select a location to view products
                            </p>
                            <p class="mt-1 text-sm">Choose a location from the dropdown above to
                                start creating cycle
                                counts.</p>
                        </div>
                    </template> --}}
            </div>
        </div>
    </div>
</div>
{{-- @livewire('organization.inventory.cycle-count-details', ['cycleId' => $cycle->id]) --}}

</div>

<script>
    function cycleCountData() {
        return {
            // State
            showModal: false,
            selectedLocation: '',
            search: '',
            selectedCategory: '',
            cycleName: '',
            countedQuantities: {},
            products: {
                data: [],
                total: 0,
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0
            },
            loading: false,
            processing: false,
            errors: {},
            flashMessage: {
                show: false,
                type: '',
                message: ''
            },

            // Initialize
            init() {
                console.log('Cycle Count component initialized');
                // Listen for Livewire events
                Livewire.on('closeCycleModal', () => {
                    console.log('Closing modal via Livewire event');
                    this.closeModal();
                });
            },


            // Modal methods
            openModal() {
                this.showModal = true;
                this.resetForm();
            },

            closeModal() {
                this.showModal = false;
                this.resetForm();
                @this.call('resetCycleForm');

            },

            resetForm() {
                this.selectedLocation = '';
                this.search = '';
                this.selectedCategory = '';
                this.cycleName = '';
                this.countedQuantities = {};
                this.products = {
                    data: [],
                    total: 0,
                    current_page: 1,
                    last_page: 1,
                    from: 0,
                    to: 0
                };
                this.errors = {};
            },

            // Event handlers
            handleLocationChange(locationId) {
                this.selectedLocation = locationId;
                this.search = '';
                this.selectedCategory = '';
                this.countedQuantities = {};
                this.products.current_page = 1;
                this.errors = {};

                if (locationId) {
                    // this.loadProducts();
                    // Emit to Livewire for tracking
                    @this.call('handleLocationChange', locationId);
                    @this.set('location_id', locationId);
                    console.log('Livewire location_id updated to:', locationId);
                }
            },

            handleSearchChange(searchTerm) {
                this.search = searchTerm;
                this.products.current_page = 1;
                // this.loadProducts();
                @this.call('handleSearchChange', searchTerm);
            },

            handleCategoryChange(categoryId) {
                this.selectedCategory = categoryId;
                this.products.current_page = 1;
                // this.loadProducts();
                @this.call('handleCategoryChange', categoryId);
            },

            changePage(page) {
                if (page >= 1 && page <= this.products.last_page) {
                    this.products.current_page = page;
                    // this.loadProducts();
                    @this.call('handlePageChange', page);
                }
            },



            // Submit cycle counts
            async submitCycleCounts() {
                this.errors = {};

                // Validate
                if (!this.selectedLocation) {
                    this.errors.location = 'Please select a location.';
                    return;
                }

                const validQuantities = Object.keys(this.countedQuantities).filter(key => {
                    const qty = this.countedQuantities[key];
                    return qty !== null && qty !== '' && !isNaN(qty);
                });

                if (validQuantities.length === 0) {
                    this.errors.quantities = 'Please enter counted quantities for at least one product.';
                    return;
                }

                this.processing = true;

                try {
                    const result = await @this.call('createBulkCycleCounts', {
                        locationId: this.selectedLocation,
                        cycleName: this.cycleName,
                        countedQuantities: this.countedQuantities
                    });

                    if (result.success) {
                        this.showFlashMessage('success', result.message);
                        this.closeModal();
                        // Refresh the cycle counts list
                        @this.call('$refresh');
                    } else {
                        this.errors.general = result.message;
                    }
                } catch (error) {
                    console.error('Error submitting cycle counts:', error);
                    this.errors.general = 'Failed to submit cycle counts. Please try again.';
                } finally {
                    this.processing = false;
                }
            },

            // Utility methods
            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit'
                });
            },

            getPaginationPages() {
                const current = this.products.current_page;
                const last = this.products.last_page;
                const pages = [];

                // Show first page
                if (current > 3) {
                    pages.push(1);
                    if (current > 4) pages.push('...');
                }

                // Show pages around current
                for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                    pages.push(i);
                }

                // Show last page
                if (current < last - 2) {
                    if (current < last - 3) pages.push('...');
                    pages.push(last);
                }

                return pages.filter(page => page !== '...' || pages.indexOf(page) === pages.lastIndexOf(page));
            },

            showFlashMessage(type, message) {
                this.flashMessage = {
                    show: true,
                    type,
                    message
                };
                setTimeout(() => {
                    this.flashMessage.show = false;
                }, 5000);
            }
        }
    }
    
</script>
