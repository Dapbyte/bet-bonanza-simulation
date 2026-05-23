<?php

namespace Database\Seeders;

use App\Models\GameSetting;
use Illuminate\Database\Seeder;

class GameSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'symbol_chances' => json_encode([
                'heart' => 10,
                'purple_sq' => 10,
                'green_pent' => 10,
                'blue_oval' => 10,
                'apple' => 12,
                'plum' => 12,
                'watermelon' => 12,
                'grape' => 12,
                'banana' => 12,
            ]),

            'multipliers' => json_encode([
                'heart'      => ['8' => 10000, '10' => 25000, '12' => 50000],
                'purple_sq'  => ['8' => 2500,  '10' => 10000, '12' => 25000],
                'green_pent' => ['8' => 2000,  '10' => 5000,  '12' => 15000],
                'blue_oval'  => ['8' => 1500,  '10' => 2000,  '12' => 12000],
                'apple'      => ['8' => 1000,  '10' => 1500,  '12' => 10000],
                'plum'       => ['8' => 800,   '10' => 1200,  '12' => 8000],
                'watermelon' => ['8' => 500,   '10' => 1000,  '12' => 5000],
                'grape'      => ['8' => 400,   '10' => 900,   '12' => 4000],
                'banana'     => ['8' => 250,   '10' => 750,   '12' => 2000],
            ]),

            'scatter_frequency' => '0',
            'default_balance' => '500000',
            'tumble_enabled' => '1',
            'max_multiplier' => '10',
        ];

        foreach ($settings as $key => $value) {
            GameSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }
    }
}
