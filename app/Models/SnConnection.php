<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['instance_url', 'username', 'password'])]
class SnConnection extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? new self;
    }
}
