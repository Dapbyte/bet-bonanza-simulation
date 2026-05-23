<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mahesa99.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'balance' => 0,
            ]
        );
    }
}
