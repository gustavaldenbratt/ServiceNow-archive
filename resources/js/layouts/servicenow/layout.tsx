import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';

export default function ServiceNowLayout({ children }: PropsWithChildren) {
    return (
        <div className="px-4 py-6">
            <Heading
                title="ServiceNow Archive"
                description="Browse archived records and manage the sync"
            />

            <section className="max-w-5xl space-y-12">{children}</section>
        </div>
    );
}
