<div><!-- Header Card -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm mb-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

            <!-- Title -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">My Counting Assignments</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Complete your assigned cycle counting tasks
                </p>
            </div>

            <!-- Cycle Dropdown -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Cycle:</label>
                <div class="relative w-full md:w-52">
                    <select wire:model.live="cycleFilter"
                        class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-gray-600 
                           rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-md 
                           focus:border-primary-md dark:bg-gray-800 dark:text-gray-100 transition-colors duration-200">
                        @foreach ($cycles as $cyc)
                            <option value="{{ $cyc->id }}">{{ $cyc->cycle_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>


    </div>

     <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm mb-4">
        <!-- Search Input -->
    <div class="mt-4 flex items-center gap-3 mb-6">
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Search by product name or code..."
                class="pl-10 pr-10 w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 
                       rounded-md shadow-sm focus:ring-2 focus:ring-primary-md focus:border-primary-md 
                       dark:bg-gray-800 dark:text-white placeholder-gray-400 transition-colors duration-200" />

            <!-- Clear Search -->
            @if ($search)
                <button wire:click="clearSearch" type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    ✕
                </button>
            @endif
        </div>
    </div>

    <!-- Search feedback below the row -->
    @if ($search)
        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Searching for: "{{ $search }}"
        </div>
    @endif

    <!-- Table -->
    @if ($showFutureCycles)
        @php
            $selectedCycle = $cycles->firstWhere('id', $cycleFilter);
            $daysUntil = $selectedCycle
                ? ceil(\Carbon\Carbon::parse($selectedCycle->schedule_date)->diffInDays(now(), false) * -1)
                : 0;
        @endphp
        <div class="bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded text-center">
            <div class="flex items-center justify-center gap-2">
                <!-- Lock Icon -->
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <p class="font-bold">Waiting to Start</p>
            </div>
            <p class="mt-2">This cycle starts in
                <span class="bg-orange-500 text-white px-2 py-1 rounded">{{ $daysUntil }} days</span>
            </p>
        </div>
    @else
        @if ($tasks->isEmpty())
            <div class="text-center py-10 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-4xl mb-3">📋</div>
                @if ($search)
                    <p class="text-lg font-medium">No matching products</p>
                    <p class="text-sm mt-1">No products match your search: "{{ $search }}"</p>
                @else
                    <p class="text-lg font-medium">No assignments assigned</p>
                    <p class="text-sm mt-1">You don't have any counting task for the selected filters.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Product Code
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Product
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Batch/Lot #
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Expiry
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Location
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Counted Qty
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Category
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($tasks as $task)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    {{ $task->product->product_code ?? '-' }}
                                </td>
                                <td class="px-4 py-4 max-w-xs text-sm text-gray-900 dark:text-gray-100">
                                    <div class="font-medium break-words">
                                        {{ $task->product->product_name ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $task->batch_number ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $task->expiry_date ? \Carbon\Carbon::parse($task->expiry_date)->format('d-m-Y') : '-' }}
                                </td>
                            <td class="px-4 py-4 whitespace-nowrap text-xs text-center text-gray-600 dark:text-gray-300">
                                    {{ $task->cycle->location->name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <input type="number" wire:model.defer="taskUpdates.{{ $task->id }}"
                                        wire:key="task-{{ $task->id }}" min="0" placeholder="Enter count"
                                    onfocus="this.placeholder=''" onblur="this.placeholder='Enter Count'" class="w-24 px-3 py-1 border rounded text-center text-sm 
                                                   dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700
                                                   placeholder-gray-400 dark:placeholder-gray-500
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        {{ $task->product->category->category_name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <button wire:click="handleUpdateTask({{ $task->id }})"
                                        class="px-3 py-1 text-white bg-blue-600 rounded-md hover:bg-blue-700 text-xs
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                                        Save
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing
                    <span class="font-medium">{{ $tasks->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $tasks->lastItem() }}</span>
                    of
                    <span class="font-medium">{{ $tasks->total() }}</span>
                    results
                </div>

                <div>
                    {{ $tasks->links() }}
                </div>
            </div>
        @endif
    @endif

    @if ($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
                </div>

                <!-- Modal panel -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg px-6 pt-6 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal Header -->
                    <div class="pb-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            ⚠️ {{ __('Variation Detected') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('There is a variation in Counted Qty') }}
                            <strong>({{ $taskCount }})</strong>
                            {{ __('and Expected Qty.') }}
                        </p>
                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Are you sure you want to continue with this count?') }}
                        </p>
                    </div>

                    <!-- Form -->
                    <form wire:submit.prevent="saveConfirmedTask">
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="note" :value="__('Note (optional)')" />
                                <textarea id="note" wire:model.defer="note" rows="3"
                                    placeholder="{{ __('Add a reason for the variation...') }}" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                                               dark:bg-gray-700 dark:text-white
                                               focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2"></textarea>
                                @error('note')
                                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <x-secondary-button type="button" wire:click="$set('showConfirmModal', false)"
                                class="px-4 py-2">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-primary-button type="submit" class="px-4 py-2">
                                {{ __('Save Count') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>