@extends('layouts.panel')

@section('title', $transaction->code)
@section('page_title', 'Detail transaksi')

@section('panel')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-ink-700">{{ $transaction->code }}</h2>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <x-payment-badge :status="$transaction->payment_status" />
            <x-order-badge :status="$transaction->order_status" />
            @if ($transaction->is_express)
                <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-medium text-violet-700">Ekspres</span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('transactions.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Data transaksi</a>
        <a href="{{ route('transactions.create') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
            Transaksi baru
        </a>
    </div>
</div>

<div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="space-y-6">

        {{-- Rincian layanan --}}
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
            <h3 class="px-5 py-4 font-semibold text-ink-700">Rincian layanan</h3>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[32rem] text-left text-sm">
                    <thead class="bg-cyan-soft text-ink-700/70">
                        <tr>
                            <th class="px-5 py-2.5 font-medium">Layanan</th>
                            <th class="px-5 py-2.5 text-right font-medium">Kuantitas</th>
                            <th class="px-5 py-2.5 text-right font-medium">Tarif</th>
                            <th class="px-5 py-2.5 text-right font-medium">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-100">
                        @foreach ($transaction->items as $item)
                            <tr>
                                <td class="px-5 py-3 font-medium text-ink-700">{{ $item->service_name }}</td>
                                <td class="px-5 py-3 text-right">{{ \App\Support\Format::quantity($item->quantity) }} {{ $item->unit }}</td>
                                <td class="px-5 py-3 text-right">{{ \App\Support\Format::rupiah($item->unit_price) }}</td>
                                <td class="px-5 py-3 text-right">{{ \App\Support\Format::rupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <dl class="space-y-1.5 border-t border-brand-100 px-5 py-4 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-700/70">Subtotal</dt>
                    <dd>{{ \App\Support\Format::rupiah($transaction->subtotal) }}</dd>
                </div>
                @if ($transaction->is_express)
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-700/70">Biaya ekspres</dt>
                        <dd>{{ \App\Support\Format::rupiah($transaction->express_fee) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3 text-base font-bold text-ink-700">
                    <dt>Total</dt>
                    <dd>{{ \App\Support\Format::rupiah($transaction->total) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-700/70">Sudah dibayar</dt>
                    <dd>{{ \App\Support\Format::rupiah($transaction->paid_amount) }}</dd>
                </div>
                <div class="flex justify-between gap-3 font-semibold">
                    <dt class="text-ink-700/70">Sisa tagihan</dt>
                    <dd @class(['text-red-600' => $transaction->remaining > 0, 'text-emerald-600' => $transaction->remaining === 0])>
                        {{ \App\Support\Format::rupiah($transaction->remaining) }}
                    </dd>
                </div>
            </dl>
        </section>

        {{-- Riwayat pembayaran --}}
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
            <h3 class="px-5 py-4 font-semibold text-ink-700">Riwayat pembayaran</h3>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[32rem] text-left text-sm">
                    <thead class="bg-cyan-soft text-ink-700/70">
                        <tr>
                            <th class="px-5 py-2.5 font-medium">Waktu</th>
                            <th class="px-5 py-2.5 font-medium">Metode</th>
                            <th class="px-5 py-2.5 text-right font-medium">Nominal</th>
                            <th class="px-5 py-2.5 font-medium">Diterima oleh</th>
                            <th class="px-5 py-2.5 font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-100">
                        @forelse ($transaction->payments as $payment)
                            <tr>
                                <td class="px-5 py-3">{{ $payment->paid_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-5 py-3">{{ $payment->method_label }}</td>
                                <td class="px-5 py-3 text-right font-medium text-ink-700">{{ \App\Support\Format::rupiah($payment->amount) }}</td>
                                <td class="px-5 py-3">{{ $payment->receiver->name }}</td>
                                <td class="px-5 py-3 text-ink-700/70">{{ $payment->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-ink-700/60">Belum ada pembayaran untuk transaksi ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Informasi transaksi --}}
    <aside class="h-fit space-y-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-ink-700/70">Pelanggan</dt>
                <dd class="mt-0.5 font-medium">
                    <a href="{{ route('customers.show', $transaction->customer) }}" class="text-brand-600 hover:text-ink-700">{{ $transaction->customer->name }}</a>
                </dd>
                <dd class="text-ink-700/70">{{ $transaction->customer->phone }}</dd>
            </div>
            <div>
                <dt class="text-ink-700/70">Tanggal transaksi</dt>
                <dd class="mt-0.5 font-medium text-ink-700">{{ $transaction->transaction_date->translatedFormat('l, d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-ink-700/70">Dicatat oleh</dt>
                <dd class="mt-0.5 font-medium text-ink-700">{{ $transaction->creator->name }}</dd>
            </div>
            <div>
                <dt class="text-ink-700/70">Catatan pesanan</dt>
                <dd class="mt-0.5 text-ink-700">{{ $transaction->notes ?: '-' }}</dd>
            </div>
        </dl>
    </aside>
</div>
@endsection