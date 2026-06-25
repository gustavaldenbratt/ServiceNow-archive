<?php

namespace App\Services\ServiceNow;

class TaskTableResolver
{
    public function __construct(private readonly ServiceNowClient $client) {}

    /**
     * @return array<int, string> table names descending from $rootTable, excluding $rootTable itself, sorted alphabetically
     */
    public function resolveDescendantsOf(string $rootTable = 'task'): array
    {
        $rows = $this->client->fetchTableHierarchy();

        $byName = [];
        $bySysId = [];

        foreach ($rows as $row) {
            $byName[$row['name']] = $row;
            $bySysId[$row['sys_id']] = $row;
        }

        if (! isset($byName[$rootTable])) {
            return [];
        }

        $rootSysId = $byName[$rootTable]['sys_id'];
        $descendants = [];

        foreach ($byName as $name => $row) {
            if ($name !== $rootTable && $this->descendsFrom($row, $bySysId, $rootSysId)) {
                $descendants[] = $name;
            }
        }

        sort($descendants);

        return $descendants;
    }

    /**
     * @param  array{name: string, sys_id: string, super_class: ?string}  $row
     * @param  array<string, array{name: string, sys_id: string, super_class: ?string}>  $bySysId
     */
    private function descendsFrom(array $row, array $bySysId, string $rootSysId): bool
    {
        $current = $row;
        $visited = [];

        while ($current['super_class'] !== null) {
            if (isset($visited[$current['sys_id']])) {
                return false;
            }

            $visited[$current['sys_id']] = true;

            if ($current['super_class'] === $rootSysId) {
                return true;
            }

            $current = $bySysId[$current['super_class']] ?? null;

            if ($current === null) {
                return false;
            }
        }

        return false;
    }
}
