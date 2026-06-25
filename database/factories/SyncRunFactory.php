<?php

namespace Database\Factories;

use App\Models\SyncRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncRun>
 */
class SyncRunFactory extends Factory
{
    protected $model = SyncRun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_name' => 'incident',
            'status' => 'success',
            'records_processed' => 0,
            'total' => null,
            'started_at' => now(),
            'finished_at' => now(),
            'error' => null,
        ];
    }
}
