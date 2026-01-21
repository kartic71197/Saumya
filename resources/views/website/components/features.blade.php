<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl sm:text-4xl font-bold text-foreground mb-4">
                Everything You Need to Run Your Business
            </h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                A complete suite of tools designed specifically for service-based businesses
            </p>
        </div>

        <div class="space-y-16">
            @php
                $features = [
                    [
                        'title' => 'Online Booking & Scheduling',
                        'description' => 'Let clients book 24/7 with a beautiful, branded booking experience. Reduce no-shows with automated reminders.',
                        'benefits' => ['Real-time availability sync', 'Automated SMS & email reminders', 'Multi-location support', 'Waitlist management'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>'
                    ],
                    [
                        'title' => 'Client Management & CRM',
                        'description' => 'Build lasting relationships with comprehensive client profiles, history tracking, and personalized communication.',
                        'benefits' => ['360° client profiles', 'Visit history & preferences', 'Automated follow-ups', 'Client segmentation'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>'
                    ],
                    [
                        'title' => 'POS & Payments',
                        'description' => 'Accept payments anywhere with integrated POS. Manage memberships, packages, and invoices effortlessly.',
                        'benefits' => ['In-store & online payments', 'Membership management', 'Package & bundle sales', 'Automated invoicing'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>'
                    ],
                    [
                        'title' => 'Inventory & Purchasing',
                        'description' => 'Never run out of stock. Track inventory levels, manage suppliers, and automate reordering.',
                        'benefits' => ['Real-time stock tracking', 'Supplier management', 'Auto-replenishment alerts', 'Cost & margin analysis'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>'
                    ],
                    [
                        'title' => 'Staff Management',
                        'description' => 'Empower your team with smart scheduling, commission tracking, and performance analytics.',
                        'benefits' => ['Shift scheduling & rosters', 'Commission calculation', 'Performance dashboards', 'Role-based permissions'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                    ],
                    [
                        'title' => 'Marketing & Automation',
                        'description' => 'Grow your business with targeted campaigns, loyalty programs, and automated marketing workflows.',
                        'benefits' => ['Email & SMS campaigns', 'Loyalty & rewards program', 'Review management', 'Referral tracking'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>'
                    ],
                    [
                        'title' => 'Analytics & Reporting',
                        'description' => 'Make data-driven decisions with real-time insights into revenue, utilization, and business performance.',
                        'benefits' => ['Revenue dashboards', 'Staff utilization reports', 'Business forecasting', 'Custom report builder'],
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>'
                    ]
                ];
            @endphp

            @foreach($features as $index => $feature)
                <div class="grid lg:grid-cols-2 gap-8 items-center" data-aos="fade-up">
                    <div class="space-y-6 {{ $index % 2 === 1 ? 'lg:order-2' : '' }}"
                        data-aos="{{ $index % 2 === 0 ? 'fade-right' : 'fade-left' }}" data-aos-delay="100">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $feature['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground mb-3">{{ $feature['title'] }}</h3>
                            <p class="text-muted-foreground leading-relaxed">{{ $feature['description'] }}</p>
                        </div>
                        <ul class="space-y-2">
                            @foreach($feature['benefits'] as $benefit)
                                <li class="flex items-center gap-3 text-foreground">
                                    <svg class="w-5 h-5 text-primary flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $benefit }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="{{ $index % 2 === 1 ? 'lg:order-1' : '' }}"
                        data-aos="{{ $index % 2 === 0 ? 'fade-left' : 'fade-right' }}" data-aos-delay="200">
                        <div class="bg-white rounded-2xl border border-border p-6 shadow-soft">

                            @if($feature['title'] === 'Online Booking & Scheduling')
                                <!-- Calendar Demo -->
                                <div class="bg-muted/30 rounded-xl p-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-sm font-semibold text-foreground">March 2024</span>
                                        <span class="text-xs text-primary font-medium">Available</span>
                                    </div>

                                    <div class="grid grid-cols-7 gap-2 text-center text-xs">
                                        @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                                            <div class="text-muted-foreground font-medium">{{ $day }}</div>
                                        @endforeach

                                        @foreach(range(1, 31) as $date)
                                            @if(in_array($date, [5, 9, 14, 21, 27]))
                                                <div class="py-2 rounded-lg bg-primary text-white font-semibold">
                                                    {{ $date }}
                                                </div>
                                            @else
                                                <div class="py-2 rounded-lg text-foreground hover:bg-muted cursor-pointer">
                                                    {{ $date }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- Default Icon Demo -->
                                <div class="bg-muted/30 rounded-xl p-6 min-h-[200px] flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 text-primary/30 mx-auto mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            {!! $feature['icon'] !!}
                                        </svg>
                                        <p class="text-muted-foreground text-sm">{{ $feature['title'] }} Demo</p>
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