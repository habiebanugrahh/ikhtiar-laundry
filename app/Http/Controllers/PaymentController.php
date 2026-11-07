<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    // Mencatat pembayaran tambahan (cicilan atau pelunasan) pada transaksi yang sudah ada
    public function store(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        abort_unless($transaction->isViewableBy($user), 403);

        $data = $request->validate([
            'payment_method' => ['required', Rule::in([
                Payment::METHOD_CASH,
                Payment::METHOD_TRANSFER,
                Payment::METHOD_QRIS,
            ])],
            'payment_input' => ['required', 'integer', 'min:1', 'max:100000000'],
        ]);

        DB::transaction(function () use ($transaction, $data, $user) {
            // Kunci baris transaksi agar dua pembayaran bersamaan tidak melebihi tagihan
            $locked = Transaction::lockForUpdate()->findOrFail($transaction->id);
            $remaining = max(0, $locked->total - $locked->paid_amount);

            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'payment_input' => 'Transaksi ini sudah lunas.',
                ]);
            }

            $method = $data['payment_method'];
            $input = (int) $data['payment_input'];
            $change = 0;

            if ($method === Payment::METHOD_CASH) {
                // Uang diterima boleh lebih besar dari sisa tagihan, selisihnya menjadi kembalian
                $amount = min($input, $remaining);
                $change = max(0, $input - $remaining);
            } elseif ($input > $remaining) {
                throw ValidationException::withMessages([
                    'payment_input' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan ' . Format::rupiah($remaining) . '.',
                ]);
            } else {
                $amount = $input;
            }

            Payment::create([
                'transaction_id' => $locked->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => $method,
                'paid_at' => now(),
                'note' => $method === Payment::METHOD_CASH
                    ? 'Uang diterima ' . Format::rupiah($input) . ', kembalian ' . Format::rupiah($change)
                    : null,
            ]);

            $locked->refreshPaymentStatus();
        });

        $transaction->refresh();

        $message = $transaction->payment_status === Transaction::PAYMENT_PAID
            ? 'Pembayaran dicatat. Transaksi sekarang lunas.'
            : 'Pembayaran dicatat. Sisa tagihan ' . Format::rupiah($transaction->remaining) . '.';

        // Kasir tidak boleh membuka transaksi lama yang sudah lunas, jadi diarahkan ke daftar
        $target = $transaction->isViewableBy($user)
            ? route('transactions.show', $transaction)
            : route('transactions.index');

        return redirect($target)->with('success', $message);
    }
}