<div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
    <div class="mt-3 sm:mt-8 md:gap-6 lg:flex lg:items-start xl:gap-8">
        <div class="mx-auto w-full flex-none lg:max-w-2xl xl:max-w-4xl">
            @include('livewire.organization.cart.billing-shipping')
            <div class="space-y-6">
                @livewire('organization.add-more-to-cart-component', ['selectedLocation' => $selectedLocation], key($selectedLocation))
                @foreach ($cartItems as $item)
                    <div
                        class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        @php
                            $product = $item['product'];
                            $images = json_decode($product['image'], true);
                            $imagePath = is_array($images) && !empty($images) ? $images[0] : $product['image'];
                            $fullImageUrl = asset('storage/' . $imagePath);
                        @endphp

                        <div onclick="openImageModal('{{ $fullImageUrl }}')"
                            class="min-w-[50px] text-gray-900 dark:text-white text-sm font-medium cursor-pointer">
                            <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $item['product']['name'] }}"
                                class="w-8 h-8 mr-1 object-cover rounded">
                        </div>
                        <!-- Product Code & Name -->
                        <div class="flex-1 min-w-[150px] text-gray-900 dark:text-gray-100 text-sm font-medium">
                            ({{ $item['product']['code'] }}) {{ $item['product']['name'] }}
                        </div>

                        <!-- Supplier -->
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $item['product']['supplier']['name'] }}
                        </div>

                        <!-- Unit Selection -->
                        <select id="unit_{{ $item['id'] }}" wire:model.live="unit_id.{{ $item['id'] }}"
                            wire:change="updateUnitPrice({{ $item['id'] }}, {{ $item['product']['id'] }}, $event.target.value)"
                            class="text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                            @foreach ($item['product']['units'] as $unit)
                                <option value="{{ $unit['unit_id'] }}">
                                    {{ $unit['unit_name'] }} ({{ $unit['unit_code'] }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Quantity Input -->
                        <input type="number" min="1" max="100" wire:model.live="cartItems.{{ $loop->index }}.quantity"
                            wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)"
                            class="w-16 text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">

                        <!-- Price -->
                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                            ${{ number_format($item['price'], 2) }}
                        </div>

                        <!-- Remove Button -->
                        <button type="button" wire:click="removeItem({{ $item['id'] }})"
                            class="text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" div
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endforeach

            </div>
        </div>
        <div class="mx-auto mt-6 max-w-4xl flex-1 space-y-6 lg:mt-0 lg:w-full sticky top-16">
            <div
                class="space-y-4 rounded-lg border border-gray-200 bg-primary-dk p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                <p class="text-xl font-semibold text-white dark:text-white">Order summary</p>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-300 dark:text-gray-400">Original price</dt>
                            <dd class="text-base font-medium text-white dark:text-white">${{ $subtotal }}</dd>
                        </dl>

                        {{-- <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-300 dark:text-gray-400">Savings</dt>
                            <dd class="text-base font-medium text-green-600">-$0.00</dd>
                        </dl> --}}

                        <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-300 dark:text-gray-400">Freight <span
                                    class="text-xs">(Not calculated)</span> </dt>
                            <dd class="text-base font-medium text-white dark:text-white">$0.00</dd>
                        </dl>

                        <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-300 dark:text-gray-400">Tax <span
                                    class="text-xs">(Not calculated)</span></dt>
                            <dd class="text-base font-medium text-white dark:text-white">$0.00</dd>
                        </dl>
                    </div>
                    <dl
                        class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                        <dt class="text-base font-bold text-white dark:text-white">Total</dt>
                        <dd class="text-base font-bold text-white dark:text-white">${{ $total }}</dd>
                    </dl>
                    <dl
                        class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                        <i class="text-xs font-semibold text-white dark:text-white">Please note: Freight and applicable
                            taxes are additional and not included in the total amount displayed.</i>
                    </dl>
                </div>
                <x-secondary-button id="send-order-btn" onclick="handleSendOrder()" type="button"
                    wire:click="createPurchaseOrder"
                    class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Send
                    Order</x-secondary-button>
                <div class="flex items-center justify-center gap-2">
                </div>
            </div>
        </div>
        <!-- Notifications Container -->
        <div class="fixed top-24 right-4 z-50 space-y-2">
            @foreach ($notifications as $notification)
                <div wire:key="{{ $notification['id'] }}" x-data="{ show: true }" x-init="setTimeout(() => {
                                    show = false;
                                    $wire.removeNotification('{{ $notification['id'] }}');
                                }, 3000)" x-show="show" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-full"
                    class="{{ $notification['type'] === 'success' ? 'text-white bg-green-400' : 'text-white bg-red-400' }} border-l-4 x-6 py-6 px-4 rounded-lg shadow-lg">
                    <p>{{ $notification['message'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="relative bg-white rounded-lg shadow-xl max-w-3xl max-h-[90vh] overflow-hidden">
            <div class="absolute top-0 right-0 p-2">
                <button onclick="closeImageModal()" class="p-1 bg-white rounded-full shadow hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" class="max-w-full max-h-[80vh] mx-auto" src="" alt="Product Image">
            </div>
        </div>
    </div>

    <script>
        function openImageModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
                closeImageModal();
            }
        });

        let guideState = {
            step: 1, // 1: location, 2: shipping, 3: order
            locationSelected: false,
            shippingSelected: false
        };

        document.addEventListener('livewire:init', function () {
            setTimeout(startGuide, 100);
        });


        function startGuide() {
            highlightElement('location-dropdown');
            console.log("1. Starting guide...");

        }

        function highlightElement(elementId) {
            removeAllHighlights();
            const element = document.getElementById(elementId);
            console.log("2.outisde", element)
            if (element && elementId === "send-order-btn") {
                element.classList.remove('bg-primary-700', 'bg-white')
                element.classList.add('bg-green-500', 'text-white')
                console.log("3. sendorderbtn")
                console.log(element)
            } else if (element) {
                console.log("4.Before adding classes:", element.className);
                element.classList.remove('border-gray-300', 'border-green-500');
                element.classList.add('border-primary-dk', 'border-2');
                console.log("5.After adding classes:", element.className);
            }
            console.log("6. Attempting to highlight:", elementId);
        };

        function removeAllHighlights() {
            const elements = ['location-dropdown', 'selectedShippingLocation', 'send-order-btn'];
            elements.forEach(elementId => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.classList.remove('border-primary-dk', 'border-2');
                }
                console.log("7. Attempting to remove highlight:", elementId)
            });
        }

        function handleLocationClick() {
            if (guideState.step === 1) {
                // Remove highlight when user clicks on location dropdown
                document.getElementById('location-dropdown').classList.remove('border-primary-dk', 'border-2');

            }
            if (guideState.step === 1 && dropdown.value) {
                handleLocationSelect();
            }
        }

        function handleLocationSelect() {
            const dropdown = document.getElementById('location-dropdown');
            const currentValue = dropdown.value;

            // Always update the previous value (for future comparisons)
            dropdown.dataset.previousValue = currentValue;

            // If dropdown has any value (including default) and we're on step 1
            if (currentValue) {
                guideState.locationSelected = true;
                guideState.step = 2;

                setTimeout(() => {
                    highlightElement('selectedShippingLocation');
                }, 500);
            }
        }

        // Add this event listener for clicks (in addition to change)
        document.getElementById('location-dropdown').addEventListener('click', function () {
            if (guideState.step === 1) {
                // Force check selection when dropdown is clicked
                handleLocationSelect();
            }
        });

        function handleShippingClick() {
            if (guideState.step === 2) {
                // Remove highlight when user clicks on shipping dropdown
                document.getElementById('selectedShippingLocation').classList.remove('border-primary-dk', 'border-2');
            }
        }

        function handleShippingSelect() {
            const dropdown = document.getElementById('selectedShippingLocation');
            const currentValue = dropdown.value;
            dropdown.dataset.previousValue = currentValue;
            if (currentValue && currentValue.trim() !== '') {
                guideState.shippingSelected = true;
                guideState.step = 3;

                // Move to final step - highlight send order button
                setTimeout(() => {
                    highlightElement('send-order-btn');
                }, 500);
            }
        }

        document.getElementById('selectedShippingLocation').addEventListener('click', function () {
            if (guideState.step === 2) {
                // Force check selection when dropdown is clicked
                handleShippingSelect();
            }
        });

        function handleSendOrder() {
            if (guideState.step === 3) {
                // Remove highlight and complete the guide
                document.getElementById('send-order-btn').classList.remove('border-primary-dk', 'border-2');
                guideState.step = 4; // Guide completed

                // Here you would normally trigger the actual order creation
                // alert('Order sent successfully!');
            }
        }

        // Optional: Reset guide function for testing
        function resetGuide() {
            guideState = {
                step: 1,
                locationSelected: false,
                shippingSelected: false
            };
            document.getElementById('location-dropdown').value = '';
            document.getElementById('shipping-dropdown').value = '0';
            startGuide();
        }
    </script>
    @include('livewire.organization.cart-modal.po-suffix-modal')

</div>