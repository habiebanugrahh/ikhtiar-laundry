<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Support\Format;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    // Form transaksi baru
    public function create(Request $request): View
    {
        // Pelanggan bisa terisi dari ?customer=ID atau dari input lama saat validasi gagal
        $customerId = old('customer_id', $request->query('customer'));

        return view('transactions.create', [
            'services' => Service::active()->orderBy('name')->get(),
            'customer' => $customerId ? Customer::find($customerId) : null,
        ]);
    }

    // Menyimpan transaksi beserta rincian layanan dan pembayaran awal
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.service_id' => [
                'required',
                'distinct',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'is_express' => ['nullable', 'boolean'],
            'express_fee' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in([
                Payment::METHOD_CASH,
                Payment::METHOD_TRANSFER,
                Payment::METHOD_QRIS,
            ])],
            'payment_input' => ['nullable', 'integer', 'min:0', 'max:100000000'],
        ]);

        // Tarif dan total selalu dihitung ulang di server, bukan dari data browser
        $services = Service::active()
            ->whereIn('id', collect($data['items'])->pluck('service_id'))
            ->get()
            ->keyBy('id');

        $rows = [];
        $subtotal = 0;

        foreach (array_values($data['items']) as $index => $item) {
            $service = $services[(int) $item['service_id']];
            $quantity = round((float) $item['quantity'], 2);

            if ($service->unit === Service::UNIT_PCS && floor($quantity) != $quantity) {
                throw ValidationException::withMessages([
                    "items.$index.quantity" => "Kuantitas {$service->name} harus bilangan bulat karena satuannya pcs.",
                ]);
            }

            $lineTotal = (int) round($quantity * $service->price);
            $subtotal += $lineTotal;

            $rows[] = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'unit' => $service->unit,
                'quantity' => $quantity,
                'unit_price' => $service->price,
                'subtotal' => $lineTotal,
            ];
        }

        $isExpress = $request->boolean('is_express');
        $expressFee = $isExpress ? (int) ($data['express_fee'] ?? Transaction::DEFAULT_EXPRESS_FEE) : 0;
        $total = $subtotal + $expressFee;

        // Pembayaran awal bersifat opsional. Kosong berarti belum dibayar.
        $method = $data['payment_method'];
        $input = (int) ($data['payment_input'] ?? 0);
        $change = 0;
        $paidNow = 0;

        if ($input > 0) {
            if ($method === Payment::METHOD_CASH) {
                // Uang diterima boleh lebih besar dari tagihan, selisihnya menjadi kembalian
                $paidNow = min($input, $total);
                $change = max(0, $input - $total);
            } elseif ($input > $total) {
                throw ValidationException::withMessages([
                    'payment_input' => 'Nominal pembayaran tidak boleh melebihi total tagihan.',
                ]);
            } else {
                $paidNow = $input;
            }
        }

        $date = now();
        $user = $request->user();

        // Ulangi sampai 3 kali jika nomor transaksi bentrok akibat input bersamaan
        for ($attempt = 1; ; $attempt++) {
            try {
                $transaction = DB::transaction(function () use ($data, $rows, $date, $user, $isExpress, $expressFee, $subtotal, $total, $method, $input, $paidNow, $change) {
                    $transaction = Transaction::create([
                        'code' => Transaction::generateCode($date),
                        'customer_id' => $data['customer_id'],
                        'user_id' => $user->id,
                        'transaction_date' => $date->toDateString(),
                        'is_express' => $isExpress,
                        'express_fee' => $expressFee,
                        'subtotal' => $subtotal,
                        'total' => $total,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    $transaction->items()->createMany($rows);

                    if ($paidNow > 0) {
                        Payment::create([
                            'transaction_id' => $transaction->id,
                            'user_id' => $user->id,
                            'amount' => $paidNow,
                            'method' => $method,
                            'paid_at' => $date,
                            'note' => $method === Payment::METHOD_CASH
                                ? 'Uang diterima ' . Format::rupiah($input) . ', kembalian ' . Format::rupiah($change)
                                : null,
                        ]);
                    }

                    $transaction->refreshPaymentStatus();

                    return $transaction;
                });

                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= 3) {
                    throw $e;
                }
            }
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi ' . $transaction->code . ' berhasil dicatat.');
    }

    // Detail transaksi
    public function show(Request $request, Transaction $transaction): View
    {
        $user = $request->user();

        // Kasir hanya boleh melihat transaksi hari ini dan transaksi yang belum lunas
        if ($user->isCashier()
            && ! $transaction->transaction_date->isToday()
            && $transaction->payment_status === Transaction::PAYMENT_PAID) {
            abort(403);
        }

        $transaction->load(['customer', 'creator', 'items', 'payments.receiver']);

        return view('transactions.show', compact('transaction'));
    }
}