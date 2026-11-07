@extends('layouts.panel')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('panel')
@php
    $today = today()->toDateString();
    $segmentBase = 'rounded-lg px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500';
@endphp

{{-- Ringkasan --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card
        label="Pendapatan hari ini"
        :value="\App\Support\Format::rupiah($incomeToday)"
        :note="$paymentCountToday . ' pembayaran. Kemarin ' . \App\Support\Format::rupiah($incomeYesterday)"
        :href="route('reports.daily')"
    />
    <x-stat-card
        label="Pendapatan bulan ini"
        :value="\App\Support\Format::rupiah($incomeMonth)"
        :note="$monthLabel"
        :href="route('reports.monthly')"
    />
    <x-stat-card
        label="Transaksi hari ini"
        :value="$transactionCountToday"
        note="Transaksi dicatat hari ini"
        :href="route('transactions.index', ['from' => $today, 'to' => $today])"
    />
    <x-stat-card
        label="Belum lunas"
        :value="$unpaid->count . ' transaksi'"
        :note="$unpaid->unpaid_count . ' belum bayar, ' . $unpaid->partial_count . ' sebagian. Sisa ' . \App\Support\Format::rupiah($unpaid->remaining)"
        :href="route('transactions.index', ['payment_status' => 'outstanding'])"
    />
</div>

{{-- Grafik pendapatan --}}
<section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="font-semibold text-ink-700">Pendapatan, {{ $chart['periodLabel'] }}</h2>
            <p class="mt-1 text-sm text-ink-700/70">
                Total diterima <span class="font-semibold text-ink-700">{{ \App\Support\Format::rupiah($chart['total']) }}</span>
            </p>
        </div>

        <nav class="flex gap-1 rounded-xl bg-cyan-soft p-1" aria-label="Pilih periode grafik">
            @foreach ($periods as $key => $label)
                <a
                    href="{{ route('owner.dashboard', ['period' => $key]) }}"
                    @if ($period === $key) aria-current="true" @endif
                    @class([
                        $segmentBase,
                        'bg-white text-ink-700 shadow-sm' => $period === $key,
                        'text-ink-700/70 hover:text-ink-700' => $period !== $key,
                    ])
                >{{ \Illuminate\Support\Str::of($label)->replace(' terakhir', '') }}</a>
            @endforeach
        </nav>
    </div>

    @if ($chart['total'] > 0)
        <div class="mt-4 h-64 sm:h-80">
            <canvas id="incomeChart" role="img" aria-label="Grafik pendapatan {{ $chart['periodLabel'] }}"></canvas>
        </div>
    @else
        <p class="mt-4 rounded-xl bg-cyan-soft px-4 py-8 text-center text-sm text-ink-700/70">Belum ada pembayaran yang diterima pada periode ini.</p>
    @endif

    <p class="mt-4 text-sm text-ink-700/70">
        Rincian lengkap:
        <a href="{{ route('reports.daily') }}" class="font-medium text-brand-600 hover:text-ink-700">Rekap harian</a>
        dan
        <a href="{{ route('reports.monthly') }}" class="font-medium text-brand-600 hover:text-ink-700">rekap bulanan</a>.
    </p>
</section>

{{-- Tagihan yang perlu ditagih --}}
<div class="mt-6">
    @include('partials.unpaid-list', ['transactions' => $unpaidTransactions])
</div>
@endsection

@if ($chart['total'] > 0)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        // Grafik pendapatan menurut periode terpilih
        const chartData = @js($chart);
        const rupiah = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(value);
        const compact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 });
        const isBar = chartData.type === 'bar';

        new Chart(document.getElementById('incomeChart'), {
            type: chartData.type,
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Pembayaran diterima',
                    data: chartData.values,
                    borderColor: '#0891b2',
                    backgroundColor: isBar ? '#06b6d4' : 'rgba(6, 182, 212, 0.15)',
                    borderWidth: 2,
                    borderRadius: isBar ? 6 : 0,
                    fill: ! isBar,
                    tension: 0.3,
                    pointRadius: isBar ? 0 : 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#0891b2',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => chartData.titles[items[0].dataIndex],
                            label: (context) => ' ' + rupiah(context.parsed.y),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 0, autoSkipPadding: 12 },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (value) => 'Rp' + compact.format(value) },
                    },
                },
            },
        });
    </script>
    @endpush
@endif