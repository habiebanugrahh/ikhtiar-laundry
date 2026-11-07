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

    // Urutan tahap pesanan dari awal sampai diambil pelanggan
    public const ORDER_FLOW = [
        self::ORDER_RECEIVED,
        self::ORDER_PROCESSING,
        self::ORDER_DONE,
        self::ORDER_PICKED_UP,
    ];

    public const PAYMENT_STATUS_LABELS = [
        self::PAYMENT_UNPAID => 'Belum bayar',
        self::PAYMENT_PARTIAL => 'Sebagian',
        self::PAYMENT_PAID => 'Lunas',
    ];

    public const ORDER_STATUS_LABELS = [
        self::ORDER_RECEIVED => 'Diterima',
        self::ORDER_PROCESSING => 'Diproses',
        self::ORDER_DONE => 'Selesai',
        self::ORDER_PICKED_UP => 'Diambil',
    ];

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

    // Daftar status pesanan beserta namanya
    public static function orderStatusLabels(): array
    {
        return [
            self::ORDER_RECEIVED => 'Diterima',
            self::ORDER_PROCESSING => 'Diproses',
            self::ORDER_DONE => 'Selesai',
            self::ORDER_PICKED_UP => 'Diambil',
        ];
    }

    // Kasir hanya boleh mengakses transaksi hari ini dan transaksi yang belum lunas
    public function isVisibleTo(User $user): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $this->transaction_date->isToday()
            || $this->payment_status !== self::PAYMENT_PAID;
    }

    // Sisa tagihan yang belum dibayar
    public function getRemainingAttribute(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    // Owner melihat semua transaksi. Kasir hanya transaksi hari ini dan yang belum lunas.
    public function isViewableBy(User $user): bool
    {
        return $user->isOwner()
            || $this->transaction_date->isToday()
            || $this->payment_status !== self::PAYMENT_PAID;
    }

    // Tahap pesanan berikutnya, atau null jika sudah diambil
    public function getNextOrderStatusAttribute(): ?string
    {
        $index = array_search($this->order_status, self::ORDER_FLOW, true);

        return $index === false ? null : (self::ORDER_FLOW[$index + 1] ?? null);
    }

    // Membuat nomor transaksi unik per hari, contoh: IKH-20261105-001
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