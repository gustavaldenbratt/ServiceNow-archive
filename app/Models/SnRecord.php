<?php

namespace App\Models;

use Database\Factories\SnRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['table_name', 'sys_id', 'number', 'sys_created_at', 'sys_updated_at', 'state', 'short_description', 'assigned_to', 'data'])]
class SnRecord extends Model
{
    /** @use HasFactory<SnRecordFactory> */
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
            'sys_updated_at' => 'datetime',
            'data' => 'array',
        ];
    }

    /**
     * @return HasMany<SnJournalEntry, $this>
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(SnJournalEntry::class, 'record_sys_id', 'sys_id')->orderBy('sys_created_at');
    }

    /**
     * @return HasMany<SnAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(SnAttachment::class, 'record_sys_id', 'sys_id');
    }
}
