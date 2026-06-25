<?php

use App\Models\User;

test('the home page redirects to the ServiceNow records archive', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('servicenow.records.index'));
});

test('guests visiting the old dashboard path are redirected to the login page', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('authenticated users visiting the old dashboard path are redirected to records', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertRedirect(route('servicenow.records.index'));
});
