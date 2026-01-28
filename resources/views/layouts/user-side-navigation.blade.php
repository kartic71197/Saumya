@php
    $user = auth()->user();
    $menuItems = [
        [
            'route' => 'dashboard',
            'title' => 'Dashboard',
            'hasSubmenu' => false,
            'svg' => 'icons.dashboard',
        ],
        [
            'route' => 'pos.sales.index',
            'title' => 'Point of sale',
            'hasSubmenu' => false,
            'svg' => 'icons.pos',
        ],
        [
            'route' => 'appointments.index',
            'title' => 'Appointments',
            'hasSubmenu' => false,
            'svg' => 'icons.appointment',
        ],
    ];

    // Only add the Catalog menu if role_id <= 2
    if ($user->role_id <= 2) {
        $menuItems[] = [
            'route' => 'organization.catalog',
            'title' => 'Products',
            'hasSubmenu' => false,
            'svg' => 'icons.master-catalog',
        ];
    }

    $menuItems[] = [
        'route' => 'purchase.index',
        'title' => 'Purchase Orders',
        'hasSubmenu' => false,
        'svg' => 'icons.purcase-orders',
    ];

    // Continue with the rest of the menu
    $menuItems = array_merge($menuItems, [
        [
            'route' => 'organization.inventory',
            'title' => 'Inventory',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'organization.inventory', 'title' => 'Inventory'],
                ['route' => 'organization.settings.inventory_adjust', 'title' => 'Adjustment'],
                ['route' => 'organization.settings.inventory_transfer', 'title' => 'Transfer'],
                ['route' => 'organization.settings.cycle_counts', 'title' => 'Cycle Count'],
            ],
            'svg' => 'icons.inventory',
        ],
        [
            'route' => 'picking.index',
            'title' => 'Pickings',
            'hasSubmenu' => false,
            'svg' => 'icons.picking',
        ],
        [
            'route' => 'report.index',
            'title' => 'Reports',
            'hasSubmenu' => false,
            'svg' => 'icons.reports',
        ],
        [
            'route' => 'barcode.index',
            'title' => 'Barcode',
            'hasSubmenu' => false,
            'svg' => 'icons.barcode',
        ],

        // [
        //     'route' => 'patient.index',
        //     'title' => 'Patients',
        //     'hasSubmenu' => false,
        //     'svg' => 'icons.patients',
        // ],
        [
            'route' => 'organization.settings',
            'title' => 'Settings',
            'hasSubmenu' => false,
            'svg' => 'icons.settings',
        ],
    ]);
@endphp

