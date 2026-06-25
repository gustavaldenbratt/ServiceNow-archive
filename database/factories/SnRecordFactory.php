<?php

namespace Database\Factories;

use App\Models\SnRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SnRecord>
 */
class SnRecordFactory extends Factory
{
    protected $model = SnRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_name' => 'incident',
            'sys_id' => Str::uuid()->toString(),
            'number' => 'INC'.fake()->unique()->numerify('#######'),
            'sys_created_at' => now()->subDays(5),
            'sys_updated_at' => now(),
            'state' => 'Closed',
            'short_description' => fake()->sentence(),
            'assigned_to' => fake()->name(),
            'data' => [],
        ];
    }
}
