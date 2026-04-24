<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        $setting = static::firstOrCreate(['key' => $key]);
        
        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->type = match (true) {
            is_int($value) => 'integer',
            is_bool($value) => 'boolean',
            is_array($value) => 'json',
            default => 'string',
        };
        
        $setting->save();
    }
}
