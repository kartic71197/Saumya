@extends('website.main')
@section('content')
    @include('website.partials.herosection')

    @include('website.partials.qr')
    
    @include('website.partials.videoSection')


    @include('website.partials.features')


    <div class="integrations">
        <div class="integrations-heading">Simplify your everyday tasks.</div>
        <div class="integrations-sub-text">by seamlessly connecting your tools and streamline your workflow.</div>
        <div class="integration-features">
            <div class="integration-feature integration-feature-1">
                <div>
                    <span class="material-symbols-outlined">
                        lab_profile
                    </span>
                </div>
                <div class="heading">
                    Reporting
                </div>
                <div class="tag-line">
                    Stay on top of things with always up-to-date reporting features.
                </div>
                <div class="summary">
                    Generate customized reports on inventory usage, costs, and trends. These reports provide valuable
                    insights for strategic decision-making and continuous improvement in inventory management practices.
                </div>
            </div>
            <div class="integration-feature integration-feature-1">
                <div>
                    <span class="material-symbols-outlined">
                        inventory
                    </span>
                </div>
                <div class="heading">
                    Inventory
                </div>
                <div class="tag-line">
                    Never lose of track of what's in stock with accurate inventory tracking.
                </div>
                <div class="summary">
                    Serving as the central hub for all your medical inventory activities, our Inventory System allows
                    users to promptly check stock quantities and issues alerts for timely supply reorders and
                    facilitates effortless monitoring for accurate inventory
                    levels.
                </div>
            </div>
            <div class="integration-feature integration-feature-1">
                <div>
                    <span class="material-symbols-outlined">
                        folder_limited
                    </span>
                </div>
                <div class="heading">
                    Minimizing Errors
                </div>
                <div class="tag-line">
                    Always ready to full fill the demand with Healthshade.
                </div>
                <div class="summary">
                    Our foremost commitment is meeting the medical supply requirements of every business. Addressing the
                    intricate demands of clinics, our efficient inventory management software delivers unparalleled
                    barcoding and reporting capabilities.
                </div>
            </div>
        </div>
        <div class="integrations-message">Get Integration with Suppliers like </div>
        <div class="suppliers">
            <img class="slowFading" src="{{ url('/suppliers/hs.png') }}" alt="henryschein">
            <img class="slowFading" src="{{ url('/suppliers/mck.png') }}" alt="henryschein">
            <img class="slowFading" src="{{ url('/suppliers/greer.png') }}" alt="greer">
            <img class="slowFading" src="{{ url('/suppliers/alk.png') }}" alt="alk">
            <img class="slowFading" src="{{ url('/suppliers/hollister.png') }}" alt="hollister">
        </div>

        <!-- Testimonials Section -->
        <section class="bg-gradient-to-br from-blue-50 to-gray-100 py-16">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-4xl font-extrabold text-center text-primary-800 mb-12 tracking-tight">What Our Clients Say</h2>
                <div class="grid md:grid-cols-2 gap-10">
                    <!-- Testimonial 1 -->
                    <div class="relative bg-white p-10 rounded-2xl shadow-lg border border-blue-100 transition-transform transform hover:-translate-y-1 hover:shadow-2xl">
                        <svg class="absolute top-6 left-6 w-10 h-10 text-blue-200" fill="currentColor" viewBox="0 0 24 24"><path d="M7.17 6.17A7.001 7.001 0 0 0 2 13a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1c0-1.306.835-2.417 2-2.83V9a3 3 0 0 0-2.83-2.83zM17.17 6.17A7.001 7.001 0 0 0 12 13a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1c0-1.306.835-2.417 2-2.83V9a3 3 0 0 0-2.83-2.83z"/></svg>
                        <p class="text-xs text-gray-700 italic mb-6 mt-4 text-lg leading-relaxed">
                            “I’ve been using HealthShade for several months now, and I can confidently say that it has transformed the way I manage my inventory data. The interface is clean, intuitive, and incredibly user-friendly. Seamlessly integrates all aspects of my inventory tracking, from partially received orders to the master catalog and medication orders all in one cohesive platform.
                            <br><br>
                            The insights and analytics provided by HealthShade have helped me make more informed decisions and stay on top of my goals. The support team is also fantastic—quick to respond and genuinely helpful. I highly recommend HealthShade to anyone looking to take control of their inventory management in a smarter, more organized way.”
                        </p>
                        <div class="flex items-center gap-3 mb-3">
                            <div>
                                <span class="inline-block h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-primary-700">S</span>
                            </div>
                            <div class="text-xs">
                                <div class="font-semibold text-primary-700 text-xs">Seema Odhav</div>
                                <div class="text-xs text-gray-500">Asthma, Allergy, & Immunology Center</div>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial 2 -->
                    <div class="relative bg-white p-10 rounded-2xl shadow-lg border border-blue-100 transition-transform transform hover:-translate-y-1 hover:shadow-2xl">
                        <svg class="absolute top-6 left-6 w-10 h-10 text-blue-200" fill="currentColor" viewBox="0 0 24 24"><path d="M7.17 6.17A7.001 7.001 0 0 0 2 13a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1c0-1.306.835-2.417 2-2.83V9a3 3 0 0 0-2.83-2.83zM17.17 6.17A7.001 7.001 0 0 0 12 13a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1c0-1.306.835-2.417 2-2.83V9a3 3 0 0 0-2.83-2.83z"/></svg>
                        <p class="text-xs text-gray-700 italic mb-6 mt-4 text-lg leading-relaxed">
                            "Healthshade inventory management software and cybersecurity solutions have changed the way we operate. Their system is intuitive, reliable, and gives us real-time visibility into stock levels, which has reduced waste, improved order accuracy, and streamlined our workflow.
                            <br><br>
                            Their cybersecurity platform is valuable! In today's digital landscape, data protection is non-negotiable, and this gave us peace of mind. Also ensured our data is protected."
                        </p>
                        <div class="flex items-center gap-3 mb-3">
                            <div>
                                <span class="inline-block h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-primary-700">A</span>
                            </div>
                            <div class="text-xs">
                                <div class="font-semibold text-primary-700 text-xs">Andrea</div>
                                <div class="text-xs text-gray-500">Healthshade Client</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('website.partials.free-trial')

    @include('website.partials.why-us')

    @include('website.partials.sounds-like-plan')
@endsection
