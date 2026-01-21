<x-modal name="patient-details-modal" width="w-100" height="h-auto" maxWidth="7xl">
    <div class="max-w-7xl mx-auto pb-6 px-6 min-h-screen">
        <!-- Chart Number Header -->
        <div class="flex justify-end items-center mb-2 gap-3">
            <button type="button" wire:click="editPatient"
                class="mb-3 group inline-flex items-center px-4 py-2 border-2 border-white text-base font-bold rounded-2xl text-white bg-green-600 hover:bg-green-500 hover:border-green-400 focus:outline-none focus:ring-4 focus:ring-green-200 transform hover:scale-105 hover:-translate-y-1 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-3 group-hover:rotate-12 transition-transform duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                Edit
            </button>
        </div>

        <div class="mb-3">
            <div
                class="bg-white rounded-3xl shadow-xl px-6 py-2 border border-blue-100 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div
                            class="text-sm font-semibold text-primary-md uppercase tracking-wider mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Chart Number
                        </div>
                        <div class="text-6xl font-black text-slate-800 tracking-tight mb-2">
                            #{{ $patient->chartnumber ?? 'N/A' }}
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="flex-1 text-right">
                        <div class="p-6">
                            <div class="flex items-center justify-end mb-3">
                                <svg class="w-8 h-8 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">
                                    {!! $formattedAddress !!}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 items-stretch">
            <!-- Financial Overview Cards -->
            <div class="xl:col-span-2 h-full">
                <div class="grid grid-cols-2 gap-3 h-full">
                    <!-- Our Cost -->
                    <div class="group h-full">
                        @include('livewire.organization.patients.partials.our-cost')
                    </div>

                    <!-- Patient Copay -->
                    <div class="group h-full">
                        @include('livewire.organization.patients.partials.patients-copay')
                    </div>

                    <!-- Profit -->
                    <div class="group h-full">
                        @include('livewire.organization.patients.partials.profit')
                    </div>

                    <!-- Profit Percentage -->
                    <div class="group h-full">
                        @include('livewire.organization.patients.partials.profit-percent')
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="xl:col-span-1 h-full">
                <div
                    class="bg-white h-full flex flex-col rounded-2xl shadow-xl border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-300">
                    <div class="bg-gradient-to-r from-primary-md to-primary-lt px-6 py-5">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Medical Information
                        </h2>
                    </div>
                    <div class="p-8 space-y-8 flex-1">
                        <div class="flex flex-col justify-end items-start">
                            <div class="flex justify-between items-center w-full border-b border-slate-100">
                                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Insurance
                                    Type
                                </div>
                                <div class="text-sm font-bold text-slate-800">
                                    {{ $patient->ins_type ?? 'Not specified' }}
                                </div>
                            </div>

                            <div class="flex justify-between items-center  w-full border-b border-slate-100">
                                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Provider
                                </div>
                                <div class="text-sm font-bold text-slate-800">
                                    {{ $patient->provider ?? 'Not specified' }}
                                </div>
                            </div>

                            <div class="flex justify-between items-center  w-full border-b border-slate-100">
                                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">ICD</div>
                                <div class="text-sm font-bold text-slate-800">{{ $patient->icd ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            @include('livewire.organization.patients.partials.prescriptions-table')
        </div>

        <!-- Action Buttons -->
        <div class="mt-12 flex flex-wrap gap-4 justify-center hidden">
            {{-- <button type="button"
                class="group inline-flex items-center px-8 py-4 border border-transparent text-base font-bold rounded-2xl text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-4 focus:ring-blue-300 transform hover:scale-105 hover:-translate-y-1 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-3 group-hover:rotate-12 transition-transform duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                Edit Patient
            </button> --}}

            {{-- <button type="button"
                class="group inline-flex items-center px-8 py-4 border-2 border-slate-300 text-base font-bold rounded-2xl text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-4 focus:ring-slate-200 transform hover:scale-105 hover:-translate-y-1 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Print Details
            </button> --}}


            {{-- <button type="button"
                class="group inline-flex items-center px-8 py-4 border-2 border-slate-300 text-base font-bold rounded-2xl text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-4 focus:ring-slate-200 transform hover:scale-105 hover:-translate-y-1 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-3 group-hover:-translate-x-1 transition-transform duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Patients
            </button> --}}
        </div>

        <!-- Quick Stats Footer -->
        <div class="mt-12 bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
            <div class="grid grid-cols-2 gap-6 text-center">
                <div class="flex items-center justify-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <div class="text-xs text-slate-500 font-semibold uppercase">Created</div>
                        <div class="text-sm font-bold text-slate-800">
                            {{ $patient?->created_at ? $patient->created_at->format('M d, Y') : 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <div class="text-xs text-slate-500 font-semibold uppercase">Updated</div>
                        <div class="text-sm font-bold text-slate-800">
                            {{ $patient?->updated_at ? $patient->updated_at->format('M d, Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-modal>