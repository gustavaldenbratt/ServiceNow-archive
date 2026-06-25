<?php

namespace App\Http\Controllers\ServiceNow;

use App\Http\Controllers\Controller;
use App\Models\SnRecord;
use App\Models\SnTableConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecordController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'table_name' => ['nullable', 'string'],
            'number' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $records = SnRecord::query()
            ->when($filters['table_name'] ?? null, fn ($query, string $tableName) => $query->where('table_name', $tableName))
            ->when($filters['number'] ?? null, fn ($query, string $number) => $query->where('number', 'like', "%{$number}%"))
            ->when($filters['state'] ?? null, fn ($query, string $state) => $query->where('state', $state))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('sys_updated_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('sys_updated_at', '<=', $to))
            ->orderByDesc('sys_updated_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('servicenow/records/index', [
            'records' => $records,
            'filters' => $filters,
            'tableNames' => SnTableConfig::query()->orderBy('table_name')->pluck('table_name'),
        ]);
    }

    public function show(SnRecord $record): Response
    {
        return Inertia::render('servicenow/records/show', [
            'record' => $record,
            'journalEntries' => $record->journalEntries()->get(),
            'attachments' => $record->attachments()->get(),
        ]);
    }
}
