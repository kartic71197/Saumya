<footer class="bg-gray-900 dark:bg-gray-950 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">
            {{-- Logo & Description --}}
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                        <span class="text-white font-bold text-lg">S</span>
                    </div>
                    <span class="text-xl font-bold">Saumya</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">
                    The all-in-one platform for service businesses.
                </p>
            </div>

            @php
                $footerLinks = [
                    'Platform' => ['Features', 'Pricing', 'Integrations', 'API'],
                    'Solutions' => ['Medspas', 'Salons', 'Wellness Centers', 'Clinics'],
                    'Resources' => ['Blog', 'Help Center', 'Case Studies', 'Webinars'],
                    'Company' => ['About', 'Careers', 'Contact', 'Press']
                ];
            @endphp

            {{-- Links --}}
            @foreach($footerLinks as $category => $links)
                <div>
                    <h4 class="font-semibold mb-4">{{ $category }}</h4>
                    <ul class="space-y-2">
                        @foreach($links as $link)
                            <li>
                                <a 
                                    href="#" 
                                    class="text-gray-400 hover:text-white text-sm transition-colors duration-200"
                                >
                                    {{ $link }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-800 dark:border-gray-700 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-400 text-sm">
                © {{ date('Y') }} Saumya. All rights reserved.
            </p>
            <div class="flex items-center gap-6 text-sm text-gray-400">
                <a href="#" class="hover:text-white transition-colors duration-200">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors duration-200">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>