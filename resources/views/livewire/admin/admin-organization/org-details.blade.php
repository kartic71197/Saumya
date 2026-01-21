<div class="flex-1 flex overflow-hidden dark:bg-gray-900">
    <div class="flex-1 overflow-y-auto">
        <div class="w-full px-6 sm:px-8 py-3">
            <!-- Close Button and Title -->
            <div class="flex justify-between items-start">
                <button wire:click="closeOrgDetails" wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed" wire:target="closeOrgDetails"
                    class="text-gray-500 p-2 hover:bg-gray-300 dark:hover:bg-gray-700 rounded-lg transition-all flex items-center gap-1 border border-gray-200 dark:border-gray-700 mb-2 bg-white px-3">

                    <!-- Back Icon (hidden while loading) -->
                    <svg wire:loading.remove wire:target="closeOrgDetails"
                        class="w-6 h-6 text-gray-700 dark:text-gray-100" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    <!-- Loading Spinner (shown while loading) -->
                    <svg wire:loading wire:target="closeOrgDetails"
                        class="w-6 h-6 animate-spin text-gray-500 dark:text-gray-100" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Back
                </button>
            </div>


            <!-- Detail Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 items-stretch">
                <!-- Logo Section -->
                <button onclick="impersonateUser({{ $adminUser->id }})" title="Go to Practice's dashboard"
                    class="relative col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 flex items-center justify-center h-auto w-full cursor-pointer overflow-hidden group">

                    <!-- Logo / Content -->
                    <div class="transition-transform duration-300 transform group-hover:scale-110">
                        @if ($organization?->image)
                            <img src="{{ asset('storage/' . $organization?->image) }}" alt="Practice's Logo"
                                class="w-auto h-24 object-cover rounded-lg">
                        @else
                            <img src="https://static.vecteezy.com/system/resources/thumbnails/005/720/408/small_2x/crossed-image-icon-picture-not-available-delete-picture-symbol-free-vector.jpg"
                                alt="Practice's Logo" class="w-auto h-24 object-cover rounded-lg">
                        @endif
                    </div>

                    <!-- Visit Overlay -->
                    <span
                        class="absolute inset-0 bg-black/50 opacity-0 flex items-center justify-center text-white text-sm font-semibold rounded-lg transition-opacity duration-300 group-hover:opacity-100">
                        Visit
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4 ml-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.25 6.75L6.75 17.25M17.25 6.75H9.75M17.25 6.75V14.25" />
                        </svg>
                    </span>
                </button>



                <!-- Organization Info -->
                <div
                    class="col-span-2 relative bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 flex flex-col justify-between h-full">

                    <!-- Edit Icon Button -->
                    <button wire:click="startEdit({{ $organization['id'] }})"
                        class="absolute top-4 right-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
                        title="Edit Practice">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="w-5 h-5 text-blue-600 dark:text-blue-400">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M21.121 2.707a3 3 0 00-4.243 0L15.2 4.387 7.293 12.293a1 1 0 00-.263.464l-1 4a1 1 0 001.22 1.22l4-1a1 1 0 00.464-.263l7.849-7.849 1.738-1.737a3 3 0 000-4.243l-.22-.22zM18.293 4.12a1 1 0 011.414 0l.171.172a1 1 0 010 1.415l-.69.69-1.586-1.586.69-.69zM15.892 6.522l1.556 1.616-6.959 6.959-2.115.528.529-2.115 6.989-6.988zM4 8a1 1 0 011-1h5a1 1 0 100-2H5a3 3 0 00-3 3v11a3 3 0 003 3h11a3 3 0 003-3v-5a1 1 0 10-2 0v5a1 1 0 01-1 1H5a1 1 0 01-1-1V8z" />
                        </svg>
                    </button>

                    <!-- Organization Info Details -->
                    <div>
                        <h3
                            class="uppercase text-xl font-bold text-gray-600 dark:text-gray-400 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">

                            {{ $name ?? 'Practice\'s Name' }}
                        </h3>

                        <div class="flex flex-wrap">

                            <!-- Plan -->
                            <div class="px-6 py-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Plan Plan</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                    {{ $plan_name ?? 'N/A' }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div class="px-6 py-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Status</p>
                                <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">Active</p>
                            </div>

                            <!-- Email -->
                            <div class="px-6 py-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Email</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                    {{ $organization['email'] ?? 'N/A' }}
                                </p>
                            </div>

                            <!-- Phone -->
                            <div class="px-6 py-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Phone</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                    {{ $organization['phone'] ?? 'N/A' }}
                                </p>
                            </div>

                            <!-- Address -->
                            <div class="px-6 py-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Address</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                    {{ "{$organization['address']}, {$organization['city']}, {$organization['state']}, {$organization['country']} ({$organization['pin']})" }}
                                </p>
                            </div>


                            <div class="px-6 py-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Billing and shipping</p>
                                <a href="{{ route('billing.index', ['organization_id' => $organization['id']]) }}"
                                    class="text-sm font-bold text-blue-400 dark:text-gray-100 mt-1">
                                    <span class="text-blue-700 px-2 py-1 flex items-center gap-1 ">
                                        View details
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.25 6.75L6.75 17.25M17.25 6.75H9.75M17.25 6.75V14.25" />
                                        </svg>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            {{-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $user_count ?? 0 }}</p>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Locations</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $location_count ?? 0 }}</p>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Suppliers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $supplier_count ?? 0 }}</p>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Products</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $product_count ?? 0 }}</p>
                </div>
            </div> --}}

            <div class="bg-white  dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div
                    class="p-6 bg-white  dark:bg-gray-800 border-b border-gray-600 dark:border-gray-700 text-sm dark:text-gray-400">
                    <div class="text-xs">
                        @if(isset($organization))
                            <livewire:tables.admin.organization-users :organization="$organization->id" />
                        @else
                            <livewire:users-list />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>