<div>

    {{-- ================= HEADER ================= --}}
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 shadow-lg rounded-2xl p-8 border border-indigo-100 dark:border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Appointment Categories
                        </h2>
                        <p class="text-base text-gray-600 dark:text-gray-300">
                            Organize your services with categories, pricing, and custom tags
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-secondary-button wire:click="$dispatch('open-modal', 'add-tag-modal')"
                            class="inline-flex items-center px-5 py-2.5 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Add Tag
                        </x-secondary-button>

                        <x-primary-button wire:click="$dispatch('open-modal', 'add-category-modal')"
                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Category
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= LIST ================= --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="space-y-6">

            @forelse($categories as $category)
                <div
                    class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-2xl transition-shadow duration-300">

                    {{-- Category Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-white mb-2">
                                    {{ $category->name }}
                                </h3>
                                <p class="text-indigo-100">
                                    {{ $category->description }}
                                </p>
                            </div>

                            <x-secondary-button wire:click="editCategory({{ $category->id }})"
                                class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30 rounded-lg transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </x-secondary-button>
                        </div>
                    </div>

                    {{-- Services --}}
                    @if ($category->services->count())
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed">
                                    <colgroup>
                                        <col class="w-[20%]">
                                        <col class="w-[25%]">
                                        <col class="w-[12%]">
                                        <col class="w-[12%]">
                                        <col class="w-[20%]">
                                        <col class="w-[11%]">
                                    </colgroup>
                                    <thead>
                                        <tr class="border-b-2 border-gray-200 dark:border-gray-700">
                                            <th
                                                class="pb-4 px-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                Service Name</th>
                                            <th
                                                class="pb-4 px-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                Description</th>
                                            <th
                                                class="pb-4 px-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                Duration</th>
                                            <th
                                                class="pb-4 px-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                Price</th>
                                            <th
                                                class="pb-4 px-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                Tags</th>
                                            <th
                                                class="pb-4 px-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($category->services as $service)
                                            <tr
                                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                                <td class="py-4 px-2 text-gray-900 dark:text-gray-100 font-medium">
                                                    <div class="whitespace-nowrap overflow-hidden text-ellipsis">
                                                        {{ $service->name }}
                                                    </div>
                                                </td>
                                                <td class="py-4 px-2 text-gray-600 dark:text-gray-400 text-sm">
                                                    <div class="line-clamp-2">
                                                        {{ $service->description }}
                                                    </div>
                                                </td>
                                                <td class="py-4 px-2">
                                                    <div class="flex justify-center">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 whitespace-nowrap">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ $service->duration }} min
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-2">
                                                    <div class="flex justify-center">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 whitespace-nowrap">
                                                            ${{ number_format($service->price, 2) }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-2">
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach ($service->tags as $tag)
                                                            <span
                                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-sm whitespace-nowrap">
                                                                {{ $tag->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="py-4 px-2">
                                                    <div class="flex justify-center">
                                                        <button wire:click="editService({{ $service->id }})"
                                                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors duration-200">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            Edit
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">No services added yet</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-6" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No categories yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first appointment
                        category</p>
                    <x-primary-button wire:click="$dispatch('open-modal', 'add-category-modal')"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Create First Category
                    </x-primary-button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ================= CATEGORY MODAL ================= --}}
    @include('livewire.appointments.modals.add-category-modal')

    {{-- ================= EDIT SERVICE MODAL ================= --}}
    @include('livewire.appointments.modals.edit-service-modal')

    {{-- ================= TAG MODAL ================= --}}
    @include('livewire.appointments.modals.add-tag-modal')



</div>
