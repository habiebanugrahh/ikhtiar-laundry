@extends('layouts.panel')

@section('title', 'Rekap Bulanan')
@section('page_title', 'Rekap Bulanan')

@section('panel')
@php
    $inputClass = 'block rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
    $navClass = 'rounded-lg px-3 py-2.5 text-sm font-medium text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500';
    $methodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
@endphp

{{-- Pemilih bulan dan tahun --}}
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-ink-700">{{ $start->translatedFormat('F Y') }}</h2>
        <p class="mt-1 text-sm text-ink-700/70">
            Total uang yang diterima selama bulan ini, berdasarkan tanggal pembayaran.
            @if ($isCurrentMonth)
                Bulan berjalan dihitung sampai hari ini.
            @endif
        </p>
    </div>

    <form method="GET" action="{{ route('reports.monthly') }}" class="flex flex-wrap items-center gap-2">
        <a href="{{ route('reports.monthly', ['year' => $previousMonth->year, 'month' => $previousMonth->month]) }}" class="{{ $navClass }}" aria-label="Bulan sebelumnya">Sebelumnya</a>

        <label for="month" class="sr-only">Bulan</label>
        <select id="month" name="month" onchange="this.form.submit()" class="{{ $inputClass }}">
            @foreach (range(1, 12) as $m)
                <option value="{{ $m }}" @selected($start->month === $m)>{{ \Illuminate\Support\Carbon::create(2000, $m, 1)->translatedFormat('F') }}</option>
            @endforeach
        </select>

        <label for="year" class="sr-only">Tahun</label>
        <select id="year" name="year" onchange="this.form.submit()" class="{{ $inputClass }}">
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected($start->year === $y)>{{ $y }}</option>
            @endforeach
        </select>

        @if ($nextMonth)
            <a href="{{ route('reports.monthly', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="{{ $navClass }}" aria-label="Bulan berikutnya">Berikutnya</a>
        @endif

        @unless ($isCurrentMonth)
            <a href="{{ route('reports.monthly') }}" class="{{ $navClass }}">Bulan ini</a>
        @endunless

        <a href="{{ route('reports.index', ['type' => 'monthly', 'year' => $start->year, 'month' => $start->month]) }}" class="{{ $navClass }}">Unduh laporan</a>

        <noscript>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Tampilkan</button>
        </noscript>
    </form>
</div>

{{-- Ringkasan pendapatan --}}
@php
    if ($changePercent === null) {
        $changeNote = 'Belum ada pembanding dari bulan lalu';
    } elseif ($changePercent > 0) {
        $changeNote = 'Naik ' . $changePercent . '% dibanding ' . ($isCurrentMonth ? 'periode yang sama bulan lalu' : 'bulan sebelumnya');
    } elseif ($changePercent < 0) {
        $changeNote = 'Turun ' . abs($changePercent) . '% dibanding ' . ($isCurrentMonth ? 'periode yang sama bulan lalu' : 'bulan sebelumnya');
    } else {
        $changeNote = 'Sama dengan ' . ($isCurrentMonth ? 'periode yang sama bulan lalu' : 'bulan sebelumnya');
    }
@endphp

<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-stat-card label="Total pembayaran diterima" :value="\App\Support\Format::rupiah($totalReceived)" :note="$changeNote" />
    <x-stat-card label="Jumlah pembayaran" :value="$paymentCount" :note="'Dari ' . $transactionCount . ' transaksi'" />
    <x-stat-card label="Rata-rata per hari" :value="\App\Support\Format::rupiah($averagePerDay)" :note="'Selama ' . $elapsedDays . ' hari'" />
    <x-stat-card
        label="Hari tertinggi"
        :value="$bestDay ? \App\Support\Format::rupiah($bestDay['total']) : '-'"
        :note="$bestDay ? $bestDay['date']->translatedFormat('l, d F Y') : 'Belum ada pembayaran'"
    />
</div>

