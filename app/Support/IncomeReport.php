<?php

namespace App\Support;

use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Data laporan pendapatan (uang yang diterima) untuk satu hari atau satu bulan.
 * Dipakai bersama oleh pratinjau, ekspor PDF, dan ekspor Excel supaya angkanya selalu sama.
 */
class IncomeReport
{
    public const TYPE_DAILY = 'daily';
    public const TYPE_MONTHLY = 'monthly';

    public const METHOD_LABELS = [
        Payment::METHOD_CASH => 'Tunai',
        Payment::METHOD_TRANSFER => 'Transfer',
        Payment::METHOD_QRIS => 'QRIS',
    ];

    public function __construct(
        public readonly string $type,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly Collection $payments,
    ) {
    }

    // Membuat laporan dari parameter permintaan. Nilai yang tidak valid diganti nilai bawaan.
    public static function make(mixed $type, mixed $date = null, mixed $year = null, mixed $month = null): self
    {
        $now = now();

        if ($type === self::TYPE_MONTHLY) {
            $year = (int) ($year ?: $now->year);
            $month = (int) ($month ?: $now->month);

            if ($month < 1 || $month > 12) {
                $month = $now->month;
            }

            if ($year < 2000 || $year > $now->year) {
                $year = $now->year;
            }

            $start = Carbon::create($year, $month, 1)->startOfMonth();

            // Bulan yang akan datang tidak punya data
            if ($start->format('Y-m') > $now->format('Y-m')) {
                $start = $now->copy()->startOfMonth();
            }

            $end = $start->copy()->endOfMonth();
            $type = self::TYPE_MONTHLY;
        } else {
            $start = self::parseDate($date) ?? today();

            if ($start->greaterThan(today())) {
                $start = today();
            }

            $end = $start->copy()->endOfDay();
            $type = self::TYPE_DAILY;
        }

        // Pembayaran dikelompokkan berdasarkan tanggal uang diterima (paid_at)
        $payments = Payment::query()
            ->with(['transaction.customer', 'receiver'])
            ->whereBetween('paid_at', [$start, $end])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        return new self($type, $start, $end, $payments);
    }

    public function isDaily(): bool
    {
        return $this->type === self::TYPE_DAILY;
    }

    public function title(): string
    {
        return $this->isDaily() ? 'Rekap Pendapatan Harian' : 'Rekap Pendapatan Bulanan';
    }

    public function periodLabel(): string
    {
        return $this->isDaily()
            ? $this->start->translatedFormat('l, d F Y')
            : $this->start->translatedFormat('F Y');
    }

    public function total(): int
    {
        return (int) $this->payments->sum('amount');
    }

    public function count(): int
    {
        return $this->payments->count();
    }

    public function transactionCount(): int
    {
        return $this->payments->pluck('transaction_id')->unique()->count();
    }

    // Total per metode pembayaran: [kode => ['label' => ..., 'total' => ...]]
    public function byMethod(): array
    {
        $result = [];

        foreach (self::METHOD_LABELS as $method => $label) {
            $result[$method] = [
                'label' => $label,
                'total' => (int) $this->payments->where('method', $method)->sum('amount'),
            ];
        }

        return $result;
    }

    public function fileName(string $extension): string
    {
        return $this->isDaily()
            ? 'rekap-harian-' . $this->start->format('Y-m-d') . '.' . $extension
            : 'rekap-bulanan-' . $this->start->format('Y-m') . '.' . $extension;
    }

    // Parameter URL untuk membuka kembali laporan yang sama
    public function routeParams(): array
    {
        return $this->isDaily()
            ? ['type' => self::TYPE_DAILY, 'date' => $this->start->toDateString()]
            : ['type' => self::TYPE_MONTHLY, 'year' => $this->start->year, 'month' => $this->start->month];
    }

    private static function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}