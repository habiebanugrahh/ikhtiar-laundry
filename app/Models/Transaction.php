<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';

    public const ORDER_RECEIVED = 'received';
    public const ORDER_PROCESSING = 'processing';
    public const ORDER_DONE = 'done';
    public const ORDER_PICKED_UP = 'picked_up';

    public const DEFAULT_EXPRESS_FEE = 5000;

    protected $fillable = [
        'code',
        'customer_id',
        'user_id',
        'transaction_date',
        'is_express',
        'express_fee',
        'subtotal',
        'total',
        'paid_amount',
        'payment_status',
        'order_status',
        'picked_up_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'is_express' => 'boolean',
            'picked_up_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Sisa tagihan yang belum dibayar
    public function getRemainingAttribute(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    // Membuat nomor transaksi unik per hari
    public static function generateCode(CarbonInterface $date): string
    {
        $prefix = 'IKH-' . $date->format('Ymd') . '-';

        $lastCode = static::where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $next = $lastCode ? ((int) Str::afterLast($lastCode, '-')) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    // Menghitung ulang akumulasi pembayaran dan status pembayaran dari tabel payments
    public function refreshPaymentStatus(): void
    {
        $paid = (int) $this->payments()->sum('amount');

        $status = match (true) {
            $paid <= 0 => self::PAYMENT_UNPAID,
            $paid >= $this->total => self::PAYMENT_PAID,
            default => self::PAYMENT_PARTIAL,
        };

        $this->update([
            'paid_amount' => $paid,
            'payment_status' => $status,
        ]);
    }
}