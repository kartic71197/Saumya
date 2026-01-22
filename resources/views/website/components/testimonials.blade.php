<section class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 opacity-0 animate-fade-in-view">
            @php
                $testimonials = [
                    [
                        'quote' => 'Saumya transformed how we run our medspa. Bookings increased by 40% in just 3 months.',
                        'author' => 'Dr. Sarah Chen',
                        'role' => 'Owner, Glow Med Spa'
                    ],
                    [
                        'quote' => 'The automation features alone save us 20+ hours per week. It\'s a game changer.',
                        'author' => 'Marcus Johnson',
                        'role' => 'CEO, Elite Salon Group'
                    ],
                    [
                        'quote' => 'Best investment we\'ve made for our wellness clinic. Our clients love the booking experience.',
                        'author' => 'Emily Rodriguez',
                        'role' => 'Director, Zen Wellness Center'
                    ]
                ];
            @endphp

            @foreach($testimonials as $index => $testimonial)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 shadow-sm hover:shadow-lg transition-all duration-300 opacity-0 animate-slide-up-fade animation-delay-{{ $index * 150 }} testimonial-card">
                    {{-- Quote Icon --}}
                    <svg class="w-8 h-8 text-blue-600/30 dark:text-blue-400/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    
                    <blockquote class="text-gray-900 dark:text-white text-lg mb-6 leading-relaxed">
                        "{{ $testimonial['quote'] }}"
                    </blockquote>
                    
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $testimonial['author'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $testimonial['role'] }}</p>
                    </div>
                </div>
            @endforeach
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

    @keyframes slide-up-fade {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-view {
        animation: fade-in-view 0.6s ease-out forwards;
    }

    .animate-slide-up-fade {
        animation: slide-up-fade 0.6s ease-out forwards;
    }

    .animation-delay-0 {
        animation-delay: 0ms;
    }

    .animation-delay-150 {
        animation-delay: 150ms;
    }

    .animation-delay-300 {
        animation-delay: 300ms;
    }

    /* Hover effect for testimonial cards */
    .testimonial-card {
        transition: transform 0.2s ease-out, box-shadow 0.3s ease-out;
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
    }

    /* Shadow utilities */
    .shadow-soft {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    /* Respect user's motion preferences */
    @media (prefers-reduced-motion: reduce) {
        .animate-fade-in-view,
        .animate-slide-up-fade {
            animation: none;
            opacity: 1;
            transform: none;
        }
        
        .testimonial-card:hover {
            transform: none;
        }
    }

    /* Initial state for animations */
    @media (prefers-reduced-motion: no-preference) {
        .animate-fade-in-view,
        .animate-slide-up-fade {
            opacity: 0;
        }
    }
</style>

<script>
    // Intersection Observer for scroll-triggered animations
    document.addEventListener('DOMContentLoaded', function() {
        // Check if user prefers reduced motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (!prefersReducedMotion) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '-100px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Trigger animation by setting play state
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.animate-fade-in-view, .animate-slide-up-fade').forEach(el => {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });
        }
    });
</script>