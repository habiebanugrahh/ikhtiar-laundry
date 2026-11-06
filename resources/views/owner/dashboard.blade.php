@extends('layouts.panel')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('panel')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card
        label="Pendapatan hari ini"
        :value="\App\Support\Format::rupiah($incomeToday)"
        :note="$paymentCountToday . ' pembayaran diterima'"
    />
    <x-stat-card
        label="Pendapatan bulan ini"
        :value="\App\Support\Format::rupiah($incomeMonth)"
        :note="$monthLabel"
    />
    <x-stat-card
        label="Transaksi hari ini"
        :value="$transactionCountToday"
        note="Transaksi dicatat hari ini"
    />
    <x-stat-card
        label="Belum lunas"
        :value="$unpaidCount . ' transaksi'"
        :note="'Sisa tagihan ' . \App\Support\Format::rupiah($unpaidAmount)"
    />
</div>

<div class="mt-6 rounded-2xl border border-dashed border-brand-400/60 bg-white p-10 text-center">
    <p class="font-semibold text-ink-700">Grafik pendapatan</p>
    <p class="mt-1 text-sm text-ink-700/70">Grafik harian dan bulanan akan ditambahkan pada tahap berikutnya.</p>
</div>
@endsection