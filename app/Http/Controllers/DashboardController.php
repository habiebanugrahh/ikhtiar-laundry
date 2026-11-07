<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PERIODS = [
        '7d' => '7 hari terakhir',
        '30d' => '30 hari terakhir',
        '12m' => '12 bulan terakhir',
    ];

    // Dashboard Owner: ringkasan pendapatan, grafik per periode, dan tagihan yang belum lunas
    public function owner(Request $request): View
    {
        $now = now();

        $period = $request->query('period');
        $period = array_key_exists((string) $period, self::PERIODS) ? $period : '30d';

        $todayPayments = Payment::whereDate('paid_at', $now->toDateString());

        return view('owner.dashboard', [
            'incomeToday' => (int) (clone $todayPayments)->sum('amount'),
            'paymentCountToday' => (clone $todayPayments)->count(),
            'incomeYesterday' => (int) Payment::whereDate('paid_at', $now->copy()->subDay()->toDateString())->sum('amount'),
            'incomeMonth' => (int) Payment::whereYear('paid_at', $now->year)->whereMonth('paid_at', $now->month)->sum('amount'),
            'monthLabel' => $now->translatedFormat('F Y'),
            'transactionCountToday' => Transaction::whereDate('transaction_date', $now->toDateString())->count(),
            'unpaid' => $this->unpaidSummary(),
            'unpaidTransactions' => $this->unpaidList(),
            'periods' => self::PERIODS,
            'period' => $period,
            'chart' => $this->incomeSeries($period),
        ]);
    }

    // Dashboard Kasir: ringkasan operasional, tagihan yang perlu ditagih, dan transaksi terbaru
    public function cashier(): View
    {
        return view('cashier.dashboard', [
            'transactionCountToday' => Transaction::whereDate('transaction_date', now()->toDateString())->count(),
            'waitingPaymentCount' => Transaction::where('payment_status', '!=', Transaction::PAYMENT_PAID)->count(),
            'inProgressCount' => Transaction::whereIn('order_status', [
                Transaction::ORDER_RECEIVED,
                Transaction::ORDER_PROCESSING,
            ])->count(),
            'unpaidTransactions' => $this->unpaidList(),
            'latestTransactions' => Transaction::with('customer')->latest('id')->limit(8)->get(),
        ]);
    }

    // Jumlah dan sisa tagihan dari transaksi yang belum lunas
    private function unpaidSummary(): object
    {
        return Transaction::query()
            ->where('payment_status', '!=', Transaction::PAYMENT_PAID)
            ->selectRaw("COUNT(*) as count,
                COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END), 0) as unpaid_count,
                COALESCE(SUM(CASE WHEN payment_status = 'partial' THEN 1 ELSE 0 END), 0) as partial_count,
                COALESCE(SUM(total - paid_amount), 0) as remaining")
            ->first();
    }

    // Transaksi belum lunas yang paling lama menunggu
    private function unpaidList()
    {
        return Transaction::query()
            ->with('customer')
            ->where('payment_status', '!=', Transaction::PAYMENT_PAID)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->limit(5)
            ->get();
    }

    // Data grafik pendapatan (uang yang diterima) menurut periode terpilih
    private function incomeSeries(string $period): array
    {
        $labels = [];
        $titles = [];
        $values = [];

        if ($period === '12m') {
            $start = today()->startOfMonth()->subMonthsNoOverflow(11);

            $rows = Payment::query()
                ->where('paid_at', '>=', $start)
                ->selectRaw('YEAR(paid_at) as y, MONTH(paid_at) as m, SUM(amount) as total')
                ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
                ->get()
                ->mapWithKeys(fn ($row) => [sprintf('%04d-%02d', $row->y, $row->m) => (int) $row->total]);

            for ($i = 0; $i < 12; $i++) {
                $month = $start->copy()->addMonthsNoOverflow($i);

                $labels[] = $month->translatedFormat('M y');
                $titles[] = $month->translatedFormat('F Y');
                $values[] = $rows[$month->format('Y-m')] ?? 0;
            }

            $type = 'bar';
        } else {
            $count = $period === '7d' ? 7 : 30;
            $start = today()->subDays($count - 1);

            $rows = Payment::query()
                ->where('paid_at', '>=', $start)
                ->selectRaw('DATE(paid_at) as day, SUM(amount) as total')
                ->groupByRaw('DATE(paid_at)')
                ->pluck('total', 'day');

            for ($i = 0; $i < $count; $i++) {
                $date = $start->copy()->addDays($i);

                $labels[] = $date->translatedFormat('j M');
                $titles[] = $date->translatedFormat('l, d F Y');
                $values[] = (int) ($rows[$date->toDateString()] ?? 0);
            }

            $type = 'line';
        }

        return [
            'type' => $type,
            'labels' => $labels,
            'titles' => $titles,
            'values' => $values,
            'total' => array_sum($values),
            'periodLabel' => self::PERIODS[$period],
        ];
    }
}