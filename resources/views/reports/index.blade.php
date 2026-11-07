@extends('layouts.panel')

@section('title', 'Laporan')
@section('page_title', 'Laporan')

@section('panel')
@php
    $inputClass = 'mt-1 block rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
    $isDaily = $report->isDaily();
    $hasData = $report->count() > 0;
    $downloadButton = 'inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2';
@endphp

<p class="max-w-2xl text-sm text-ink-700/70">
    Pilih jenis dan periode laporan, tinjau hasilnya di bawah, lalu unduh dalam format PDF atau Excel.
    Isi laporan sama dengan halaman rekap, yaitu uang yang diterima berdasarkan tanggal pembayaran.
</p>

{{-- Pemilih jenis dan periode --}}
<form method="GET" action="{{ route('reports.index') }}" class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label for="type" class="block text-sm text-ink-700/70">Jenis laporan</label>
            <select id="type" name="type" class="{{ $inputClass }}">
                <option value="daily" @selected($isDaily)>Rekap harian</option>
                <option value="monthly" @selected(! $isDaily)>Rekap bulanan</option>
            </select>
        </div>

        <div data-group="daily" @class(['hidden' => ! $isDaily])>
            <label for="date" class="block text-sm text-ink-700/70">Tanggal</label>
            <input id="date" name="date" type="date" value="{{ $report->start->toDateString() }}" max="{{ today()->toDateString() }}" class="{{ $inputClass }}">
        </div>

        <div data-group="monthly" @class(['hidden' => $isDaily])>
            <label for="month" class="block text-sm text-ink-700/70">Bulan</label>
            <select id="month" name="month" class="{{ $inputClass }}">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($report->start->month === $m)>{{ \Illuminate\Support\Carbon::create(2000, $m, 1)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>

        <div data-group="monthly" @class(['hidden' => $isDaily])>
            <label for="year" class="block text-sm text-ink-700/70">Tahun</label>
            <select id="year" name="year" class="{{ $inputClass }}">
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected($report->start->year === $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
            Tampilkan
        </button>
    </div>
</form>

{{-- Pratinjau --}}
<section class="mt-6 rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-brand-100 px-5 py-4">
        <div>
            <h2 class="font-semibold text-ink-700">{{ $report->title() }}</h2>
            <p class="text-sm text-ink-700/70">Periode: {{ $report->periodLabel() }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($hasData)
                <a href="{{ route('reports.export', ['format' => 'pdf'] + $report->routeParams()) }}" class="{{ $downloadButton }} bg-brand-600 text-white hover:bg-ink-700">Unduh PDF</a>
                <a href="{{ route('reports.export', ['format' => 'xlsx'] + $report->routeParams()) }}" class="{{ $downloadButton }} text-brand-600 ring-1 ring-brand-400/60 hover:bg-brand-100">Unduh Excel</a>
            @else
                <span aria-disabled="true" class="{{ $downloadButton }} cursor-not-allowed bg-slate-100 text-slate-400">Unduh PDF</span>
                <span aria-disabled="true" class="{{ $downloadButton }} cursor-not-allowed bg-slate-100 text-slate-400">Unduh Excel</span>
            @endif
        </div>
    </div>

    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total pembayaran diterima" :value="\App\Support\Format::rupiah($report->total())" />
        <x-stat-card label="Jumlah pembayaran" :value="$report->count()" />
        <x-stat-card label="Jumlah transaksi" :value="$report->transactionCount()" />

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
            <p class="text-sm text-ink-700/70">Berdasarkan metode</p>
            <dl class="mt-2 space-y-1.5 text-sm">
                @foreach ($report->byMethod() as $method)
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-700/70">{{ $method['label'] }}</dt>
                        <dd class="font-semibold text-ink-700">{{ \App\Support\Format::rupiah($method['total']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">No</th>
                    <th class="px-5 py-2.5 font-medium">Nomor transaksi</th>
                    <th class="px-5 py-2.5 font-medium">Pelanggan</th>
                    <th class="px-5 py-2.5 font-medium">Tanggal pembayaran</th>
                    <th class="px-5 py-2.5 font-medium">Metode</th>
                    <th class="px-5 py-2.5 text-right font-medium">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($previewRows as $payment)
                    <tr>
                        <td class="px-5 py-3 text-ink-700/60">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3 font-medium">
                            <a href="{{ route('transactions.show', $payment->transaction) }}" class="text-brand-600 hover:text-ink-700">{{ $payment->transaction->code }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $payment->transaction->customer->name }}</td>
                        <td class="px-5 py-3">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3">{{ $payment->method_label }}</td>
                        <td class="px-5 py-3 text-right font-medium text-ink-700">{{ \App\Support\Format::rupiah($payment->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-ink-700/60">Tidak ada pembayaran pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>

            @if ($hasData)
                <tfoot class="border-t border-brand-100 bg-cyan-soft">
                    <tr>
                        <td colspan="5" class="px-5 py-3 font-semibold text-ink-700">Total periode</td>
                        <td class="px-5 py-3 text-right font-bold text-ink-700">{{ \App\Support\Format::rupiah($report->total()) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if ($report->count() > $previewRows->count())
        <p class="border-t border-brand-100 px-5 py-3 text-xs text-ink-700/60">
            Pratinjau menampilkan {{ $previewRows->count() }} dari {{ $report->count() }} pembayaran. File PDF dan Excel memuat semuanya.
        </p>
    @endif
</section>
@endsection

@push('scripts')
<script>
    // Tampilkan isian tanggal untuk laporan harian, atau bulan dan tahun untuk laporan bulanan
    const typeSelect = document.getElementById('type');

    function toggleGroups() {
        document.querySelectorAll('[data-group]').forEach((group) => {
            group.classList.toggle('hidden', group.dataset.group !== typeSelect.value);
        });
    }

    typeSelect.addEventListener('change', toggleGroups);
</script>
@endpush