<section class="pt-24 pb-16 lg:pt-32 lg:pb-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Left Content --}}
            <div class="space-y-8 opacity-0 -translate-x-12 animate-fade-in-left">
                <div class="space-y-6">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
                        Run Your Business 
                        <span class="bg-gradient-to-r from-violet-500 via-blue-600 to-purple-600 bg-clip-text text-transparent">Smarter</span> 
                        with Saumya
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-lg">
                        The all-in-one platform for medspas, salons, and wellness centers. 
                        Manage bookings, clients, inventory, and staff—all in one place.
                    </p>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 opacity-0 translate-y-5 animate-fade-in-up animation-delay-200">
                    <a href="#" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 border-2 border-blue-600 dark:border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        Get a Demo
                    </a>
                    <a href="#" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg">
                        Start Free Trial
                    </a>
                </div>

                {{-- Trust Indicators --}}
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400 opacity-0 animate-fade-in animation-delay-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>No credit card required</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Free 14-day trial</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Cancel anytime</span>
                    </div>
                </div>
            </div>

            {{-- Right Content - Dashboard Mockup --}}
            <div class="relative lg:pl-8 opacity-0 translate-x-12 animate-fade-in-right animation-delay-300">
                {{-- Background Glow --}}
                <div class="absolute inset-0 bg-gradient-to-br from-violet-500/20 via-blue-600/10 to-purple-600/20 rounded-3xl blur-3xl"></div>
                
                {{-- Main Dashboard Card --}}
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Dashboard Header --}}
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-400/60"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400/60"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400/60"></div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">dashboard.saumya.com</span>
                        </div>
                    </div>

                    {{-- Dashboard Content --}}
                    <div class="p-6 space-y-6">
                        {{-- Stats Row --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Today's Bookings</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">24</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Revenue</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">$3,847</p>
                            </div>
                        </div>

                        {{-- Schedule Preview --}}
                        <div class="space-y-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Upcoming</h4>
                            <div class="space-y-2">
                                @php
                                    $bookings = [
                                        ['time' => '10:00 AM', 'name' => 'Sarah J.', 'service' => 'Facial Treatment'],
                                        ['time' => '11:30 AM', 'name' => 'Mike R.', 'service' => 'Massage Therapy'],
                                        ['time' => '2:00 PM', 'name' => 'Emma L.', 'service' => 'Hair Styling'],
                                    ];
                                @endphp

                                @foreach($bookings as $booking)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/20 rounded-lg px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-600/10 flex items-center justify-center">
                                                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">
                                                    {{ collect(explode(' ', $booking['name']))->map(fn($n) => $n[0])->join('') }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $booking['name'] }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $booking['service'] }}</p>
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $booking['time'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating Notification --}}
                <div class="absolute -top-4 -right-4 lg:top-8 lg:-right-8 opacity-0 scale-90 animate-fade-in-scale animation-delay-800">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3 animate-float">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">New Booking!</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Just now</p>
                        </div>
                    </div>
                </div>

                {{-- Revenue Badge --}}
                <div class="absolute -bottom-4 -left-4 lg:bottom-8 lg:-left-8 opacity-0 scale-90 animate-fade-in-scale animation-delay-1000">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3 animate-float-delayed">
                        <div class="w-10 h-10 rounded-full bg-blue-600/10 flex items-center justify-center text-lg">
                            💰
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-600 dark:text-blue-400">+$1,250</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Today's revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes fade-in-left {
        from {
            opacity: 0;
            transform: translateX(-3rem);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fade-in-right {
        from {
            opacity: 0;
            transform: translateX(3rem);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(1.25rem);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fade-in-scale {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .animate-fade-in-left {
        animation: fade-in-left 0.6s ease-out forwards;
    }

    .animate-fade-in-right {
        animation: fade-in-right 0.8s ease-out forwards;
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
    }

    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }

    .animate-fade-in-scale {
        animation: fade-in-scale 0.5s ease-out forwards;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float 3s ease-in-out infinite;
        animation-delay: 0.5s;
    }

    .animation-delay-200 {
        animation-delay: 0.2s;
    }

    .animation-delay-300 {
        animation-delay: 0.3s;
    }

    .animation-delay-400 {
        animation-delay: 0.4s;
    }

    .animation-delay-800 {
        animation-delay: 0.8s;
    }

    .animation-delay-1000 {
        animation-delay: 1s;
    }
</style>