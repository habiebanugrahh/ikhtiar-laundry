<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyLaundryData extends Command
{
    protected $signature = 'laundry:verify';

    protected $description = 'Memeriksa konsistensi data transaksi, pembayaran, dan total rekap';

    public function handle(): int
    {
        // Setiap pemeriksaan menghitung jumlah baris yang datanya tidak konsisten (harus 0)
        $checks = [
            'Total transaksi = subtotal + biaya ekspres' =>
                'SELECT COUNT(*) FROM transactions WHERE total <> subtotal + express_fee',

            'Subtotal transaksi = jumlah subtotal rincian layanan' =>
                'SELECT COUNT(*) FROM transactions t
                 LEFT JOIN (SELECT transaction_id, SUM(subtotal) AS s FROM transaction_items GROUP BY transaction_id) i
                    ON i.transaction_id = t.id
                 WHERE t.subtotal <> COALESCE(i.s, 0)',

            'Subtotal rincian = kuantitas x tarif (toleransi pembulatan)' =>
                'SELECT COUNT(*) FROM transaction_items WHERE ABS(subtotal - quantity * unit_price) > 0.5001',

            'Akumulasi dibayar = jumlah pembayaran tercatat' =>
                'SELECT COUNT(*) FROM transactions t
                 LEFT JOIN (SELECT transaction_id, SUM(amount) AS p FROM payments GROUP BY transaction_id) p
                    ON p.transaction_id = t.id
                 WHERE t.paid_amount <> COALESCE(p.p, 0)',

            'Tidak ada transaksi yang dibayar melebihi total' =>
                'SELECT COUNT(*) FROM transactions WHERE paid_amount > total',

            'Status pembayaran sesuai nominal yang dibayar' =>
                "SELECT COUNT(*) FROM transactions
                 WHERE (payment_status = 'paid' AND paid_amount < total)
                    OR (payment_status = 'unpaid' AND paid_amount > 0)
                    OR (payment_status = 'partial' AND (paid_amount = 0 OR paid_amount >= total))",
        ];

        $rows = [];
        $failed = false;

        foreach ($checks as $label => $sql) {
            $row = DB::selectOne($sql);
            $problems = (int) array_values((array) $row)[0];

            $rows[] = [$label, $problems, $problems === 0 ? 'OK' : 'GAGAL'];
            $failed = $failed || $problems > 0;
        }

        // Seluruh uang diterima di tabel payments harus sama dengan akumulasi di tabel transactions
        $paymentsTotal = (int) Payment::sum('amount');
        $accumulatedTotal = (int) Transaction::sum('paid_amount');
        $same = $paymentsTotal === $accumulatedTotal;

        $rows[] = [
            'Total payments = total paid_amount pada transactions',
            $same ? 0 : abs($paymentsTotal - $accumulatedTotal),
            $same ? 'OK' : 'GAGAL',
        ];
        $failed = $failed || ! $same;

        $this->table(['Pemeriksaan', 'Baris atau selisih bermasalah', 'Hasil'], $rows);

        $this->newLine();
        $this->line('Jumlah transaksi   : ' . Transaction::count());
        $this->line('Jumlah pembayaran  : ' . Payment::count());
        $this->line('Total uang diterima: Rp' . number_format($paymentsTotal, 0, ',', '.'));
        $this->newLine();

        if ($failed) {
            $this->error('Ada data yang tidak konsisten. Periksa baris bertanda GAGAL.');

            return self::FAILURE;
        }

        $this->info('Semua pemeriksaan lolos. Data transaksi, pembayaran, dan rekap konsisten.');

        return self::SUCCESS;
    }
}