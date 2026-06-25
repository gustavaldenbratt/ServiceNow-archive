<?php

use App\Models\SnAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('an attachment can be downloaded with its original content type', function () {
    Storage::fake('local');

    $attachment = SnAttachment::factory()->create([
        'storage_path' => 'sn-attachments/incident/rec-1/att-1-screenshot.png',
        'content_type' => 'image/png',
        'file_name' => 'screenshot.png',
    ]);
    Storage::disk('local')->put($attachment->storage_path, 'fake-bytes');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('servicenow.attachments.show', $attachment));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('image/png');
});
