<?php

namespace App\Models;

use Database\Factories\SnAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sys_id', 'record_sys_id', 'table_name', 'file_name', 'content_type', 'size_bytes', 'storage_path'])]
class SnAttachment extends Model
{
    /** @use HasFactory<SnAttachmentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->content_type, 'image/');
    }
}
