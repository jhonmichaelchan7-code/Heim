<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSetting extends Model
{
    use HasFactory;

    protected $table = 'pos_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $val = $setting->value;
        $decoded = json_decode($val, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $val;
    }

    public static function set(string $key, $value): void
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }
}
