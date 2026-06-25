<?php

use App\Services\ServiceNow\ServiceNowClient;
use App\Services\ServiceNow\TaskTableResolver;

test('resolves direct and transitive descendants of task, excluding task itself and unrelated tables', function () {
    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchTableHierarchy')->andReturn([
        ['name' => 'task', 'sys_id' => 'task-id', 'super_class' => null],
        ['name' => 'incident', 'sys_id' => 'incident-id', 'super_class' => 'task-id'],
        ['name' => 'sc_task', 'sys_id' => 'sc_task-id', 'super_class' => 'task-id'],
        ['name' => 'sc_req_item', 'sys_id' => 'sc_req_item-id', 'super_class' => 'sc_task-id'],
        ['name' => 'sys_user', 'sys_id' => 'sys_user-id', 'super_class' => null],
    ]);

    $resolver = new TaskTableResolver($client);

    expect($resolver->resolveDescendantsOf('task'))->toBe(['incident', 'sc_req_item', 'sc_task']);
});

test('returns an empty array when the root table is not present', function () {
    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchTableHierarchy')->andReturn([
        ['name' => 'incident', 'sys_id' => 'incident-id', 'super_class' => null],
    ]);

    $resolver = new TaskTableResolver($client);

    expect($resolver->resolveDescendantsOf('task'))->toBe([]);
});

test('does not infinite-loop on a cyclic super_class chain', function () {
    $client = Mockery::mock(ServiceNowClient::class);
    $client->shouldReceive('fetchTableHierarchy')->andReturn([
        ['name' => 'task', 'sys_id' => 'task-id', 'super_class' => null],
        ['name' => 'a', 'sys_id' => 'a-id', 'super_class' => 'b-id'],
        ['name' => 'b', 'sys_id' => 'b-id', 'super_class' => 'a-id'],
    ]);

    $resolver = new TaskTableResolver($client);

    expect($resolver->resolveDescendantsOf('task'))->toBe([]);
});
