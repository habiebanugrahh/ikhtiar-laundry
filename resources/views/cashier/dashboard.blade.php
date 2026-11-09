@extends('layouts.panel')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('panel')
<x-laundry.hero :title="'Halo, ' . auth()->user()->name" subtitle="Siap mencatat pesanan hari ini?">
    <a href="{{ route('transactions.create') }}" class="inline-flex rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:bg-brand-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-ink-700">
        Transaksi baru
    </a>
</x-laundry.hero>

<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <x-stat-card label="Transaksi hari ini" :value="$transactionCountToday" note="Transaksi dicatat hari ini" :href="route('transactions.index')" />
    <x-stat-card label="Menunggu pembayaran" :value="$waitingPaymentCount" note="Belum bayar atau baru dibayar sebagian" :href="route('transactions.index', ['payment_status' => 'outstanding'])" />
    <x-stat-card label="Pesanan dalam proses" :value="$inProgressCount" note="Berstatus diterima atau diproses" />
</div>

<div class="mt-6">
    @include('partials.unpaid-list', ['transactions' => $unpaidTransactions])
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
                        <td colspan="5" class="px-5 py-2"><x-empty-state>                            Belum ada transaksi. Mulai dengan menekan tombol Transaksi baru.
                        </x-empty-state></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection