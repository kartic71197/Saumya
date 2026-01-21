<nav x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-border/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <div class="w-14 h-14 rounded-lg bg-primary flex items-center justify-center">
                    <img src="logos/logo.png" alt="">
                </div>
                {{-- <span class="text-xl font-bold text-foreground">Saumya</span> --}}
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-8">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-muted-foreground hover:text-foreground flex items-center gap-1">
                        Platform
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak 
                         class="absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-border p-4 grid gap-2">
                        @foreach([
                            ['Appointment Scheduling', 'Smart booking system with automated reminders'],
                            ['Customer Management', 'Complete CRM for your business'],
                            ['Analytics & Reports', 'Real-time insights and business intelligence'],
                            ['Inventory Management', 'Track stock levels and automate reorders'],
                            ['Payment Processing', 'Accept payments seamlessly'],
                            ['Marketing Tools', 'Campaigns, loyalty programs & promotions']
                        ] as $item)
                        <a href="#" class="block p-3 rounded-lg hover:bg-muted transition-colors">
                            <div class="font-medium text-foreground">{{ $item[0] }}</div>
                            <p class="text-sm text-muted-foreground">{{ $item[1] }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-muted-foreground hover:text-foreground flex items-center gap-1">
                        Solutions
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak 
                         class="absolute top-full left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-border p-4 grid gap-2">
                        @foreach([
                            ['Retail & E-commerce', 'Complete retail management solution'],
                            ['Restaurants & Cafes', 'Table management & ordering system'],
                            ['Salons & Spas', 'Appointment booking & staff scheduling'],
                            ['Fitness & Wellness', 'Memberships & class scheduling'],
                            ['Professional Services', 'Client management & invoicing']
                        ] as $item)
                        <a href="#" class="block p-3 rounded-lg hover:bg-muted transition-colors">
                            <div class="font-medium text-foreground">{{ $item[0] }}</div>
                            <p class="text-sm text-muted-foreground">{{ $item[1] }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-muted-foreground hover:text-foreground flex items-center gap-1">
                        Resources
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak 
                         class="absolute top-full left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-border p-4 grid gap-2">
                        @foreach([
                            ['Documentation', 'Guides and API references'],
                            ['Blog', 'Tips, news & industry insights'],
                            ['Video Tutorials', 'Step-by-step walkthroughs'],
                            ['Webinars', 'Live sessions with experts'],
                            ['Help Center', '24/7 support & FAQs']
                        ] as $item)
                        <a href="#" class="block p-3 rounded-lg hover:bg-muted transition-colors">
                            <div class="font-medium text-foreground">{{ $item[0] }}</div>
                            <p class="text-sm text-muted-foreground">{{ $item[1] }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>

                <a href="#" class="text-muted-foreground hover:text-foreground">Pricing</a>
            </div>

            <!-- Desktop CTA -->
            <div class="hidden md:flex items-center gap-3">
                <a href="#"
       class="px-12 py-3 rounded-xl gradient-bg
          text-white font-semibold hover:opacity-90
          shadow-lg transition">
                    Log In
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-b border-border max-h-[80vh] overflow-y-auto">
        <div class="px-4 py-4 space-y-4">
            <div>
                <h3 class="font-semibold text-foreground mb-2">Platform</h3>
                <div class="space-y-2 pl-2">
                    @foreach(['Appointment Scheduling', 'Customer Management', 'Analytics & Reports', 'Inventory Management', 'Payment Processing', 'Marketing Tools'] as $item)
                    <a href="#" class="block text-muted-foreground hover:text-foreground py-1 text-sm">{{ $item }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="font-semibold text-foreground mb-2">Solutions</h3>
                <div class="space-y-2 pl-2">
                    @foreach(['Retail & E-commerce', 'Restaurants & Cafes', 'Salons & Spas', 'Fitness & Wellness', 'Professional Services'] as $item)
                    <a href="#" class="block text-muted-foreground hover:text-foreground py-1 text-sm">{{ $item }}</a>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-col gap-3 pt-4 border-t border-border">
                <a href="#" class="w-full text-center px-4 py-3 rounded-lg border-2 border-primary text-primary font-medium">Get a Demo</a>
                <a href="#" class="w-full text-center px-4 py-3 rounded-lg gradient-bg text-white font-medium">Start Free Trial</a>
            </div>
        </div>
    </div>
</nav>