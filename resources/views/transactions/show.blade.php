@extends('layouts.panel')

@section('title', $transaction->code)
@section('page_title', 'Detail transaksi')

@section('panel')
@php
    $flow = \App\Models\Transaction::ORDER_FLOW;
    $currentIndex = array_search($transaction->order_status, $flow, true);
    $nextStatus = $transaction->next_order_status;
    $remaining = $transaction->remaining;
    $inputClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
@endphp

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
        <a href="{{ route('transactions.receipt', ['transaction' => $transaction, 'print' => 1]) }}" target="_blank" rel="noopener" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">Cetak nota</a>
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
                    <dd @class(['text-red-600' => $remaining > 0, 'text-emerald-600' => $remaining === 0])>
                        {{ \App\Support\Format::rupiah($remaining) }}
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

    <aside class="h-fit space-y-6">

        {{-- Status pesanan --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
            <h3 class="font-semibold text-ink-700">Status pesanan</h3>

            <ol class="mt-3 space-y-2">
                @foreach ($flow as $index => $step)
                    <li class="flex items-center gap-3 text-sm">
                        <span @class([
                            'flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                            'bg-brand-500 text-white' => $index <= $currentIndex,
                            'bg-slate-100 text-slate-500' => $index > $currentIndex,
                        ])>{{ $index + 1 }}</span>
                        <span @class([
                            'font-semibold text-ink-700' => $index === $currentIndex,
                            'text-ink-700/70' => $index !== $currentIndex,
                        ])>{{ \App\Models\Transaction::ORDER_STATUS_LABELS[$step] }}</span>
                    </li>
                @endforeach
            </ol>

            @if ($transaction->picked_up_at)
                <p class="mt-3 text-xs text-ink-700/60">Diambil pada {{ $transaction->picked_up_at->translatedFormat('d M Y, H:i') }}</p>
            @endif

            @if ($nextStatus)
                <form method="POST" action="{{ route('transactions.status', $transaction) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="order_status" value="{{ $nextStatus }}">
                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                        Tandai {{ \App\Models\Transaction::ORDER_STATUS_LABELS[$nextStatus] }}
                    </button>
                </form>
            @else
                <p class="mt-4 rounded-lg bg-cyan-soft px-3 py-2 text-sm text-ink-700">Pesanan sudah diambil pelanggan.</p>
            @endif

            @if ($transaction->order_status === \App\Models\Transaction::ORDER_PICKED_UP && $remaining > 0)
                <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">Pesanan sudah diambil, tetapi tagihan belum lunas.</p>
            @endif

            @error('order_status')
                <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <details class="mt-4 border-t border-brand-100 pt-3">
                    <summary class="cursor-pointer text-sm font-medium text-brand-600 hover:text-ink-700">Koreksi status</summary>
                    <form method="POST" action="{{ route('transactions.status', $transaction) }}" class="mt-3 flex gap-2">
                        @csrf
                        @method('PATCH')
                        <label for="correct-status" class="sr-only">Status pesanan</label>
                        <select id="correct-status" name="order_status" class="{{ $inputClass }}">
                            @foreach (\App\Models\Transaction::ORDER_STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected($transaction->order_status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100">Simpan</button>
                    </form>
            </details>
        </section>

        {{-- Tambah pembayaran --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
            <h3 class="font-semibold text-ink-700">Pembayaran</h3>

            @if ($remaining > 0)
                <p class="mt-1 text-sm text-ink-700/70">
                    Sisa tagihan <span class="font-semibold text-red-600">{{ \App\Support\Format::rupiah($remaining) }}</span>
                </p>

                <form
                    method="POST"
                    action="{{ route('transactions.payments.store', $transaction) }}"
                    x-data="paymentForm({ method: @js(old('payment_method', 'cash')), input: @js(old('payment_input', '')), remaining: {{ $remaining }} })"
                    class="mt-4 space-y-3"
                    novalidate
                >
                    @csrf

                    <div>
                        <label for="payment_method" class="block text-sm text-ink-700/70">Metode</label>
                        <select id="payment_method" name="payment_method" x-model="method" class="mt-1 {{ $inputClass }}">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_input" class="block text-sm text-ink-700/70" x-text="method === 'cash' ? 'Uang diterima (Rp)' : 'Nominal dibayar (Rp)'"></label>
                        <div class="mt-1 flex gap-2">
                            <input
                                id="payment_input"
                                name="payment_input"
                                type="number"
                                min="1"
                                step="1"
                                inputmode="numeric"
                                x-model="input"
                                class="{{ $inputClass }} @error('payment_input') border-red-400 @enderror"
                            >
                            <button type="button" x-on:click="payFull()" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100">
                                Lunasi
                            </button>
                        </div>
                        @error('payment_input')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <p x-show="method !== 'cash' && inputAmount > remaining" x-cloak class="text-sm text-red-600">
                        Nominal melebihi sisa tagihan.
                    </p>

                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between gap-3" x-show="change > 0" x-cloak>
                            <dt class="text-ink-700/70">Kembalian</dt>
                            <dd class="font-semibold text-ink-700" x-text="rupiah(change)"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-700/70">Sisa setelah pembayaran</dt>
                            <dd class="font-semibold" x-bind:class="left > 0 ? 'text-red-600' : 'text-emerald-600'" x-text="rupiah(left)"></dd>
                        </div>
                    </dl>

                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                        Catat pembayaran
                    </button>
                </form>
            @else
                <p class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">Transaksi ini sudah lunas.</p>
            @endif
        </section>

        {{-- Informasi transaksi --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
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
        </section>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    // Form pembayaran lanjutan: hitung kembalian dan sisa tagihan
    function paymentForm(config) {
        return {
            method: config.method,
            input: config.input,
            remaining: config.remaining,

            get inputAmount() {
                return Math.max(0, parseInt(this.input) || 0);
            },
            get recorded() {
                return Math.min(this.inputAmount, this.remaining);
            },
            get change() {
                return this.method === 'cash' ? Math.max(0, this.inputAmount - this.remaining) : 0;
            },
            get left() {
                return Math.max(0, this.remaining - this.recorded);
            },
            payFull() {
                this.input = this.remaining;
            },
            rupiah(value) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(value || 0);
            },
        };
    }
</script>
@endpush