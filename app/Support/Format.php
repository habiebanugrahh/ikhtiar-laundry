<?php

namespace App\Support;

class Format
{
    // Mengubah angka menjadi format rupiah
    public static function rupiah(int|float|string|null $amount): string
    {
        return 'Rp' . number_format((float) $amount, 0, ',', '.');
    }
}