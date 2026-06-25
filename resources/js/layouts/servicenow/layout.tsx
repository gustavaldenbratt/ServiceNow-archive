import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editConnection } from '@/routes/servicenow/connection';
import { index as indexRecords } from '@/routes/servicenow/records';
import { index as indexRuns } from '@/routes/servicenow/runs';
import { index as indexTables } from '@/routes/servicenow/tables';
import type { NavItem } from '@/types';

const navItems: NavItem[] = [
    { title: 'Records', href: indexRecords(), icon: null },
    { title: 'Tables', href: indexTables(), icon: null },
    { title: 'Runs', href: indexRuns(), icon: null },
    { title: 'Connection', href: editConnection(), icon: null },
];

export default function ServiceNowLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="px-4 py-6">
            <Heading
                title="ServiceNow Archive"
                description="Browse archived records and manage the sync"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1 space-x-0"
                        aria-label="ServiceNow"
                    >
                        {navItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': isCurrentOrParentUrl(item.href),
                                })}
                            >
                                <Link href={item.href}>{item.title}</Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-4xl">
                    <section className="space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
