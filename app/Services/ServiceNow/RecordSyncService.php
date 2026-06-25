<?php

namespace App\Services\ServiceNow;

use App\Models\SnAttachment;
use App\Models\SnJournalEntry;
use App\Models\SnRecord;
use App\Models\SnTableConfig;
use App\Models\SyncRun;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RecordSyncService
{
    private const PAGE_SIZE = 200;

    public function __construct(private readonly ServiceNowClient $client) {}

    public function sync(SnTableConfig $config, SyncRun $run): void
    {
        $since = $this->dateAttribute($config->last_synced_cursor)
            ?? $this->dateAttribute($config->fetch_since);
        $offset = 0;

        while (true) {
            $page = $this->client->fetchRecords($config->table_name, $since, $offset, self::PAGE_SIZE);

            if ($page === []) {
                break;
            }

            $sysIds = [];
            $maxUpdatedAt = null;

            foreach ($page as $raw) {
                $record = $this->upsertRecord($config->table_name, $raw);
                $sysIds[] = $record->sys_id;
                $updatedAt = $this->dateAttribute($record->sys_updated_at);

                if ($updatedAt !== null && (! $maxUpdatedAt || $updatedAt->gt($maxUpdatedAt))) {
                    $maxUpdatedAt = $updatedAt;
                }
            }

            $this->syncJournalEntries($config->table_name, $sysIds);
            $this->syncAttachments($config->table_name, $sysIds);

            $run->increment('records_processed', count($page));

            if ($maxUpdatedAt !== null) {
                $config->update(['last_synced_cursor' => $maxUpdatedAt]);
            }

            if (count($page) < self::PAGE_SIZE) {
                break;
            }

            $offset += self::PAGE_SIZE;
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function upsertRecord(string $table, array $raw): SnRecord
    {
        return SnRecord::query()->updateOrCreate(
            [
                'table_name' => $table,
                'sys_id' => $this->extractValue($raw, 'sys_id'),
            ],
            [
                'number' => $this->extractValue($raw, 'number'),
                'sys_created_at' => $this->extractValue($raw, 'sys_created_on'),
                'sys_updated_at' => $this->extractValue($raw, 'sys_updated_on'),
                'state' => $this->extractDisplayValue($raw, 'state'),
                'short_description' => $this->extractValue($raw, 'short_description'),
                'assigned_to' => $this->extractDisplayValue($raw, 'assigned_to'),
                'data' => $raw,
            ],
        );
    }

    /**
     * @param  array<int, string>  $sysIds
     */
    private function syncJournalEntries(string $table, array $sysIds): void
    {
        if ($sysIds === []) {
            return;
        }

        foreach ($this->client->fetchJournalEntries($table, $sysIds) as $raw) {
            SnJournalEntry::query()->updateOrCreate(
                ['sys_id' => $this->extractValue($raw, 'sys_id')],
                [
                    'record_sys_id' => $this->extractValue($raw, 'element_id'),
                    'table_name' => $table,
                    'element' => $this->extractValue($raw, 'element'),
                    'value' => $this->extractValue($raw, 'value'),
                    'sys_created_by' => $this->extractValue($raw, 'sys_created_by'),
                    'sys_created_at' => $this->extractValue($raw, 'sys_created_on'),
                ],
            );
        }
    }

    /**
     * @param  array<int, string>  $sysIds
     */
    private function syncAttachments(string $table, array $sysIds): void
    {
        if ($sysIds === []) {
            return;
        }

        foreach ($this->client->fetchAttachmentsMeta($table, $sysIds) as $raw) {
            $attachmentSysId = $this->extractValue($raw, 'sys_id');

            if ($attachmentSysId === null || SnAttachment::query()->where('sys_id', $attachmentSysId)->exists()) {
                continue;
            }

            $recordSysId = $this->extractValue($raw, 'table_sys_id');

            if ($recordSysId === null) {
                continue;
            }

            $fileName = $this->extractValue($raw, 'file_name') ?? $attachmentSysId;
            $storagePath = "sn-attachments/{$table}/{$recordSysId}/{$attachmentSysId}-{$fileName}";

            try {
                $contents = $this->client->downloadAttachment($attachmentSysId);
            } catch (Throwable $e) {
                report($e);

                continue;
            }

            Storage::disk('local')->put($storagePath, $contents);

            SnAttachment::query()->create([
                'sys_id' => $attachmentSysId,
                'record_sys_id' => $recordSysId,
                'table_name' => $table,
                'file_name' => $fileName,
                'content_type' => $this->extractValue($raw, 'content_type'),
                'size_bytes' => (int) $this->extractValue($raw, 'size_bytes'),
                'storage_path' => $storagePath,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function extractValue(array $raw, string $field): ?string
    {
        $entry = $raw[$field] ?? null;

        if (is_array($entry)) {
            $value = $entry['value'] ?? null;

            return $value === null ? null : (string) $value;
        }

        return $entry === null ? null : (string) $entry;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function extractDisplayValue(array $raw, string $field): ?string
    {
        $entry = $raw[$field] ?? null;

        if (is_array($entry)) {
            $value = $entry['display_value'] ?? ($entry['value'] ?? null);

            return $value === null ? null : (string) $value;
        }

        return $entry === null ? null : (string) $entry;
    }

    private function dateAttribute(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (is_string($value) && $value !== '') {
            return CarbonImmutable::parse($value);
        }

        return null;
    }
}
