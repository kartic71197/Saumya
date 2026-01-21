<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
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
            ] as $index => $testimonial)
            <div class="bg-white rounded-2xl border border-border p-8 shadow-soft hover:shadow-lg transition-shadow duration-300 hover:-translate-y-1"
                 data-aos="fade-up" data-aos-delay="{{ $index * 150 }}">
                <svg class="w-8 h-8 text-primary/30 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>
                <blockquote class="text-foreground text-lg mb-6 leading-relaxed">
                    "{{ $testimonial['quote'] }}"
                </blockquote>
                <div>
                    <p class="font-semibold text-foreground">{{ $testimonial['author'] }}</p>
                    <p class="text-sm text-muted-foreground">{{ $testimonial['role'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>