<?php

namespace App\Support;

class Format
{
    // Mengubah angka menjadi format rupiah, contoh: Rp150.000
    public static function rupiah(int|float|string|null $amount): string
    {
        return 'Rp' . number_format((float) $amount, 0, ',', '.');
    }

    // Menampilkan kuantitas tanpa nol di belakang koma, contoh: 3,5 atau 10
    public static function quantity(int|float|string|null $value): string
    {
        $formatted = number_format((float) $value, 2, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}