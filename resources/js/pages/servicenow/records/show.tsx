import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { show as showAttachment } from '@/routes/servicenow/attachments';
import { index, show } from '@/routes/servicenow/records';

type FieldValue =
    | { value: string | null; display_value?: string | null }
    | string
    | number
    | boolean
    | null;

type SnRecordProps = {
    id: number;
    table_name: string;
    number: string | null;
    sys_updated_at: string | null;
    data: globalThis.Record<string, FieldValue> | unknown;
};

type JournalEntry = {
    id: number;
    element: string;
    value: string | null;
    sys_created_by: string | null;
    sys_created_at: string | null;
};

type Attachment = {
    id: number;
    file_name: string;
    content_type: string | null;
};

function fieldLabel(field: FieldValue): string {
    if (field === null) {
        return '-';
    }

    if (typeof field === 'string') {
        return field;
    }

    if (typeof field === 'number' || typeof field === 'boolean') {
        return String(field);
    }

    return field.display_value ?? field.value ?? '-';
}

export default function RecordShow({
    record,
    journalEntries,
    attachments,
}: {
    record: SnRecordProps;
    journalEntries: JournalEntry[];
    attachments: Attachment[];
}) {
    const fields = Object.entries(
        (record.data ?? {}) as globalThis.Record<string, FieldValue>,
    );

    return (
        <>
            <Head title={record.number ?? `Record #${record.id}`} />

            <div className="space-y-8">
                <div className="flex flex-wrap items-center gap-3">
                    <Heading
                        variant="small"
                        title={record.number ?? `Record #${record.id}`}
                    />
                    <Badge variant="outline">{record.table_name}</Badge>
                </div>

                <div className="space-y-3">
                    <Heading variant="small" title="Fields" />
                    <dl className="grid grid-cols-1 gap-3 md:grid-cols-2">
                        {fields.map(([field, value]) => (
                            <div key={field} className="min-w-0 space-y-0.5">
                                <dt className="text-xs font-medium break-words text-muted-foreground">
                                    {field}
                                </dt>
                                <dd className="text-sm break-words">
                                    {fieldLabel(value)}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </div>

                <div className="space-y-3">
                    <Heading variant="small" title="Journal" />
                    {journalEntries.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No comments or work notes.
                        </p>
                    )}
                    <div className="space-y-3">
                        {journalEntries.map((entry) => (
                            <div
                                key={entry.id}
                                className="rounded-lg border p-3"
                            >
                                <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <Badge variant="outline">
                                        {entry.element}
                                    </Badge>
                                    <span>{entry.sys_created_by}</span>
                                    <span>{entry.sys_created_at}</span>
                                </div>
                                <p className="mt-1 whitespace-pre-wrap text-sm">
                                    {entry.value}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="space-y-3">
                    <Heading variant="small" title="Attachments" />
                    {attachments.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No attachments.
                        </p>
                    )}
                    <div className="flex flex-wrap gap-4">
                        {attachments.map((attachment) =>
                            attachment.content_type?.startsWith('image/') ? (
                                <a
                                    key={attachment.id}
                                    href={showAttachment(attachment.id).url}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <img
                                        src={showAttachment(attachment.id).url}
                                        alt={attachment.file_name}
                                        className="h-32 w-32 rounded-lg border object-cover"
                                    />
                                </a>
                            ) : (
                                <a
                                    key={attachment.id}
                                    href={showAttachment(attachment.id).url}
                                    className="rounded-lg border px-3 py-2 text-sm underline"
                                >
                                    {attachment.file_name}
                                </a>
                            ),
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

RecordShow.layout = {
    breadcrumbs: [
        { title: 'Records', href: index() },
        { title: 'Record', href: show(0) },
    ],
};
