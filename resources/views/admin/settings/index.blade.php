<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-4">
        <!-- Header Section -->
        <div class="p-2 sm:p-6 bg-white dark:bg-gray-800 shadow-md rounded-lg mb-6">
            <section class="w-full">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Settings') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Manage your Practice\'s settings, including plan plans, payment details, and renewal preferences.') }}
                        </p>
                    </div>
                    <div class="relative w-full md:w-64 mt-4 md:mt-0">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="searchReports" placeholder="Search settings..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all">
                    </div>
                </header>
            </section>
        </div>

        <!-- Settings List -->
        <div
            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

            <div id="settingsList" class="divide-y divide-gray-100 dark:divide-gray-700">
                @php
                    $settings = [
                        [
                            'route' => 'admin.plans.index',
                            'title' => 'Plans',
                            'desc' => 'Manage plans, pricing tiers, and billing cycles for all Practices.',
                            'icon' =>
                                'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
                            'category' => 'System',
                            'priority' => 'high',
                        ],
                        [
                            'route' => 'invoices.index',
                            'title' => 'Payments and Invoices',
                            'desc' => 'View and manage all invoices, including payment history and billing details.',
                            'icon' =>
                                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                            'category' => 'Billing',
                            'priority' => 'medium',
                        ],
                        [
                            'route' => 'admin.units.index',
                            'title' => 'Units',
                            'desc' =>
                                'Define standard measurement units and conversion rates used across the platform.',
                            'icon' =>
                                'M7.58209 8.96025 9.8136 11.1917l-1.61782 1.6178c-1.08305-.1811-2.23623.1454-3.07364.9828-1.1208 1.1208-1.32697 2.8069-.62368 4.1363.14842.2806.42122.474.73509.5213.06726.0101.1347.0133.20136.0098-.00351.0666-.00036.1341.00977.2013.04724.3139.24069.5867.52125.7351 1.32944.7033 3.01552.4971 4.13627-.6237.8375-.8374 1.1639-1.9906.9829-3.0736l4.8107-4.8108c1.0831.1811 2.2363-.1454 3.0737-.9828 1.1208-1.1208 1.3269-2.80688.6237-4.13632-.1485-.28056-.4213-.474-.7351-.52125-.0673-.01012-.1347-.01327-.2014-.00977.0035-.06666.0004-.13409-.0098-.20136-.0472-.31386-.2406-.58666-.5212-.73508-1.3294-.70329-3.0155-.49713-4.1363.62367-.8374.83741-1.1639 1.9906-.9828 3.07365l-1.7788 1.77875-2.23152-2.23148-1.41419 1.41424Zm1.31056-3.1394c-.04235-.32684-.24303-.61183-.53647-.76186l-1.98183-1.0133c-.38619-.19746-.85564-.12345-1.16234.18326l-.86321.8632c-.3067.3067-.38072.77616-.18326 1.16235l1.0133 1.98182c.15004.29345.43503.49412.76187.53647l1.1127.14418c.3076.03985.61628-.06528.8356-.28461l.86321-.8632c.21932-.21932.32446-.52801.2846-.83561l-.14417-1.1127ZM19.4448 16.4052l-3.1186-3.1187c-.7811-.781-2.0474-.781-2.8285 0l-.1719.172c-.7811.781-.7811 2.0474 0 2.8284l3.1186 3.1187c.7811.781 2.0474.781 2.8285 0l.1719-.172c.7811-.781.7811-2.0474 0-2.8284Z',
                            'category' => 'Products',
                            'priority' => 'medium',
                        ],
                        [
                            'route' => 'admin.users.index',
                            'title' => 'Users',
                            'desc' => 'Manage all users registered across the platform.',
                            'icon' =>
                                'M16.5 7.5a4.5 4.5 0 10-9 0 4.5 4.5 0 009 0zM21 20.25  v-1.5a4.5 4.5 0 00-4.5-4.5h-9a4.5 4.5 0 00-4.5 4.5v1.5',
                            'category' => 'System',
                            'priority' => 'medium',
                        ],

                        [
                            'route' => 'admin.supplier.index',
                            'title' => 'Suppliers',
                            'desc' =>
                                'Add, edit, and organize manufacturer information, contact details, and supplier relationships.',
                            'icon' =>
                                'M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z',
                            'category' => 'Organization',
                            'priority' => 'medium',
                        ],

                        // [
                        //     'route' => 'admin.field-reps.index',
                        //     'title' => 'Field Representatives',
                        //     'desc' => 'Manage field representatives linked to suppliers and organizations.',
                        //     'icon' =>
                        //         'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
                        //     'category' => 'Organization',
                        //     'priority' => 'medium',
                        // ],
                    ];
                @endphp
                @if (!auth()->user()->is_medical_rep)
                    @php
                        if (!auth()->user()->is_medical_rep) {
                            // $settings[] = [
                            //     'route' => 'billing_shipping.index',
                            //     'title' => 'Billing And Shipping',
                            //     'desc' => 'Manage billing addresses, contact information, and invoice recipient details for your organization.',
                            //     'icon' => 'M19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5ZM5 7H19V17H5V7ZM7 9H17V11H7V9ZM7 13H12V15H7V13Z',
                            //     'category' => 'System',
                            //     'priority' => 'high'
                            // ];
                            // $settings[] = [
                            //     'route' => 'organization.settings.cycle_counts',
                            //     'title' => 'Ship To',
                            //     'desc' => 'Configure shipping addresses, delivery locations, and distribution points for order fulfillment.',
                            //     'icon' => 'M2 12C2 11.4477 2.44772 11 3 11H7.58579L5.29289 8.70711C4.90237 8.31658 4.90237 7.68342 5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289L10.7071 11.2929C11.0976 11.6834 11.0976 12.3166 10.7071 12.7071L6.70711 16.7071C6.31658 17.0976 5.68342 17.0976 5.29289 16.7071C4.90237 16.3166 4.90237 15.6834 5.29289 15.2929L7.58579 13H3C2.44772 13 2 12.5523 2 12ZM13 3C13 2.44772 13.4477 2 14 2H20C21.1046 2 22 2.89543 22 4V20C22 21.1046 21.1046 22 20 22H14C13.4477 22 13 21.5523 13 21V3ZM15 4V20H20V4H15Z',
                            //     'category' => 'Organization',
                            //     'priority' => 'high'
                            // ];
                        }
                    @endphp
                @endif

                @foreach ($settings as $index => $setting)
                    <div class="setting-item group relative hover:bg-gradient-to-r hover:from-primary-lt/5 hover:to-primary-md/5 transition-all duration-300 cursor-pointer"
                        data-title="{{ strtolower($setting['title']) }}" data-priority="{{ $setting['priority'] }}">
                        <!-- Left border on hover -->
                        <div
                            class="absolute left-0 top-0 h-full w-1 bg-primary-md transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left">
                        </div>

                        <div class="px-4 py-4 ml-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-1 min-w-0">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-gradient-to-br from-primary-lt to-primary-md rounded-lg flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="{{ $setting['icon'] }}" />
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3
                                                class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-primary-dk transition-colors duration-200">
                                                {{ __($setting['title']) }}
                                            </h3>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                                                                        {{ $setting['category'] === 'System' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                                                                                        {{ $setting['category'] === 'Products' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                                                                        {{ $setting['category'] === 'Inventory' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                                                                                        {{ $setting['category'] === 'Organization' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                                                                                        {{ $setting['category'] === 'Security' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                                                                                        {{ $setting['category'] === 'Billing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                                                                                        {{ $setting['category'] === 'CRM' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300' : '' }}">
                                                    {{ $setting['category'] }}
                                                </span>
                                                {{-- @if ($setting['priority'] === 'high')
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-primary-md text-white">
                                                    Essential
                                                </span>
                                                @endif --}}
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed pr-3">
                                            {{ __($setting['desc']) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="flex-shrink-0 ml-3">
                                    <a href="{{ route($setting['route']) }}"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold text-primary-dk bg-primary-lt/20 border border-primary-lt rounded-lg hover:bg-primary-md hover:text-white hover:border-primary-md focus:ring-2 focus:ring-primary-lt/50 focus:outline-none transition-all duration-200 group/btn shadow-sm hover:shadow-md">
                                        {{ __('Configure') }}
                                        <svg class="w-3 h-3 ml-1.5 rtl:rotate-180 group-hover/btn:translate-x-1 transition-transform duration-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="hidden">
            <div
                class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 mt-6 overflow-hidden">
                <div class="text-center py-12">
                    <div class="mx-auto max-w-md">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-primary-lt to-primary-md rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.034 0-3.9.785-5.291 2.09m0 0L4.5 19.5m0 0L2 22m2.5-2.5L2 17m5.291 2.09A7.962 7.962 0 0112 21a7.962 7.962 0 015.291-2.09M17 17l2.5 2.5L22 17l-2.5-2.5" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No settings found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms or browse
                            all
                            available settings above.</p>
                        <button
                            onclick="document.getElementById('searchSettings').value=''; document.getElementById('searchSettings').dispatchEvent(new Event('input'));"
                            class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-medium text-primary-dk bg-primary-lt/20 border border-primary-lt rounded-lg hover:bg-primary-lt/30 transition-colors duration-200">
                            Clear Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript for Filtering -->
    <script>
        document.getElementById('searchSettings').addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            const settingItems = document.querySelectorAll('.setting-item');
            const noResults = document.getElementById('noResults');
            const settingsList = document.getElementById('settingsList').parentElement;
            let visibleCount = 0;

            settingItems.forEach(item => {
                const title = item.getAttribute('data-title');
                const description = item.querySelector('p').textContent.toLowerCase();
                const category = item.querySelector('span').textContent.toLowerCase();

                if (title.includes(filter) || description.includes(filter) || category.includes(filter)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message and settings list
            if (visibleCount === 0 && filter !== '') {
                settingsList.style.display = 'none';
                noResults.classList.remove('hidden');
            } else {
                settingsList.style.display = 'block';
                noResults.classList.add('hidden');
            }
        });

        // Add keyboard navigation
        document.getElementById('searchSettings').addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
                this.blur();
            }
        });

        // Add click handler for list items
        document.querySelectorAll('.setting-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!e.target.closest('a')) {
                    const link = this.querySelector('a');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });

        // Add smooth scroll to top on search
        document.getElementById('searchSettings').addEventListener('focus', function() {
            this.parentElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        });
    </script>
</x-app-layout>
