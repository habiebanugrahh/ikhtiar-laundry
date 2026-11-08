@php
    /** @var \App\Models\User $user */
    $user = auth()->user();

    // Ikon sederhana (path SVG 24x24)
    $icons = [
        'home' => 'M3 12l9-9 9 9M5 10v10h14V10',
        'plus' => 'M12 5v14M5 12h14',
        'list' => 'M4 6h16M4 12h16M4 18h16',
        'users' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.9M16 2.1a4 4 0 010 7.8',
        'tag' => 'M3 12V4h8l10 10-8 8L3 12zM7.5 8.5h.01',
        'user' => 'M12 11a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0',
        'calendar' => 'M7 3v3M17 3v3M4 8h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z',
        'chart' => 'M4 20V10M10 20V4M16 20v-8M22 20H2',
        'download' => 'M12 3v12m0 0l-4-4m4 4l4-4M4 21h16',
        'logout' => 'M15 17l5-5-5-5M20 12H9M9 21H5a1 1 0 01-1-1V4a1 1 0 011-1h4',
        'lock' => 'M8 11V8a4 4 0 118 0v3M6 11h12v9H6v-9z',
    ];

    // Menu per role. Menu yang bukan hak akses tidak ditampilkan.
    if ($user->isOwner()) {
        $groups = [
            ['title' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'owner.dashboard', 'match' => ['owner.dashboard'], 'icon' => 'home'],
                ['label' => 'Transaksi Baru', 'route' => 'transactions.create', 'match' => ['transactions.create'], 'icon' => 'plus'],
                ['label' => 'Data Transaksi', 'route' => 'transactions.index', 'match' => ['transactions.index', 'transactions.show'], 'icon' => 'list'],
                ['label' => 'Data Pelanggan', 'route' => 'customers.index', 'match' => ['customers.*'], 'icon' => 'users'],
            ]],
            ['title' => 'Laporan', 'items' => [
                ['label' => 'Rekap Harian', 'route' => 'reports.daily', 'match' => ['reports.daily'], 'icon' => 'calendar'],
                ['label' => 'Rekap Bulanan', 'route' => 'reports.monthly', 'match' => ['reports.monthly'], 'icon' => 'chart'],
                ['label' => 'Laporan', 'route' => 'reports.index', 'match' => ['reports.index'], 'icon' => 'download'],
            ]],
            ['title' => 'Pengaturan', 'items' => [
                ['label' => 'Pengaturan Tarif', 'route' => 'services.index', 'match' => ['services.*'], 'icon' => 'tag'],
                ['label' => 'Data Karyawan', 'route' => 'employees.index', 'match' => ['employees.*'], 'icon' => 'user'],
            ]],
        ];
    } else {
        $groups = [
            ['title' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'cashier.dashboard', 'match' => ['cashier.dashboard'], 'icon' => 'home'],
                ['label' => 'Transaksi Baru', 'route' => 'transactions.create', 'match' => ['transactions.create'], 'icon' => 'plus'],
                ['label' => 'Data Transaksi', 'route' => 'transactions.index', 'match' => ['transactions.index', 'transactions.show'], 'icon' => 'list'],
                ['label' => 'Data Pelanggan', 'route' => 'customers.index', 'match' => ['customers.*'], 'icon' => 'users'],
            ]],
        ];
    }

    $roleLabel = $user->isOwner() ? 'Owner' : 'Kasir';
@endphp

<div class="flex h-full flex-col">

    {{-- Identitas usaha --}}
    <div class="flex items-center gap-3 px-5 py-5">
        <div class="flex size-9 items-center justify-center rounded-xl bg-brand-500 text-sm font-bold text-white">I</div>
        <div class="leading-tight">
            <p class="font-bold text-ink-700">Ikhtiar Laundry</p>
            <p class="text-xs text-ink-700/60">Rekapitulasi pendapatan</p>
        </div>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-4" aria-label="Menu utama">
        @foreach ($groups as $group)
            <div>
                @if ($group['title'])
                    <p class="mb-1 px-3 text-xs font-medium text-ink-700/50">{{ $group['title'] }}</p>
                @endif

                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php $active = request()->routeIs(...$item['match']); @endphp
                        <li>
                            <a
                                href="{{ route($item['route']) }}"
                                @if ($active) aria-current="page" @endif
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500',
                                    'bg-brand-100 text-ink-700' => $active,
                                    'text-ink-700/80 hover:bg-cyan-soft hover:text-ink-700' => ! $active,
                                ])
                            >
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="{{ $icons[$item['icon']] }}"/>
                                </svg>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Pengguna dan logout --}}
    <div class="border-t border-brand-100 p-3">
        <div class="px-3 pb-2">
            <p class="truncate text-sm font-semibold text-ink-700">{{ $user->name }}</p>
            <p class="text-xs text-ink-700/60">{{ $roleLabel }}</p>
        </div>

        <a
            href="{{ route('password.edit') }}"
            @if (request()->routeIs('password.*')) aria-current="page" @endif
            @class([
                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500',
                'bg-brand-100 text-ink-700' => request()->routeIs('password.*'),
                'text-ink-700/80 hover:bg-cyan-soft hover:text-ink-700' => ! request()->routeIs('password.*'),
            ])
        >
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="{{ $icons['lock'] }}"/>
            </svg>
            Ubah kata sandi
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-ink-700/80 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
            >
                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="{{ $icons['logout'] }}"/>
                </svg>
                Keluar
            </button>
        </form>
    </div>
</div>