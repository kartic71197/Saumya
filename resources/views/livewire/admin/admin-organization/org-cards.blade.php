<div>
    <div class="max-w-8xl mx-auto px-4 ml-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md rounded-lg mb-6">
            <section class="w-full">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Practices') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Manage and configure all practices.') }}
                        </p>
                    </div>
                    <!-- Search Bar -->
                    <div class="relative w-full md:w-64 mt-4 md:mt-0">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="searchSettings" placeholder="Search Practice..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all">
                    </div>
                </header>
            </section>
        </div>

        <!-- Settings Grid -->
        <div id="settingsGrid" class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($org_data as $org)
                <div class="setting-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-2"
                    data-title="{{ strtolower($org['name']) }}">
                    <!-- Logo Section -->
                    <div
                        class="h-20 flex items-center justify-center relative overflow-hidden border-b border-gray-200 dark:border-gray-700">
                        @if ($org?->image)
                            <img src="{{ asset('storage/' . $org?->image) }}" alt="Practice\'s Logo"
                                class="w-auto h-12 object-cover">
                        @else
                        
                            <img src="https://static.vecteezy.com/system/resources/thumbnails/005/720/408/small_2x/crossed-image-icon-picture-not-available-delete-picture-symbol-free-vector.jpg" alt="Practice\'s Logo"
                                class="w-auto h-12 object-cover">
                        @endif
                    </div>

                    <!-- Content Section -->
                    <div class="py-3 px-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">{{ __($org['name']) }}</h3>
                            <p class="text-xs text-primary-dk font-medium">
                                {{ __($org->plan?->name) }}
                            </p>
                        </div>
                        <!-- Info Items -->
                        <div class="space-y-3 mb-5">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-500">Email</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $org['email'] ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M18 9V6M18 6V3M18 6H15M18 6H21M18.5 21C9.93959 21 3 14.0604 3 5.5C3 5.11378 3.01413 4.73086 3.04189 4.35173C3.07375 3.91662 3.08968 3.69907 3.2037 3.50103C3.29814 3.33701 3.4655 3.18146 3.63598 3.09925C3.84181 3 4.08188 3 4.56201 3H7.37932C7.78308 3 7.98496 3 8.15802 3.06645C8.31089 3.12515 8.44701 3.22049 8.55442 3.3441C8.67601 3.48403 8.745 3.67376 8.88299 4.05321L10.0491 7.26005C10.2096 7.70153 10.2899 7.92227 10.2763 8.1317C10.2643 8.31637 10.2012 8.49408 10.0942 8.64506C9.97286 8.81628 9.77145 8.93713 9.36863 9.17882L8 10C9.2019 12.6489 11.3501 14.7999 14 16L14.8212 14.6314C15.0629 14.2285 15.1837 14.0271 15.3549 13.9058C15.5059 13.7988 15.6836 13.7357 15.8683 13.7237C16.0777 13.7101 16.2985 13.7904 16.74 13.9509L19.9468 15.117C20.3262 15.255 20.516 15.324 20.6559 15.4456C20.7795 15.553 20.8749 15.6891 20.9335 15.842C21 16.015 21 16.2169 21 16.6207V19.438C21 19.9181 21 20.1582 20.9007 20.364C20.8185 20.5345 20.663 20.7019 20.499 20.7963C20.3009 20.9103 20.0834 20.9262 19.6483 20.9581C19.2691 20.9859 18.8862 21 18.5 21Z"
                                            stroke="#7d7d7d" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </g>
                                </svg>
                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-500">Phone</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $org['phone'] ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <button wire:click="showOrgDetails({{ $org['id'] }})" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            wire:target="showOrgDetails({{ $org['id'] }})"
                            class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-md rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 transition-all">

                            <span wire:loading.remove wire:target="showOrgDetails({{ $org['id'] }})">View Details</span>
                            <!-- Arrow Icon (hidden while loading) -->
                            <svg wire:loading.remove wire:target="showOrgDetails({{ $org['id'] }})"
                                class="w-4 h-4 ml-2 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M1 5h12m0 0L9 1m4 4L9 9" />
                            </svg>

                            <!-- Loading Spinner (shown while loading) -->
                            <svg wire:loading wire:target="showOrgDetails({{ $org['id'] }})"
                                class="w-4 h-4 ml-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>

                            <span wire:loading wire:target="showOrgDetails({{ $org['id'] }})">Loading..</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- JavaScript for Filtering -->
    <script>
        document.getElementById('searchSettings').addEventListener('input', function () {
            let filter = this.value.toLowerCase();
            let settingCards = document.querySelectorAll('.setting-card');

            settingCards.forEach(card => {
                let title = card.getAttribute('data-title');
                card.style.display = title.includes(filter) ? 'block' : 'none';
            });
        });
    </script>
</div>