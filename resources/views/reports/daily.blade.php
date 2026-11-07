@extends('layouts.panel')

@section('title', 'Rekap Harian')
@section('page_title', 'Rekap Harian')

@section('panel')
@php
    $inputClass = 'block rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
    $methodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
    $navClass = 'rounded-lg px-3 py-2.5 text-sm font-medium text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500';
@endphp

{{-- Pemilih tanggal --}}
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-ink-700">{{ $date->translatedFormat('l, d F Y') }}</h2>
        <p class="mt-1 text-sm text-ink-700/70">Total uang yang diterima pada tanggal ini, berdasarkan tanggal pembayaran.</p>
    </div>

    <form method="GET" action="{{ route('reports.daily') }}" class="flex flex-wrap items-center gap-2">
        <a href="{{ route('reports.daily', ['date' => $previousDate->toDateString()]) }}" class="{{ $navClass }}" aria-label="Hari sebelumnya">Sebelumnya</a>

        <label for="date" class="sr-only">Pilih tanggal</label>
        <input
            id="date"
            name="date"
            type="date"
            value="{{ $date->toDateString() }}"
            max="{{ today()->toDateString() }}"
            onchange="this.form.submit()"
            class="{{ $inputClass }}"
        >

        @if ($nextDate)
            <a href="{{ route('reports.daily', ['date' => $nextDate->toDateString()]) }}" class="{{ $navClass }}" aria-label="Hari berikutnya">Berikutnya</a>
        @endif

        @unless ($isToday)
            <a href="{{ route('reports.daily') }}" class="{{ $navClass }}">Hari ini</a>
        @endunless

        <noscript>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Tampilkan</button>
        </noscript>
    </form>
</div>

{{-- Ringkasan --}}
<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-stat-card label="Total pembayaran diterima" :value="\App\Support\Format::rupiah($totalReceived)" />
    <x-stat-card label="Jumlah pembayaran" :value="$payments->count()" note="Pembayaran yang tercatat" />
    <x-stat-card label="Jumlah transaksi" :value="$transactionCount" note="Transaksi yang menerima pembayaran" />

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
        <p class="text-sm text-ink-700/70">Berdasarkan metode</p>
        <dl class="mt-2 space-y-1.5 text-sm">
            @foreach ($methodLabels as $method => $label)
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-700/70">{{ $label }}</dt>
                    <dd class="font-semibold text-ink-700">{{ \App\Support\Format::rupiah($byMethod[$method]) }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</div>

<p class="mt-3 text-xs text-ink-700/60">
    Transaksi dicatat pada tanggal ini: {{ $billedCount }} dengan nilai tagihan {{ \App\Support\Format::rupiah($billedTotal) }}.
    Rekap ini menghitung uang yang benar-benar diterima, bukan nilai tagihan, sehingga transaksi yang belum dibayar tidak ikut dihitung.
</p>

{{-- Rincian pembayaran --}}
<section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <h3 class="px-5 py-4 font-semibold text-ink-700">Rincian pembayaran</h3>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Waktu</th>
                    <th class="px-5 py-2.5 font-medium">Nomor transaksi</th>
                    <th class="px-5 py-2.5 font-medium">Pelanggan</th>
                    <th class="px-5 py-2.5 font-medium">Metode</th>
                    <th class="px-5 py-2.5 font-medium">Diterima oleh</th>
                    <th class="px-5 py-2.5 text-right font-medium">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-5 py-3">{{ $payment->paid_at->format('H:i') }}</td>
                        <td class="px-5 py-3 font-medium">
                            <a href="{{ route('transactions.show', $payment->transaction) }}" class="text-brand-600 hover:text-ink-700">{{ $payment->transaction->code }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $payment->transaction->customer->name }}</td>
                        <td class="px-5 py-3">{{ $payment->method_label }}</td>
                        <td class="px-5 py-3">{{ $payment->receiver->name }}</td>
                        <td class="px-5 py-3 text-right font-medium text-ink-700">{{ \App\Support\Format::rupiah($payment->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-ink-700/60">Tidak ada pembayaran yang diterima pada tanggal ini.</td>
                    </tr>
                @endforelse
            </tbody>

            @if ($payments->isNotEmpty())
                <tfoot class="border-t border-brand-100 bg-cyan-soft">
                    <tr>
                        <td colspan="5" class="px-5 py-3 font-semibold text-ink-700">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-ink-700">{{ \App\Support\Format::rupiah($totalReceived) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>
@endsection