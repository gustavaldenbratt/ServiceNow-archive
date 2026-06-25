<?php

use App\Services\ServiceNow\ServiceNowClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

function makeClient(): ServiceNowClient
{
    return new ServiceNowClient('https://dev1234.service-now.com', 'svc-account', 'secret');
}

test('test connection returns true on a successful response', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::response(['result' => []]),
    ]);

    expect(makeClient()->testConnection())->toBeTrue();
});

test('test connection returns false on a failed response', function () {
    Sleep::fake();

    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    expect(makeClient()->testConnection())->toBeFalse();
});

test('fetch table hierarchy paginates until a short page is returned', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::sequence()
            ->push(['result' => array_fill(0, 200, ['name' => 'x', 'sys_id' => '1', 'super_class' => null])])
            ->push(['result' => [['name' => 'task', 'sys_id' => 'task-id', 'super_class' => null]]]),
    ]);

    $rows = makeClient()->fetchTableHierarchy();

    expect($rows)->toHaveCount(201);
    expect($rows[200])->toBe(['name' => 'task', 'sys_id' => 'task-id', 'super_class' => null]);
});

test('fetch table hierarchy unwraps a reference-style super class', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::response([
            'result' => [
                ['name' => 'incident', 'sys_id' => 'inc-id', 'super_class' => ['value' => 'task-id']],
            ],
        ]),
    ]);

    $rows = makeClient()->fetchTableHierarchy();

    expect($rows[0]['super_class'])->toBe('task-id');
});

test('fetch records sends a since filter and pagination params', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/table/incident*' => Http::response(['result' => [['sys_id' => ['value' => 'a']]]]),
    ]);

    $result = makeClient()->fetchRecords('incident', Carbon::parse('2026-01-01 00:00:00'), 50, 200);

    expect($result)->toHaveCount(1);

    Http::assertSent(function ($request) {
        parse_str((string) parse_url((string) $request->url(), PHP_URL_QUERY), $query);

        return str_starts_with((string) $request->url(), 'https://dev1234.service-now.com/api/now/table/incident?')
            && $query === [
                'sysparm_query' => 'sys_updated_on>=2026-01-01 00:00:00^ORDERBYsys_updated_on',
                'sysparm_display_value' => 'all',
                'sysparm_exclude_reference_link' => 'true',
                'sysparm_offset' => '50',
                'sysparm_limit' => '200',
            ];
    });
});

test('fetch journal entries filters by element ids and journal fields', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_journal_field*' => Http::response(['result' => [['sys_id' => 'j1']]]),
    ]);

    $result = makeClient()->fetchJournalEntries('incident', ['a', 'b']);

    expect($result)->toHaveCount(1);

    Http::assertSent(function ($request) {
        return str_contains((string) $request->url(), 'element_idINa%2Cb')
            && str_contains((string) $request->url(), 'elementINcomments%2Cwork_notes');
    });
});

test('fetch journal entries returns empty array without a request when there are no sys ids', function () {
    Http::fake();

    expect(makeClient()->fetchJournalEntries('incident', []))->toBe([]);
    Http::assertNothingSent();
});

test('fetch attachments meta queries by table and table sys id', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/attachment*' => Http::response(['result' => [['sys_id' => 'att1']]]),
    ]);

    $result = makeClient()->fetchAttachmentsMeta('incident', ['a', 'b']);

    expect($result)->toHaveCount(1);

    Http::assertSent(function ($request) {
        return str_contains((string) $request->url(), 'table_name%3Dincident%5Etable_sys_idINa%2Cb');
    });
});

test('download attachment returns the raw response body', function () {
    Http::fake([
        'dev1234.service-now.com/api/now/attachment/att1/file' => Http::response('binary-bytes'),
    ]);

    expect(makeClient()->downloadAttachment('att1'))->toBe('binary-bytes');
});

test('a transient failure is retried before succeeding', function () {
    Sleep::fake();

    Http::fake([
        'dev1234.service-now.com/api/now/table/incident*' => Http::sequence()
            ->push(['error' => 'boom'], 500)
            ->push(['result' => [['sys_id' => ['value' => 'a']]]]),
    ]);

    $result = makeClient()->fetchRecords('incident', null, 0, 200);

    expect($result)->toHaveCount(1);
    Http::assertSentCount(2);
});

test('a failure that exhausts all retries throws', function () {
    Sleep::fake();

    Http::fake([
        'dev1234.service-now.com/api/now/table/incident*' => Http::response(['error' => 'boom'], 500),
    ]);

    expect(fn () => makeClient()->fetchRecords('incident', null, 0, 200))
        ->toThrow(RequestException::class);

    Http::assertSentCount(4);
});