{{-- Grafik tren harian --}}
<section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
    <h3 class="font-semibold text-ink-700">Tren pendapatan harian</h3>

    @if ($totalReceived > 0)
        <div class="mt-4 h-64 sm:h-80">
            <canvas id="dailyChart" role="img" aria-label="Grafik pembayaran diterima per hari pada {{ $start->translatedFormat('F Y') }}"></canvas>
        </div>
    @else
        <p class="mt-4 rounded-xl bg-cyan-soft px-4 py-8 text-center text-sm text-ink-700/70">Belum ada pembayaran yang diterima pada bulan ini.</p>
    @endif
</section>

{{-- Metode pembayaran dan ringkasan transaksi --}}
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
        <h3 class="font-semibold text-ink-700">Berdasarkan metode pembayaran</h3>
        <dl class="mt-3 space-y-2 text-sm">
            @foreach ($methodLabels as $method => $label)
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-700/70">{{ $label }}</dt>
                    <dd class="font-semibold text-ink-700">{{ \App\Support\Format::rupiah($byMethod[$method] ?? 0) }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
        <h3 class="font-semibold text-ink-700">Ringkasan transaksi bulan ini</h3>
        <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-ink-700/70">Transaksi dicatat</dt>
                <dd class="font-semibold text-ink-700">{{ $billed->count }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-ink-700/70">Nilai tagihan</dt>
                <dd class="font-semibold text-ink-700">{{ \App\Support\Format::rupiah($billed->total) }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-ink-700/70">Belum lunas</dt>
                <dd class="font-semibold text-ink-700">{{ $billed->unpaid_count }} transaksi</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-ink-700/70">Sisa tagihan</dt>
                <dd @class(['font-semibold', 'text-red-600' => $billed->outstanding > 0, 'text-ink-700' => $billed->outstanding <= 0])>
                    {{ \App\Support\Format::rupiah($billed->outstanding) }}
                </dd>
            </div>
        </dl>
        <p class="mt-3 text-xs text-ink-700/60">
            Bagian ini menghitung transaksi yang dicatat pada bulan ini. Uang yang diterima bulan ini bisa berasal dari transaksi bulan sebelumnya, sehingga angkanya tidak selalu sama dengan nilai tagihan.
        </p>
    </section>
</div>

{{-- Rincian per tanggal --}}
<section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <h3 class="px-5 py-4 font-semibold text-ink-700">Rincian per tanggal</h3>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[28rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Tanggal</th>
                    <th class="px-5 py-2.5 text-right font-medium">Jumlah pembayaran</th>
                    <th class="px-5 py-2.5 text-right font-medium">Total diterima</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @foreach ($days as $day)
                    <tr @class(['text-ink-700/50' => $day['total'] === 0])>
                        <td class="px-5 py-2.5">
                            <a href="{{ route('reports.daily', ['date' => $day['date']->toDateString()]) }}" class="font-medium text-brand-600 hover:text-ink-700">
                                {{ $day['date']->translatedFormat('l, d F Y') }}
                            </a>
                        </td>
                        <td class="px-5 py-2.5 text-right">{{ $day['count'] }}</td>
                        <td class="px-5 py-2.5 text-right font-medium">{{ \App\Support\Format::rupiah($day['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-brand-100 bg-cyan-soft">
                <tr>
                    <td class="px-5 py-3 font-semibold text-ink-700">Total</td>
                    <td class="px-5 py-3 text-right font-semibold text-ink-700">{{ $paymentCount }}</td>
                    <td class="px-5 py-3 text-right font-bold text-ink-700">{{ \App\Support\Format::rupiah($totalReceived) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>
@endsection

@if ($totalReceived > 0)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        // Grafik tren pembayaran diterima per hari
        const chartData = @js($chart);
        const rupiah = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(value);
        const compact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 });

        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Pembayaran diterima',
                    data: chartData.values,
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(6, 182, 212, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
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
                            title: (items) => 'Tanggal ' + items[0].label + ' ' + chartData.monthLabel,
                            label: (context) => ' ' + rupiah(context.parsed.y),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        title: { display: true, text: 'Tanggal' },
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