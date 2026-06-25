<?php

namespace App\Jobs;

use App\Models\SnTableConfig;
use App\Models\SyncRun;
use App\Services\ServiceNow\RecordSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncTableJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $tableConfigId) {}

    /**
     * Execute the job.
     */
    public function handle(RecordSyncService $service): void
    {
        $config = SnTableConfig::query()->findOrFail($this->tableConfigId);

        $run = SyncRun::query()->create([
            'table_name' => $config->table_name,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $service->sync($config, $run);

            $run->update(['status' => 'success', 'finished_at' => now()]);
            $config->update(['last_run_status' => 'success']);
        } catch (Throwable $e) {
            $run->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);
            $config->update(['last_run_status' => 'failed']);

            throw $e;
        }
    }
}
