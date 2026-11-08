<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceSeeder::class,
        ]);

        // Data contoh (pelanggan dan transaksi 90 hari) hanya dibuat di lingkungan lokal
        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}