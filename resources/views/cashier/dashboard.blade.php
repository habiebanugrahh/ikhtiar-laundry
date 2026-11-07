@extends('layouts.panel')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('panel')
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-ink-700/70">Halo, {{ auth()->user()->name }}. Siap mencatat pesanan hari ini?</p>

    <a
        href="{{ route('transactions.create') }}"
        class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
    >
        Transaksi baru
    </a>
</div>

<div class="mt-5 grid gap-4 sm:grid-cols-3">
    <x-stat-card label="Transaksi hari ini" :value="$transactionCountToday" note="Transaksi dicatat hari ini" />
    <x-stat-card label="Menunggu pembayaran" :value="$waitingPaymentCount" note="Belum bayar atau baru dibayar sebagian" />
    <x-stat-card label="Pesanan dalam proses" :value="$inProgressCount" note="Berstatus diterima atau diproses" />
</div>

<section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <h2 class="px-5 py-4 font-semibold text-ink-700">Transaksi terbaru</h2>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Nomor</th>
                    <th class="px-5 py-2.5 font-medium">Pelanggan</th>
                    <th class="px-5 py-2.5 text-right font-medium">Total</th>
                    <th class="px-5 py-2.5 font-medium">Pembayaran</th>
                    <th class="px-5 py-2.5 font-medium">Pesanan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($latestTransactions as $transaction)
                    <tr>
                        <td class="px-5 py-3 font-medium text-ink-700">
                            @if ($transaction->isVisibleTo(auth()->user()))
                                <a href="{{ route('transactions.show', $transaction) }}" class="text-brand-600 hover:text-ink-700">{{ $transaction->code }}</a>
                            @else
                                {{ $transaction->code }}
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ $transaction->customer->name }}</td>
                        <td class="px-5 py-3 text-right">{{ \App\Support\Format::rupiah($transaction->total) }}</td>
                        <td class="px-5 py-3"><x-payment-badge :status="$transaction->payment_status" /></td>
                        <td class="px-5 py-3"><x-order-badge :status="$transaction->order_status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-ink-700/60">
                            Belum ada transaksi. Mulai dengan menekan tombol Transaksi baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection