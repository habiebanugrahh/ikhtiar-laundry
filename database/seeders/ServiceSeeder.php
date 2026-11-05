<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Cuci Kiloan', 'unit' => Service::UNIT_KG, 'price' => 6000],
            ['name' => 'Cuci Setrika', 'unit' => Service::UNIT_KG, 'price' => 8000],
            ['name' => 'Setrika Saja', 'unit' => Service::UNIT_KG, 'price' => 5000],
            ['name' => 'Selimut', 'unit' => Service::UNIT_PCS, 'price' => 25000],
            ['name' => 'Bedcover', 'unit' => Service::UNIT_PCS, 'price' => 35000],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service + ['is_active' => true]
            );
        }
    }
}