<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026); 

        $users = User::all();
        $services = Service::active()->get();

        // Pelanggan contoh
        $names = [
            'Siti Aminah', 'Budi Santoso', 'Rina Wulandari', 'Agus Pratama',
            'Dewi Lestari', 'Hendra Saputra', 'Nur Aisyah', 'Fajar Ramadhan',
            'Maya Sari', 'Rudi Hartono', 'Putri Ayu', 'Joko Susilo',
        ];

        $customers = collect($names)->map(fn ($name) => Customer::create([
            'name' => $name,
            'phone' => '0812' . mt_rand(10000000, 99999999),
            'address' => 'Jl. Meranti No. ' . mt_rand(1, 120),
        ]));

        $methods = ['cash', 'cash', 'cash', 'transfer', 'qris'];

        // Transaksi contoh selama 90 hari terakhir
        for ($daysAgo = 89; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo)->startOfDay();
            $count = mt_rand(4, 10);

            for ($i = 0; $i < $count; $i++) {
                $customer = $customers[mt_rand(0, $customers->count() - 1)];
                $user = $users[mt_rand(0, $users->count() - 1)];

                // Pilih 1 sampai 2 layanan berbeda
                $picked = $services->shuffle()->take(mt_rand(1, 2));

                $items = [];
                $subtotal = 0;

                foreach ($picked as $service) {
                    $quantity = $service->unit === Service::UNIT_KG
                        ? mt_rand(20, 150) / 10
                        : mt_rand(1, 3);

                    $lineTotal = (int) round($quantity * $service->price);
                    $subtotal += $lineTotal;

                    $items[] = [
                        'service_id' => $service->id,
                        'service_name' => $service->name,
                        'unit' => $service->unit,
                        'quantity' => $quantity,
                        'unit_price' => $service->price,
                        'subtotal' => $lineTotal,
                    ];
                }

                $isExpress = mt_rand(1, 100) <= 15;
                $expressFee = $isExpress ? Transaction::DEFAULT_EXPRESS_FEE : 0;
                $total = $subtotal + $expressFee;

                // Status pesanan: transaksi lama pasti sudah diambil
                if ($daysAgo > 3) {
                    $orderStatus = Transaction::ORDER_PICKED_UP;
                } else {
                    $orderStatus = [
                        Transaction::ORDER_RECEIVED,
                        Transaction::ORDER_PROCESSING,
                        Transaction::ORDER_DONE,
                        Transaction::ORDER_PICKED_UP,
                    ][mt_rand(0, 3)];
                }

                $transaction = Transaction::create([
                    'code' => Transaction::generateCode($date),
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'transaction_date' => $date->toDateString(),
                    'is_express' => $isExpress,
                    'express_fee' => $expressFee,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'order_status' => $orderStatus,
                    'picked_up_at' => $orderStatus === Transaction::ORDER_PICKED_UP
                        ? $date->copy()->addDays(mt_rand(1, 2))->setTime(16, 0)->min(now())
                        : null,
                ]);

                $transaction->items()->createMany($items);

                // Skenario pembayaran
                $roll = mt_rand(1, 100);

                if ($roll <= 75 || ($roll > 88 && $daysAgo > 7)) {
                    // Lunas pada hari transaksi
                    $this->pay($transaction, $total, $date, $methods, $user);
                } elseif ($roll <= 88) {
                    // Bayar sebagian
                    $first = (int) (round($total * mt_rand(40, 60) / 100 / 1000) * 1000);
                    $first = max(1000, min($first, $total - 1000));

                    $this->pay($transaction, $first, $date, $methods, $user);

                    // Transaksi lama dilunasi beberapa hari kemudian
                    if ($daysAgo > 7) {
                        $later = $date->copy()->addDays(mt_rand(1, 5));
                        $this->pay($transaction, $total - $first, $later, $methods, $user);
                    }
                }
                // Selain itu transaksi belum dibayar sama sekali

                $transaction->refreshPaymentStatus();
            }
        }
    }

    private function pay(Transaction $transaction, int $amount, $date, array $methods, User $user): void
    {
        $paidAt = $date->copy()->setTime(mt_rand(8, 20), mt_rand(0, 59));

        if ($paidAt->greaterThan(now())) {
            $paidAt = now();
        }

        Payment::create([
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'method' => $methods[mt_rand(0, count($methods) - 1)],
            'paid_at' => $paidAt,
        ]);
    }
}