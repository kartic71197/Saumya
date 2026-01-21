<div class="min-h-screen">
    <style>
        .filter-section {
            transition: max-height 0.3s ease-out;
            overflow: hidden;
        }

        .filter-section.collapsed {
            max-height: 0;
        }

        .filter-section.expanded {
            max-height: 500px;
        }

        .date-input::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            background: #3b82f6;
            color: white;
            border-radius: 12px;
            font-size: 12px;
        }

        .badge .remove {
            cursor: pointer;
            font-weight: bold;
        }
    </style>
    <div class="container mx-auto px-4 pb-8">
        <div class="px-6 py-2 bg-white dark:bg-gray-800 rounded-lg border">
            <div class="flex justify-between items-center gap-2 pt-3 pb-2">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Patient Details & Medications') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Manage patients for your clinics') }}
                    </p>
                </div>
                <!-- Add button -->
                <div class="flex items-center justify-center gap-3">
                    @php
                        $user = auth()->user();
                        $role = $user->role;
                    @endphp
                    @if ($role?->hasPermission('add_patient') || $user->role_id <= 2)
                        <x-primary-button wire:click="addPatient" class="px-4 py-2">
                            {{ __('+ Add Patient') }}
                        </x-primary-button>
                    @endif

                    <x-secondary-button x-on:click="$dispatch('open-modal', 'import-patient-modal')"
                        class="px-4 py-2 bg-green-600 text-green-500">
                        {{ __('Import') }}
                    </x-secondary-button>

                    {{-- <x-secondary-button wire:click="exportPatients" class="px-4 py-2">
                        {{ __('Export') }}
                    </x-secondary-button> --}}
                    <x-secondary-button onclick="exportFilteredData()" class="px-4 py-2">
                        {{ __('Export') }}
                    </x-secondary-button>

                </div>
            </div>
            <!-- Advanced Search and Filter Bar -->
            <div class="rounded-lg my-2 border border-gray-200 bg-gray-50">
                <!-- Basic Search -->
                <div class="p-4">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" id="searchInput"
                                placeholder="Search by chart number, provider, medication, or location..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="flex gap-2">
                            <button id="advancedFilterToggle" onclick="toggleAdvancedFilters()"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                                    </path>
                                </svg>
                                Advanced Filters
                                <svg id="toggleIcon" class="w-4 h-4 transform transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <button id="clearFiltersBtn"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Clear All
                            </button>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <div id="activeFilters" class="mt-3 hidden">
                        <div class="text-sm text-gray-600 mb-2">Active Filters:</div>
                        <div id="filterBadges" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <!-- Advanced Filters Section -->
                <div id="advancedFilters" class="filter-section collapsed">
                    <div class="border-t border-gray-200 p-4 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Date Filters -->
                            <div class="space-y-3">
                                <h4 class="font-medium text-gray-900">Date Filters</h4>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Date Given From:</label>
                                    <input type="date" id="dateFrom"
                                        class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Date Given To:</label>
                                    <input type="date" id="dateTo"
                                        class="date-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Quick Date Range:</label>
                                    <select id="quickDateRange"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select range</option>
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="last7">Last 7 days</option>
                                        <option value="last30">Last 30 days</option>
                                        <option value="last90">Last 90 days</option>
                                        <option value="thisMonth">This month</option>
                                        <option value="lastMonth">Last month</option>
                                        <option value="thisYear">This year</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Medication Filters -->
                            <div class="space-y-3">
                                <h4 class="font-medium text-gray-900">Medication Filters</h4>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Medication Name:</label>
                                    <input id="medicationFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </input>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Min Quantity:</label>
                                        <input type="number" id="minQuantity"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Max Quantity:</label>
                                        <input type="number" id="maxQuantity"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="999">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Expiry Status:</label>
                                    <select id="expiryFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">All</option>
                                        <option value="expired">Expired</option>
                                        <option value="expiring">Expiring Soon (90 days)</option>
                                        <option value="valid">Valid</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Financial & Other Filters -->
                            <div class="space-y-3">
                                <h4 class="font-medium text-gray-900">Financial & Other</h4>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Provider:</label>
                                    <select id="providerFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">All Providers</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Location:</label>
                                    <select id="locationFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">All Locations</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Insurance Type:</label>
                                    <select id="insuranceFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">All Insurance Types</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Min Profit:</label>
                                        <input type="number" id="minProfit" step="0.01"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Max Profit:</label>
                                        <input type="number" id="maxProfit" step="0.01"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="9999.99">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        {{-- <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-600">
                                <span id="filteredCount">0</span> records match your filters
                            </div>
                            <div class="flex gap-2">
                                <button onclick="saveFilterPreset()"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                    Save Preset
                                </button>
                                <button onclick="applyFilters()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Apply Filters
                                </button>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
            <!-- Summary Cards -->
            @include('livewire.organization.patients.partials.summary-cards')

            <!-- Main Table -->
            <div class="bg-white shadow-sm border border-gray-200 overflow-hidden" wire:ignore>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-500 border-b border-gray-200">
                            <tr>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Patient Info</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Initials</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Provider & Insurance</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Location</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Medication Details</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Dosage & Frequency</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Financial Info</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Batch & Expiry</th>
                                <th
                                    class="text-left px-4 py-3 text-xs font-semibold text-gray-50 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="patientTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-700">
                    Showing <span id="showingStart">1</span> to <span id="showingEnd">10</span> of <span
                        id="totalRecords">0</span> results
                </div>
                <div class="flex gap-2">
                    <button id="prevBtn" onclick="changePage(-1)"
                        class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <div id="pageNumbers" class="flex gap-1">
                        <!-- Page numbers will be populated here -->
                    </div>
                    <button id="nextBtn" onclick="changePage(1)"
                        class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
            @include('livewire.organization.patients.modals.add-prescription-modal')
            @include('livewire.organization.patients.modals.patient-details-modal')
            @include('livewire.organization.patients.modals.edit-payment-modal')
            @include('livewire.organization.patients.modals.patient-modal')
            @include('livewire.organization.patients.modals.delete-patient-modal')
            @include('livewire.organization.patients.modals.import-patient-modal')

            <script>

                $(document).ready(function () {
                    // populateFilterOptions();
                    // initializeTable();
                    setupEventListeners();
                });
                // Sample data combining patients with their medication details
                let filteredData = [];
                let sampleData = [];
                let activeFilters = {};

                $.ajax({
                    url: '/patients-data',
                    type: 'GET',
                    success: function (data) {
                        sampleData = data;
                        filteredData = data;
                        initializeTable()
                    },
                    error: function (xhr) {
                        console.error('Error fetching data:', xhr);
                    }
                });

                let currentPage = 1;
                const itemsPerPage = 50;

                // Helper function to check if a value is empty/null/undefined
                function isEmpty(value) {
                    return value === null || value === undefined || value === '' || value === 0;
                }

                // Helper function to format display value, returns empty string if value is empty
                function formatValue(value, prefix = '', suffix = '') {
                    if (isEmpty(value)) return '';
                    return `${prefix}${value}${suffix}`;
                }

                // Helper function to create HTML element only if value exists
                function createElementIfExists(value, className = '', prefix = '', suffix = '') {
                    if (isEmpty(value)) return '';
                    return `<div class="${className}">${prefix}${value}${suffix}</div>`;
                }
                function applyFilters() {
                    filterData();
                }
                // Toggle advanced filters visibility
                function toggleAdvancedFilters() {
                    const filtersDiv = document.getElementById('advancedFilters');
                    const toggleIcon = document.getElementById('toggleIcon');

                    if (filtersDiv.classList.contains('collapsed')) {
                        filtersDiv.classList.remove('collapsed');
                        filtersDiv.classList.add('expanded');
                        toggleIcon.style.transform = 'rotate(180deg)';
                    } else {
                        filtersDiv.classList.remove('expanded');
                        filtersDiv.classList.add('collapsed');
                        toggleIcon.style.transform = 'rotate(0deg)';
                    }
                }
                // Populate filter dropdown options based on data
                function populateFilterOptions() {
                    // const medications = [...new Set(sampleData.map(item => item.product_name).filter(Boolean))];
                    const providers = [...new Set(sampleData.map(item => item.provider).filter(Boolean))];
                    const locations = [...new Set(sampleData.map(item => item.location).filter(Boolean))];
                    const insuranceTypes = [...new Set(sampleData.map(item => item.ins_type).filter(Boolean))];

                    // Preserve current selections before repopulating
                    const currentProvider = document.getElementById('providerFilter').value;
                    const currentLocation = document.getElementById('locationFilter').value;
                    const currentInsurance = document.getElementById('insuranceFilter').value;


                    // populateSelect('medicationFilter', medications);
                    populateSelect('providerFilter', providers);
                    populateSelect('locationFilter', locations);
                    console.log('All location values from sampleData:', locations);
                    populateSelect('insuranceFilter', insuranceTypes);
                    // Restore selections after repopulating
                    if (currentProvider) document.getElementById('providerFilter').value = currentProvider;
                    if (currentLocation) document.getElementById('locationFilter').value = currentLocation;
                    if (currentInsurance) document.getElementById('insuranceFilter').value = currentInsurance;
                }

                function populateSelect(selectId, options) {
                    const select = document.getElementById(selectId);
                    const currentValue = select.value;
                    // CLEAR EXISTING OPTIONS EXCEPT THE FIRST "All" OPTION
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    options.sort().forEach(option => {
                        const optionElement = document.createElement('option');
                        optionElement.value = option;
                        optionElement.textContent = option;
                        select.appendChild(optionElement);
                    });

                    // Restore the previous value if it still exists in options
                    if (currentValue && options.includes(currentValue)) {
                        select.value = currentValue;
                    }
                }

                function setupEventListeners() {
                    document.getElementById('searchInput').addEventListener('input', debounce(filterData, 300));
                    document.getElementById('quickDateRange').addEventListener('change', handleQuickDateRange);

                    // Add event listeners to all filter inputs
                    const filterInputs = [
                        'dateFrom', 'dateTo', 'medicationFilter', 'minQuantity', 'maxQuantity',
                        'expiryFilter', 'providerFilter', 'locationFilter', 'insuranceFilter',
                        'minProfit', 'maxProfit', 'statusFilter'
                    ];

                    filterInputs.forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.addEventListener('change', debounce(filterData, 300));
                            element.addEventListener('input', debounce(filterData, 300));
                        }
                    });
                }
                function debounce(func, wait) {
                    let timeout;
                    return function executedFunction(...args) {
                        const later = () => {
                            clearTimeout(timeout);
                            func(...args);
                        };
                        clearTimeout(timeout);
                        timeout = setTimeout(later, wait);
                    };
                }
                function handleQuickDateRange() {
                    const quickRange = document.getElementById('quickDateRange').value;
                    const today = new Date();
                    let fromDate = null;
                    let toDate = null;

                    switch (quickRange) {
                        case 'today':
                            fromDate = toDate = today;
                            break;
                        case 'yesterday':
                            const yesterday = new Date(today);
                            yesterday.setDate(yesterday.getDate() - 1);
                            fromDate = toDate = yesterday;
                            break;
                        case 'last7':
                            fromDate = new Date(today);
                            fromDate.setDate(fromDate.getDate() - 7);
                            toDate = today;
                            break;
                        case 'last30':
                            fromDate = new Date(today);
                            fromDate.setDate(fromDate.getDate() - 30);
                            toDate = today;
                            break;
                        case 'last90':
                            fromDate = new Date(today);
                            fromDate.setDate(fromDate.getDate() - 90);
                            toDate = today;
                            break;
                        case 'thisMonth':
                            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                            toDate = today;
                            break;
                        case 'lastMonth':
                            fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                            toDate = new Date(today.getFullYear(), today.getMonth(), 0);
                            break;
                        case 'thisYear':
                            fromDate = new Date(today.getFullYear(), 0, 1);
                            toDate = today;
                            break;
                    }

                    if (fromDate) {
                        document.getElementById('dateFrom').value = formatDate(fromDate);
                    }
                    if (toDate) {
                        document.getElementById('dateTo').value = formatDate(toDate);
                    }

                    filterData();
                }
                function formatDate(date) {
                    return date.toISOString().split('T')[0];
                }

                document.getElementById('clearFiltersBtn').addEventListener('click', function () {
                    clearAllFilters();
                });
                function clearAllFilters() {
                    console.log('clear all filters called');
                    // Clear all input fields
                    document.getElementById('searchInput').value = '';
                    document.getElementById('dateFrom').value = '';
                    document.getElementById('dateTo').value = '';
                    document.getElementById('quickDateRange').value = '';
                    document.getElementById('medicationFilter').value = '';
                    document.getElementById('minQuantity').value = '';
                    document.getElementById('maxQuantity').value = '';
                    document.getElementById('expiryFilter').value = '';
                    document.getElementById('providerFilter').value = '';
                    document.getElementById('locationFilter').value = '';
                    document.getElementById('insuranceFilter').value = '';
                    document.getElementById('minProfit').value = '';
                    document.getElementById('maxProfit').value = '';
                    activeFilters = {};
                    filteredData = [...sampleData];
                    currentPage = 1;
                    updateFilterBadges();
                    renderTable();
                    updatePagination();
                    updateSummaryCards();
                    document.getElementById('filteredCount').textContent = filteredData.length;
                }


                function initializeTable() {
                    populateFilterOptions();
                    setupEventListeners();
                    updateSummaryCards();
                    renderTable();
                    updatePagination();
                    // clearAllFilters();
                }

                function updateSummaryCards() {
                    const uniquePatients = [...new Set(filteredData.map(item => item.id))];
                    const activePatients = uniquePatients.filter(id =>
                        filteredData.find(item => item.id === id).is_active
                    );

                    const totalOurCost = filteredData.reduce((sum, item) => {
                        return sum + (parseFloat(item.our_cost) || 0);
                    }, 0);

                    const totalRevenue = filteredData.reduce((sum, item) => {
                        return sum + (parseFloat(item.paid) || 0);
                    }, 0);

                    const totalPaid = filteredData.reduce((sum, item) => {
                        const copay = parseFloat(item.pt_copay) || 0;
                        const paid = parseFloat(item.paid) || 0;
                        return sum + copay + paid;
                    }, 0);

                    const profit = filteredData.reduce((sum, item) => {
                        return sum + (parseFloat(item.profit) || 0);
                    }, 0);

                    document.getElementById('totalPatients').textContent = activePatients.length;
                    document.getElementById('ourCost').textContent = `$${totalOurCost.toFixed(2)}`;
                    document.getElementById('paid').textContent = `$${totalPaid.toFixed(2)}`;
                    document.getElementById('profit').textContent = `$${profit.toFixed(2)}`;
                }

                function groupDataByPatient(data) {
                    const grouped = {};
                    data.forEach(item => {
                        if (!grouped[item.id]) {
                            grouped[item.id] = {
                                patient: {
                                    id: item.id,
                                    chartnumber: item.chartnumber,
                                    initials: item.initials,
                                    ins_type: item.ins_type,
                                    provider: item.provider,
                                    icd: item.icd,
                                    address: item.address,
                                    city: item.city,
                                    state: item.state,
                                    country: item.country,
                                    pin_code: item.pin_code,
                                    is_active: item.is_active,
                                    organization_id: item.organization_id,
                                    location: item.location
                                },
                                medications: []
                            };
                            console.log('Created patient group:', grouped[item.id].patient);

                        }
                        grouped[item.id].medications.push({
                            date_given: item.date_given,
                            product_name: item.product_name,
                            quantity: item.quantity,
                            dose: item.dose,
                            frequency: item.frequency,
                            paid: item.paid,
                            our_cost: item.our_cost,
                            price: item.price,
                            pt_copay: item.pt_copay,
                            profit: item.profit,
                            batch_number: item.batch_number,
                            expiry_date: item.expiry_date,
                            unit: item.unit,
                            detail_id: item.detail_id
                        });
                    });
                    return Object.values(grouped);
                }

                function renderTable() {
                    const tbody = document.getElementById('patientTableBody');
                    const groupedData = groupDataByPatient(filteredData);
                    const start = (currentPage - 1) * itemsPerPage;
                    const end = start + itemsPerPage;

                    // Calculate total rows needed for pagination
                    let totalRows = 0;
                    groupedData.forEach(group => {
                        totalRows += group.medications.length;
                    });

                    let currentRowCount = 0;
                    let pageData = [];

                    for (const group of groupedData) {
                        if (currentRowCount + group.medications.length > start && currentRowCount < end) {
                            pageData.push(group);
                        }
                        currentRowCount += group.medications.length;
                        if (currentRowCount >= end) break;
                    }

                    tbody.innerHTML = pageData.map(group => {
                        const patient = group.patient;
                        const medications = group.medications;

                        console.log('Rendering patient:', patient.chartnumber, 'Location:', patient.location);

                        return medications.map((medication, medIndex) => {
                            const isExpiring = medication.expiry_date && new Date(medication.expiry_date) <= new Date(Date.now() + 90 * 24 * 60 * 60 * 1000);
                            const isFirstMed = medIndex === 0;
                            const medicationCount = medications.length;

                            return `
                        <tr class="hover:bg-gray-50 transition-colors ${medIndex % 2 === 0 ? 'bg-white' : 'bg-gray-25'} ">
                            <td class="px-4 py-4 ${isFirstMed ? '' : 'border-l-4 border-gray-200'}">
                                ${isFirstMed ? `
                                                                                <div class="text-sm cursor-pointer" onclick="openDetails(${patient.id})">
                                                                                    ${createElementIfExists(patient.chartnumber, 'font-semibold text-blue-600 underline')}
                                                                                    ${createElementIfExists(patient.icd, 'text-gray-600', 'ICD: ')}
                                                                                    ${medicationCount > 1 ? `<div class="mt-1 text-xs text-blue-600 font-medium">${medicationCount} medications</div>` : ''}
                                                                                </div>
                                                                                ` : ''}
                            </td>
                            <td class="px-4 py-4">
                                ${isFirstMed ? `
                                                                                <div class="text-sm">
                                                                                    ${createElementIfExists(patient.initials)}
                                                                                </div>
                                                                                ` : ''}
                            </td>
                            <td class="px-4 py-4">
                                ${isFirstMed ? `
                                                                                <div class="text-sm">
                                                                                    ${createElementIfExists(patient.provider, 'font-medium text-gray-900')}
                                                                                    ${createElementIfExists(patient.ins_type, 'text-gray-600')}
                                                                                </div>
                                                                                ` : ''}
                            </td>
                            <td class="px-4 py-4">
                                ${isFirstMed ? `
                                                                                <div class="text-sm">
                                                                                    ${createElementIfExists(patient.location, 'text-gray-900')}
                                                                                </div>
                                                                                ` : ''}
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm">
                                    ${createElementIfExists(medication.product_name, 'font-medium text-gray-900')}
                                    ${medication.date_given ? createElementIfExists(formatDateToMMDDYYYY(new Date(medication.date_given)), 'text-gray-600', 'Date: ') : ''}
                                    ${(!isEmpty(medication.quantity) || !isEmpty(medication.unit)) ?
                                    `<div class="text-gray-500 text-xs">QTY:${formatValue(medication.quantity)} ${formatValue(medication.unit)}</div>`.trim() :
                                    ''
                                }
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm">
                                    ${createElementIfExists(medication.dose, 'font-medium text-gray-900')}
                                    ${createElementIfExists(medication.frequency, 'text-gray-600')}
                                </div>
                            </td>
                            
<td class="px-4 py-4 relative">
    <!-- Edit button positioned at top right -->
${medication.detail_id != null ? `<button class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 transition-colors" 
                                                            onclick="editPayments(${medication.detail_id})"
                                                            title="Edit medication">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>` : ''}

    <div class="text-sm pr-6">
        ${createElementIfExists(medication.paid, 'text-gray-900', 'Paid: $')}
        ${createElementIfExists(medication.our_cost, 'text-gray-600', 'Cost: $')}
        ${createElementIfExists(medication.price, 'text-gray-600', 'Price: $')}
        ${createElementIfExists(medication.pt_copay, 'text-gray-600', 'Copay: $')}
        ${!isEmpty(medication.profit) ?
                                    `<div class="${medication.profit >= 0 ? 'text-green-600' : 'text-red-600'} font-medium">
                                                                Profit: $${medication.profit}
                                                            </div>` :
                                    ''
                                }
    </div>
</td>
                            <td class="px-4 py-4">
                                <div class="text-sm">
                                    ${createElementIfExists(medication.batch_number, 'text-gray-900')}
                                    ${medication.expiry_date ?
                                    `<div class="${isExpiring ? 'text-red-600 font-medium' : 'text-gray-600'}">
                                                                                            ${formatDateToMMDDYYYY(new Date(medication.expiry_date))}
                                                                                        </div>` :
                                    ''
                                }
                                    ${isExpiring ? '<div class="text-xs text-red-500">Expiring Soon!</div>' : ''}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1">
                                    ${isFirstMed ? `
                                                                                        <button onclick="openPrescribeModal(${patient.id})" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">Add Prescription</button>
                                                                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                        `;
                        }).join('');
                    }).join('');
                }
                function openPrescribeModal(patientId) {
                    // Call Livewire method
                    @this.call('openPrescribeModal', patientId);
                }
                function openDetails(patientId) {
                    // Call Livewire method
                    @this.call('openDetails', patientId);
                }
                function editPayments(medication) {
                    // Call Livewire method to open edit modal
                    @this.call('editPayments', medication);
                }
                function formatDateToMMDDYYYY(date) {
                   const month = String(date.getMonth() + 1).padStart(2, '0');
                   const day = String(date.getDate()).padStart(2, '0');
                   const year = date.getFullYear();
                   return `${month}-${day}-${year}`;
                }

                function updatePagination() {
                    // Calculate total rows (sum of all medications across all patients)
                    const groupedData = groupDataByPatient(filteredData);
                    const totalRecords = groupedData.reduce((sum, group) => sum + group.medications.length, 0);
                    const totalPages = Math.ceil(totalRecords / itemsPerPage);
                    const start = Math.min((currentPage - 1) * itemsPerPage + 1, totalRecords);
                    const end = Math.min(currentPage * itemsPerPage, totalRecords);

                    document.getElementById('showingStart').textContent = start;
                    document.getElementById('showingEnd').textContent = end;
                    document.getElementById('totalRecords').textContent = totalRecords;

                    document.getElementById('prevBtn').disabled = currentPage === 1;
                    document.getElementById('nextBtn').disabled = currentPage === totalPages || totalPages === 0;

                    // Generate page numbers
                    const pageNumbers = document.getElementById('pageNumbers');
                    pageNumbers.innerHTML = '';

                    for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                        const button = document.createElement('button');
                        button.textContent = i;
                        button.className = `px-3 py-2 border rounded-lg ${i === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'}`;
                        button.onclick = () => goToPage(i);
                        pageNumbers.appendChild(button);
                    }
                }

                function changePage(direction) {
                    const groupedData = groupDataByPatient(filteredData);
                    const totalRecords = groupedData.reduce((sum, group) => sum + group.medications.length, 0);
                    const totalPages = Math.ceil(totalRecords / itemsPerPage);
                    const newPage = currentPage + direction;

                    if (newPage >= 1 && newPage <= totalPages) {
                        currentPage = newPage;
                        renderTable();
                        updatePagination();
                    }
                }

                function goToPage(page) {
                    currentPage = page;
                    renderTable();
                    updatePagination();
                }

                function filterData() {
                    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                    console.log('searchTerm:', searchTerm);
                    const dateFrom = document.getElementById('dateFrom').value || null;
                    console.log('dateFrom:', dateFrom);
                    const dateTo = document.getElementById('dateTo').value || null;
                    console.log('dateTo:', dateTo);
                    const medication = document.getElementById('medicationFilter').value || null;
                    console.log('medication:', medication);
                    const minQuantity = parseFloat(document.getElementById('minQuantity').value) || 0;
                    console.log('minQuantity:', minQuantity);
                    const maxQuantity = parseFloat(document.getElementById('maxQuantity').value) || Infinity;
                    console.log('maxQuantity:', maxQuantity);
                    const expiryFilter = document.getElementById('expiryFilter').value || null;
                    console.log('expiryFilter:', expiryFilter);
                    const provider = document.getElementById('providerFilter').value || null;
                    console.log('provider:', provider);
                    const location = document.getElementById('locationFilter').value || null;
                    console.log('location:', location);
                    const insurance = document.getElementById('insuranceFilter').value || null;
                    console.log('insurance:', insurance);
                    const minProfit = parseFloat(document.getElementById('minProfit').value) || -Infinity;
                    console.log('minProfit:', minProfit);
                    const maxProfit = parseFloat(document.getElementById('maxProfit').value) || Infinity;
                    console.log('maxProfit:', maxProfit);

                    activeFilters = {};
                    if (searchTerm) activeFilters.search = searchTerm;
                    if (dateFrom) activeFilters.dateFrom = dateFrom;
                    if (dateTo) activeFilters.dateTo = dateTo;
                    if (medication) activeFilters.medication = medication;
                    if (minQuantity > 0) activeFilters.minQuantity = minQuantity;
                    if (maxQuantity < Infinity) activeFilters.maxQuantity = maxQuantity;
                    if (expiryFilter) activeFilters.expiry = expiryFilter;
                    if (provider) activeFilters.provider = provider;
                    if (location) activeFilters.location = location;
                    if (insurance) activeFilters.insurance = insurance;
                    if (minProfit > -Infinity) activeFilters.minProfit = minProfit;
                    if (maxProfit < Infinity) activeFilters.maxProfit = maxProfit;

                    filteredData = sampleData.filter(item => {
                        // Basic search filter
                        const matchesSearch = !searchTerm ||
                            (item.chartnumber && item.chartnumber.toLowerCase().includes(searchTerm)) ||
                            (item.provider && item.provider.toLowerCase().includes(searchTerm)) ||
                            (item.location && item.location.toLowerCase().includes(searchTerm)) ||
                            (item.product_name && item.product_name.toLowerCase().includes(searchTerm)) ||
                            (item.batch_number && item.batch_number.toLowerCase().includes(searchTerm));

                        // Date range filter
                        const matchesDateRange = (!dateFrom && !dateTo) ||
                            (item.date_given &&
                                (!dateFrom || new Date(item.date_given) >= new Date(dateFrom)) &&
                                (!dateTo || new Date(item.date_given) <= new Date(dateTo)));

                        // Medication filter
                        const matchesMedication = !medication || ( item.product_name && item.product_name.toLowerCase().includes(medication));
                        console.log(medication);

                        // Quantity filter
                        const matchesQuantity = (item.quantity >= minQuantity) && (item.quantity <= maxQuantity);

                        // Expiry filter
                        const matchesExpiry = !expiryFilter || checkExpiryStatus(item.expiry_date, expiryFilter);

                        // Provider filter
                        const matchesProvider = !provider || item.provider === provider;

                        // Location filter
                        const matchesLocation = !location || item.location === location;

                        // Insurance filter
                        const matchesInsurance = !insurance || item.ins_type === insurance;

                        // Profit filter
                        const profit = parseFloat(item.profit) || 0;
                        const matchesProfit = profit >= minProfit && profit <= maxProfit;


                        return matchesSearch && matchesDateRange && matchesMedication &&
                            matchesQuantity && matchesExpiry && matchesProvider &&
                            matchesLocation && matchesInsurance && matchesProfit;
                    });

                    currentPage = 1;
                    updateFilterBadges();
                    renderTable();
                    updatePagination();
                    updateSummaryCards();
                    document.getElementById('filteredCount').textContent = filteredData?.length;
                }

                function restoreFilters() {
                    if (!activeFilters) return;

                    // Restore all filter values to their respective UI elements
                    if (activeFilters.search) document.getElementById('searchInput').value = activeFilters.search;
                    if (activeFilters.dateFrom) document.getElementById('dateFrom').value = activeFilters.dateFrom;
                    if (activeFilters.dateTo) document.getElementById('dateTo').value = activeFilters.dateTo;
                    if (activeFilters.medication) document.getElementById('medicationFilter').value = activeFilters.medication;
                    if (activeFilters.minQuantity) document.getElementById('minQuantity').value = activeFilters.minQuantity;
                    if (activeFilters.maxQuantity) document.getElementById('maxQuantity').value = activeFilters.maxQuantity;
                    if (activeFilters.expiry) document.getElementById('expiryFilter').value = activeFilters.expiry;
                    if (activeFilters.provider) document.getElementById('providerFilter').value = activeFilters.provider;
                    if (activeFilters.location) document.getElementById('locationFilter').value = activeFilters.location;
                    if (activeFilters.insurance) document.getElementById('insuranceFilter').value = activeFilters.insurance;
                    if (activeFilters.minProfit) document.getElementById('minProfit').value = activeFilters.minProfit;
                    if (activeFilters.maxProfit) document.getElementById('maxProfit').value = activeFilters.maxProfit;

                    // For dropdowns, we need to ensure the option exists before setting the value
                    if (activeFilters.provider) {
                        const providerFilter = document.getElementById('providerFilter');
                        // Check if option exists, if not add it temporarily
                        if (!Array.from(providerFilter.options).some(opt => opt.value === activeFilters.provider)) {
                            const option = document.createElement('option');
                            option.value = activeFilters.provider;
                            option.textContent = activeFilters.provider;
                            providerFilter.appendChild(option);
                        }
                        providerFilter.value = activeFilters.provider;
                    }

                    if (activeFilters.location) {
                        const locationFilter = document.getElementById('locationFilter');
                        if (!Array.from(locationFilter.options).some(opt => opt.value === activeFilters.location)) {
                            const option = document.createElement('option');
                            option.value = activeFilters.location;
                            option.textContent = activeFilters.location;
                            locationFilter.appendChild(option);
                        }
                        locationFilter.value = activeFilters.location;
                    }

                    if (activeFilters.insurance) {
                        const insuranceFilter = document.getElementById('insuranceFilter');
                        if (!Array.from(insuranceFilter.options).some(opt => opt.value === activeFilters.insurance)) {
                            const option = document.createElement('option');
                            option.value = activeFilters.insurance;
                            option.textContent = activeFilters.insurance;
                            insuranceFilter.appendChild(option);
                        }
                        insuranceFilter.value = activeFilters.insurance;
                    }

                    if (activeFilters.minProfit) document.getElementById('minProfit').value = activeFilters.minProfit;
                    if (activeFilters.maxProfit) document.getElementById('maxProfit').value = activeFilters.maxProfit;

                }

                function updateFilterBadges() {
                    const badgesContainer = document.getElementById('filterBadges');
                    const activeFiltersDiv = document.getElementById('activeFilters');

                    badgesContainer.innerHTML = '';

                    if (Object.keys(activeFilters).length === 0) {
                        activeFiltersDiv.classList.add('hidden');
                        return;
                    }

                    activeFiltersDiv.classList.remove('hidden');

                    Object.entries(activeFilters).forEach(([key, value]) => {
                        const badge = document.createElement('span');
                        badge.className = 'badge';

                        let displayText = '';
                        switch (key) {
                            case 'search':
                                displayText = `Search: ${value}`;
                                break;
                            case 'dateFrom':
                                displayText = `From: ${value}`;
                                break;
                            case 'dateTo':
                                displayText = `To: ${value}`;
                                break;
                            case 'medication':
                                displayText = `Medication: ${value}`;
                                break;
                            case 'minQuantity':
                                displayText = `Min Qty: ${value}`;
                                break;
                            case 'maxQuantity':
                                displayText = `Max Qty: ${value}`;
                                break;
                            case 'expiry':
                                displayText = `Expiry: ${value}`;
                                break;
                            case 'provider':
                                displayText = `Provider: ${value}`;
                                break;
                            case 'location':
                                displayText = `Location: ${value}`;
                                break;
                            case 'insurance':
                                displayText = `Insurance: ${value}`;
                                break;
                            case 'minProfit':
                                displayText = `Min Profit: ${value}`;
                                break;
                            case 'maxProfit':
                                displayText = `Max Profit: ${value}`;
                                break;
                            case 'status':
                                displayText = `Status: ${value}`;
                                break;
                        }

                        badge.innerHTML = `${displayText} <span class="remove" onclick="removeFilter('${key}')">&times;</span>`;
                        badgesContainer.appendChild(badge);
                    });
                }
                // Remove individual filter
                function removeFilter(filterKey) {
                    delete activeFilters[filterKey];
                    console.log(filterKey, 'removed');
                    // Clear the corresponding input field
                    switch (filterKey) {
                        case 'search':
                            document.getElementById('searchInput').value = '';
                            break;
                        case 'dateFrom':
                            document.getElementById('dateFrom').value = '';
                            break;
                        case 'dateTo':
                            document.getElementById('dateTo').value = '';
                            break;
                        case 'medication':
                            document.getElementById('medicationFilter').value = '';
                            break;
                        case 'minQuantity':
                            document.getElementById('minQuantity').value = '';
                            break;
                        case 'maxQuantity':
                            document.getElementById('maxQuantity').value = '';
                            break;
                        case 'expiry':
                            document.getElementById('expiryFilter').value = '';
                            break;
                        case 'provider':
                            document.getElementById('providerFilter').value = '';
                            break;
                        case 'location':
                            document.getElementById('locationFilter').value = '';
                            break;
                        case 'insurance':
                            document.getElementById('insuranceFilter').value = '';
                            break;
                        case 'minProfit':
                            document.getElementById('minProfit').value = '';
                            break;
                        case 'maxProfit':
                            document.getElementById('maxProfit').value = '';
                            break;
                    }

                    filterData();
                }



                function addPrescription(patientId) {
                    // Set hidden field if needed (optional)
                    document.getElementById('prescriptionPatientId').value = patientId;

                    // Show modal
                    document.getElementById('addPrescriptionModal').classList.remove('hidden');
                    document.getElementById('overlay').classList.remove('hidden');
                }

                function closeModal() {
                    document.getElementById('addPrescriptionModal').classList.add('hidden');
                    document.getElementById('overlay').classList.add('hidden');
                }

                function exportData() {
                    alert('Export functionality would generate CSV/Excel with all visible data');
                }

                function exportFilteredData() {
                    const currentFilters = {
                        ...activeFilters
                    };
                    // Send filteredData to Livewire for export
                    @this.call('exportPatients', filteredData);
                    setTimeout(() => {
                        activeFilters = currentFilters;
                        populateFilterOptions();
                        restoreFilters();
                        updateFilterBadges();
                    }, 100);
                }

                // Listen for Livewire events that might cause re-renders
                document.addEventListener('livewire:load', function() {
                    // Repopulate options when Livewire loads
                    if (sampleData.length > 0) {
                        populateFilterOptions();
                        if (Object.keys(activeFilters).length > 0) {
                            restoreFilters();
                        }
                    }
                });

                document.addEventListener('livewire:update', function() {
                    // Repopulate options after Livewire updates
                    setTimeout(() => {
                        if (sampleData.length > 0) {
                            populateFilterOptions();
                            if (Object.keys(activeFilters).length > 0) {
                                restoreFilters();
                            }
                        }
                    }, 50);
                });
            </script>
        </div>
    </div>
</div>