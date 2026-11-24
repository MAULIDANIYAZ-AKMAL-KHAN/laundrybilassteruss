<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Laundry',
                'password' => Hash::make('password123'),
                'is_admin' => true,
                'is_cashier' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@gmail.com'],
            [
                'name' => 'Kasir Laundry',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'is_cashier' => true,
            ]
        );

        $this->call(LaundryItemSeeder::class);
    }
}
