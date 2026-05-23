<?php

namespace Database\Seeders;

use App\Models\GameSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed game settings first (needed for default_balance)
        $this->call(GameSettingsSeeder::class);

        // Seed admin account
        $this->call(AdminSeeder::class);

        // Seed demo player with default balance
        $defaultBalance = (int) GameSetting::getValue('default_balance', 500000);

        User::updateOrCreate(
            ['email' => 'player@mahesa99.com'],
            [
                'name' => 'Player Demo',
                'password' => bcrypt('player123'),
                'role' => 'player',
                'balance' => $defaultBalance,
            ]
        );
    }
}
