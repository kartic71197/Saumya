<x-app-layout>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">

            <div class="text-xs">
                {{-- Wrapper component for filters + powergrid table --}}
                <livewire:reports.invoice-report-component />
            </div>

        </div>
    </div>

    {{-- Enhanced Invoice Modal --}}
    <div id="invoiceModal"
        class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center transition-all duration-300">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95"
            id="modalContent">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white">Invoice Details</h3>
                </div>
                <button onclick="closeInvoice()"
                    class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center text-white transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="overflow-y-auto max-h-[calc(90vh-80px)]" id="invoiceContent">
                {{-- Loading state --}}
                <div class="flex items-center justify-center p-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Loading invoice...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced JavaScript --}}
    <script>
        let currentInvoiceId = null;

        function openInvoice(id) {
            currentInvoiceId = id;
            const modal = document.getElementById('invoiceModal');
            const modalContent = document.getElementById('modalContent');
            const invoiceContent = document.getElementById('invoiceContent');

            // Show modal with loading state
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);

            // Show loading state
            invoiceContent.innerHTML = `
                <div class="flex items-center justify-center p-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Loading invoice...</span>
                </div>
            `;

            // Fetch invoice data
            fetch(`/report/getinvoices/${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    invoiceContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching invoice:', error);
                    invoiceContent.innerHTML = `
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.134 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Error Loading Invoice</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">Unable to load the invoice details. Please try again.</p>
                            <button onclick="openInvoice(${id})" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Retry
                            </button>
                        </div>
                    `;
                });
        }

        function closeInvoice() {
            const modal = document.getElementById('invoiceModal');
            const modalContent = document.getElementById('modalContent');

            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
                currentInvoiceId = null;
            }, 300);
        }

        // Close modal on escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.getElementById('invoiceModal').classList.contains('hidden')) {
                closeInvoice();
            }
        });

        // Close modal on backdrop click
        document.getElementById('invoiceModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeInvoice();
            }
        });
    </script>
</x-app-layout>
