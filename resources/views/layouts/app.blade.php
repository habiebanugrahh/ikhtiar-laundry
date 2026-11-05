<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ikhtiar Laundry') | Ikhtiar Laundry</title>

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS via CDN (sementara untuk tahap awal) --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    {{-- Tema warna: cyan, blue, dan white --}}
    <style type="text/tailwindcss">
        @theme {
            --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;

            --color-cyan-soft: #ecfeff;
            --color-brand-100: #cffafe;
            --color-brand-400: #22d3ee;
            --color-brand-500: #06b6d4;
            --color-brand-600: #0891b2;
            --color-ink-700: #0c4a6e;
            --color-ink-900: #082f49;
        }
    </style>

    @stack('head')
</head>
<body class="min-h-screen bg-cyan-soft font-sans text-ink-900 antialiased">

    @yield('content')

    @stack('scripts')
</body>
</html>