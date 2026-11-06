<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Dashboard Owner: ringkasan pendapatan dan pembayaran belum lunas
    public function owner(): View
    {
        $now = now();

        $todayPayments = Payment::whereDate('paid_at', $now->toDateString());
        $monthPayments = Payment::whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month);

        $unpaid = Transaction::where('payment_status', '!=', Transaction::PAYMENT_PAID);

        return view('owner.dashboard', [
            'incomeToday' => (clone $todayPayments)->sum('amount'),
            'paymentCountToday' => (clone $todayPayments)->count(),
            'incomeMonth' => $monthPayments->sum('amount'),
            'monthLabel' => $now->translatedFormat('F Y'),
            'transactionCountToday' => Transaction::whereDate('transaction_date', $now->toDateString())->count(),
            'unpaidCount' => (clone $unpaid)->count(),
            'unpaidAmount' => (clone $unpaid)->sum(DB::raw('total - paid_amount')),
        ]);
    }

    // Dashboard Kasir: ringkasan operasional dan transaksi terbaru
    public function cashier(): View
    {
        return view('cashier.dashboard', [
            'transactionCountToday' => Transaction::whereDate('transaction_date', now()->toDateString())->count(),
            'waitingPaymentCount' => Transaction::where('payment_status', '!=', Transaction::PAYMENT_PAID)->count(),
            'inProgressCount' => Transaction::whereIn('order_status', [
                Transaction::ORDER_RECEIVED,
                Transaction::ORDER_PROCESSING,
            ])->count(),
            'latestTransactions' => Transaction::with('customer')->latest('id')->limit(8)->get(),
        ]);
    }
}