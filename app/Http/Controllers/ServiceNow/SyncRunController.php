<?php

namespace App\Http\Controllers\ServiceNow;

use App\Http\Controllers\Controller;
use App\Models\SyncRun;
use Inertia\Inertia;
use Inertia\Response;

class SyncRunController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('servicenow/runs', [
            'runs' => SyncRun::query()->latest('started_at')->paginate(25),
        ]);
    }
}
