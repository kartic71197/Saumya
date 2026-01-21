<style>
    .card-shadow {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .hover-shadow:hover {
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>
<div class="max-w-7xl mx-auto mt-3">
    <!-- Toggle View Buttons -->
    <div class="mb-6 flex space-x-2">
        <button id="tableViewBtn"
            class="px-4 py-2 bg-primary-md text-white rounded-lg hover:bg-primary-dk transition-colors">
            Table View
        </button>
        <button id="cardViewBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            Card View
        </button>
    </div>

    <!-- Table View -->
    <div id="tableView" class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            @if($prescriptions && count($prescriptions) > 0)
                <table class="min-w-full">
                    <thead class="bg-gradient-to-r from-primary-md to-primary-dk text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Lot/Expiry</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Drug</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Dose/Freq</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Cost</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Copay</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Insurance</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wider">Profit</th>
                        </tr>
                    </thead>

                        <tbody class="divide-y divide-gray-200">
                            <!-- Data -->
                            @foreach ($prescriptions as $prescription)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($prescription->date_given)->format('M, d Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <div class="max-w-32">
                                            <div class="truncate" title="LOT12345">{{ $prescription->batch_number ?? 'N/A'}}</div>
                                            <div class="text-xs text-gray-400">
                                                ({{ $prescription->expiry_date ? \Carbon\Carbon::parse($prescription->expiry_date)->format('M, d Y') : 'N/A' }})
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="max-w-40 truncate">
                                            {{ $prescription->product->product_name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $prescription->dose }}/{{ $prescription->frequency }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $organization?->currency }}{{ $prescription->our_cost }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $organization?->currency }}{{ $prescription->pt_copay }}</td>
                                    <td class="px-4 py-3 text-sm text-green-600 font-medium">
                                        {{ $organization?->currency }}{{ $prescription->paid }}</td>
                                    <td class="px-4 py-3 text-sm text-primary-md font-semibold">
                                        {{ $organization?->currency }}{{ $prescription->profit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                </table>
            @else
                <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="mx-auto h-24 w-24 text-gray-300">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No prescriptions found</h3>
                        <p class="mt-2 text-gray-600">Get started by adding your first prescription record.</p>
                    </div>
            @endif
        </div>
    </div>

    <!-- Card View -->
    <div id="cardView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!--  Cards -->
        @if(count($prescriptions) > 0)
            @foreach ($prescriptions as $prescription)
                <div class="bg-white rounded-lg card-shadow hover-shadow transition-all duration-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1" title="{{ $prescription->product->product_name }}">
                                {{ $prescription->product->product_name ?? 'N/A' }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ $prescription->date_given ? \Carbon\Carbon::parse($prescription->date_given)->format('M, d Y') : 'N/A' }}
                            </p>
                        </div>
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            Active
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dose/Frequency:</span>
                            <span class="text-sm font-medium">{{ $prescription->dose }}/{{ $prescription->frequency }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Batch/Expiry:</span>
                            <span class="text-sm font-medium">{{ $prescription->batch_number ?? 'N/A' }}
                                ({{ $prescription->expiry_date ? \Carbon\Carbon::parse($prescription->expiry_date)->format('M, d Y') : 'N/A' }})</span>
                        </div>

                        <div class="border-t pt-3 mt-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Our Cost</p>
                                    <p class="text-lg font-semibold text-gray-900">${{ $prescription->our_cost ?? '0.00' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Profit</p>
                                    <p class="text-lg font-semibold text-primary-md">${{ $prescription->profit ?? '0.00' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Copay</p>
                                    <p class="text-sm font-medium text-gray-700">{{ $prescription->pt_copay ?? '0.00' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Insurance</p>
                                    <p class="text-sm font-medium text-green-600">{{ $prescription->paid ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <!-- Empty State for Cards -->
            <div id="emptyCards" class="col-span-full text-center py-12 bg-white rounded-lg card-shadow">
                <div class="mx-auto h-24 w-24 text-gray-300">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No prescriptions found</h3>
                <p class="mt-2 text-gray-600">Get started by adding your first prescription record.</p>
            </div>
        @endif
    
    </div>

    <!-- Pagination -->
    {{-- <div class="mt-6 flex items-center justify-between bg-white px-4 py-3 rounded-lg card-shadow">
        <div class="flex items-center text-sm text-gray-700">
            <span>Showing 1 to 3 of 3 results</span>
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200 transition-colors"
                disabled>
                Previous
            </button>
            <button class="px-3 py-2 text-sm bg-primary-md text-white rounded hover:bg-primary-dk transition-colors">
                1
            </button>
            <button class="px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200 transition-colors"
                disabled>
                Next
            </button>
        </div>
    </div> --}}
</div>

<script>
    // View toggle functionality
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');

    tableViewBtn.addEventListener('click', () => {
        tableView.classList.remove('hidden');
        cardView.classList.add('hidden');
        tableViewBtn.classList.add('bg-primary-md', 'text-white');
        tableViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
        cardViewBtn.classList.add('bg-gray-200', 'text-gray-700');
        cardViewBtn.classList.remove('bg-primary-md', 'text-white');
    });

    cardViewBtn.addEventListener('click', () => {
        cardView.classList.remove('hidden');
        tableView.classList.add('hidden');
        cardViewBtn.classList.add('bg-primary-md', 'text-white');
        cardViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
        tableViewBtn.classList.add('bg-gray-200', 'text-gray-700');
        tableViewBtn.classList.remove('bg-primary-md', 'text-white');
    });

    // Add tooltip functionality for truncated text
    document.addEventListener('DOMContentLoaded', function () {
        const truncatedElements = document.querySelectorAll('.truncate');
        truncatedElements.forEach(element => {
            element.addEventListener('mouseenter', function () {
                if (this.scrollWidth > this.clientWidth) {
                    this.setAttribute('title', this.textContent);
                }
            });
        });
    });
</script>