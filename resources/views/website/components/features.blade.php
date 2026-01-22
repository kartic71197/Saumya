<section class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-16 opacity-0 translate-y-8 animate-fade-in-up">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Everything You Need to Run Your Business
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                A complete suite of tools designed specifically for service-based businesses
            </p>
        </div>

        @php
            $features = [
                [
                    'icon' => 'calendar',
                    'title' => 'Online Booking & Scheduling',
                    'description' => 'Let clients book 24/7 with a beautiful, branded booking experience. Reduce no-shows with automated reminders.',
                    'benefits' => ['Real-time availability sync', 'Automated SMS & email reminders', 'Multi-location support', 'Waitlist management'],
                    'demo' => 'calendar'
                ],
                [
                    'icon' => 'users',
                    'title' => 'Client Management & CRM',
                    'description' => 'Build lasting relationships with comprehensive client profiles, history tracking, and personalized communication.',
                    'benefits' => ['360° client profiles', 'Visit history & preferences', 'Automated follow-ups', 'Client segmentation'],
                    'demo' => 'crm'
                ],
                [
                    'icon' => 'credit-card',
                    'title' => 'POS & Payments',
                    'description' => 'Accept payments anywhere with integrated POS. Manage memberships, packages, and invoices effortlessly.',
                    'benefits' => ['In-store & online payments', 'Membership management', 'Package & bundle sales', 'Automated invoicing'],
                    'demo' => 'pos'
                ],
                [
                    'icon' => 'package',
                    'title' => 'Inventory & Purchasing',
                    'description' => 'Never run out of stock. Track inventory levels, manage suppliers, and automate reordering.',
                    'benefits' => ['Real-time stock tracking', 'Supplier management', 'Auto-replenishment alerts', 'Cost & margin analysis'],
                    'demo' => 'inventory'
                ],
                [
                    'icon' => 'user-circle',
                    'title' => 'Staff Management',
                    'description' => 'Empower your team with smart scheduling, commission tracking, and performance analytics.',
                    'benefits' => ['Shift scheduling & rosters', 'Commission calculation', 'Performance dashboards', 'Role-based permissions'],
                    'demo' => 'staff'
                ],
                [
                    'icon' => 'mail',
                    'title' => 'Marketing & Automation',
                    'description' => 'Grow your business with targeted campaigns, loyalty programs, and automated marketing workflows.',
                    'benefits' => ['Email & SMS campaigns', 'Loyalty & rewards program', 'Review management', 'Referral tracking'],
                    'demo' => 'marketing'
                ],
                [
                    'icon' => 'bar-chart',
                    'title' => 'Analytics & Reporting',
                    'description' => 'Make data-driven decisions with real-time insights into revenue, utilization, and business performance.',
                    'benefits' => ['Revenue dashboards', 'Staff utilization reports', 'Business forecasting', 'Custom report builder'],
                    'demo' => 'analytics'
                ]
            ];

            // Icon SVGs
            $icons = [
                'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
                'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>',
                'credit-card' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>',
                'package' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>',
                'user-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                'mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>',
                'bar-chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>'
            ];
        @endphp

        <div class="space-y-8">
            @foreach($features as $index => $feature)
                <div class="grid lg:grid-cols-2 gap-8 items-center opacity-0 translate-y-12 animate-slide-in-view animation-delay-{{ $index * 100 }}">
                    {{-- Content Side --}}
                    <div class="space-y-6 {{ $index % 2 === 1 ? 'lg:order-2' : '' }}">
                        <div class="w-12 h-12 rounded-xl bg-blue-600/10 dark:bg-blue-400/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $icons[$feature['icon']] !!}
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ $feature['title'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $feature['description'] }}</p>
                        </div>
                        <ul class="space-y-2">
                            @foreach($feature['benefits'] as $benefit)
                                <li class="flex items-center gap-3 text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ $benefit }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Demo Side --}}
                    <div class="{{ $index % 2 === 1 ? 'lg:order-1' : '' }}">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            @if($feature['demo'] === 'calendar')
                                {{-- Calendar Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-3">Calendar View</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">March 2024</div>
                                    <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                            <div class="py-1 text-gray-600 dark:text-gray-400 font-medium">{{ $day }}</div>
                                        @endforeach
                                        @for($i = 1; $i <= 31; $i++)
                                            <div class="py-1 rounded {{ in_array($i, [5, 12, 15, 22]) ? 'bg-blue-600 dark:bg-blue-500 text-white font-medium' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer' }}">
                                                {{ $i }}
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                            @elseif($feature['demo'] === 'crm')
                                {{-- CRM Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 rounded-full bg-blue-600/10 dark:bg-blue-400/10 flex items-center justify-center text-lg">👩</div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Sarah Johnson</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">VIP Client • 24 visits</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-white dark:bg-gray-950 rounded-lg p-3 text-center">
                                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">$4,280</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Lifetime Value</div>
                                        </div>
                                        <div class="bg-white dark:bg-gray-950 rounded-lg p-3 text-center">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">24</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Total Visits</div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($feature['demo'] === 'pos')
                                {{-- POS Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-900 dark:text-white font-medium">Today's Revenue</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg font-bold text-gray-900 dark:text-white">$3,847.50</span>
                                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">+23% ↑</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach(['Visa •••• 4242', 'Mastercard •••• 8888', 'Apple Pay'] as $method)
                                            <div class="bg-white dark:bg-gray-950 rounded-lg px-3 py-2 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                                {{ $method }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            @elseif($feature['demo'] === 'inventory')
                                {{-- Inventory Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Stock Levels</span>
                                        <span class="text-xs text-blue-600 dark:text-blue-400 cursor-pointer">View All</span>
                                    </div>
                                    @php
                                        $items = [
                                            ['name' => 'Facial Serum', 'level' => 85],
                                            ['name' => 'Massage Oil', 'level' => 23],
                                            ['name' => 'Hair Treatment', 'level' => 56]
                                        ];
                                    @endphp
                                    @foreach($items as $item)
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-900 dark:text-white">{{ $item['name'] }}</span>
                                                <span class="{{ $item['level'] < 30 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $item['level'] }}%</span>
                                            </div>
                                            <div class="h-1.5 bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $item['level'] < 30 ? 'bg-red-600 dark:bg-red-500' : 'bg-blue-600 dark:bg-blue-500' }}" style="width: {{ $item['level'] }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif($feature['demo'] === 'staff')
                                {{-- Staff Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-3">Team Performance</div>
                                    @php
                                        $staff = [
                                            ['initials' => 'ER', 'name' => 'Emily Rose', 'role' => 'Stylist', 'revenue' => '$2,450', 'bookings' => 28],
                                            ['initials' => 'JL', 'name' => 'James Lee', 'role' => 'Therapist', 'revenue' => '$2,100', 'bookings' => 24],
                                            ['initials' => 'SC', 'name' => 'Sofia Chen', 'role' => 'Aesthetician', 'revenue' => '$3,200', 'bookings' => 32]
                                        ];
                                    @endphp
                                    @foreach($staff as $member)
                                        <div class="flex items-center justify-between bg-white dark:bg-gray-950 rounded-lg p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-blue-600/10 dark:bg-blue-400/10 flex items-center justify-center text-xs font-medium text-blue-600 dark:text-blue-400">
                                                    {{ $member['initials'] }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['name'] }}</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $member['role'] }}</div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $member['revenue'] }}</div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ $member['bookings'] }} bookings</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif($feature['demo'] === 'marketing')
                                {{-- Marketing Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Spring Promo Campaign</div>
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-white dark:bg-gray-950 rounded-lg p-2">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">2,847</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Sent</div>
                                        </div>
                                        <div class="bg-white dark:bg-gray-950 rounded-lg p-2">
                                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">68%</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Open Rate</div>
                                        </div>
                                        <div class="bg-white dark:bg-gray-950 rounded-lg p-2">
                                            <div class="text-lg font-bold text-green-600 dark:text-green-400">$4.2K</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Revenue</div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($feature['demo'] === 'analytics')
                                {{-- Analytics Demo --}}
                                <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1 bg-white dark:bg-gray-950 rounded-lg p-4 text-center">
                                            <div class="text-2xl font-bold text-gray-900 dark:text-white">$127K</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Monthly Revenue</div>
                                        </div>
                                        <div class="flex-1 bg-white dark:bg-gray-950 rounded-lg p-4 text-center">
                                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">89%</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Utilization</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slide-in-view {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
    }

    .animate-slide-in-view {
        animation: slide-in-view 0.6s ease-out forwards;
    }

    .animation-delay-0 { animation-delay: 0ms; }
    .animation-delay-100 { animation-delay: 100ms; }
    .animation-delay-200 { animation-delay: 200ms; }
    .animation-delay-300 { animation-delay: 300ms; }
    .animation-delay-400 { animation-delay: 400ms; }
    .animation-delay-500 { animation-delay: 500ms; }
    .animation-delay-600 { animation-delay: 600ms; }

    .shadow-soft {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-fade-in-up,
        .animate-slide-in-view {
            animation: none;
            opacity: 1;
            transform: none;
        }
    }

    @media (prefers-reduced-motion: no-preference) {
        .animate-fade-in-up,
        .animate-slide-in-view {
            opacity: 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (!prefersReducedMotion) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '-100px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.animate-fade-in-up, .animate-slide-in-view').forEach(el => {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });
        }
    });
</script>