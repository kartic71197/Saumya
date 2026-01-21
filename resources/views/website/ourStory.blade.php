{{--

OUR STORY

SHIV AND DR.GIRI PICTURE

Our journey began not in a boardroom, but on a golf course — where two professionals from vastly different worlds
discovered a shared vision.

Shivkumar Salgotra, a seasoned expert with over a decade of experience in process optimization and supply chain
management, spent years helping organizations run more efficiently. While working for a healthcare company, Shivkumar
noticed a critical issue—inefficiencies and a lack of visibility in how clinics managed their medical supplies. This
observation sparked the idea for a smarter, more streamlined inventory solution tailored specifically for healthcare
practices.

Fate introduced Shivkumar to Dr. Giri Dandamudi, a physician and entrepreneur, during a casual round of golf. Their
conversation quickly turned from swing techniques to supply chain woes. Dr. Giri, having experienced similar challenges
in his own practice, immediately recognized the value of Shivkumar's idea.

What started as a shared frustration soon evolved into a shared mission: to develop an intuitive, reliable, and scalable
software platform that could revolutionize inventory management for clinics, hospitals, and healthcare startups.

Combining Shivkumar's operational expertise with Dr. With Giri's clinical insight and years of experience, they
co-founded HEALTHSHADE dedicated to empowering healthcare providers with the tools they need to automate their clinic
inefficiencies — reducing waste, improving compliance, managing inventory and ultimately enhancing patient care.

--}}

