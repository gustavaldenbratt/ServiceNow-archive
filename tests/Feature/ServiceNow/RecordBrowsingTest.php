<?php

use App\Models\SnAttachment;
use App\Models\SnJournalEntry;
use App\Models\SnRecord;
use App\Models\SnTableConfig;
use App\Models\User;

test('records page lists records newest updated first', function () {
    $user = User::factory()->create();

    $older = SnRecord::factory()->create(['sys_updated_at' => now()->subDay()]);
    $newer = SnRecord::factory()->create(['sys_updated_at' => now()]);

    $response = $this->actingAs($user)->get(route('servicenow.records.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('records.data.0.id', $newer->id)
        ->where('records.data.1.id', $older->id));
});

test('records can be filtered by table, number, state, and date range', function () {
    $user = User::factory()->create();

    SnTableConfig::factory()->create(['table_name' => 'incident']);
    SnTableConfig::factory()->create(['table_name' => 'problem']);

    $match = SnRecord::factory()->create([
        'table_name' => 'incident',
        'number' => 'INC0000123',
        'state' => 'Closed',
        'sys_updated_at' => '2026-03-15 00:00:00',
    ]);

    SnRecord::factory()->create(['table_name' => 'problem']);
    SnRecord::factory()->create([
        'table_name' => 'incident',
        'number' => 'INC9999999',
        'state' => 'Open',
    ]);

    $response = $this->actingAs($user)->get(route('servicenow.records.index', [
        'table_name' => 'incident',
        'number' => '123',
        'state' => 'Closed',
        'from' => '2026-01-01',
        'to' => '2026-12-31',
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('records.data.0.id', $match->id)
        ->has('records.data', 1));
});

test('record detail shows the record, its journal entries in order, and its attachments', function () {
    $user = User::factory()->create();

    $record = SnRecord::factory()->create([
        'sys_id' => 'rec-1',
        'data' => ['short_description' => ['value' => 'hi']],
    ]);

    $first = SnJournalEntry::factory()->create([
        'record_sys_id' => 'rec-1',
        'sys_created_at' => now()->subHour(),
    ]);

    $second = SnJournalEntry::factory()->create([
        'record_sys_id' => 'rec-1',
        'sys_created_at' => now(),
    ]);

    SnAttachment::factory()->create(['record_sys_id' => 'rec-1']);

    $response = $this->actingAs($user)->get(route('servicenow.records.show', $record));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('record.id', $record->id)
        ->where('journalEntries.0.id', $first->id)
        ->where('journalEntries.1.id', $second->id)
        ->has('attachments', 1));
});
