import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    CalendarDays,
    ClipboardCheck,
    FolderOpen,
    Home,
    LayoutGrid,
    Mail,
    UserRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TeamSwitcher } from '@/components/team-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, estadisticas as estadisticasRoute } from '@/routes';
import agenda from '@/routes/agenda';
import mailbox from '@/routes/mailbox';
import tareas from '@/routes/tareas';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const page = usePage();
    const dashboardUrl = page.props.currentTeam
        ? dashboard().url
        : '/';

    const mainNavItems: NavItem[] = [
        {
            title: 'Panel principal',
            href: dashboardUrl,
            icon: LayoutGrid,
        },
        {
            title: 'Consultar legajos',
            href: '/legajos',
            icon: FolderOpen,
        },
        ...(page.props.can?.accessStatistics
            ? [{ title: 'Estadísticas', href: estadisticasRoute().url, icon: BarChart3 }]
            : []),
        ...(page.props.can?.accessTasks
            ? [{ title: 'Tareas', href: tareas.index().url, icon: ClipboardCheck }]
            : []),
        ...(page.props.can?.accessAgenda
            ? [{ title: 'Agenda', href: agenda.index().url, icon: CalendarDays }]
            : []),
        ...(page.props.can?.accessMailbox
            ? [{ title: 'Mailbox', href: mailbox.index().url, icon: Mail }]
            : []),
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Inicio institucional',
            href: '/',
            icon: Home,
        },
        {
            title: 'Mi cuenta',
            href: '/settings/profile',
            icon: UserRound,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardUrl} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <TeamSwitcher />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
