<section class="py-16 bg-muted/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-8" data-aos="fade-up">
            <p class="text-muted-foreground text-lg">
                Trusted by thousands of service businesses worldwide
            </p>
            
            <div class="flex flex-wrap justify-center items-center gap-6 lg:gap-12">
                @foreach([
                    ['GS', 'Glow Spa'],
                    ['ZW', 'Zen Wellness'],
                    ['BH', 'Beauty Hub'],
                    ['SM', 'Serene Med'],
                    ['ES', 'Elite Salon'],
                    ['PS', 'Pure Skin']
                ] as $index => $business)
                <div class="flex items-center gap-3" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="text-sm font-semibold text-primary">
                            {{ $business[0] }}
                        </span>
                    </div>
                    <span class="text-muted-foreground font-medium">
                        {{ $business[1] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>