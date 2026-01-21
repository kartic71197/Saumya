<div class="col-span-1 bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Our Cost</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                ${{ number_format($totalOurCost ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                </path>
            </svg>
        </div>
    </div>
</div>