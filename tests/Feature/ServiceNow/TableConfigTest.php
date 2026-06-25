<?php

use App\Jobs\SyncTableJob;
use App\Models\SnConnection;
use App\Models\SnTableConfig;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

test('tables page lists configured tables and discovered available tables', function () {
    $user = User::factory()->create();
    SnTableConfig::factory()->create(['table_name' => 'incident']);

    SnConnection::query()->create([
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc',
        'password' => 'secret',
    ]);

    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::response([
            'result' => [
                ['name' => 'task', 'sys_id' => 'task-id', 'super_class' => null],
                ['name' => 'incident', 'sys_id' => 'incident-id', 'super_class' => ['value' => 'task-id']],
                ['name' => 'sc_task', 'sys_id' => 'sc_task-id', 'super_class' => ['value' => 'task-id']],
            ],
        ]),
    ]);

    $response = $this->actingAs($user)->get(route('servicenow.tables.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('availableTables', ['sc_task'])
        ->has('configs', 1));
});

test('tables page does not blow up when no connection is configured', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('servicenow.tables.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('availableTables', []));
});

test('a table config can be added', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('servicenow.tables.store'), [
        'table_name' => 'incident',
        'fetch_since' => '2025-01-01',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('servicenow.tables.index'));
    expect(SnTableConfig::query()->where('table_name', 'incident')->exists())->toBeTrue();
});

test('the same table cannot be added twice', function () {
    $user = User::factory()->create();
    SnTableConfig::factory()->create(['table_name' => 'incident']);

    $response = $this->actingAs($user)->post(route('servicenow.tables.store'), [
        'table_name' => 'incident',
        'fetch_since' => '2025-01-01',
    ]);

    $response->assertSessionHasErrors('table_name');
});

test('a table config can be enabled, disabled, and have its fetch_since updated', function () {
    $user = User::factory()->create();
    $config = SnTableConfig::factory()->create(['table_name' => 'incident', 'enabled' => true]);

    $response = $this->actingAs($user)->patch(route('servicenow.tables.update', $config), [
        'enabled' => false,
        'fetch_since' => '2024-06-01',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('servicenow.tables.index'));
    $config->refresh();
    expect($config->enabled)->toBeFalse();
    expect($config->fetch_since->toDateString())->toBe('2024-06-01');
});

test('triggering a sync dispatches SyncTableJob for that table config', function () {
    Bus::fake();

    $user = User::factory()->create();
    $config = SnTableConfig::factory()->create(['table_name' => 'incident']);

    $response = $this->actingAs($user)->post(route('servicenow.tables.sync', $config));

    $response->assertRedirect(route('servicenow.tables.index'));
    Bus::assertDispatched(SyncTableJob::class, fn ($job) => $job->tableConfigId === $config->id);
});
