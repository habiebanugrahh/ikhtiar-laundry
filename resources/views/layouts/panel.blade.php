@extends('layouts.app')

{{-- Layout untuk semua halaman setelah login: sidebar di kiri, konten di kanan --}}
@section('content')
<div class="min-h-screen lg:flex">

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-ink-900/40 lg:hidden"></div>

    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-white ring-1 ring-brand-100 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
    >
        @include('partials.sidebar')
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-brand-100 bg-white/90 px-4 py-3 backdrop-blur lg:px-8">
            <button
                type="button"
                id="sidebar-open"
                class="rounded-lg p-2 text-ink-700 hover:bg-cyan-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 lg:hidden"
                aria-label="Buka menu"
            >
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <h1 class="text-lg font-semibold text-ink-700">@yield('page_title')</h1>

            <p class="ml-auto hidden text-sm text-ink-700/70 sm:block">{{ now()->translatedFormat('l, d F Y') }}</p>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8">
            @include('partials.flash')

            @yield('panel')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Buka dan tutup sidebar di layar kecil
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function setSidebar(open) {
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay.classList.toggle('hidden', !open);
    }

    document.getElementById('sidebar-open').addEventListener('click', () => setSidebar(true));
    overlay.addEventListener('click', () => setSidebar(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setSidebar(false); });
</script>
@endpush