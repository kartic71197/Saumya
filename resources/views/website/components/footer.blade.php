<footer class="bg-text text-white py-16 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">

            <!-- Logo & Description -->
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                        <span class="text-white font-bold text-lg">S</span>
                    </div>
                    <span class="text-xl font-bold text-white">Saumya</span>
                </div>
                <p class="text-white/60 text-sm leading-relaxed">
                    The all-in-one platform for service businesses.
                </p>
            </div>

            <!-- Platform -->
            <div>
                <h4 class="font-semibold mb-4 text-white">Platform</h4>
                <ul class="space-y-2">
                    @foreach(['Features', 'Pricing', 'Integrations', 'API'] as $link)
                        <li>
                            <a href="#" class="text-white/60 hover:text-white text-sm transition-colors">
                                {{ $link }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Solutions -->
            <div>
                <h4 class="font-semibold mb-4 text-white">Solutions</h4>
                <ul class="space-y-2">
                    @foreach(['Medspas', 'Salons', 'Wellness Centers', 'Clinics'] as $link)
                        <li>
                            <a href="#" class="text-white/60 hover:text-white text-sm transition-colors">
                                {{ $link }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h4 class="font-semibold mb-4 text-white">Resources</h4>
                <ul class="space-y-2">
                    @foreach(['Blog', 'Help Center', 'Case Studies', 'Webinars'] as $link)
                        <li>
                            <a href="#" class="text-white/60 hover:text-white text-sm transition-colors">
                                {{ $link }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h4 class="font-semibold mb-4 text-white">Company</h4>
                <ul class="space-y-2">
                    @foreach(['About', 'Careers', 'Contact', 'Press'] as $link)
                        <li>
                            <a href="#" class="text-white/60 hover:text-white text-sm transition-colors">
                                {{ $link }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

        <!-- Bottom -->
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-white/60 text-sm">
                © {{ date('Y') }} Saumya. All rights reserved.
            </p>
            <div class="flex items-center gap-6 text-sm text-white/60">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
