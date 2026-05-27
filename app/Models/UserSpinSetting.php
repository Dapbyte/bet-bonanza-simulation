<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSpinSetting extends Model
{
    protected $fillable = [
        'user_id',
        'is_enabled',
        'spin_window_size',
        'symbol_rates',
        'scatter_rate',
        'bomb_rate',
        'window_spin_count',
        'window_symbol_counts',
        'window_scatter_count',
        'window_bomb_count',
    ];

    protected $casts = [
        'symbol_rates' => 'array',
        'window_symbol_counts' => 'array',
        'is_enabled' => 'boolean',
        'spin_window_size' => 'integer',
        'scatter_rate' => 'integer',
        'bomb_rate' => 'integer',
        'window_spin_count' => 'integer',
        'window_scatter_count' => 'integer',
        'window_bomb_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'is_enabled' => false,
                'spin_window_size' => 100,
                'symbol_rates' => [],
                'scatter_rate' => 3,
                'bomb_rate' => 8,
                'window_spin_count' => 0,
                'window_symbol_counts' => [],
                'window_scatter_count' => 0,
                'window_bomb_count' => 0,
            ]
        );
    }
}
