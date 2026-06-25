<?php

namespace Database\Factories;

use App\Models\SnTableConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SnTableConfig>
 */
class SnTableConfigFactory extends Factory
{
    protected $model = SnTableConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_name' => 'incident',
            'fetch_since' => now()->subYear(),
            'enabled' => true,
            'last_synced_cursor' => null,
            'last_run_status' => null,
        ];
    }
}
