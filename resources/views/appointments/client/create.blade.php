<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - {{ $organization->name }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div x-data="appointmentBooking()" class="min-h-screen p-6">
        <div class="max-w-7xl mx-auto">
            <a href="{{ route('appointments.client.index') }}"
                class="inline-flex items-center text-gray-600 hover:text-gray-800 mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h1 class="text-3xl font-light text-gray-800 mb-8">Book Appointment</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if ($organization->logo)
                                    <img src="{{ asset('storage/' . $organization->logo) }}"
                                        alt="{{ $organization->name }}" class="w-full h-full object-cover" />
                                @else
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-medium text-gray-800 mb-1">{{ $organization->name }}</h2>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    {{ $organization->address ?? 'Address not available' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                        <h2 class="text-lg font-medium text-gray-800 mb-4">Selected Services</h2>

                        <template x-if="selectedServices.length === 0">
                            <p class="text-gray-400 text-sm">No services selected</p>
                        </template>

                        <div class="space-y-3 mb-6">
                            <template x-for="service in selectedServices" :key="service.id">
                                <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-700" x-text="service.name"></p>
                                        <p class="text-xs text-gray-500" x-text="service.duration + ' min'"></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600" x-text="'$' + service.price"></span>
                                        <button @click="removeService(service.id)"
                                            class="text-gray-400 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Total Duration</span>
                                <span class="text-sm font-medium text-gray-800" x-text="totalDuration + ' min'"></span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Slots Needed</span>
                                <span class="text-sm font-medium text-gray-800"
                                    x-text="requiredSlots + ' x 15min'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-gray-800">Total Price</span>
                                <span class="text-2xl font-semibold text-gray-900"
                                    x-text="'$' + totalPrice.toFixed(2)"></span>
                            </div>
                        </div>

                        <template x-if="selectedSlots.length > 0">
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-700 mb-1">Selected Time</p>
                                <p class="text-sm text-gray-600" x-text="formatSelectedSlots()"></p>
                                <p class="text-sm text-gray-600 mt-2">
                                    <span class="font-medium">Staff:</span>
                                    <span x-text="getStaffName()"></span>
                                </p>
                            </div>
                        </template>

                        <button @click="showBookingForm = true"
                            :disabled="selectedServices.length === 0 || selectedSlots.length === 0"
                            :class="(selectedServices.length === 0 || selectedSlots.length === 0) ?
                            'bg-gray-300 cursor-not-allowed' : 'bg-gray-800 hover:bg-gray-700'"
                            class="w-full mt-6 text-white py-3 rounded-lg transition-colors">
                            Continue to Booking
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="mb-6 bg-white rounded-lg shadow-sm p-2 inline-flex">
                        <button @click="viewMode = 'services'"
                            :class="viewMode === 'services' ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            class="px-6 py-2 rounded-md font-medium transition-colors">
                            Select Services
                        </button>
                        <button @click="viewMode = 'calendar'"
                            :class="viewMode === 'calendar' ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            class="px-6 py-2 rounded-md font-medium transition-colors">
                            Select Time
                        </button>
                    </div>

                    <div x-show="viewMode === 'services'">
                        <div class="mb-6 overflow-x-auto pb-2">
                            <div class="flex gap-2 min-w-max">
                                <button @click="selectedCategory = null"
                                    :class="selectedCategory === null ? 'bg-gray-800 text-white' :
                                        'bg-white text-gray-700 hover:bg-gray-100'"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap shadow-sm">
                                    All Categories
                                </button>
                                <template x-for="category in categories" :key="category.id">
                                    <button @click="selectedCategory = category.id"
                                        :class="selectedCategory === category.id ? 'bg-gray-800 text-white' :
                                            'bg-white text-gray-700 hover:bg-gray-100'"
                                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap shadow-sm"
                                        x-text="category.name">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <template x-for="category in filteredCategories" :key="category.id">
                                <div class="bg-white rounded-lg shadow-sm p-6">
                                    <h3 class="text-lg font-medium text-gray-800 mb-4" x-text="category.name"></h3>

                                    <div class="space-y-3">
                                        <template x-for="service in category.services" :key="service.id">
                                            <div
                                                class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <h4 class="font-medium text-gray-800 mb-1"
                                                            x-text="service.name"></h4>
                                                        <p class="text-sm text-gray-500 mb-2"
                                                            x-text="service.description"></p>
                                                        <div class="flex items-center gap-4 text-sm">
                                                            <span class="text-gray-600"
                                                                x-text="service.duration + ' min'"></span>
                                                            <span class="font-semibold text-gray-900"
                                                                x-text="'$' + service.price"></span>
                                                        </div>
                                                    </div>
                                                    <button @click="addService(service)"
                                                        :disabled="isServiceSelected(service.id)"
                                                        :class="isServiceSelected(service.id) ?
                                                            'bg-gray-300 cursor-not-allowed' :
                                                            'bg-gray-800 hover:bg-gray-700'"
                                                        class="text-white w-8 h-8 rounded-full flex items-center justify-center transition-colors ml-4 flex-shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="viewMode === 'calendar'" class="bg-white rounded-lg shadow-sm p-6">
                        <template x-if="selectedServices.length === 0">
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-500 text-lg">Please select services first</p>
                                <button @click="viewMode = 'services'"
                                    class="mt-4 px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                                    Select Services
                                </button>
                            </div>
                        </template>

                        <template x-if="selectedServices.length > 0">
                            <div>
                                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-sm text-blue-800">
                                        <span class="font-medium">Note:</span> Your appointment requires
                                        <span class="font-semibold" x-text="requiredSlots"></span> consecutive
                                        15-minute slot(s) (<span x-text="totalDuration"></span> minutes total).
                                        Click on the starting time slot.
                                    </p>
                                </div>

                                <!-- Staff Selection -->
                                <div class="mb-6">
                                    <h3 class="text-sm font-medium text-gray-700 mb-4">Select Staff</h3>
                                    <div class="flex gap-4 overflow-x-auto p-2">
                                        <!-- Any Option -->
                                        {{-- <button @click="selectedStaff = ''"
                                            :class="selectedStaff === '' ? 'ring-2 ring-gray-800' :
                                                'ring-1 ring-gray-200'"
                                            class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-all flex-shrink-0">
                                            <div
                                                class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white text-xl font-semibold">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">Any</span>
                                        </button> --}}

                                        <!-- Staff Members -->
                                        <template x-for="staff in staffMembers" :key="staff.id">
                                            <button @click="selectedStaff = staff.id"
                                                :class="selectedStaff === staff.id ? 'ring-2 ring-gray-800' :
                                                    'ring-1 ring-gray-200'"
                                                class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-all flex-shrink-0">
                                                <div
                                                    class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white text-xl font-semibold overflow-hidden">
                                                    <template x-if="staff.avatar">
                                                        <img :src="'/storage/' + staff.avatar" :alt="staff.name"
                                                            class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!staff.avatar">
                                                        <span x-text="staff.name.charAt(0).toUpperCase()"></span>
                                                    </template>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700 text-center"
                                                    x-text="staff.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <div class="flex items-center gap-4 mb-4">
                                        <button @click="previousWeek()" class="p-2 hover:bg-gray-100 rounded-full">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <div class="flex-1 overflow-x-auto">
                                            <div class="flex gap-2 min-w-max">
                                                <template x-for="date in weekDates" :key="date.dateStr">
                                                    <button @click="selectedDate = date.dateStr"
                                                        :class="selectedDate === date.dateStr ? 'bg-gray-800 text-white' :
                                                            'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                                        class="px-4 py-3 rounded-lg transition-colors min-w-[100px]">
                                                        <div class="text-xs font-medium" x-text="date.dayName"></div>
                                                        <div class="text-lg font-semibold" x-text="date.day"></div>
                                                        <div class="text-xs" x-text="date.month"></div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <button @click="nextWeek()" class="p-2 hover:bg-gray-100 rounded-full">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Morning
                                            </span>
                                            <div class="h-px flex-1 bg-gray-200"></div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="timeSlot in morningSlots" :key="timeSlot">
                                                <button @click="selectSlot(0, timeSlot)"
                                                    :class="isTimeSlotSelected(timeSlot) ?
                                                        'bg-gray-800 text-white border-gray-800' :
                                                        'bg-white text-gray-700 border-gray-300 hover:border-gray-400 hover:bg-gray-50'"
                                                    class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all"
                                                    x-text="formatTime(timeSlot)">
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Afternoon
                                            </span>
                                            <div class="h-px flex-1 bg-gray-200"></div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="timeSlot in afternoonSlots" :key="timeSlot">
                                                <button @click="selectSlot(0, timeSlot)"
                                                    :class="isTimeSlotSelected(timeSlot) ?
                                                        'bg-gray-800 text-white border-gray-800' :
                                                        'bg-white text-gray-700 border-gray-300 hover:border-gray-400 hover:bg-gray-50'"
                                                    class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all"
                                                    x-text="formatTime(timeSlot)">
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Evening
                                            </span>
                                            <div class="h-px flex-1 bg-gray-200"></div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="timeSlot in eveSlots" :key="timeSlot">
                                                <button @click="selectSlot(0, timeSlot)"
                                                    :class="isTimeSlotSelected(timeSlot) ?
                                                        'bg-gray-800 text-white border-gray-800' :
                                                        'bg-white text-gray-700 border-gray-300 hover:border-gray-400 hover:bg-gray-50'"
                                                    class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all"
                                                    x-text="formatTime(timeSlot)">
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showBookingForm" x-cloak @click.self="showBookingForm = false"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-6 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-medium text-gray-800">Complete Booking</h2>
                    <button @click="showBookingForm = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('appointments.client.store', $organization->slug) }}" method="POST"
                    class="space-y-6">
                    @csrf

                    <template x-for="service in selectedServices" :key="service.id">
                        <input type="hidden" name="services[]" :value="service.id">
                    </template>

                    <input type="hidden" name="appointment_date" :value="selectedSlots[0]?.date">
                    <input type="hidden" name="start_time" :value="selectedSlots[0]?.time">
                    <input type="hidden" name="staff_id" :value="selectedStaff">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="customer_name" required value="{{ old('customer_name') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="customer_email" required value="{{ old('customer_email') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" name="customer_phone" required value="{{ old('customer_phone') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-800 mb-3">Booking Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date & Time</span>
                                <span class="text-gray-800" x-text="formatSelectedSlots()"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Staff</span>
                                <span class="text-gray-800" x-text="getStaffName()"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Services</span>
                                <span class="text-gray-800" x-text="selectedServices.length + ' selected'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Duration</span>
                                <span class="text-gray-800" x-text="totalDuration + ' min'"></span>
                            </div>
                            <div class="flex justify-between text-lg font-medium pt-2 border-t border-gray-200">
                                <span class="text-gray-800">Total</span>
                                <span class="text-gray-900" x-text="'$' + totalPrice.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-lg transition-colors font-medium">
                        Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function appointmentBooking() {
            return {
                categories: @json($categories),
                staffMembers: @json($staffMembers),
                selectedCategory: null,
                selectedServices: [],
                selectedStaff: '',
                showBookingForm: false,
                viewMode: 'services',
                selectedDate: null,
                selectedSlots: [],
                weekStartDate: new Date(),

                init() {
                    this.selectedDate = this.formatDate(new Date());
                },
                getStaffName() {
                    if (this.selectedStaff === '') {
                        return 'Any Available Staff';
                    }
                    const staff = this.staffMembers.find(s => s.id === this.selectedStaff);
                    return staff ? staff.name : 'Not selected';
                },

                get filteredCategories() {
                    if (this.selectedCategory === null) {
                        return this.categories;
                    }
                    return this.categories.filter(cat => cat.id === this.selectedCategory);
                },

                get totalPrice() {
                    return this.selectedServices.reduce((sum, service) => sum + parseFloat(service.price), 0);
                },

                get totalDuration() {
                    return this.selectedServices.reduce((sum, service) => sum + parseInt(service.duration), 0);
                },

                get requiredSlots() {
                    return Math.ceil(this.totalDuration / 15);
                },

                get weekDates() {
                    const dates = [];
                    for (let i = 0; i < 7; i++) {
                        const date = new Date(this.weekStartDate);
                        date.setDate(date.getDate() + i);
                        dates.push({
                            dateStr: this.formatDate(date),
                            dayName: date.toLocaleDateString('en-US', {
                                weekday: 'short'
                            }),
                            day: date.getDate(),
                            month: date.toLocaleDateString('en-US', {
                                month: 'short'
                            }),
                            fullDate: date
                        });
                    }
                    return dates;
                },

                get timeSlots() {
                    const slots = [];
                    const startHour = 9;
                    const endHour = 17;

                    for (let hour = startHour; hour < endHour; hour++) {
                        for (let minute = 0; minute < 60; minute += 15) {
                            const timeStr = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                            slots.push(timeStr);
                        }
                    }
                    return slots;
                },

                get morningSlots() {
                    return this.timeSlots.filter(slot => {
                        const hour = parseInt(slot.split(':')[0]);
                        return hour >= 9 && hour < 12;
                    });
                },

                get afternoonSlots() {
                    return this.timeSlots.filter(slot => {
                        const hour = parseInt(slot.split(':')[0]);
                        return hour >= 12 && hour < 17;
                    });
                },
                get eveSlots() {
                    return this.timeSlots.filter(slot => {
                        const hour = parseInt(slot.split(':')[0]);
                        return hour >= 17 && hour < 21; 
                    });
                },

                formatTime(timeStr) {
                    const [hour, minute] = timeStr.split(':');
                    const hourNum = parseInt(hour);
                    const period = hourNum >= 12 ? 'PM' : 'AM';
                    const displayHour = hourNum > 12 ? hourNum - 12 : (hourNum === 0 ? 12 : hourNum);
                    return `${displayHour}:${minute} ${period}`;
                },

                formatDate(date) {
                    return date.toISOString().split('T')[0];
                },

                getDayHeader(dayIndex) {
                    return this.weekDates[dayIndex]?.dayName || '';
                },

                previousWeek() {
                    this.weekStartDate.setDate(this.weekStartDate.getDate() - 7);
                    this.weekStartDate = new Date(this.weekStartDate);
                },

                nextWeek() {
                    this.weekStartDate.setDate(this.weekStartDate.getDate() + 7);
                    this.weekStartDate = new Date(this.weekStartDate);
                },

                selectSlot(dayIndex, time) {
                    const slotIndex = this.timeSlots.indexOf(time);

                    // Check if we can fit required slots
                    if (slotIndex + this.requiredSlots > this.timeSlots.length) {
                        alert('Not enough consecutive slots available for this time.');
                        return;
                    }

                    // Clear previous selection
                    this.selectedSlots = [];

                    // Get selected date info
                    const selectedDateObj = this.weekDates.find(d => d.dateStr === this.selectedDate);

                    // Select consecutive slots
                    for (let i = 0; i < this.requiredSlots; i++) {
                        const slotTime = this.timeSlots[slotIndex + i];
                        this.selectedSlots.push({
                            date: this.selectedDate,
                            time: slotTime,
                            dayName: selectedDateObj.dayName,
                            day: selectedDateObj.day,
                            month: selectedDateObj.month,
                            slotIndex: slotIndex + i
                        });
                    }
                },

                isTimeSlotSelected(time) {
                    return this.selectedSlots.some(slot => slot.time === time);
                },

                formatSelectedSlots() {
                    if (this.selectedSlots.length === 0) return '';
                    const first = this.selectedSlots[0];
                    const last = this.selectedSlots[this.selectedSlots.length - 1];

                    // Get the selected date info
                    const selectedDateObj = this.weekDates.find(d => d.dateStr === this.selectedDate);

                    // Calculate end time
                    const [hour, minute] = last.time.split(':').map(Number);
                    const endMinute = minute + 30;
                    const endHour = hour + Math.floor(endMinute / 60);
                    const finalMinute = endMinute % 60;
                    const endTime = `${endHour.toString().padStart(2, '0')}:${finalMinute.toString().padStart(2, '0')}`;

                    return `${selectedDateObj.dayName}, ${selectedDateObj.month} ${selectedDateObj.day} from ${this.formatTime(first.time)} to ${this.formatTime(endTime)}`;
                },

                addService(service) {
                    if (!this.isServiceSelected(service.id)) {
                        this.selectedServices.push(service);
                        // Clear slot selection when services change
                        this.selectedSlots = [];
                    }
                },

                removeService(serviceId) {
                    this.selectedServices = this.selectedServices.filter(s => s.id !== serviceId);
                    // Clear slot selection when services change
                    this.selectedSlots = [];
                },

                isServiceSelected(serviceId) {
                    return this.selectedServices.some(s => s.id === serviceId);
                }
            }
        }
    </script>
</body>

</html>
