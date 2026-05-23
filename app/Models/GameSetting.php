<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'updated_at'];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Get a setting value as decoded JSON.
     */
    public static function getJson(string $key, mixed $default = null): mixed
    {
        $value = static::getValue($key);

        if ($value === null) {
            return $default;
        }

        return json_decode($value, true) ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'updated_at' => now(),
            ]
        );
    }
}
