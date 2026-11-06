@extends('layouts.panel')

@section('title', $customer->name)
@section('page_title', 'Detail pelanggan')

@section('panel')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-ink-700">{{ $customer->name }}</h2>
        <p class="mt-1 text-sm text-ink-700/70">{{ $customer->phone }}</p>
        <p class="mt-0.5 max-w-xl text-sm text-ink-700/70">{{ $customer->address ?: 'Alamat belum diisi.' }}</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('customers.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Kembali</a>
        <a href="{{ route('transactions.create', ['customer' => $customer->id]) }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100">Transaksi baru</a>
        <a href="{{ route('customers.edit', $customer) }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
            Ubah data
        </a>
    </div>
</div>

<div class="mt-5 grid gap-4 sm:grid-cols-3">
    <x-stat-card label="Jumlah transaksi" :value="$transactionCount" />
    <x-stat-card label="Total transaksi" :value="\App\Support\Format::rupiah($totalSpent)" />
    <x-stat-card label="Sisa tagihan" :value="\App\Support\Format::rupiah($remaining)" note="Dari transaksi yang belum lunas" />
</div>

<section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <h3 class="px-5 py-4 font-semibold text-ink-700">Riwayat transaksi</h3>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Nomor</th>
                    <th class="px-5 py-2.5 font-medium">Tanggal</th>
                    <th class="px-5 py-2.5 text-right font-medium">Total</th>
                    <th class="px-5 py-2.5 font-medium">Pembayaran</th>
                    <th class="px-5 py-2.5 font-medium">Pesanan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-5 py-3 font-medium text-ink-700">{{ $transaction->code }}</td>
                        <td class="px-5 py-3">{{ $transaction->transaction_date->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">{{ \App\Support\Format::rupiah($transaction->total) }}</td>
                        <td class="px-5 py-3"><x-payment-badge :status="$transaction->payment_status" /></td>
                        <td class="px-5 py-3"><x-order-badge :status="$transaction->order_status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-ink-700/60">Pelanggan ini belum punya transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($transactionCount > $transactions->count())
        <p class="border-t border-brand-100 px-5 py-3 text-xs text-ink-700/60">
            Menampilkan {{ $transactions->count() }} transaksi terbaru dari {{ $transactionCount }} transaksi.
        </p>
    @endif
</section>
@endsection