<?php

namespace App\Models;

use Database\Factories\SnJournalEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sys_id', 'record_sys_id', 'table_name', 'element', 'value', 'sys_created_by', 'sys_created_at'])]
class SnJournalEntry extends Model
{
    /** @use HasFactory<SnJournalEntryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sys_created_at' => 'datetime',
        ];
    }
}
