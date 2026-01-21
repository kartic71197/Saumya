<div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Billing Information') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("Enter billing values for each supplier and location. Expand a location to manage its suppliers.") }}
            </p>
        </header>

        {{-- Search Bar --}}
        <div class="mt-6 mb-4">
            <input 
                type="text" 
                id="searchInput"
                placeholder="Search locations..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
            />
        </div>

        <form method="post" action="{{ route('billing.update', ['organization_id' => $organization_id]) }}" class="mt-6 space-y-6">
            @csrf
            @method('post')

            {{-- Accordion Section --}}
            <div class="space-y-3" id="accordionContainer">
                @forelse ($locations as $location)
                    @php
                        $isDefault = $location->is_default ?? false;
                        $supplierCount = $suppliers->count();
                    @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden location-accordion">
                        {{-- Accordion Header --}}
                        <button 
                            type="button"
                            class="accordion-header w-full flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            onclick="toggleAccordion(this)"
                        >
                            <div class="flex items-center gap-4">
                                <input 
                                    type="radio" 
                                    name="default_location" 
                                    value="{{ $location->id }}" 
                                    {{ $isDefault ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                    onclick="event.stopPropagation()"
                                />
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 location-name">
                                        {{ $location->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $supplierCount }} {{ $supplierCount === 1 ? 'supplier' : 'suppliers' }}
                                    </p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 accordion-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Accordion Content --}}
                        <div class="accordion-content">
                            <div class="p-4 bg-white dark:bg-gray-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($suppliers as $supplier)
                                    @php
                                        $key = $location->id . '-' . $supplier->id;
                                        $existingValue = $billingData[$key][0]->bill_to ?? '';
                                    @endphp
                                    <div class="p-3 rounded-lg supplier-item">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 supplier-name">
                                                {{ $supplier->supplier_name }}
                                            </label>
                                            @if ($user->role_id == 1)
                                                <input 
                                                    type="text" 
                                                    name="billingData[{{ $location->id }}][{{ $supplier->id }}]"
                                                    value="{{ old("billingData.$location->id.$supplier->id", $existingValue) }}"
                                                    placeholder="Bill to reference"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                                                />
                                            @else
                                                <input 
                                                    type="text" 
                                                    value="{{ $existingValue }}"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-200 dark:bg-gray-600 text-gray-500 cursor-not-allowed" 
                                                    disabled
                                                />
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-600 dark:text-gray-300">
                        No billing details available.
                    </div>
                @endforelse
            </div>

            {{-- Submit Button --}}
            @if ($user->role_id == 1 && $locations->isNotEmpty() && $suppliers->isNotEmpty())
                <div class="flex justify-end items-center gap-4 pt-4">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                </div>
            @endif
        </form>
    </div>
</div>

<style>
    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .accordion-content.active {
        max-height: 3000px;
        transition: max-height 0.5s ease-in;
    }
</style>

<script>
    function toggleAccordion(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('.accordion-icon');
        const isActive = content.classList.contains('active');
        
        // Close all accordions
        document.querySelectorAll('.accordion-content').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.accordion-icon').forEach(item => {
            item.style.transform = 'rotate(0deg)';
        });
        
        // Open clicked accordion if it was closed
        if (!isActive) {
            content.classList.add('active');
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const accordions = document.querySelectorAll('.location-accordion');
        
        accordions.forEach(accordion => {
            const locationName = accordion.querySelector('.location-name').textContent.toLowerCase();
            const supplierNames = Array.from(accordion.querySelectorAll('.supplier-name'))
                .map(label => label.textContent.toLowerCase())
                .join(' ');
            
            if (locationName.includes(searchTerm) || supplierNames.includes(searchTerm)) {
                accordion.style.display = 'block';
            } else {
                accordion.style.display = 'none';
            }
        });
    });
</script>