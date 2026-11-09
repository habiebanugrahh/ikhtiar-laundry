@extends('layouts.panel')

@section('title', 'Data Transaksi')
@section('page_title', 'Data Transaksi')

@section('panel')
@php
    $isOwner = auth()->user()->isOwner();
    $hasFilter = $search !== '' || $paymentStatus || $orderStatus || $from || $to;
    $inputClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="max-w-xl text-sm text-ink-700/70">
        @if ($isOwner)
            Seluruh transaksi Ikhtiar Laundry. Gunakan filter untuk mempersempit hasil.
        @else
            Menampilkan transaksi hari ini dan transaksi yang belum lunas.
        @endif
    </p>

    <a href="{{ route('transactions.create') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
        Transaksi baru
    </a>
</div>

<form method="GET" action="{{ route('transactions.index') }}" class="mt-5 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-brand-100">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2 lg:col-span-1">
            <label for="q" class="block text-sm text-ink-700/70">Cari</label>
            <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Nomor, nama, atau telepon" class="mt-1 {{ $inputClass }}">
        </div>

        <div>
            <label for="payment_status" class="block text-sm text-ink-700/70">Pembayaran</label>
            <select id="payment_status" name="payment_status" class="mt-1 {{ $inputClass }}">
                <option value="">Semua</option>
                <option value="outstanding" @selected($paymentStatus === 'outstanding')>Belum lunas (belum bayar dan sebagian)</option>
                @foreach (\App\Models\Transaction::PAYMENT_STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="order_status" class="block text-sm text-ink-700/70">Pesanan</label>
            <select id="order_status" name="order_status" class="mt-1 {{ $inputClass }}">
                <option value="">Semua</option>
                @foreach (\App\Models\Transaction::ORDER_STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($orderStatus === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if ($isOwner)
            <div class="grid grid-cols-2 gap-3 sm:col-span-2 lg:col-span-4 lg:max-w-md">
                <div>
                    <label for="from" class="block text-sm text-ink-700/70">Dari tanggal</label>
                    <input id="from" name="from" type="date" value="{{ $from }}" class="mt-1 {{ $inputClass }}">
                </div>
                <div>
                    <label for="to" class="block text-sm text-ink-700/70">Sampai tanggal</label>
                    <input id="to" name="to" type="date" value="{{ $to }}" class="mt-1 {{ $inputClass }}">
                </div>
            </div>
        @endif
    </div>

    <div class="mt-4 flex items-center gap-3">
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
            Terapkan
        </button>
        @if ($hasFilter)
            <a href="{{ route('transactions.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Reset</a>
        @endif
    </div>
</form>

{{-- Ringkasan seluruh hasil filter, bukan hanya halaman yang tampil --}}
<div class="mt-5 grid gap-4 sm:grid-cols-3">
    <x-stat-card label="Jumlah transaksi" :value="$summary->count" :note="$hasFilter ? 'Sesuai filter' : null" />
    <x-stat-card label="Total nilai transaksi" :value="\App\Support\Format::rupiah($summary->total)" />
    <x-stat-card label="Sisa tagihan" :value="\App\Support\Format::rupiah($summary->remaining)" note="Yang belum diterima" />
</div>

<section class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[52rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Nomor</th>
                    <th class="px-5 py-2.5 font-medium">Tanggal</th>
                    <th class="px-5 py-2.5 font-medium">Pelanggan</th>
                    <th class="px-5 py-2.5 text-right font-medium">Total</th>
                    <th class="px-5 py-2.5 text-right font-medium">Sisa</th>
                    <th class="px-5 py-2.5 font-medium">Pembayaran</th>
                    <th class="px-5 py-2.5 font-medium">Pesanan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-5 py-3 font-medium">
                            <a href="{{ route('transactions.show', $transaction) }}" class="text-brand-600 hover:text-ink-700">{{ $transaction->code }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $transaction->transaction_date->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3">{{ $transaction->customer->name }}</td>
                        <td class="px-5 py-3 text-right">{{ \App\Support\Format::rupiah($transaction->total) }}</td>
                        <td @class(['px-5 py-3 text-right', 'font-medium text-red-600' => $transaction->remaining > 0])>
                            {{ \App\Support\Format::rupiah($transaction->remaining) }}
                        </td>
                        <td class="px-5 py-3"><x-payment-badge :status="$transaction->payment_status" /></td>
                        <td class="px-5 py-3"><x-order-badge :status="$transaction->order_status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-2"><x-empty-state>                            @if ($hasFilter)
                                Tidak ada transaksi yang cocok dengan filter.
                            @else
                                Belum ada transaksi.
                            @endif
                        </x-empty-state></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($transactions->hasPages())
    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
@endif
@endsection