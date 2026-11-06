<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHOD_CASH = 'cash';
    public const METHOD_TRANSFER = 'transfer';
    public const METHOD_QRIS = 'qris';

    protected $fillable = [
        'transaction_id',
        'user_id',
        'amount',
        'method',
        'paid_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    // Nama metode pembayaran untuk ditampilkan
    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            self::METHOD_CASH => 'Tunai',
            self::METHOD_TRANSFER => 'Transfer',
            self::METHOD_QRIS => 'QRIS',
            default => $this->method,
        };
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}