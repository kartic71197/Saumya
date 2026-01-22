<section class="py-16 bg-gray-50 dark:bg-gray-900/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-8 opacity-0 animate-fade-in-view">
            <p class="text-gray-600 dark:text-gray-400 text-lg opacity-0 animate-fade-in-up animation-delay-100">
                Trusted by thousands of service businesses worldwide
            </p>
            
            <div class="flex flex-wrap justify-center items-center gap-6 lg:gap-12">
                @php
                    $businesses = [
                        ['initials' => 'GS', 'name' => 'Glow Spa'],
                        ['initials' => 'ZW', 'name' => 'Zen Wellness'],
                        ['initials' => 'BH', 'name' => 'Beauty Hub'],
                        ['initials' => 'SM', 'name' => 'Serene Med'],
                        ['initials' => 'ES', 'name' => 'Elite Salon'],
                        ['initials' => 'PS', 'name' => 'Pure Skin'],
                    ];
                @endphp

                @foreach($businesses as $index => $business)
                    <div class="flex items-center gap-3 opacity-0 animate-fade-in-up animation-delay-{{ 200 + ($index * 100) }}">
                        <div class="w-10 h-10 rounded-full bg-blue-600/10 dark:bg-blue-400/10 flex items-center justify-center">
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                {{ $business['initials'] }}
                            </span>
                        </div>
                        <span class="text-gray-600 dark:text-gray-400 font-medium">
                            {{ $business['name'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes fade-in-view {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-view {
        animation: fade-in-view 0.6s ease-out forwards;
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.5s ease-out forwards;
    }

    .animation-delay-100 {
        animation-delay: 0.1s;
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

    .animation-delay-500 {
        animation-delay: 0.5s;
    }

    .animation-delay-600 {
        animation-delay: 0.6s;
    }

    .animation-delay-700 {
        animation-delay: 0.7s;
    }

    /* Intersection Observer alternative for scroll animations */
    @media (prefers-reduced-motion: no-preference) {
        .animate-fade-in-view,
        .animate-fade-in-up {
            opacity: 0;
        }
    }
</style>

<script>
    // Optional: Add Intersection Observer for scroll-triggered animations
    document.addEventListener('DOMContentLoaded', function() {
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

        // Observe all animated elements
        document.querySelectorAll('.animate-fade-in-view, .animate-fade-in-up').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    });
</script>