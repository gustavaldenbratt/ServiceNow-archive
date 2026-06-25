import { Form, Head } from '@inertiajs/react';
import TableConfigController from '@/actions/App/Http/Controllers/ServiceNow/TableConfigController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/servicenow/tables';

type TableConfig = {
    id: number;
    table_name: string;
    fetch_since: string | null;
    enabled: boolean;
    last_synced_cursor: string | null;
    last_run_status: string | null;
};

export default function Tables({
    configs,
    availableTables,
    hasConnection,
    tableDiscoveryFailed,
}: {
    configs: TableConfig[];
    availableTables: string[];
    hasConnection: boolean;
    tableDiscoveryFailed: boolean;
}) {
    return (
        <>
            <Head title="ServiceNow tables" />

            <div className="space-y-8">
                <div className="space-y-4">
                    <Heading
                        variant="small"
                        title="Add a table"
                        description="Pick any table that extends task and choose how far back to fetch"
                    />

                    <Form
                        {...TableConfigController.store.form()}
                        options={{ preserveScroll: true }}
                        resetOnSuccess
                        className="flex flex-wrap items-end gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="table_name">Table</Label>
                                    {availableTables.length > 0 ? (
                                        <select
                                            id="table_name"
                                            name="table_name"
                                            defaultValue=""
                                            required
                                            className="flex h-9 min-w-48 rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                                        >
                                            <option value="" disabled>
                                                Select a table
                                            </option>
                                            {availableTables.map((table) => (
                                                <option
                                                    key={table}
                                                    value={table}
                                                >
                                                    {table}
                                                </option>
                                            ))}
                                        </select>
                                    ) : (
                                        <Input
                                            id="table_name"
                                            name="table_name"
                                            placeholder={
                                                hasConnection
                                                    ? 'incident'
                                                    : 'Configure the connection first'
                                            }
                                            disabled={!hasConnection}
                                            required
                                        />
                                    )}
                                    {hasConnection &&
                                        availableTables.length === 0 && (
                                            <p className="max-w-80 text-xs text-muted-foreground">
                                                {tableDiscoveryFailed
                                                    ? 'Table discovery failed; enter a table name manually.'
                                                    : 'No task tables were discovered; enter a table name manually.'}
                                            </p>
                                        )}
                                    <InputError message={errors.table_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="fetch_since">
                                        Fetch since
                                    </Label>
                                    <Input
                                        id="fetch_since"
                                        type="date"
                                        name="fetch_since"
                                        required
                                    />
                                    <InputError message={errors.fetch_since} />
                                </div>

                                <Button
                                    disabled={processing || !hasConnection}
                                >
                                    Add table
                                </Button>
                            </>
                        )}
                    </Form>
                </div>

                <div className="space-y-4">
                    <Heading variant="small" title="Configured tables" />

                    {configs.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No tables configured yet.
                        </p>
                    )}

                    <div className="space-y-4">
                        {configs.map((config) => (
                            <div
                                key={config.id}
                                className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                            >
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium">
                                            {config.table_name}
                                        </span>
                                        {config.last_run_status && (
                                            <Badge
                                                variant={
                                                    config.last_run_status ===
                                                    'success'
                                                        ? 'secondary'
                                                        : 'destructive'
                                                }
                                            >
                                                {config.last_run_status}
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Last synced cursor:{' '}
                                        {config.last_synced_cursor ?? 'never'}
                                    </p>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Form
                                        {...TableConfigController.update.form(
                                            config.id,
                                        )}
                                        options={{ preserveScroll: true }}
                                        className="flex flex-wrap items-center gap-3"
                                    >
                                        {({ processing }) => (
                                            <>
                                                <Input
                                                    type="date"
                                                    name="fetch_since"
                                                    defaultValue={
                                                        config.fetch_since ?? ''
                                                    }
                                                    className="w-40"
                                                />
                                                <Label className="flex items-center gap-2">
                                                    <input
                                                        type="hidden"
                                                        name="enabled"
                                                        value="0"
                                                    />
                                                    <input
                                                        type="checkbox"
                                                        name="enabled"
                                                        value="1"
                                                        defaultChecked={
                                                            config.enabled
                                                        }
                                                        className="size-4 rounded-[4px] border border-input accent-primary"
                                                    />
                                                    Enabled
                                                </Label>
                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={processing}
                                                >
                                                    Save
                                                </Button>
                                            </>
                                        )}
                                    </Form>

                                    <Form
                                        {...TableConfigController.sync.form(
                                            config.id,
                                        )}
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                size="sm"
                                                disabled={processing}
                                            >
                                                Sync now
                                            </Button>
                                        )}
                                    </Form>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}

Tables.layout = {
    breadcrumbs: [{ title: 'Tables', href: index() }],
};
