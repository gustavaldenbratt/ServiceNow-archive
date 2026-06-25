<?php

use App\Models\SnAttachment;
use App\Models\SnJournalEntry;
use App\Models\SnRecord;
use App\Models\SnTableConfig;
use App\Models\SyncRun;
use App\Services\ServiceNow\RecordSyncService;
use App\Services\ServiceNow\ServiceNowClient;
use Illuminate\Support\Facades\Storage;

test('syncs a single page of records, journals, and attachments and advances the cursor', function () {
    Storage::fake('local');

    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchRecords')
        ->once()
        ->andReturn([
            [
                'sys_id' => ['value' => 'rec-1'],
                'number' => ['value' => 'INC0000001'],
                'sys_created_on' => ['value' => '2026-01-01 00:00:00'],
                'sys_updated_on' => ['value' => '2026-01-02 00:00:00'],
                'state' => ['value' => '7', 'display_value' => 'Closed'],
                'short_description' => ['value' => 'Printer is broken'],
                'assigned_to' => ['value' => 'sys-id-of-user', 'display_value' => 'Jane Doe'],
            ],
        ]);
    $client->shouldReceive('fetchJournalEntries')
        ->once()
        ->with('incident', ['rec-1'])
        ->andReturn([
            [
                'sys_id' => 'journal-1',
                'element_id' => 'rec-1',
                'element' => 'work_notes',
                'value' => 'Investigating',
                'sys_created_by' => 'jane',
                'sys_created_on' => '2026-01-01 12:00:00',
            ],
        ]);
    $client->shouldReceive('fetchAttachmentsMeta')
        ->once()
        ->with('incident', ['rec-1'])
        ->andReturn([
            [
                'sys_id' => 'att-1',
                'table_sys_id' => 'rec-1',
                'file_name' => 'screenshot.png',
                'content_type' => 'image/png',
                'size_bytes' => '2048',
            ],
        ]);
    $client->shouldReceive('downloadAttachment')->once()->with('att-1')->andReturn('binary-bytes');

    $config = SnTableConfig::factory()->create(['table_name' => 'incident', 'last_synced_cursor' => null]);
    $run = SyncRun::factory()->create(['table_name' => 'incident', 'records_processed' => 0]);

    (new RecordSyncService($client))->sync($config, $run);

    $record = SnRecord::query()->where('sys_id', 'rec-1')->first();
    expect($record->number)->toBe('INC0000001');
    expect($record->state)->toBe('Closed');
    expect($record->assigned_to)->toBe('Jane Doe');
    expect($record->short_description)->toBe('Printer is broken');

    expect(SnJournalEntry::query()->where('sys_id', 'journal-1')->exists())->toBeTrue();

    $attachment = SnAttachment::query()->where('sys_id', 'att-1')->first();
    expect($attachment->storage_path)->not->toBeNull();
    Storage::disk('local')->assertExists($attachment->storage_path);

    expect($config->refresh()->last_synced_cursor->toDateTimeString())->toBe('2026-01-02 00:00:00');
    expect($run->refresh()->records_processed)->toBe(1);
});

test('continues the page when an attachment download fails', function () {
    Storage::fake('local');

    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchRecords')->once()->andReturn([
        ['sys_id' => ['value' => 'rec-1'], 'sys_updated_on' => ['value' => '2026-01-02 00:00:00']],
    ]);
    $client->shouldReceive('fetchJournalEntries')->once()->andReturn([]);
    $client->shouldReceive('fetchAttachmentsMeta')->once()->andReturn([
        ['sys_id' => 'att-1', 'table_sys_id' => 'rec-1', 'file_name' => 'a.png'],
    ]);
    $client->shouldReceive('downloadAttachment')->once()->andThrow(new RuntimeException('network blip'));

    $config = SnTableConfig::factory()->create(['table_name' => 'incident']);
    $run = SyncRun::factory()->create(['table_name' => 'incident']);

    (new RecordSyncService($client))->sync($config, $run);

    expect(SnAttachment::query()->count())->toBe(0);
    expect($config->refresh()->last_synced_cursor)->not->toBeNull();
});

test('skips already-downloaded attachments', function () {
    Storage::fake('local');

    SnAttachment::factory()->create(['sys_id' => 'att-1']);

    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchRecords')->once()->andReturn([
        ['sys_id' => ['value' => 'rec-1']],
    ]);
    $client->shouldReceive('fetchJournalEntries')->once()->andReturn([]);
    $client->shouldReceive('fetchAttachmentsMeta')->once()->andReturn([
        ['sys_id' => 'att-1', 'table_sys_id' => 'rec-1', 'file_name' => 'a.png'],
    ]);
    $client->shouldNotReceive('downloadAttachment');

    $config = SnTableConfig::factory()->create(['table_name' => 'incident']);
    $run = SyncRun::factory()->create(['table_name' => 'incident']);

    (new RecordSyncService($client))->sync($config, $run);

    expect(SnAttachment::query()->count())->toBe(1);
});

test('stops paging once a short page is returned and persists the cursor per page', function () {
    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchRecords')
        ->once()
        ->with('incident', null, 0, 200)
        ->andReturn(array_map(
            fn ($i) => ['sys_id' => ['value' => "rec-{$i}"], 'sys_updated_on' => ['value' => '2026-01-01 00:00:00']],
            range(1, 200),
        ));
    $client->shouldReceive('fetchRecords')
        ->once()
        ->with('incident', null, 200, 200)
        ->andReturn([
            ['sys_id' => ['value' => 'rec-201'], 'sys_updated_on' => ['value' => '2026-01-02 00:00:00']],
        ]);
    $client->shouldReceive('fetchJournalEntries')->twice()->andReturn([]);
    $client->shouldReceive('fetchAttachmentsMeta')->twice()->andReturn([]);

    $config = SnTableConfig::factory()->create(['table_name' => 'incident', 'fetch_since' => null, 'last_synced_cursor' => null]);
    $run = SyncRun::factory()->create(['table_name' => 'incident', 'records_processed' => 0]);

    (new RecordSyncService($client))->sync($config, $run);

    expect(SnRecord::query()->count())->toBe(201);
    expect($run->refresh()->records_processed)->toBe(201);
});
