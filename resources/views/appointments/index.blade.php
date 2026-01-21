<x-app-layout>
    <div class="container mx-auto px-4 py-6" x-data="appointmentCalendar()">
        <!-- Header -->
        {{-- <div class="mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Appointments Calendar</h1>
                <a href="{{ route('appointments.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    New Appointment
                </a>
            </div>
        </div> --}}

        <!-- Enhanced Week Navigation -->
        <div
            class="mb-6 bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 rounded-2xl shadow-xl p-6 border border-purple-100">
            <!-- Quick Navigation Buttons -->
            <div
                class="flex items-center justify-between mb-5 pb-5 border-b-2 border-gradient-to-r from-purple-200 to-pink-200">
                <div class="flex items-center gap-3">
                    <button @click="goToToday()"
                        class="px-5 py-2.5 bg-gradient-to-r from-primary-dk to-primary-md text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Today
                        </span>
                    </button>
                    <button @click="goToTomorrow()"
                        class="px-5 py-2.5 bg-gradient-to-r from-primary-dk to-primary-md text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            Tomorrow
                        </span>
                    </button>
                    <button @click="goToThisWeek()"
                        class="px-5 py-2.5 bg-gradient-to-r from-primary-dk to-primary-md text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        This Week
                    </button>
                    <button @click="goToNextWeek()"
                        class="px-5 py-2.5 bg-gradient-to-r from-primary-dk to-primary-md text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        Next Week
                    </button>
                </div>
                <div class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-primary-dk to-primary-md"
                    x-text="currentWeekRange"></div>
            </div>

            <!-- Week Scroll with Dropdown -->
            <div class="flex items-center gap-4">
                <button @click="previousWeek()"
                    class="p-3 bg-white hover:bg-gradient-to-br hover:from-purple-100 hover:to-pink-100 rounded-full shadow-md hover:shadow-lg transition-all duration-200 flex-shrink-0 border-2 border-purple-200">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div
                    class="flex gap-3 overflow-x-auto p-2 scrollbar-thin scrollbar-thumb-purple-300 scrollbar-track-transparent">
                    <template x-for="date in weekDates" :key="date.dateStr">
                        <button @click="selectedDate = date.dateStr; loadAppointments()"
                            :class="[
                                selectedDate === date.dateStr ?
                                'bg-gradient-to-br from-primary-md to-primary-dk text-white shadow-2xl scale-110 ring-4 ring-purple-300' :
                                date.isToday ?
                                'bg-gradient-to-br from-blue-400 to-cyan-400 text-white border-4 border-blue-600 shadow-xl' :
                                'bg-white text-gray-700 hover:bg-gradient-to-br hover:from-purple-50 hover:to-pink-50 shadow-md hover:shadow-xl border-2 border-gray-200',
                                'relative'
                            ]"
                            class="px-5 py-4 rounded-2xl transition-all duration-300 w-[110px] flex-shrink-0 transform hover:-translate-y-1">
                            <div class="text-xs font-bold uppercase tracking-wider" x-text="date.dayName"></div>
                            <div class="text-2xl font-extrabold my-1" x-text="date.day"></div>
                            <div class="text-xs font-semibold" x-text="date.month"></div>
                            <div x-show="date.isToday && selectedDate !== date.dateStr"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full animate-pulse shadow-lg">
                            </div>
                        </button>
                    </template>
                </div>

                <button @click="nextWeek()"
                    class="p-3 bg-white hover:bg-gradient-to-br hover:from-purple-100 hover:to-pink-100 rounded-full shadow-md hover:shadow-lg transition-all duration-200 flex-shrink-0 border-2 border-purple-200">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Week Selector Dropdown -->
                <div class="ml-auto flex-shrink-0">
                    <select @change="jumpToWeek($event.target.value)"
                        class="px-5 py-3 border-2 border-purple-300 rounded-xl text-sm font-semibold text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-4 focus:ring-purple-300 shadow-md cursor-pointer">
                        <option value="">🗓️ Jump to week...</option>
                        <template x-for="week in weekOptions" :key="week.value">
                            <option :value="week.value" :selected="week.value === getCurrentWeekValue()"
                                x-text="week.label"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-[1000px]">
                    <!-- Header Row with Staff Names -->
                    <div class="grid gap-px bg-gray-200"
                        :style="`grid-template-columns: 80px repeat(${staff.length}, 1fr);`">
                        <div class="bg-gray-50 p-3 font-semibold text-sm text-gray-700">Time</div>
                        <template x-for="member in staff" :key="member.id">
                            <div class="bg-gray-50 p-3 text-center">
                                <div class="font-semibold text-sm text-gray-900" x-text="member.name"></div>
                                <div class="text-xs text-gray-500"
                                    x-text="getStaffAppointmentCount(member.id) + ' appts'"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Time Slots Grid -->
                    <div class="relative">
                        <template x-for="timeSlot in timeSlots" :key="timeSlot">
                            <div class="grid gap-px bg-gray-200"
                                :style="`grid-template-columns: 80px repeat(${staff.length}, 1fr);`">
                                <!-- Time Label -->
                                <div class="bg-white p-2 text-xs font-medium"
                                    :class="timeSlot.endsWith(':00') ? 'text-gray-900 border-t-2 border-gray-300' :
                                        'text-gray-400 border-t border-gray-100'">
                                    <span x-show="timeSlot.endsWith(':00')" x-text="timeSlot"></span>
                                </div>

                                <!-- Staff Columns -->
                                <template x-for="member in staff" :key="member.id">
                                    <div class="bg-white relative h-[60px] border-t border-gray-200 hover:bg-gray-50 cursor-pointer p-2"
                                        :class="timeSlot.endsWith(':00') ? 'border-t-2 border-gray-300' :
                                            'border-t border-gray-100'"
                                        @click="openCreateModal(member.id, timeSlot)">
                                        <!-- Render appointments in this slot -->
                                        <template x-for="appointment in getAppointmentsForSlot(member.id, timeSlot)"
                                            :key="appointment.id">
                                            <div x-show="timeSlot === appointment.start_time.substring(0, 5)"
                                                @click.stop="showAppointmentDetail(appointment)"
                                                class="absolute inset-x-1 rounded-lg p-2 cursor-pointer hover:shadow-xl hover:scale-[1.02] transition-all z-10 border-l-4"
                                                :style="getAppointmentStyle(appointment)"
                                                :class="getAppointmentColor(appointment.id)">
                                                <div class="text-xs font-bold truncate"
                                                    x-text="appointment.customer_name ?? '—'"></div>
                                                <div class="text-xs font-medium truncate opacity-90"
                                                    x-text="appointment.service_name"></div>
                                                <div class="text-xs mt-1 flex items-center gap-1 opacity-80">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span
                                                        x-text="appointment.start_time + ' - ' + appointment.end_time"></span>
                                                </div>
                                                <div class="absolute top-1 right-1">
                                                    <span class="inline-block w-2 h-2 rounded-full"
                                                        :class="getStatusDot(appointment.status)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Detail Modal -->
        <div x-show="showDetailModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showDetailModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Appointment Details</h3>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <template x-if="selectedAppointment">
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Customer:</span>
                            <p class="font-medium" x-text="selectedAppointment.customer_name"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Staff:</span>
                            <p class="font-medium" x-text="selectedAppointment.staff_name"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Service:</span>
                            <p class="font-medium" x-text="selectedAppointment.service_name"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Time:</span>
                            <p class="font-medium"
                                x-text="selectedAppointment.start_time + ' - ' + selectedAppointment.end_time"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Duration:</span>
                            <p class="font-medium" x-text="selectedAppointment.duration + ' minutes'"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Price:</span>
                            <p class="font-medium" x-text="'$' + selectedAppointment.price"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Status:</span>
                            <span class="inline-block px-2 py-1 text-xs rounded-full"
                                :class="getStatusBadge(selectedAppointment.status)"
                                x-text="selectedAppointment.status"></span>
                        </div>

                        <div class="pt-4 flex gap-2">
                            <button @click="updateAppointmentStatus('confirmed')"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Confirm
                            </button>
                            <button @click="updateAppointmentStatus('completed')"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Complete
                            </button>
                            <button @click="updateAppointmentStatus('cancelled')"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function appointmentCalendar() {
            return {
                selectedDate: '{{ now()->format('Y-m-d') }}',
                todayDate: '{{ now()->format('Y-m-d') }}',
                weekDates: [],
                currentWeekStart: null,
                appointments: [],
                staff: @json($staff),
                timeSlots: [],
                showDetailModal: false,
                selectedAppointment: null,
                weekOptions: [],
                appointmentColors: [
                    'bg-blue-100 text-blue-900 border-blue-500',
                    'bg-purple-100 text-purple-900 border-purple-500',
                    'bg-pink-100 text-pink-900 border-pink-500',
                    'bg-rose-100 text-rose-900 border-rose-500',
                    'bg-orange-100 text-orange-900 border-orange-500',
                    'bg-amber-100 text-amber-900 border-amber-500',
                    'bg-lime-100 text-lime-900 border-lime-500',
                    'bg-emerald-100 text-emerald-900 border-emerald-500',
                    'bg-teal-100 text-teal-900 border-teal-500',
                    'bg-cyan-100 text-cyan-900 border-cyan-500',
                    'bg-sky-100 text-sky-900 border-sky-500',
                    'bg-indigo-100 text-indigo-900 border-indigo-500',
                    'bg-violet-100 text-violet-900 border-violet-500',
                    'bg-fuchsia-100 text-fuchsia-900 border-fuchsia-500',
                ],

                init() {
                    this.currentWeekStart = new Date('{{ now()->startOfWeek()->format('Y-m-d') }}');
                    this.generateWeekDates();
                    this.generateTimeSlots();
                    this.loadAppointments();
                    this.generateWeekOptions();
                },
                generateWeekOptions() {
                    this.weekOptions = [];
                    const today = new Date();

                    // Generate options for 12 weeks (3 months forward and backward)
                    for (let i = -12; i <= 12; i++) {
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay() + (i * 7));

                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6);

                        const startStr = weekStart.toISOString().split('T')[0];
                        const label =
                            `${weekStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${weekEnd.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;

                        this.weekOptions.push({
                            value: startStr,
                            label: i === 0 ? `This Week (${label})` : label
                        });
                    }
                },

                getCurrentWeekValue() {
                    return this.currentWeekStart.toISOString().split('T')[0];
                },

                jumpToWeek(weekStartDate) {
                    if (!weekStartDate) return;

                    this.currentWeekStart = new Date(weekStartDate);
                    this.selectedDate = weekStartDate;
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                generateWeekDates() {
                    this.weekDates = [];
                    const today = new Date(this.todayDate);

                    for (let i = 0; i < 7; i++) {
                        const date = new Date(this.currentWeekStart);
                        date.setDate(date.getDate() + i);
                        const dateStr = date.toISOString().split('T')[0];

                        this.weekDates.push({
                            dateStr: dateStr,
                            dayName: date.toLocaleDateString('en-US', {
                                weekday: 'short'
                            }),
                            day: date.getDate(),
                            month: date.toLocaleDateString('en-US', {
                                month: 'short'
                            }),
                            isToday: dateStr === this.todayDate
                        });
                    }
                },

                generateTimeSlots() {
                    const slots = [];
                    for (let hour = 8; hour < 20; hour++) {
                        slots.push(`${hour.toString().padStart(2, '0')}:00`);
                        slots.push(`${hour.toString().padStart(2, '0')}:30`);
                    }
                    this.timeSlots = slots;
                },

                goToToday() {
                    const today = new Date(this.todayDate);
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    this.currentWeekStart = startOfWeek;
                    this.selectedDate = this.todayDate;
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                goToTomorrow() {
                    const tomorrow = new Date(this.todayDate);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    const tomorrowStr = tomorrow.toISOString().split('T')[0];

                    const startOfWeek = new Date(tomorrow);
                    startOfWeek.setDate(tomorrow.getDate() - tomorrow.getDay());
                    this.currentWeekStart = startOfWeek;
                    this.selectedDate = tomorrowStr;
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                goToThisWeek() {
                    const today = new Date(this.todayDate);
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    this.currentWeekStart = startOfWeek;
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                goToNextWeek() {
                    const today = new Date(this.todayDate);
                    const nextWeek = new Date(today);
                    nextWeek.setDate(today.getDate() + 7);
                    const startOfWeek = new Date(nextWeek);
                    startOfWeek.setDate(nextWeek.getDate() - nextWeek.getDay());
                    this.currentWeekStart = startOfWeek;
                    this.selectedDate = nextWeek.toISOString().split('T')[0];
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                previousWeek() {
                    this.currentWeekStart.setDate(this.currentWeekStart.getDate() - 7);
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                nextWeek() {
                    this.currentWeekStart.setDate(this.currentWeekStart.getDate() + 7);
                    this.generateWeekDates();
                    this.loadAppointments();
                },

                get currentWeekRange() {
                    const start = this.weekDates[0];
                    const end = this.weekDates[6];
                    return `${start.month} ${start.day} - ${end.month} ${end.day}`;
                },

                async loadAppointments() {
                    try {
                        const response = await fetch(`/appointments/get?date=${this.selectedDate}`);
                        const data = await response.json();
                        this.appointments = data.appointments;
                    } catch (error) {
                        console.error('Error loading appointments:', error);
                    }
                },

                getAppointmentsForSlot(staffId, timeSlot) {
                    const normalizedSlot = timeSlot.length === 5 ? timeSlot + ':00' : timeSlot;
                    return this.appointments.filter(apt => {
                        return apt.staff_id === staffId &&
                            apt.start_time <= normalizedSlot &&
                            apt.end_time > normalizedSlot;
                    });
                },

                getStaffAppointmentCount(staffId) {
                    return this.appointments.filter(apt => apt.staff_id === staffId).length;
                },

                getAppointmentStyle(appointment) {
                    const startParts = appointment.start_time.split(':');
                    const endParts = appointment.end_time.split(':');
                    const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
                    const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
                    const durationMinutes = endMinutes - startMinutes;
                    const displaySlots = Math.ceil(durationMinutes / 15);
                    const height = displaySlots * 30;
                    return `height: ${height}px; top: 0;`;
                },

                getAppointmentColor(appointmentId) {
                    // Use appointment ID to consistently assign a color
                    const colorIndex = appointmentId % this.appointmentColors.length;
                    return this.appointmentColors[colorIndex];
                },

                getStatusDot(status) {
                    const colors = {
                        'pending': 'bg-yellow-500',
                        'confirmed': 'bg-green-500',
                        'completed': 'bg-blue-500',
                        'cancelled': 'bg-red-500'
                    };
                    return colors[status] || 'bg-gray-500';
                },

                getStatusBadge(status) {
                    const colors = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'confirmed': 'bg-green-100 text-green-800',
                        'completed': 'bg-blue-100 text-blue-800',
                        'cancelled': 'bg-red-100 text-red-800'
                    };
                    return colors[status] || 'bg-gray-100 text-gray-800';
                },

                showAppointmentDetail(appointment) {
                    this.selectedAppointment = appointment;
                    this.showDetailModal = true;
                },

                async updateAppointmentStatus(status) {
                    try {
                        const response = await fetch(`/appointments/${this.selectedAppointment.id}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                status
                            })
                        });

                        if (response.ok) {
                            this.showDetailModal = false;
                            this.loadAppointments();
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                    }
                },

                openCreateModal(staffId, timeSlot) {
                    window.location.href =
                        `/appointments/create?staff_id=${staffId}&date=${this.selectedDate}&time=${timeSlot}`;
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>
