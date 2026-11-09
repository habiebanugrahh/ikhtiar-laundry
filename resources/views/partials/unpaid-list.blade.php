{{-- Daftar transaksi yang belum lunas, paling lama menunggu di atas. Butuh variabel $transactions. --}}
<section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <div class="flex items-center justify-between gap-3 px-5 py-4">
        <h2 class="font-semibold text-ink-700">Perlu ditagih</h2>
        <a href="{{ route('transactions.index', ['payment_status' => 'outstanding']) }}" class="text-sm font-medium text-brand-600 hover:text-ink-700">Lihat semua</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Nomor</th>
                    <th class="px-5 py-2.5 font-medium">Pelanggan</th>
                    <th class="px-5 py-2.5 font-medium">Tanggal</th>
                    <th class="px-5 py-2.5 text-right font-medium">Sisa tagihan</th>
                    <th class="px-5 py-2.5 font-medium">Pembayaran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-5 py-3 font-medium">
                            <a href="{{ route('transactions.show', $transaction) }}" class="text-brand-600 hover:text-ink-700">{{ $transaction->code }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $transaction->customer->name }}</td>
                        <td class="px-5 py-3">{{ $transaction->transaction_date->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3 text-right font-medium text-red-600">{{ \App\Support\Format::rupiah($transaction->remaining) }}</td>
                        <td class="px-5 py-3"><x-payment-badge :status="$transaction->payment_status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-2"><x-empty-state title="Tidak ada tagihan tertunda">Semua transaksi sudah lunas.</x-empty-state></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>