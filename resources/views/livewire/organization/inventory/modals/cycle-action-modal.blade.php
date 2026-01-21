<x-modal name="cycle_action_modal" maxWidth="2xl">
    <div class="p-6 space-y-5">

        {{-- Modal Header --}}
        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-4">
            <div>
                <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                    {{ $productName ?? 'Product Name' }}
                </h3>
                @if($productCode)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Product Code: {{ $productCode }}
                    </p>
                @endif
            </div>
            <button wire:click="closeActionModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        {{-- Action Options --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 mt-3">
                Select Action
            </label>

            <div class="flex gap-6">
                <label
                    class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400">
                    <input type="radio" wire:model.live="selectedAction" value="reset"
                        class="w-4 h-4 text-orange-500 focus:ring-orange-400">
                    <span class="font-medium">Reset Product</span>
                </label>

                <label
                    class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400">
                    <input type="radio" wire:model.live="selectedAction" value="reject"
                        class="w-4 h-4 text-red-500 focus:ring-red-400">
                    <span class="font-medium">Reject Product</span>
                </label>

                <label
                    class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                    <input type="radio" wire:model.live="selectedAction" value="reassign"
                        class="w-4 h-4 text-indigo-500 focus:ring-indigo-400">
                    <span class="font-medium">Reassign Product</span>
                </label>
            </div>
        </div>

        {{-- Dynamic Fields --}}
        @if($selectedAction)
            <div class="mt-6 mb-4">
                {{-- RESET --}}
                @if($selectedAction === 'reset')
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                            This will reset the product to its initial state.
                        </p>
                        <input type="text" wire:model.defer="productCodeConfirm"
                            placeholder="Enter product code to confirm reset"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:text-white">

                        @error('productCodeConfirm')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- REJECT --}}
                @if($selectedAction === 'reject')
                    <div>
                        <p class="text-xs text-red-600 dark:text-red-400 mb-2">
                            ⚠️ Warning: This action cannot be undone.
                        </p>
                        <input type="text" wire:model.defer="productCodeConfirm"
                            placeholder="Enter product code to confirm rejection"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">

                        @error('productCodeConfirm')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- REASSIGN --}}
                @if($selectedAction === 'reassign')
                    <div>
                        <select wire:model.defer="selectedUser"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                            style="max-height: 150px; overflow-y: auto;">
                            <option value="">-- Choose a user --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedUser')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                            The product will be reassigned to the selected user.
                        </p>
                    </div>
                @endif
            </div>
        @endif


        {{-- Footer Actions --}}
        <div class="flex justify-end gap-2 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="closeActionModal"
                class="px-4 py-2 rounded bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors">
                Cancel
            </button>

            @if($selectedAction)
                <button wire:click="performCycleAction" class="px-4 py-2 rounded text-white transition-colors
                            {{ $selectedAction === 'reset' ? 'bg-orange-600 hover:bg-orange-700' : '' }}
                            {{ $selectedAction === 'reject' ? 'bg-red-600 hover:bg-red-700' : '' }}
                            {{ $selectedAction === 'reassign' ? 'bg-indigo-600 hover:bg-indigo-700' : '' }}">
                    @if($selectedAction === 'reset')
                        Reset
                    @elseif($selectedAction === 'reject')
                        Reject
                    @elseif($selectedAction === 'reassign')
                        Reassign
                    @endif
                </button>
            @endif
        </div>
    </div>
</x-modal>