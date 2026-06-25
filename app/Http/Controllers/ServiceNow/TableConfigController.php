<?php

namespace App\Http\Controllers\ServiceNow;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceNow\TableConfigStoreRequest;
use App\Http\Requests\ServiceNow\TableConfigUpdateRequest;
use App\Jobs\SyncTableJob;
use App\Models\SnConnection;
use App\Models\SnTableConfig;
use App\Services\ServiceNow\ServiceNowClient;
use App\Services\ServiceNow\TaskTableResolver;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TableConfigController extends Controller
{
    public function index(): Response
    {
        $configs = SnTableConfig::query()->orderBy('table_name')->get();

        return Inertia::render('servicenow/tables', [
            'configs' => $configs,
            'availableTables' => $this->discoverAvailableTables($configs->pluck('table_name')->all()),
        ]);
    }

    public function store(TableConfigStoreRequest $request): RedirectResponse
    {
        SnTableConfig::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Table added.')]);

        return to_route('servicenow.tables.index');
    }

    public function update(TableConfigUpdateRequest $request, SnTableConfig $tableConfig): RedirectResponse
    {
        $tableConfig->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Table updated.')]);

        return to_route('servicenow.tables.index');
    }

    public function sync(SnTableConfig $tableConfig): RedirectResponse
    {
        SyncTableJob::dispatch($tableConfig->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Sync started.')]);

        return to_route('servicenow.tables.index');
    }

    /**
     * @param  array<int, string>  $configuredTables
     * @return array<int, string>
     */
    private function discoverAvailableTables(array $configuredTables): array
    {
        $connection = SnConnection::current();

        if (! $connection->instance_url) {
            return [];
        }

        try {
            $client = new ServiceNowClient($connection->instance_url, (string) $connection->username, (string) $connection->password);
            $resolver = new TaskTableResolver($client);

            return array_values(array_diff($resolver->resolveDescendantsOf('task'), $configuredTables));
        } catch (Throwable) {
            return [];
        }
    }
}
