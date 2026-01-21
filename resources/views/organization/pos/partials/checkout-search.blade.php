 <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                {{-- Search/Filter --}}
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" placeholder="Search products by name, code, batch..." x-model="searchQuery"
                            @input="filterCart()"
                            class="w-full pl-10 pr-3 py-3 text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                {{-- <template x-if="roleId < 3">
                    <select x-model="selectedLocation" @change="locationChanged()"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <template x-for="location in locations" :key="location.id">
                            <option :value="location.id.toString()" x-text="location.name"
                                :selected="location.id.toString() === selectedLocationId"></option>
                        </template>
                    </select>
                </template>

                <template x-if="roleId >= 3 && !selectedLocation">
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <div>
                                <p class="font-semibold text-red-800">No Location Assigned</p>
                                <p class="text-sm text-red-600">Please contact your administrator to assign a location
                                    to
                                    your account.</p>
                            </div>
                        </div>
                    </div>
                </template> --}}
            </div>
        </div>