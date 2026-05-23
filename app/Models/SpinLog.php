<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'bet_amount',
        'win_amount',
        'multiplier_reached',
        'symbols_hit',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'symbols_hit' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
