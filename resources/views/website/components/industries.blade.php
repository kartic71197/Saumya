<section class="py-20 lg:py-28 bg-gray-50 dark:bg-gray-900/30 overflow-hidden">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-block px-4 py-1.5 bg-blue-600/10 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400 rounded-full text-sm font-medium mb-4 opacity-0 animate-fade-in">
                For Every Industry
            </span>
            <h2 class="text-3xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Built for every business, designed for growth
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                Everything you need to grow and thrive. Saumya is packed with tools to boost sales, 
                manage your calendar, and retain clients, so you can focus on what you do best.
            </p>
            <a 
                href="#" 
                class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg group"
            >
                Get started now
                <svg class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>

        @php
            $industries = [
                [
                    'name' => 'Salon',
                    'icon' => 'scissors',
                    'image' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-pink-500 to-rose-600',
                ],
                [
                    'name' => 'Barber',
                    'icon' => 'spray',
                    'image' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-amber-500 to-orange-600',
                ],
                [
                    'name' => 'Nails',
                    'icon' => 'sparkles',
                    'image' => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-fuchsia-500 to-purple-600',
                ],
                [
                    'name' => 'Spa & Sauna',
                    'icon' => 'waves',
                    'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-cyan-500 to-teal-600',
                ],
                [
                    'name' => 'Medspa',
                    'icon' => 'syringe',
                    'image' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-violet-500 to-indigo-600',
                ],
                [
                    'name' => 'Massage',
                    'icon' => 'hand',
                    'image' => 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-emerald-500 to-green-600',
                ],
                [
                    'name' => 'Fitness',
                    'icon' => 'dumbbell',
                    'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-red-500 to-rose-600',
                ],
                [
                    'name' => 'Physical Therapy',
                    'icon' => 'activity',
                    'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-blue-500 to-sky-600',
                ],
                [
                    'name' => 'Health Practices',
                    'icon' => 'stethoscope',
                    'image' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800&auto=format&fit=crop&q=60',
                    'color' => 'from-teal-500 to-cyan-600',
                ],
            ];

            $gridClasses = [
                'col-span-6 lg:col-span-4 row-span-2', // Salon - tall left
                'col-span-6 lg:col-span-4 row-span-1', // Barber - top middle
                'col-span-6 lg:col-span-4 row-span-2', // Nails - tall right
                'col-span-6 lg:col-span-4 row-span-1', // Spa & Sauna - below barber
                'col-span-12 lg:col-span-8 row-span-1', // Medspa - wide center
                'col-span-6 lg:col-span-4 row-span-1', // Massage - bottom left
                'col-span-6 lg:col-span-4 row-span-1', // Fitness - bottom center
                'col-span-6 lg:col-span-4 row-span-1', // Physical Therapy - bottom right
                'col-span-6 lg:col-span-4 row-span-1', // Health Practices - bottom right
            ];

            $icons = [
                'scissors' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>',
                'spray' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v1a2 2 0 01-2 2H2m0-4h4m0 0v3a2 2 0 002 2h4a2 2 0 002-2v-3m-4-7h4m-4 0v8m4-8v8m-4-8a2 2 0 00-2-2H6a2 2 0 00-2 2v4"></path>',
                'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v1a2 2 0 01-2 2H2m10-6h.01M15 8h.01M20 6h.01M12 12h.01M17 17h.01"></path>',
                'waves' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6c2.5 0 4.5 2 4.5 4.5S5.5 15 3 15m18-9c-2.5 0-4.5 2-4.5 4.5S18.5 15 21 15M9 18c0-2.5 2-4.5 4.5-4.5S18 15.5 18 18"></path>',
                'syringe' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"></path>',
                'hand' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path>',
                'dumbbell' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>',
                'activity' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
                'stethoscope' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
            ];
        @endphp

        {{-- Industry Grid - Asymmetric Bento Layout --}}
        <div class="grid grid-cols-12 gap-4 lg:gap-5 auto-rows-[140px] md:auto-rows-[160px] lg:auto-rows-[180px]">
            @foreach($industries as $index => $industry)
                @php
                    $isWide = $index === 4 || $index === 8;
                    $isTall = $index === 0 || $index === 2;
                @endphp

                <a
                    href="#"
                    class="group relative overflow-hidden rounded-2xl lg:rounded-3xl transition-all duration-500 hover:shadow-2xl hover:-translate-y-1 hover:z-10 {{ $gridClasses[$index] }}"
                >
                    {{-- Background Image --}}
                    <img
                        src="{{ $industry['image'] }}"
                        alt="{{ $industry['name'] }}"
                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                    
                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-br {{ $industry['color'] }} opacity-50 mix-blend-multiply transition-opacity duration-300 group-hover:opacity-60"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    
                    {{-- Content --}}
                    <div class="absolute inset-0 p-4 md:p-5 lg:p-6 flex {{ $isWide ? 'flex-row items-end justify-between' : 'flex-col justify-between' }}">
                        {{-- Icon --}}
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:bg-white/30 {{ $isWide ? 'order-2' : '' }}">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $icons[$industry['icon']] !!}
                            </svg>
                        </div>
                        
                        {{-- Title --}}
                        <div class="{{ $isWide ? 'order-1' : '' }}">
                            <h3 class="text-white font-bold mb-0.5 transition-transform duration-300 group-hover:translate-x-1 {{ $isTall ? 'text-xl md:text-2xl lg:text-3xl' : ($isWide ? 'text-xl md:text-2xl' : 'text-base md:text-lg lg:text-xl') }}">
                                {{ $industry['name'] }}
                            </h3>
                            <div class="flex items-center gap-1.5 opacity-0 -translate-y-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0">
                                <span class="text-white/80 text-xs md:text-sm">Explore</span>
                                <svg class="w-3 h-3 md:w-4 md:h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Shine Effect on Hover --}}
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }

    /* Mix blend mode support */
    .mix-blend-multiply {
        mix-blend-mode: multiply;
    }

    /* Backdrop blur fallback */
    @supports not (backdrop-filter: blur(12px)) {
        .backdrop-blur-sm {
            background-color: rgba(255, 255, 255, 0.3);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-fade-in {
            animation: none;
            opacity: 1;
        }
        
        .group:hover img {
            transform: none !important;
        }
        
        .group:hover .transition-transform {
            transform: none !important;
        }
    }
</style>