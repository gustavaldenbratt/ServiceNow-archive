<?php

namespace App\Models;

use Database\Factories\SnTableConfigFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['table_name', 'fetch_since', 'enabled', 'last_synced_cursor', 'last_run_status'])]
class SnTableConfig extends Model
{
    /** @use HasFactory<SnTableConfigFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fetch_since' => 'date',
            'enabled' => 'boolean',
            'last_synced_cursor' => 'datetime',
        ];
    }
}
