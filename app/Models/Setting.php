<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting by key and cast it if it's JSON.
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    /**
     * Save a setting by key, converting objects/arrays to JSON.
     */
    public static function set(string $key, $value): self
    {
        $val = is_array($value) || is_object($value) ? json_encode($value) : $value;
        return self::updateOrCreate(['key' => $key], ['value' => $val]);
    }
}
