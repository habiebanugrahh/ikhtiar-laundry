<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ikhtiar Laundry') | Ikhtiar Laundry</title>

    {{-- Tailwind CSS, Alpine.js, dan font dibangun oleh Vite (npm run dev atau npm run build) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="min-h-screen bg-cyan-soft font-sans text-ink-900 antialiased">

    @yield('content')

    @stack('scripts')
</body>
</html>