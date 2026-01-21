<x-app-layout>
    @if($currentPlan['name'] != 'NA')
        <div class="max-w-screen-md mx-auto mb-12">
            <div
                class="relative flex flex-col p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg overflow-hidden">
                <!-- Active status badge -->
                <div class="absolute top-0 right-0 mt-4 mr-4">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1.5 rounded-full">Active
                        Plan</span>
                </div>

                <div class="text-center">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Your Current Plan:
                        <span
                            class="text-primary-md dark:text-primary-light">{{ ucfirst($currentPlan['name']) }}</span>
                    </h3>

                    <div class="flex items-baseline justify-center mb-4">
                        <span
                            class="text-3xl font-extrabold text-gray-900 dark:text-white">${{ number_format($currentPlan['price'], 2) }}</span>
                        <span class="ml-2 text-gray-500 dark:text-gray-400 text-sm">
                            /
                            {{ $currentPlan['duration'] == 12 ? 'year' : $currentPlan['duration'] . ' months' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-center">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Expires On</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <time datetime="{{ $currentPlan['expiry_date'] }}">
                                    {{ \Carbon\Carbon::parse($currentPlan['expiry_date'])->format('M j, Y') }}
                                </time>
                            </p>
                        </div>

                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Max Users</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $currentPlan['max_users'] }}
                            </p>
                        </div>

                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Max Clinics</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $currentPlan['max_locations'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Error Message Display -->
    @if(session('error'))
        <div class="max-w-7xl mx-auto mb-6">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Header with better spacing and typography -->
    <div class="mx-auto max-w-screen-md text-center mb-12">
        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-4">
            Find the right plan for you
        </h2>
        <p class="text-gray-600 dark:text-gray-400 text-lg mb-8">
            Choose a plan which fits best for your Practices. All plans include core features.
        </p>

        <!-- Filter toggle -->
        {{-- <div class="inline-flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl shadow-inner mb-8">
            @foreach ([0 => 'All Plans', 3 => 'Quarterly', 6 => 'Semi-Annual', 12 => 'Annual'] as $value => $label)
                <button type="button" data-duration="{{ $value }}"
                    id="filter-{{ strtolower(str_replace(' ', '-', $label)) }}"
                    class="plan-filter-btn px-4 py-2 text-sm font-medium rounded-lg transition-all duration-300 focus:outline-none
                                {{ $planDuration == $value ? 'bg-white dark:bg-gray-700 text-primary-md shadow-sm' : 'bg-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div> --}}
    </div>

    <!-- Plan cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
        @forelse ($plans as $plan)
            @php
                $isCurrentPlan = $plan->id == $selectedPlan;
                $isLowerPlan = $currentPrice > 0 && $plan->price < $currentPrice;
                $canUpgrade = !$isCurrentPlan && !$isLowerPlan;
            @endphp

            <div id="plan-{{ $plan->id }}" data-duration="{{ $plan->duration }}"
                data-plan="{{ json_encode($plan) }}"
                class="plan-card relative flex flex-col h-full p-6 rounded-2xl border shadow-lg transition-all duration-300 transform overflow-hidden
                    {{ $isLowerPlan ? 'bg-gray-100 dark:bg-gray-900 border-gray-300 dark:border-gray-600 opacity-75 cursor-not-allowed' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1' }}">

                @if($isCurrentPlan)
                    <div class="absolute top-0 right-0 mt-4 mr-4">
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1.5 rounded-full">Current Plan</span>
                    </div>
                @endif

                {{-- @if($isLowerPlan)
                    <div class="absolute top-0 left-0 mt-4 ml-4">
                        <span class="bg-gray-200 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-full">Downgrade</span>
                    </div>
                @endif --}}

                <h3 class="mb-2 text-2xl font-bold {{ $isLowerPlan ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white' }}">
                    {{ ucfirst($plan->name) }}
                </h3>
                <p class="mb-6 text-gray-500 dark:text-gray-400 text-sm flex-grow">
                    {{ $plan->description }}
                </p>

                <div class="flex items-baseline mb-6">
                    <span class="text-3xl font-extrabold {{ $isLowerPlan ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white' }}">${{ $plan->price }}</span>
                    <span class="ml-2 text-gray-500 dark:text-gray-400 text-sm">
                        {{ $plan->duration == '12' ? '/year' : '/' . $plan->duration . ' months' }}
                    </span>
                </div>

                <ul role="list" class="mb-8 space-y-3 text-left">
                    @php
                        $features = [
                            'Individual configuration',
                            'No setup, or hidden fees',
                            'Maximum users: <span class="font-semibold">' . $plan->max_users . '</span>',
                            'Premium support: <span class="font-semibold">' . $plan->duration . ' months</span>',
                            'Maximum clinics: <span class="font-semibold">' . $plan->max_locations . '</span>',
                        ];
                    @endphp
                    @foreach ($features as $feature)
                        <li class="flex items-center">
                            <svg class="flex-shrink-0 w-5 h-5 {{ $isLowerPlan ? 'text-gray-400 dark:text-gray-500' : 'text-green-500 dark:text-green-400' }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-3 {{ $isLowerPlan ? 'text-gray-500 dark:text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">{!! $feature !!}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-auto">
                    @if($isCurrentPlan)
                        <button type="button"
                            class="w-full py-3 px-6 font-medium text-center text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg cursor-default">
                            Current Plan
                        </button>
                    @elseif($isLowerPlan)
                        <div class="relative group">
                            <button type="button"
                                class="w-full py-3 px-6 font-medium text-center text-gray-500 bg-gray-200 dark:bg-gray-700 dark:text-gray-400 rounded-lg cursor-not-allowed transition-colors duration-200">
                                Contact to Downgrade
                            </button>
                            <!-- Hover tooltip -->
                            <div class="absolute bottom-full left-0 right-0 mb-2 px-3 py-2 bg-red-600 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10 mx-2 text-center">
                                Downgrade to {{ ucfirst($plan->name) }} is not allowed. Please contact support for assistance.
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-red-600"></div>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('checkout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <input type="hidden" name="plan_price" value="{{ $plan->price }}">
                            <button type="submit"
                                class="w-full py-3 px-6 font-medium text-center text-white bg-primary-md hover:bg-primary-dk focus:ring-4 focus:ring-primary-200 rounded-lg transition-all duration-300 dark:focus:ring-primary-900">
                                Upgrade Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty

            <div class="col-span-3 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No plans available</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please check back later or contact support.</p>
            </div>
        @endforelse
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.plan-filter-btn');
            const planCards = document.querySelectorAll('.plan-card');

            // Filter logic
            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const selectedDuration = this.dataset.duration;

                    // Toggle button styles
                    filterButtons.forEach(btn => {
                        btn.classList.remove('bg-white', 'dark:bg-gray-700', 'text-primary-md', 'shadow-sm');
                        btn.classList.add('bg-transparent', 'text-gray-700', 'dark:text-gray-300');
                        btn.setAttribute('aria-pressed', 'false');
                    });

                    this.classList.remove('bg-transparent', 'text-gray-700', 'dark:text-gray-300');
                    this.classList.add('bg-white', 'dark:bg-gray-700', 'text-primary-md', 'shadow-sm');
                    this.setAttribute('aria-pressed', 'true');

                    // Filter cards
                    planCards.forEach(card => {
                        const cardDuration = card.dataset.duration;
                        if (selectedDuration === '0' || selectedDuration === cardDuration) {
                            card.style.display = 'flex';
                            card.classList.remove('opacity-0');
                            card.classList.add('opacity-100', 'transition-opacity', 'duration-300');
                        } else {
                            card.classList.remove('opacity-100');
                            card.classList.add('opacity-0');
                            setTimeout(() => {
                                card.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
        });
    </script>

</x-app-layout>