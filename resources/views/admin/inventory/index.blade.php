<x-app-layout>
    <div class="p-5">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">

            <!-- Tab Contents - Added proper spacing and container styling -->
            <div id="inventory-section" class="tab-content p-6">
                <livewire:admin.inventory.inventory-component />
            </div>
            <div id="batch-section" class="tab-content hidden p-6">
                <livewire:organization.batch-inventory />
            </div>
        </div>
        <!-- Image Modal - Improved design and animations -->
        <div id="imageModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-75 transition-opacity duration-200">
            <div
                class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full mx-4 overflow-hidden transform transition-transform duration-200 scale-95 opacity-0">
                <div class="absolute top-3 right-3">
                    <button onclick="closeImageModal()"
                        class="p-1.5 bg-white dark:bg-gray-700 rounded-full shadow hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                    <img id="modalImage" class="max-w-full max-h-[75vh] object-contain" src="" alt="Product Image">
                </div>
            </div>
        </div>
    </div>



    <script>
        // Tab switching logic - Enhanced for better visual feedback
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.tab-btn');
            const sections = {
                inventory: document.getElementById('inventory-section'),
                batch: document.getElementById('batch-section')
            };

            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    const target = button.dataset.section;

                    // Show the selected section, hide others
                    Object.keys(sections).forEach(key => {
                        sections[key].classList.toggle('hidden', key !== target);
                        // Add fade transition
                        if (key === target) {
                            sections[key].classList.add('opacity-100');
                            sections[key].classList.remove('opacity-0');
                        }
                    });

                    // Update button styles
                    buttons.forEach(btn => {
                        // Apply active styles with border-b-2
                        if (btn.dataset.section === target) {
                            btn.classList.add('text-primary-md', 'border-primary-md');
                            btn.classList.remove('text-gray-500', 'border-transparent');
                        } else {
                            btn.classList.remove('text-primary-md', 'border-primary-md');
                            btn.classList.add('text-gray-500', 'border-transparent');
                        }
                    });
                });
            });
        });

        // Enhanced image modal logic with smooth animations
        function openImageModal(imageUrl) {
            console.log('Opening image modal with URL:', imageUrl);
            const modal = document.getElementById('imageModal');
            const modalContent = modal.querySelector('div');

            document.getElementById('modalImage').src = imageUrl;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Trigger animation
            setTimeout(() => {
                modal.classList.add('bg-opacity-75');
                modal.classList.remove('bg-opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function openProductModal(id, context, locationId) {
            console.log('openProductModal called with ID:', id, 'Context in admin:', context, 'Location:', locationId);

            // Check if Livewire is available
            if (typeof Livewire !== 'undefined') {
                console.log('Livewire is available, dispatching event...');
                Livewire.dispatch('openProductDetailBrowser', { 
                    id: id,
                    context: context,
                    location_id: locationId
                
                });
                console.log('Event dispatched');
            } else {
                console.error('Livewire is not available!');
            }
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            const modalContent = modal.querySelector('div');

            // Trigger animation
            modal.classList.remove('bg-opacity-75');
            modal.classList.add('bg-opacity-0');
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');

            // Hide after animation completes
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 1000);
        }

        document.getElementById('imageModal').addEventListener('click', function (event) {
            if (event.target === this) closeImageModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeImageModal();
        });
    </script>

    <style>
        /* Tab button styles */
        .tab-btn {
            transition-property: all;
            transition-duration: 200ms;
        }

        /* Added transition for tab content */
        .tab-content {
            transition-property: opacity;
            transition-duration: 200ms;
        }

        /* Improved modal animations */
        #imageModal {
            --tw-bg-opacity: 0;
            transition-property: all;
            transition-duration: 200ms;
        }

        /* Focus styles for better accessibility */
        button:focus {
            outline: none;
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
            --tw-ring-color: rgba(var(--color-primary-md), 0.5);
        }
    </style>
</x-app-layout>


<script>
    document.addEventListener('livewire:load', () => {
        console.log('[INVENTORY INDEX] livewire:load fired');

        Livewire.on('openProductDetail', (productId) => {
            console.log('[INVENTORY INDEX] openProductDetail received with ID:', productId);
            Livewire.emit('cartIconClick', productId);
        });
    });
</script>