@extends('website.main')
@section('content')
    <style>
        .fade-in {
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .timeline {
            position: relative;
            margin: 0 auto;
            padding: 2rem 0;
            max-width: 900px;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #fbbf24, #38bdf8);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .timeline-step {
            position: relative;
            width: 50%;
            padding: 2rem 2rem 2rem 0;
            text-align: right;
        }

        .timeline-step.left {
            left: 0;
        }

        .timeline-step.right {
            left: 50%;
            padding: 2rem 0 2rem 2rem;
            text-align: left;
        }

        .timeline-dot {
            position: absolute;
            top: 2.5rem;
            right: -1.25rem;
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, #fbbf24, #38bdf8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 4px solid #fff;
            z-index: 2;
        }

        .timeline-step.right .timeline-dot {
            left: -1.25rem;
            right: auto;
        }

        @media (max-width: 768px) {
            .timeline:before {
                left: 20px;
            }

            .timeline-step,
            .timeline-step.right,
            .timeline-step.left {
                width: 100%;
                padding: 2rem 0 2rem 3.5rem;
                text-align: left;
                left: 0;
            }

            .timeline-dot,
            .timeline-step.right .timeline-dot {
                left: 0;
                right: auto;
            }
        }
    </style>
    <div
        class="bg-white dark:bg-slate-900 min-h-screen text-gray-900 dark:text-white transition-colors duration-300 fade-in">
        <!-- Hero Section -->
        <section class="p-6 px-4 sm:px-6 lg:px-8 bg-white dark:bg-slate-900 transition-colors duration-300">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-extrabold mb-4 text-sky-400 dark:text-sky-300 tracking-tight">Our Story</h1>
                <div class="w-24 h-1 bg-gradient-to-r from-amber-400 to-sky-400 dark:from-amber-500 dark:to-sky-500 mx-auto rounded-full mb-8"></div>
                <p class="text-lg md:text-xl text-gray-600 dark:text-slate-300 max-w-2xl mx-auto">A journey of vision,
                    partnership, and innovation in healthcare supply management.</p>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="timeline">
            <!-- Step 1: Opening -->
            <div class="timeline-step left">
                <div class="timeline-dot">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                </div>
                <div
                    class="bg-gray-50 dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-lg flex flex-col items-center justify-center">
                    <p class="text-medium text-gray-700 dark:text-slate-300 leading-relaxed text-center p-4">
                        Our journey began not in a boardroom, but on a golf course — where two professionals from vastly
                        different worlds discovered a shared vision.
                    </p>
                </div>
            </div>
            <!-- Step 2: Shivkumar's Story -->
            <div class="timeline-step right">
                <div class="timeline-dot">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">SS</span>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-slate-900 rounded-2xl p-8 border border-amber-200 dark:border-amber-600 shadow-lg flex flex-col gap-4">
                    <div class="flex flex-row items-center justify-between gap-6 flex-wrap md:flex-nowrap bg-white dark:bg-slate-900">
                        <div
                            class="w-28 h-28 rounded-full overflow-hidden border-4 border-amber-400 dark:border-amber-600 shadow-lg flex-shrink-0">
                            <img src="{{ asset('shiv.png') }}" alt="Shivkumar Salgotra" class="object-cover w-full h-full" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-amber-500 font-bold text-lg text-start dark:text-amber-400">Shivkumar Salgotra</h3>
                            <p class="text-gray-500 dark:text-slate-400 text-sm mb-4 text-start">Process Optimization Expert
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 bg-gray-50 dark:bg-slate-800 rounded-xl p-4 border border-gray-200 dark:border-slate-700 mb-4">
                        <p class="text-medium text-gray-700 dark:text-slate-300 leading-relaxed mb-2 text-start">
                            Shivkumar Salgotra, a seasoned expert with over a decade of experience in process optimization
                            and supply chain management, spent years helping organizations run more efficiently.
                        </p>
                        <p class="text-medium text-gray-700 dark:text-slate-300 leading-relaxed mb-2">
                            While working for a healthcare company, Shivkumar noticed a critical issue—inefficiencies and a
                            lack of visibility in how clinics managed their medical supplies. This observation sparked the
                            idea for a smarter, more streamlined inventory solution tailored specifically for healthcare
                            practices.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Step 3: The Meeting -->
            <div class="timeline-step left">
                <div class="timeline-dot">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 12l2 2 4-4" />
                    </svg>
                </div>
                <div
                    class="bg-gray-50 dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 shadow-lg">
                    <h2
                        class="text-2xl font-bold mb-3 bg-gradient-to-r from-amber-400 to-sky-400 bg-clip-text text-transparent">
                        The Fateful Meeting</h2>
                    <p class="text-gray-700 dark:text-slate-300 leading-relaxed mb-2">
                        Fate introduced Shivkumar to Dr. Giri Dandamudi, a physician and entrepreneur, during a casual round
                        of golf. Their conversation quickly turned from swing techniques to supply chain woes.
                    </p>
                    <p class="text-gray-700 dark:text-slate-300 leading-relaxed mb-2">
                        Dr. Giri, having experienced similar challenges in his own practice, immediately recognized the
                        value of Shivkumar's idea.
                    </p>
                </div>
            </div>
            <!-- Step 4: Dr. Giri's Story -->
            <div class="timeline-step right">
                <div class="timeline-dot">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">DG</span>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-slate-900 rounded-2xl p-8 border border-sky-200 dark:border-sky-600 shadow-lg flex flex-col gap-4">
                    <div class="flex flex-row items-center justify-between gap-6 flex-wrap md:flex-nowrap bg-white dark:bg-slate-900">
                        <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-sky-400 dark:border-sky-600 shadow-lg flex-shrink-0">
                            <img src="{{ asset('dr-giri.png') }}" alt="Dr. Giri Dandamudi" class="object-cover w-full h-full" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sky-500 font-bold text-lg dark:text-sky-400">Dr. Giri Dandamudi</h3>
                            <p class="text-gray-500 dark:text-slate-400 text-sm mb-4">Physician & Entrepreneur</p>

                        </div>
                    </div>
                    <div class="mt-4 bg-gray-50 dark:bg-slate-800 rounded-xl p-4 border border-gray-200 dark:border-sky-700 mb-4">
                        <p class="text-gray-700 dark:text-slate-300 leading-relaxed mb-2">
                            What started as a shared frustration soon evolved into a shared mission: to develop an
                            intuitive, reliable, and scalable software platform that could revolutionize inventory
                            management for clinics, hospitals, and healthcare startups.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Step 5: The Mission -->
            <div class="timeline-step left">
                <div class="timeline-dot">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3" />
                    </svg>
                </div>
                <div
                    class="bg-gray-50 dark:bg-slate-800 rounded-2xl p-8 border border-gray-200 dark:border-slate-700 shadow-lg">
                    <h2
                        class="text-2xl font-bold mb-3 bg-gradient-to-r from-amber-400 to-sky-400 bg-clip-text text-transparent">
                        Our Mission</h2>
                    <p class="text-gray-700 dark:text-slate-300 leading-relaxed mb-6">
                        Combining Shivkumar's operational expertise with Dr. Giri's clinical insight and years of
                        experience, they co-founded <span class="text-amber-500 font-bold">HEALTHSHADE</span> dedicated to
                        empowering healthcare providers with the tools they need to automate their clinic inefficiencies.
                    </p>
                    {{-- <div class="grid md:grid-cols-3 gap-6">
                        <div
                            class="text-center p-6 bg-white dark:bg-slate-700 rounded-xl shadow-md border border-gray-200 dark:border-slate-600">
                            <div
                                class="w-16 h-16 bg-amber-400 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold">1</span>
                            </div>
                            <h3 class="text-amber-500 font-bold mb-2">Reducing Waste</h3>
                            <p class="text-gray-600 dark:text-slate-300 text-sm">Eliminate unnecessary costs and expired
                                supplies</p>
                        </div>
                        <div
                            class="text-center p-6 bg-white dark:bg-slate-700 rounded-xl shadow-md border border-gray-200 dark:border-slate-600">
                            <div
                                class="w-16 h-16 bg-sky-400 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold">2</span>
                            </div>
                            <h3 class="text-sky-500 font-bold mb-2">Improving Compliance</h3>
                            <p class="text-gray-600 dark:text-slate-300 text-sm">Maintain regulatory standards effortlessly
                            </p>
                        </div>
                        <div
                            class="text-center p-6 bg-white dark:bg-slate-700 rounded-xl shadow-md border border-gray-200 dark:border-slate-600">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-amber-400 to-sky-400 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold">3</span>
                            </div>
                            <h3 class="text-amber-500 font-bold mb-2">Managing Inventory</h3>
                            <p class="text-gray-600 dark:text-slate-300 text-sm">Streamline supply chain operations</p>
                        </div>
                    </div> --}}
                </div>
            </div>
            <!-- Step 6: Final Mission Statement -->
            <div class="timeline-step right">
                <div class="timeline-dot">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-lg">
                    <p class="text-medium text-gray-700 dark:text-slate-300 leading-relaxed text-center p-4">
                        Our ultimate goal is <span class="text-sky-500 font-bold">enhancing patient care</span> by ensuring
                        healthcare providers have the right supplies at the right time, allowing them to focus on what
                        matters most—their patients.
                    </p>
                </div>
            </div>
        </section>
    </div>
@endsection