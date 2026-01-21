<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeClass }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Healthshade') }}</title>
    <link rel="icon" href="{{ url('icon.PNG') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
        integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-DDN6Y7KLYX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-DDN6Y7KLYX');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')
        @if(auth()->user()->role_id == '1')
            @include('layouts.admin-side-navigation')
        @elseif(auth()->user()->is_medical_rep)
            @include('layouts.medical-rep-navigation')
        @else
            @include('layouts.user-side-navigation')
        @endif

        {{-- <!-- impersonate controller -->
        @if(session()->has('impersonator_id'))
            <div style="
                    position: fixed;
                    bottom: 20px;   
                    right: 20px;
                    background: #ffefc2;
                    padding: 12px 16px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                ">
                <span><strong>IMPERSONATING:</strong> You are signed in as {{ Auth::user()->name }}:</span>
                <form method="POST" action="{{ route('impersonate.stop') }}">
                    @csrf
                    <button class="text-red-600 text-semibold btn btn-sm btn-outline-dark">Stop</button>
                </form>
            </div>
        @endif --}}


        <!-- flash -->
        <main class="md:ml-20 pt-20 p-3">
            <!-- Global Back Button -->
           <!-- Global Back Button -->
@if (!in_array(Route::currentRouteName(), ['dashboard', 'medical_rep.dashboard','organization.catalog', 'organization.inventory', 'purchase.index', 'picking.index', 'report.index', 'barcode.index', 'patient.index', 'organization.settings', 'organization.settings.inventory_adjust',
        'organization.settings.inventory_transfer', 'organization.settings.cycle_counts', 'admin.organization.index', 'admin.inventory.index', 'admin.purchase.index', 'admin.settings.index', 'admin.blogs.index', 'potential-users.index', 'ticket.index']))
    <div class="flex items-center justify-start ml-4 mb-1">
        <button onclick="history.back()"
            class="text-gray-500 p-2 hover:bg-gray-300 dark:hover:bg-gray-700 rounded-lg transition-all flex items-center gap-1 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3">
            
            <!-- Back Icon -->
            <svg class="w-6 h-6 text-gray-700 dark:text-gray-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            
            Back
        </button>
    </div>
@endif

            @if (session('error'))
                <div id="error-alert" class="bg-red-500 text-white p-4 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div id="success-alert" class="bg-green-500 text-white p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <livewire:notification.notification-component />
            <livewire:product-details-component />
            <div x-data="{ showTable: false }" x-init="setTimeout(() => showTable = true, 200)">
                <div x-show="showTable" x-cloak x-transition:enter="transition ease-out duration-1000"
                    x-transition:enter-start="opacity-0 translate-y-60"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select the alert div
            const errorAlert = document.getElementById('error-alert');
            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.transition = 'opacity 0.5s ease';
                    errorAlert.style.opacity = '0';
                    setTimeout(() => errorAlert.remove(), 500);
                }, 5000);
            }
        });
    </script>
    @stack('scripts')
</body>

</html>