import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { index } from '@/routes/servicenow/runs';

type SyncRun = {
    id: number;
    table_name: string;
    status: string;
    records_processed: number;
    started_at: string | null;
    finished_at: string | null;
    error: string | null;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

export default function Runs({ runs }: { runs: Paginated<SyncRun> }) {
    return (
        <>
            <Head title="ServiceNow sync runs" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Sync runs"
                    description="History of sync attempts for each configured table"
                />

                <div className="space-y-2">
                    {runs.data.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No sync runs yet.
                        </p>
                    )}

                    {runs.data.map((run) => (
                        <div
                            key={run.id}
                            className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                        >
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <span className="font-medium">
                                        {run.table_name}
                                    </span>
                                    <Badge
                                        variant={
                                            run.status === 'success'
                                                ? 'secondary'
                                                : run.status === 'failed'
                                                  ? 'destructive'
                                                  : 'outline'
                                        }
                                    >
                                        {run.status}
                                    </Badge>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {run.records_processed} records processed
                                    {run.started_at &&
                                        ` - started ${run.started_at}`}
                                    {run.finished_at &&
                                        ` - finished ${run.finished_at}`}
                                </p>
                                {run.error && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {run.error}
                                    </p>
                                )}
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex flex-wrap gap-2">
                    {runs.links.map((link, i) => (
                        <Link
                            key={i}
                            href={link.url ?? ''}
                            className={`rounded px-2 py-1 text-sm ${
                                link.active
                                    ? 'bg-muted font-medium'
                                    : 'text-muted-foreground'
                            } ${link.url ? '' : 'pointer-events-none opacity-50'}`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            </div>
        </>
    );
}

Runs.layout = {
    breadcrumbs: [{ title: 'Runs', href: index() }],
};
