<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner',
                'password' => 'owner123',
                'role' => User::ROLE_OWNER,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Kasir Satu',
                'password' => 'kasir321',
                'role' => User::ROLE_CASHIER,
                'is_active' => true,
            ]
        );
    }
}