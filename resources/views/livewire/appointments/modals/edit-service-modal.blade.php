<x-modal name="edit-service-modal" maxWidth="2xl" wire:close="resetServiceForm">
    <div
        class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 p-6 border-b border-indigo-100 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit Service</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update service details</p>
    </div>

    <form wire:submit.prevent="updateService">
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Service Name</label>
                <x-text-input wire:model="editServiceName" class="w-full" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Duration (min)
                    </label>

                    <select wire:model="editServiceDuration"
                        class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700
               focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select duration</option>

                        @for ($i = 15; $i <= 240; $i += 15)
                            <option value="{{ $i }}">{{ $i }} min</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Price ($)</label>
                    <x-text-input wire:model="editServicePrice" type="number" step="0.01" class="w-full" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                <textarea wire:model="editServiceDescription" rows="3"
                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tags (Hold Ctrl/Cmd for
                    multiple)</label>
                <select wire:model="editServiceTags" multiple
                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                    size="4">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div
            class="flex justify-end gap-3 p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
            <x-secondary-button type="button" wire:click="$dispatch('close-modal', 'edit-service-modal')">
                Cancel
            </x-secondary-button>

            <x-primary-button type="submit"
                class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">
                Update Service
            </x-primary-button>
        </div>
    </form>
</x-modal>
