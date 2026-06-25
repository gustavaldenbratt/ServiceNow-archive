<?php

namespace Database\Factories;

use App\Models\SnAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SnAttachment>
 */
class SnAttachmentFactory extends Factory
{
    protected $model = SnAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sysId = Str::uuid()->toString();

        return [
            'sys_id' => $sysId,
            'record_sys_id' => Str::uuid()->toString(),
            'table_name' => 'incident',
            'file_name' => 'screenshot.png',
            'content_type' => 'image/png',
            'size_bytes' => 1024,
            'storage_path' => "sn-attachments/incident/record/{$sysId}-screenshot.png",
        ];
    }
}
