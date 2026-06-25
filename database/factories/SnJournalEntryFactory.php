<?php

namespace Database\Factories;

use App\Models\SnJournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SnJournalEntry>
 */
class SnJournalEntryFactory extends Factory
{
    protected $model = SnJournalEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sys_id' => Str::uuid()->toString(),
            'record_sys_id' => Str::uuid()->toString(),
            'table_name' => 'incident',
            'element' => 'comments',
            'value' => fake()->sentence(),
            'sys_created_by' => fake()->userName(),
            'sys_created_at' => now(),
        ];
    }
}
