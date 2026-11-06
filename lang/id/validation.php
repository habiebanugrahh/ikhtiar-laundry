<?php

// Pesan validasi Bahasa Indonesia untuk aturan yang dipakai di sistem ini
return [
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'numeric' => ':Attribute harus berupa angka.',
    'boolean' => ':Attribute tidak valid.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => 'Format :attribute tidak valid.',
    'array' => ':Attribute harus berupa daftar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'unique' => ':Attribute sudah digunakan.',
    'regex' => 'Format :attribute tidak valid.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
        'array' => ':Attribute minimal berisi :min data.',
    ],
    'max' => [
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
        'array' => ':Attribute maksimal berisi :max data.',
    ],

    // Nama kolom yang tampil di pesan error
    'attributes' => [
        'name' => 'nama',
        'username' => 'username',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'unit' => 'satuan',
        'price' => 'tarif',
        'is_active' => 'status',
        'phone' => 'nomor telepon',
        'address' => 'alamat',
        'customer_id' => 'pelanggan',
        'transaction_date' => 'tanggal transaksi',
        'items' => 'layanan',
        'items.*.service_id' => 'layanan',
        'items.*.quantity' => 'kuantitas',
        'express_fee' => 'biaya ekspres',
        'notes' => 'catatan',
        'payment_method' => 'metode pembayaran',
        'payment_input' => 'nominal pembayaran',
        'quantity' => 'kuantitas',
        'amount' => 'nominal pembayaran',
        'method' => 'metode pembayaran',
    ],
];