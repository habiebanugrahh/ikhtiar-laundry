<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota {{ $transaction->code }} | Ikhtiar Laundry</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        /* Ukuran kertas A4 dengan margin cetak 15 mm */
        @page { size: A4; margin: 15mm; }

        /* Pertahankan warna latar (misalnya header tabel) saat dicetak */
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body { font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased print:bg-white">

@php
    $isPaid = $transaction->payment_status === \App\Models\Transaction::PAYMENT_PAID;
    $remaining = $transaction->remaining;
@endphp

{{-- Toolbar, tidak ikut tercetak --}}
<div class="mx-auto flex max-w-[210mm] items-center justify-between gap-3 px-4 py-4 print:hidden">
    <a href="{{ route('transactions.show', $transaction) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
        Kembali ke transaksi
    </a>
    <button type="button" onclick="window.print()" class="rounded-lg bg-cyan-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2">
        Cetak nota
    </button>
</div>

{{-- Lembar nota --}}
<main class="mx-auto max-w-[210mm] bg-white p-8 shadow-sm ring-1 ring-slate-200 sm:p-12 print:max-w-none print:p-0 print:shadow-none print:ring-0">

    {{-- Kop --}}
    <header class="flex flex-wrap items-start justify-between gap-6 border-b-2 border-cyan-600 pb-5">
        <div class="flex items-start gap-3">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-xl font-bold text-white">I</div>
            <div>
                <h1 class="text-2xl font-bold text-sky-900">Ikhtiar Laundry</h1>
                <p class="text-sm text-slate-600">Jasa pencucian dan perawatan pakaian</p>
                <p class="text-sm text-slate-600">Jalan Meranti No. 68</p>
            </div>
        </div>

        <div class="text-right">
            <p class="text-sm font-medium text-slate-500">Nota transaksi</p>
            <p class="text-xl font-bold text-sky-900">{{ $transaction->code }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ $transaction->transaction_date->translatedFormat('d F Y') }}, {{ $transaction->created_at->format('H:i') }}</p>
        </div>
    </header>

    {{-- Informasi pelanggan dan transaksi --}}
    <section class="mt-6 grid gap-6 text-sm sm:grid-cols-2">
        <div>
            <h2 class="font-semibold text-sky-900">Pelanggan</h2>
            <p class="mt-1 font-medium">{{ $transaction->customer->name }}</p>
            <p class="text-slate-600">{{ $transaction->customer->phone }}</p>
            @if ($transaction->customer->address)
                <p class="text-slate-600">{{ $transaction->customer->address }}</p>
            @endif
        </div>

        <div>
            <h2 class="font-semibold text-sky-900">Rincian transaksi</h2>
            <dl class="mt-1 space-y-0.5">
                <div class="flex gap-2"><dt class="w-28 text-slate-600">Layanan</dt><dd class="font-medium">{{ $transaction->is_express ? 'Ekspres' : 'Reguler' }}</dd></div>
                <div class="flex gap-2"><dt class="w-28 text-slate-600">Dicatat oleh</dt><dd class="font-medium">{{ $transaction->creator->name }}</dd></div>
            </dl>
        </div>
    </section>

    {{-- Rincian layanan --}}
    <table class="mt-6 w-full text-left text-sm">
        <thead>
            <tr class="bg-cyan-50 text-sky-900">
                <th class="w-10 px-3 py-2 font-semibold">No</th>
                <th class="px-3 py-2 font-semibold">Layanan</th>
                <th class="px-3 py-2 text-right font-semibold">Kuantitas</th>
                <th class="px-3 py-2 text-right font-semibold">Tarif</th>
                <th class="px-3 py-2 text-right font-semibold">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 border-b border-slate-200">
            @foreach ($transaction->items as $item)
                <tr>
                    <td class="px-3 py-2.5 text-slate-600">{{ $loop->iteration }}</td>
                    <td class="px-3 py-2.5 font-medium">{{ $item->service_name }}</td>
                    <td class="px-3 py-2.5 text-right">{{ \App\Support\Format::quantity($item->quantity) }} {{ $item->unit }}</td>
                    <td class="px-3 py-2.5 text-right">{{ \App\Support\Format::rupiah($item->unit_price) }}</td>
                    <td class="px-3 py-2.5 text-right">{{ \App\Support\Format::rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total dan status pembayaran --}}
    <section class="mt-5 flex flex-wrap items-start justify-between gap-6">
        <div class="pt-1">
            <p @class([
                'inline-block rounded-md border-2 px-4 py-1.5 text-lg font-bold tracking-wide',
                'border-emerald-600 text-emerald-700' => $isPaid,
                'border-red-600 text-red-700' => ! $isPaid,
            ])>
                {{ $isPaid ? 'LUNAS' : 'BELUM LUNAS' }}
            </p>
            @if (! $isPaid)
                <p class="mt-2 text-sm text-slate-600">Sisa tagihan {{ \App\Support\Format::rupiah($remaining) }}</p>
            @endif
        </div>

        <dl class="w-full space-y-1.5 text-sm sm:w-72">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">Subtotal</dt>
                <dd>{{ \App\Support\Format::rupiah($transaction->subtotal) }}</dd>
            </div>
            @if ($transaction->is_express)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-600">Biaya ekspres</dt>
                    <dd>{{ \App\Support\Format::rupiah($transaction->express_fee) }}</dd>
                </div>
            @endif
            <div class="flex justify-between gap-3 border-t border-slate-300 pt-1.5 text-base font-bold text-sky-900">
                <dt>Total</dt>
                <dd>{{ \App\Support\Format::rupiah($transaction->total) }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">Sudah dibayar</dt>
                <dd>{{ \App\Support\Format::rupiah($transaction->paid_amount) }}</dd>
            </div>
            <div class="flex justify-between gap-3 font-semibold">
                <dt class="text-slate-600">Sisa tagihan</dt>
                <dd>{{ \App\Support\Format::rupiah($remaining) }}</dd>
            </div>
        </dl>
    </section>

    {{-- Riwayat pembayaran --}}
    @if ($transaction->payments->isNotEmpty())
        <section class="mt-6 text-sm">
            <h2 class="font-semibold text-sky-900">Pembayaran diterima</h2>
            <table class="mt-2 w-full text-left">
                <thead>
                    <tr class="bg-cyan-50 text-sky-900">
                        <th class="px-3 py-1.5 font-semibold">Tanggal</th>
                        <th class="px-3 py-1.5 font-semibold">Metode</th>
                        <th class="px-3 py-1.5 text-right font-semibold">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 border-b border-slate-200">
                    @foreach ($transaction->payments as $payment)
                        <tr>
                            <td class="px-3 py-1.5">{{ $payment->paid_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-3 py-1.5">{{ $payment->method_label }}</td>
                            <td class="px-3 py-1.5 text-right">{{ \App\Support\Format::rupiah($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Catatan --}}
    @if ($transaction->notes)
        <section class="mt-6 text-sm">
            <h2 class="font-semibold text-sky-900">Catatan pesanan</h2>
            <p class="mt-1 text-slate-700">{{ $transaction->notes }}</p>
        </section>
    @endif

    {{-- Tanda tangan --}}
    <section class="mt-12 grid grid-cols-2 gap-10 text-center text-sm">
        <div>
            <p class="text-slate-600">Penerima,</p>
            <div class="mt-16 border-t border-slate-400 pt-1 font-medium">{{ $transaction->creator->name }}</div>
        </div>
        <div>
            <p class="text-slate-600">Pelanggan,</p>
            <div class="mt-16 border-t border-slate-400 pt-1 font-medium">{{ $transaction->customer->name }}</div>
        </div>
    </section>

    {{-- Penutup --}}
    <footer class="mt-10 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
        <p>Terima kasih telah mempercayakan cucian Anda kepada Ikhtiar Laundry.</p>
        <p class="mt-0.5">Harap bawa nota ini saat mengambil cucian.</p>
    </footer>
</main>

<script>
    // Buka dialog cetak otomatis jika halaman dibuka dengan ?print=1
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.addEventListener('load', () => setTimeout(() => window.print(), 500));
    }
</script>
</body>
</html>