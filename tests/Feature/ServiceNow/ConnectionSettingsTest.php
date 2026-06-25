<?php

use App\Models\SnConnection;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

test('connection page is displayed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('servicenow.connection.edit'));

    $response->assertOk();
});

test('connection settings can be saved', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('servicenow.connection.update'), [
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc-account',
        'password' => 'secret',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('servicenow.connection.edit'));

    $connection = SnConnection::query()->first();
    expect($connection->instance_url)->toBe('https://dev1234.service-now.com');
    expect($connection->username)->toBe('svc-account');
    expect($connection->password)->toBe('secret');
});

test('leaving the password blank on update keeps the existing password', function () {
    $user = User::factory()->create();
    SnConnection::query()->create([
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc-account',
        'password' => 'original-secret',
    ]);

    $this->actingAs($user)->put(route('servicenow.connection.update'), [
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc-account',
        'password' => '',
    ]);

    expect(SnConnection::query()->first()->password)->toBe('original-secret');
});

test('testing the connection reports success', function () {
    $user = User::factory()->create();
    SnConnection::query()->create([
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc-account',
        'password' => 'secret',
    ]);

    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::response(['result' => []]),
    ]);

    $response = $this->actingAs($user)->post(route('servicenow.connection.test'));

    $response->assertRedirect(route('servicenow.connection.edit'));
    $response->assertInertiaFlash('toast.type', 'success');
});

test('testing the connection reports failure', function () {
    Sleep::fake();

    $user = User::factory()->create();
    SnConnection::query()->create([
        'instance_url' => 'https://dev1234.service-now.com',
        'username' => 'svc-account',
        'password' => 'wrong',
    ]);

    Http::fake([
        'dev1234.service-now.com/api/now/table/sys_db_object*' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $response = $this->actingAs($user)->post(route('servicenow.connection.test'));

    $response->assertRedirect(route('servicenow.connection.edit'));
    $response->assertInertiaFlash('toast.type', 'error');
});
