<x-app-layout>
    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">

            <!-- Grid Layout -Creating 2 diffrent divs or sections for left-side Supplier Lista nd Right-side sections of Billing and Shipping 
            Also, we are including both sides fiels below and providng them a new well-structured UI-->
            <div class="min-w-7xl grid grid-cols-8 gap-3">

                {{-- Left Section --}}
                <div class="col-span-2">
                    @include('billing_and_shipping.partials.left-suppliers')
                </div>

                {{-- Right Section --}}
                <div class="col-span-6">
                    @include('billing_and_shipping.partials.right-billing-shipping')
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSupplierId = {{ $suppliers->first()->id ?? 'null' }};
        const userRoleId = {{ $user->role_id }};
        const organizationId = {{ $organization_id }};

        // Load data on page load for first supplier
        document.addEventListener('DOMContentLoaded', function() {
            if (currentSupplierId) {
                loadSupplierData(currentSupplierId);
            }
        });

        // Supplier selection
        function selectSupplier(event, supplierId, supplierName) {
            console.log(supplierId);
            currentSupplierId = supplierId;

            // Update active state
            document.querySelectorAll('.supplier-nav-item').forEach(item => {
                item.classList.remove('bg-blue-100', 'text-blue-700', 'border-blue-500');
                item.classList.add('text-gray-700');
            });

            event.currentTarget.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-500');
            event.currentTarget.classList.remove('text-gray-700');

            // Update title
            document.querySelectorAll('.supplierTitle').forEach(el => {
                el.textContent = supplierName;
            });



            // Load data
            loadSupplierData(supplierId);
        }

        // Load supplier data via AJAX
        function loadSupplierData(supplierId) {
            console.log('Loading supplier:', supplierId);

            const loadingState = document.getElementById('loadingState');
            const contentArea = document.getElementById('contentArea');

            if (loadingState && contentArea) {
                loadingState.classList.remove('hidden');
                contentArea.classList.add('hidden');
            }

            const billingInput = document.getElementById('billingSupplierId');
            const shippingInput = document.getElementById('shippingSupplierId');

            if (billingInput) billingInput.value = supplierId;
            if (shippingInput) shippingInput.value = supplierId;

            fetch(`/organization/${organizationId}/supplier/${supplierId}/data`)
                .then(response => response.json())
                .then(data => {
                    populateBillingLocations(data.locations, data.billing_data, supplierId);
                    populateShippingLocations(data.locations, data.shipping_data, supplierId);

                    if (loadingState && contentArea) {
                        loadingState.classList.add('hidden');
                        contentArea.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading supplier data:', error);

                    if (loadingState && contentArea) {
                        loadingState.classList.add('hidden');
                        contentArea.classList.remove('hidden');
                    }

                    alert('Failed to load supplier data.');
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('supplierSearch');

            if (!searchInput) return;

            searchInput.addEventListener('input', function (e) {
                const searchTerm = e.target.value.toLowerCase();

                document.querySelectorAll('.supplier-nav-item').forEach(item => {
                    const supplierName =
                        item.getAttribute('data-supplier-name')?.toLowerCase() || '';

                    item.style.display = supplierName.includes(searchTerm)
                        ? 'block'
                        : 'none';
                });
            });
        });
        // Populate billing locations
        function populateBillingLocations(locations, billingData, supplierId) {
            const container = document.getElementById('billingLocations');
            container.innerHTML = '';

            if (!locations || locations.length === 0) {
                container.innerHTML = '<p class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">No locations available</p>';
                return;
            }

            locations.forEach(location => {
                const key = `${location.id}-${supplierId}`;
                const value = billingData[key] || '';

                const locationCard = document.createElement('div');
                locationCard.className = 'p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700';

                locationCard.innerHTML = `
                    <div class="flex items-start justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                            ${location.name}
                        </label>
                        ${location.is_default ? '' : ''}
                    </div>
                    ${userRoleId == 1 ? `
                        <input 
                            type="text" 
                            name="billingData[${location.id}][${supplierId}]"
                            value="${value}"
                            placeholder="Bill to reference"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                        />
                    ` : `
                        <input 
                            type="text" 
                            value="${value}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-600 text-gray-500 cursor-not-allowed" 
                            disabled
                        />
                    `}
                `;

                container.appendChild(locationCard);
            });
        }

        // Populate shipping locations
        function populateShippingLocations(locations, shippingData, supplierId) {
            const container = document.getElementById('shippingLocations');
            container.innerHTML = '';

            if (!locations || locations.length === 0) {
                container.innerHTML = '<p class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">No locations available</p>';
                return;
            }

            locations.forEach(location => {
                const key = `${location.id}-${supplierId}`;
                const value = shippingData[key] || '';

                const locationCard = document.createElement('div');
                locationCard.className = 'p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700';

                locationCard.innerHTML = `
                    <div class="flex items-start justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                            ${location.name}
                        </label>
                        
                        ${location.is_default_shipping ? '' : ''}
                    </div>
                    ${userRoleId == 1 ? `
                        <input 
                            type="text" 
                            name="shippingData[${location.id}][${supplierId}]"
                            value="${value}"
                            placeholder="Ship to reference"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                        />
                    ` : `
                        <input 
                            type="text" 
                            value="${value}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-600 text-gray-500 cursor-not-allowed" 
                            disabled
                        />
                    `}
                `;

                container.appendChild(locationCard);
            });
        }

        // Search functionality
        document.getElementById('supplierSearch').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const supplierItems = document.querySelectorAll('.supplier-nav-item');

            supplierItems.forEach(item => {
                const supplierName = item.getAttribute('data-supplier-name').toLowerCase();
                if (supplierName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Form submission handlers
        document.getElementById('billingForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Billing information updated successfully!');
                    } else {
                        alert('Failed to update billing information.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });

        document.getElementById('shippingForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Shipping information updated successfully!');
                    } else {
                        alert('Failed to update shipping information.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });
    </script>
</x-app-layout>