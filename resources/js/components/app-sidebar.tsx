import { Link } from '@inertiajs/react';
import { Database, History, Plug, Table2 } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { edit as serviceNowConnection } from '@/routes/servicenow/connection';
import { index as serviceNowRecords } from '@/routes/servicenow/records';
import { index as serviceNowRuns } from '@/routes/servicenow/runs';
import { index as serviceNowTables } from '@/routes/servicenow/tables';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Records',
        href: serviceNowRecords(),
        icon: Database,
    },
    {
        title: 'Tables',
        href: serviceNowTables(),
        icon: Table2,
    },
    {
        title: 'Runs',
        href: serviceNowRuns(),
        icon: History,
    },
    {
        title: 'Connection',
        href: serviceNowConnection(),
        icon: Plug,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={serviceNowRecords()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
