<div class="col-span-1 bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Profit</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                ${{ number_format($totalProfit ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
        </div>
    </div>
</div>