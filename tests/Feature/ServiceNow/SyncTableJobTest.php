<?php

use App\Jobs\SyncTableJob;
use App\Models\SnConnection;
use App\Models\SnTableConfig;
use App\Models\SyncRun;
use App\Services\ServiceNow\RecordSyncService;
use App\Services\ServiceNow\ServiceNowClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

test('a successful run creates a SyncRun and marks it and the config successful', function () {
    SnConnection::query()->create([
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc',
        'password' => 'secret',
    ]);

    Http::fake([
        'dev1234.service-now.com/api/now/table/incident*' => Http::response(['result' => []]),
    ]);

    $config = SnTableConfig::factory()->create(['table_name' => 'incident']);

    (new SyncTableJob($config->id))->handle(app(RecordSyncService::class));

    $run = SyncRun::query()->where('table_name', 'incident')->first();
    expect($run->status)->toBe('success');
    expect($config->refresh()->last_run_status)->toBe('success');
});

test('a failed run marks the run and config failed and rethrows', function () {
    Sleep::fake();

    SnConnection::query()->create([
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc',
        'password' => 'secret',
    ]);

    Http::fake([
        'dev1234.service-now.com/api/now/table/incident*' => Http::response(['error' => 'boom'], 500),
    ]);

    $config = SnTableConfig::factory()->create(['table_name' => 'incident']);

    expect(fn () => (new SyncTableJob($config->id))->handle(app(RecordSyncService::class)))
        ->toThrow(RequestException::class);

    $run = SyncRun::query()->where('table_name', 'incident')->first();
    expect($run->status)->toBe('failed');
    expect($run->error)->not->toBeNull();
    expect($config->refresh()->last_run_status)->toBe('failed');
});

test('resolving ServiceNowClient without a configured connection throws', function () {
    expect(fn () => app(ServiceNowClient::class))->toThrow(RuntimeException::class);
});
