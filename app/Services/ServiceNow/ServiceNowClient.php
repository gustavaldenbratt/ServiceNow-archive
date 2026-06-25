<?php

namespace App\Services\ServiceNow;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class ServiceNowClient
{
    private const HIERARCHY_PAGE_SIZE = 200;

    public function __construct(
        private readonly string $instanceUrl,
        private readonly string $username,
        private readonly string $password,
    ) {}

    public function testConnection(): bool
    {
        try {
            $this->request()
                ->get('table/sys_db_object', ['sysparm_limit' => 1])
                ->throw();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, array{name: string, sys_id: string, super_class: ?string}>
     */
    public function fetchTableHierarchy(): array
    {
        $rows = [];
        $offset = 0;

        while (true) {
            $page = $this->request()->get('table/sys_db_object', [
                'sysparm_fields' => 'name,sys_id,super_class',
                'sysparm_limit' => self::HIERARCHY_PAGE_SIZE,
                'sysparm_offset' => $offset,
            ])->throw()->json('result') ?? [];

            foreach ($page as $row) {
                $rows[] = [
                    'name' => $row['name'],
                    'sys_id' => $row['sys_id'],
                    'super_class' => $row['super_class']['value'] ?? $row['super_class'] ?? null,
                ];
            }

            if (count($page) < self::HIERARCHY_PAGE_SIZE) {
                break;
            }

            $offset += self::HIERARCHY_PAGE_SIZE;
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchRecords(string $table, ?CarbonInterface $since, int $offset, int $limit): array
    {
        $query = $since
            ? 'sys_updated_on>='.$since->format('Y-m-d H:i:s').'^ORDERBYsys_updated_on'
            : 'ORDERBYsys_updated_on';

        return $this->request()->get("table/{$table}", [
            'sysparm_query' => $query,
            'sysparm_display_value' => 'all',
            'sysparm_exclude_reference_link' => 'true',
            'sysparm_offset' => $offset,
            'sysparm_limit' => $limit,
        ])->throw()->json('result') ?? [];
    }

    /**
     * @param  array<int, string>  $sysIds
     * @return array<int, array<string, mixed>>
     */
    public function fetchJournalEntries(string $table, array $sysIds): array
    {
        if ($sysIds === []) {
            return [];
        }

        $query = 'element_idIN'.implode(',', $sysIds).'^elementINcomments,work_notes^ORDERBYsys_created_on';

        return $this->request()->get('table/sys_journal_field', [
            'sysparm_query' => $query,
            'sysparm_display_value' => 'all',
            'sysparm_exclude_reference_link' => 'true',
            'sysparm_limit' => 1000,
        ])->throw()->json('result') ?? [];
    }

    /**
     * @param  array<int, string>  $sysIds
     * @return array<int, array<string, mixed>>
     */
    public function fetchAttachmentsMeta(string $table, array $sysIds): array
    {
        if ($sysIds === []) {
            return [];
        }

        $query = "table_name={$table}^table_sys_idIN".implode(',', $sysIds);

        return $this->request()->get('attachment', [
            'sysparm_query' => $query,
            'sysparm_limit' => 1000,
        ])->throw()->json('result') ?? [];
    }

    public function downloadAttachment(string $attachmentSysId): string
    {
        return $this->request()
            ->get("attachment/{$attachmentSysId}/file")
            ->throw()
            ->body();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->instanceUrl, '/').'/api/now/')
            ->withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->retry(4, fn (int $attempt): int => 100 * (2 ** $attempt));
    }
}
