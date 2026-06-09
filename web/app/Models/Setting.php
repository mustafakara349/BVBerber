<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'description'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::where('setting_key', $key)->value('setting_value') ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