<!-- Sidebar -->
<div x-data="{
    sidebarOpen: true,
    openSubmenu: null,
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        if (!this.sidebarOpen) {
            this.openSubmenu = null;
        }
    },
    toggleSubmenu(index) {
        if (this.sidebarOpen) {
            this.openSubmenu = this.openSubmenu === index ? null : index;
        }
    }
}"
    class="fixed top-16 left-0 h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-white dark:from-gray-900 dark:to-gray-800 border-r border-slate-200 dark:border-gray-700 flex flex-col shadow-xl z-50 transition-all duration-300 ease-in-out"
    :class="sidebarOpen ? 'w-64' : 'w-20'">

    <!-- Header with Logo and Toggle -->
    <div
        class="flex items-center justify-between px-4 py-5 border-b border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <!-- Organization Logo -->
        <div class="flex items-center gap-3 min-w-0" x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            @if (!empty(auth()->user()->organization?->image))
                <img src="{{ asset('storage/' . auth()->user()->organization?->image) }}" alt="Organization Logo"
                    class="w-10 h-10 rounded-lg object-cover shadow-sm ring-2 ring-slate-200 dark:ring-gray-700">
            @endif
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate tracking-tight">
                    {{ auth()->user()->organization?->name ?? 'Organization' }}
                </span>
                <span class="text-xs text-slate-500 dark:text-gray-400 font-medium">
                    Navigation
                </span>
            </div>
        </div>

        <!-- Collapsed State Logo -->
        <div x-show="!sidebarOpen" class="mx-auto" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100">
            @if (!empty(auth()->user()->organization?->image))
                <img src="{{ asset('storage/' . auth()->user()->organization?->image) }}" alt="Organization Logo"
                    class="w-10 h-10 rounded-lg object-cover shadow-md ring-2 ring-slate-300 dark:ring-gray-600">
            @else
                <div
                    class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-md to-primary-md/80 flex items-center justify-center shadow-md">
                    <span class="text-white font-bold text-lg">
                        {{ substr(auth()->user()->organization?->name ?? 'O', 0, 1) }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Toggle Button -->
        <button @click="toggleSidebar()" x-show="sidebarOpen"
            class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 transition-all duration-200 group flex-shrink-0">
            <svg class="w-5 h-5 text-slate-600 dark:text-gray-300 transition-transform duration-300 group-hover:scale-110"
                :class="sidebarOpen ? 'rotate-0' : 'rotate-180'" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>

        <!-- Collapsed Toggle Button -->
        <button @click="toggleSidebar()" x-show="!sidebarOpen"
            class="absolute -right-3 top-20 p-1.5 rounded-full bg-white dark:bg-gray-700 border-2 border-slate-200 dark:border-gray-600 hover:border-primary-md dark:hover:border-primary-md transition-all duration-200 shadow-lg group">
            <svg class="w-4 h-4 text-slate-600 dark:text-gray-300 transition-transform duration-300 group-hover:scale-110"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-3 space-y-1.5">
        @foreach ($menuItems as $index => $item)
            @php
                // Check if current route matches this item or any of its submenu items
                $isActive = request()->routeIs($item['route'] ?? '');
                if (!empty($item['hasSubmenu'])) {
                    foreach ($item['submenu'] as $sub) {
                        if (request()->routeIs($sub['route'] ?? '')) {
                            $isActive = true;
                            break;
                        }
                    }
                }
            @endphp

            <div class="relative">
                @if (!empty($item['hasSubmenu']))
                    <!-- Menu Item with Submenu -->
                    <button @click="toggleSubmenu({{ $index }})"
                        class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl transition-all duration-200 group
                            {{ $isActive
                                ? 'bg-gradient-to-r from-primary-md to-primary-md/90 text-white shadow-lg shadow-primary-md/30'
                                : 'text-slate-700 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700/50 hover:shadow-md' }}"
                        :class="sidebarOpen ? 'justify-between' : 'justify-center'">

                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Icon -->
                            <div
                                class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                                {!! View::make($item['svg'])->render() !!}
                            </div>

                            <!-- Title -->
                            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="text-sm font-semibold truncate tracking-wide">
                                {{ $item['title'] }}
                            </span>
                        </div>

                        <!-- Chevron -->
                        <svg x-show="sidebarOpen" class="w-4 h-4 transition-transform duration-300 flex-shrink-0"
                            :class="openSubmenu === {{ $index }} ? 'rotate-180' : ''" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Submenu -->
                    <div x-show="sidebarOpen && openSubmenu === {{ $index }}"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="ml-9 mt-2 space-y-1 border-l-2 border-slate-200 dark:border-gray-700 pl-4">
                        @foreach ($item['submenu'] as $sub)
                            <a href="{{ route($sub['route']) }}"
                                class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 group
                                    {{ request()->routeIs($sub['route'] ?? '')
                                        ? 'bg-primary-md/15 text-primary-md font-semibold shadow-sm'
                                        : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-gray-200 hover:translate-x-1' }}">
                                <span
                                    class="w-2 h-2 rounded-full transition-all duration-200 
                                    {{ request()->routeIs($sub['route'] ?? '')
                                        ? 'bg-primary-md shadow-sm shadow-primary-md/50'
                                        : 'bg-slate-300 dark:bg-gray-600 group-hover:bg-primary-md/50' }}">
                                </span>
                                <span class="truncate font-medium">{{ $sub['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- Simple Menu Item -->
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition-all duration-200 group
                            {{ $isActive
                                ? 'bg-gradient-to-r from-primary-md to-primary-md/90 text-white shadow-lg shadow-primary-md/30'
                                : 'text-slate-700 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700/50 hover:shadow-md' }}"
                        :class="sidebarOpen ? '' : 'justify-center'">

                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                            {!! View::make($item['svg'])->render() !!}
                        </div>

                        <!-- Title -->
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" class="text-sm font-semibold truncate tracking-wide">
                            {{ $item['title'] }}
                        </span>
                    </a>
                @endif

                <!-- Tooltip for collapsed state -->
                <div x-show="!sidebarOpen"
                    class="absolute left-full ml-3 px-3 py-2 bg-slate-900 dark:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none whitespace-nowrap z-50 border border-slate-700"
                    style="top: 50%; transform: translateY(-50%);">
                    {{ $item['title'] }}
                    <div
                        class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-900 dark:border-r-gray-700">
                    </div>
                </div>
            </div>
        @endforeach
    </nav>

    <!-- Footer Section -->
    <div class="border-t border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800" x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="px-4 py-4">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-slate-50 dark:bg-gray-700/50">
                <svg class="w-4 h-4 text-primary-md" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                </svg>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">MedSync v1.0</span>
                    <span class="text-xs text-slate-500 dark:text-gray-400">All systems operational</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom scrollbar for sidebar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, rgba(148, 163, 184, 0.3), rgba(148, 163, 184, 0.5));
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, rgba(148, 163, 184, 0.5), rgba(148, 163, 184, 0.7));
    }

    /* Ensure Alpine.js transitions work smoothly */
    [x-cloak] {
        display: none !important;
    }

    /* Smooth shadow transitions */
    .shadow-lg {
        transition: box-shadow 0.2s ease-in-out;
    }
</style>
