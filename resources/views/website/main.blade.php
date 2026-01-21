<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saumya') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Custom Tailwind Config -->


    <style>
        [x-cloak] {
            display: none !important;
        }

        .gradient-text {
            background: linear-gradient(to right, hsl(262, 83%, 58%), hsl(262, 83%, 58%), hsl(330, 85%, 60%));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-bg {
            background: linear-gradient(to right, hsl(262, 83%, 50%), hsl(262, 83%, 58%), hsl(330, 85%, 60%));
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float 6s ease-in-out infinite 2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .shadow-soft {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="bg-muted text-foreground antialiased">
    @include('website.components.navbar')
    @include('website.components.hero')
    @include('website.components.trust-section')
    @include('website.components.testimonials')
    @include('website.components.features')
    @include('website.components.cta')
    @include('website.components.footer')

    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 100
        });
    </script>
</body>

</html>
