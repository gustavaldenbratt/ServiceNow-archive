<?php

namespace App\Http\Controllers\ServiceNow;

use App\Http\Controllers\Controller;
use App\Models\SnAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function show(SnAttachment $attachment): StreamedResponse
    {
        return Storage::disk('local')->response(
            $attachment->storage_path,
            $attachment->file_name,
            ['Content-Type' => $attachment->content_type ?? 'application/octet-stream'],
        );
    }
}
