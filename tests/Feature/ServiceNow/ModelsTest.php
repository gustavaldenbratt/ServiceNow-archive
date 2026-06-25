<?php

use App\Models\SnAttachment;
use App\Models\SnJournalEntry;
use App\Models\SnRecord;
use App\Models\SnTableConfig;
use App\Models\SyncRun;

test('sn record has journal entries and attachments via sys_id', function () {
    $record = SnRecord::factory()->create(['sys_id' => 'rec-1', 'table_name' => 'incident']);

    SnJournalEntry::factory()->create(['record_sys_id' => 'rec-1', 'table_name' => 'incident', 'sys_created_at' => now()->subMinute()]);
    SnJournalEntry::factory()->create(['record_sys_id' => 'rec-1', 'table_name' => 'incident', 'sys_created_at' => now()]);
    SnAttachment::factory()->create(['record_sys_id' => 'rec-1', 'table_name' => 'incident']);

    expect($record->journalEntries)->toHaveCount(2);
    expect($record->journalEntries->first()->sys_created_at->lessThan($record->journalEntries->last()->sys_created_at))->toBeTrue();
    expect($record->attachments)->toHaveCount(1);
});

test('sn record data is cast to an array', function () {
    $record = SnRecord::factory()->create(['data' => ['short_description' => ['value' => 'hi']]]);

    expect($record->refresh()->data)->toBe(['short_description' => ['value' => 'hi']]);
});

test('sn table config casts enabled and dates', function () {
    $config = SnTableConfig::factory()->create(['enabled' => 1]);

    expect($config->enabled)->toBeTrue();
    expect($config->fetch_since)->not->toBeNull();
});

test('sync run can be created with defaults', function () {
    $run = SyncRun::factory()->create();

    expect($run->status)->toBe('success');
});
