<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <div class="min-h-screen py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-light text-gray-800 mb-3">Book an Appointment</h1>
                <p class="text-gray-600 text-lg">Select an organization to get started</p>
            </div>

            <!-- Organizations Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($organizations as $organization)
                    <a href="{{ route('appointments.client.create', $organization->slug) }}"
                        class="group bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-white/50 hover:border-blue-100 hover:-translate-y-1">

                        <!-- Organization Logo/Image Section -->
                        <div
                            class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-8 flex items-center justify-center h-48">
                            @if ($organization->image)
                                <img src="{{ asset('storage/' . $organization->image) }}"
                                    alt="{{ $organization->name }}" class="max-h-32 max-w-full object-contain" />
                            @else
                                <div class="w-24 h-24 bg-white rounded-2xl shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Organization Details Section -->
                        <div class="p-6 bg-white">
                            <!-- Organization Name -->
                            <h3
                                class="text-xl font-medium text-gray-800 mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ $organization->name }}
                            </h3>

                            <!-- Address -->
                            <div class="flex items-start gap-2 mb-4">
                                <svg class="w-4 h-4 text-indigo-400 mt-0.5 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">
                                    {{ $organization->address ?? 'Address not available' }}
                                </p>
                            </div>

                            <!-- Stats Section -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <!-- Services Count -->
                                @php
                                    $servicesCount = $organization->appointmentCategories->sum(function ($cat) {
                                        return $cat->services->count();
                                    });
                                    $categoriesCount = $organization->appointmentCategories->count();
                                @endphp

                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>{{ $servicesCount }} services</span>
                                    </div>
                                    @if ($categoriesCount > 0)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            <span>{{ $categoriesCount }} categories</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Arrow Icon -->
                                <div
                                    class="text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <!-- Empty State -->
                    <div class="col-span-full">
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-12 text-center border border-white/50">
                            <div
                                class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-medium text-gray-800 mb-2">No Practice/Clinic/Spa Available</h3>
                            <p class="text-gray-500">There are currently no Practice/Clinic/Spa offering appointments.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>

</html>