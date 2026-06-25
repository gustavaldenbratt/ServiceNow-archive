<?php

use App\Models\SyncRun;
use App\Models\User;

test('runs page lists sync runs newest first', function () {
    $user = User::factory()->create();

    $older = SyncRun::factory()->create(['table_name' => 'incident', 'started_at' => now()->subHour()]);
    $newer = SyncRun::factory()->create(['table_name' => 'problem', 'started_at' => now()]);

    $response = $this->actingAs($user)->get(route('servicenow.runs.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('runs.data.0.id', $newer->id)
        ->where('runs.data.1.id', $older->id));
});
