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
            DemoDataSeeder::class, // hapus baris ini sebelum sistem dipakai beneran
        ]);
    }
}