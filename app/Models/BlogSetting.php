<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $storedValue = static::query()->where('key', $key)->value('value');
        if ($storedValue === null) {
            return $default;
        }

        $decoded = json_decode($storedValue, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $storedValue;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR)]
        );
    }
}
