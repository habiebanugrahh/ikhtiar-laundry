<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    // Rekap pendapatan harian: uang yang diterima pada tanggal terpilih
    public function daily(Request $request): View
    {
        $date = $this->parseDate($request->query('date')) ?? today();

        // Tanggal yang akan datang tidak punya pembayaran, jadi dibatasi sampai hari ini
        if ($date->greaterThan(today())) {
            $date = today();
        }

        // Pembayaran dikelompokkan berdasarkan tanggal uang diterima (paid_at)
        $payments = Payment::query()
            ->with(['transaction.customer', 'receiver'])
            ->whereDate('paid_at', $date->toDateString())
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        $byMethod = collect([
            Payment::METHOD_CASH,
            Payment::METHOD_TRANSFER,
            Payment::METHOD_QRIS,
        ])->mapWithKeys(fn ($method) => [$method => (int) $payments->where('method', $method)->sum('amount')]);

        // Pembanding: transaksi yang dicatat pada tanggal yang sama (nilai tagihan, bukan uang masuk)
        $billed = Transaction::query()
            ->whereDate('transaction_date', $date->toDateString())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        return view('reports.daily', [
            'date' => $date,
            'isToday' => $date->isToday(),
            'previousDate' => $date->copy()->subDay(),
            'nextDate' => $date->isToday() ? null : $date->copy()->addDay(),
            'payments' => $payments,
            'totalReceived' => (int) $payments->sum('amount'),
            'transactionCount' => $payments->pluck('transaction_id')->unique()->count(),
            'byMethod' => $byMethod,
            'billedCount' => (int) $billed->count,
            'billedTotal' => (int) $billed->total,
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
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