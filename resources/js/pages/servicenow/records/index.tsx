import { Form, Head, Link } from '@inertiajs/react';
import RecordController from '@/actions/App/Http/Controllers/ServiceNow/RecordController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, show } from '@/routes/servicenow/records';

type SnRecordRow = {
    id: number;
    table_name: string;
    number: string | null;
    short_description: string | null;
    state: string | null;
    assigned_to: string | null;
    sys_updated_at: string | null;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

type Filters = {
    table_name?: string;
    number?: string;
    state?: string;
    from?: string;
    to?: string;
};

export default function RecordsIndex({
    records,
    filters,
    tableNames,
}: {
    records: Paginated<SnRecordRow>;
    filters: Filters;
    tableNames: string[];
}) {
    return (
        <>
            <Head title="ServiceNow records" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Records"
                    description="Browse archived ServiceNow records"
                />

                <Form
                    {...RecordController.index.form()}
                    options={{ preserveState: true, preserveScroll: true }}
                    className="flex flex-wrap items-end gap-4"
                >
                    {({ processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="table_name">Table</Label>
                                <select
                                    id="table_name"
                                    name="table_name"
                                    defaultValue={filters.table_name ?? ''}
                                    className="flex h-9 min-w-40 rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                                >
                                    <option value="">All tables</option>
                                    {tableNames.map((name) => (
                                        <option key={name} value={name}>
                                            {name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="number">Number</Label>
                                <Input
                                    id="number"
                                    name="number"
                                    defaultValue={filters.number ?? ''}
                                    placeholder="INC0012345"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="state">State</Label>
                                <Input
                                    id="state"
                                    name="state"
                                    defaultValue={filters.state ?? ''}
                                    placeholder="Closed"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="from">From</Label>
                                <Input
                                    id="from"
                                    type="date"
                                    name="from"
                                    defaultValue={filters.from ?? ''}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="to">To</Label>
                                <Input
                                    id="to"
                                    type="date"
                                    name="to"
                                    defaultValue={filters.to ?? ''}
                                />
                            </div>

                            <Button disabled={processing}>Filter</Button>
                        </>
                    )}
                </Form>

                <div className="space-y-2">
                    {records.data.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No records match these filters.
                        </p>
                    )}

                    {records.data.map((record) => (
                        <Link
                            key={record.id}
                            href={show(record.id)}
                            className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4 hover:bg-muted/50"
                        >
                            <div className="min-w-0 space-y-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="font-medium">
                                        {record.number ?? `#${record.id}`}
                                    </span>
                                    <Badge variant="outline">
                                        {record.table_name}
                                    </Badge>
                                    {record.state && (
                                        <Badge variant="secondary">
                                            {record.state}
                                        </Badge>
                                    )}
                                </div>
                                <p className="text-sm break-words text-muted-foreground">
                                    {record.short_description}
                                </p>
                            </div>
                            <div className="text-sm text-muted-foreground">
                                {record.sys_updated_at}
                            </div>
                        </Link>
                    ))}
                </div>

                <div className="flex flex-wrap gap-2">
                    {records.links.map((link, index) => (
                        <Link
                            key={index}
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

RecordsIndex.layout = {
    breadcrumbs: [{ title: 'Records', href: index() }],
};
