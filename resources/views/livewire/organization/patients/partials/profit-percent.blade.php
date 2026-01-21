<div class="col-span-1 bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow">
    @php
        $isPositive = $profitPercent >= 0;
    @endphp
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Profit %</p>
            <p class="text-3xl font-bold mt-2 {{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                {{ $isPositive ? '+' : '' }}{{ number_format($profitPercent, 1) }}%
            </p>
        </div>
        <div class="p-3 rounded-full {{ $isPositive ? 'bg-green-100' : 'bg-red-100' }}">
            @if($isPositive)
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12">
                    </path>
                </svg>
            @else
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6">
                    </path>
                </svg>
            @endif
        </div>
    </div>
</div>