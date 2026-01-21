<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">

    {{-- Closed Cycle --}}
    @if ($cycle->status === 'closed')
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            This cycle <strong>{{ $cycle->cycle_name }}</strong> was closed on
            {{ $cycle->ended_at?->format('d-M-Y H:i') }}
        </div>
    @endif

    {{-- Show management content if cycle is active --}}
    @if ($cycle->status !== 'closed')
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Cycle: {{ $cycle->cycle_name ?? 'Cycle #' . $cycle->id }} ({{ $cycle->cycle_code }})
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    {{ $cycle->location?->name ?? '-' }}
                </p>
            </div>

            <div class="flex justify-end">
                <button wire:click="openDeleteModal({{ $cycle->id }})" type="button"
                    class="px-6 py-2 bg-red-600 text-md text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-400 mb-6">Manage cycle count details and track product quantities</p>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Tasks Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-800 mr-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Total Counts</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_tasks'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks Card -->
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-green-100 dark:bg-green-800 mr-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-green-800 dark:text-green-300">Completed</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed_tasks'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks Card -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-yellow-100 dark:bg-yellow-800 mr-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_tasks'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-800 mr-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-purple-800 dark:text-purple-300">Progress</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['completion_percentage'] }}%</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Table - Show for both active and closed cycles --}}
    <div class="text-xs">
        @if ($cycle->cycleCounts->count() > 0)
            <livewire:tables.inventory.cycle-count-list :cycle_id="$cycle->id" :key="'cycle-count-list-' . $cycle->id" />
        @else
            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                No cycle counts found for this cycle.
                Please assign products to users first.
            </div>
        @endif
    </div>

    @if ($cycle->status !== 'closed' && $stats['completed_tasks'] == $stats['total_tasks'])
            <div class="mt-4 flex justify-end space-x-2">
                <!-- Reset Button -->
                <button wire:click="openRejectModal"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                    Reset
                </button>
                <!-- Approve Button - directly calls closeCycle without modal -->
                <button wire:click="closeCycle"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Approve
                </button>
            </div>
        @endif

    <!-- Include Modals -->
    @include('livewire.organization.inventory.modals.delete-cycle-modal')
    @include('livewire.organization.inventory.modals.reset-categories-modal')
    @include('livewire.organization.inventory.modals.cycle-action-modal')

</div>