<?php

namespace App\Http\Controllers\ServiceNow;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceNow\ConnectionUpdateRequest;
use App\Models\SnConnection;
use App\Services\ServiceNow\ServiceNowClient;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConnectionController extends Controller
{
    public function edit(): Response
    {
        $connection = SnConnection::current();

        return Inertia::render('servicenow/connection', [
            'connection' => [
                'instance_url' => $connection->instance_url,
                'username' => $connection->username,
                'has_password' => $connection->password !== null,
            ],
        ]);
    }

    public function update(ConnectionUpdateRequest $request): RedirectResponse
    {
        $connection = SnConnection::query()->first() ?? new SnConnection;
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $connection->fill($data);
        $connection->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Connection settings saved.')]);

        return to_route('servicenow.connection.edit');
    }

    public function test(): RedirectResponse
    {
        $connection = SnConnection::current();

        if (! $connection->instance_url) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Configure the connection before testing it.')]);

            return to_route('servicenow.connection.edit');
        }

        $client = new ServiceNowClient($connection->instance_url, (string) $connection->username, (string) $connection->password);
        $successful = $client->testConnection();

        Inertia::flash('toast', [
            'type' => $successful ? 'success' : 'error',
            'message' => $successful
                ? __('Connection successful.')
                : __('Could not connect to ServiceNow with these credentials.'),
        ]);

        return to_route('servicenow.connection.edit');
    }
}
