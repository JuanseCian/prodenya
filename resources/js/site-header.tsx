import {
    BarChart3,
    CalendarDays,
    CheckSquare,
    FolderOpen,
    Home,
    MessageSquare,
    ShieldCheck,
} from 'lucide-react';
import { createRoot } from 'react-dom/client';
import { NotificationCenter } from '@/components/notification-center';
import { Toaster } from '@/components/ui/sonner';
import { LiquidNavbar } from '@/components/ui/liquid-glass-navbar';
import type {
    LiquidNavItem,
    NimbusFx,
} from '@/components/ui/liquid-glass-navbar';
import type { NotificationItem } from '@/types';

const iconMap = {
    agenda: CalendarDays,
    admin: ShieldCheck,
    estadisticas: BarChart3,
    home: Home,
    legajos: FolderOpen,
    mensajes: MessageSquare,
    tareas: CheckSquare,
};

type HeaderNavItem = {
    label: string;
    href: string;
    icon: keyof typeof iconMap;
    badge?: string | number;
    fx?: NimbusFx;
};

const mount = document.querySelector<HTMLElement>(
    '[data-liquid-header-navbar]',
);

if (mount) {
    const rawItems = mount.dataset.items;

    try {
        const headerItems = JSON.parse(rawItems ?? '[]') as HeaderNavItem[];
        const items: LiquidNavItem[] = headerItems.map((item) => {
            const Icon = iconMap[item.icon] ?? Home;

            return {
                ...item,
                icon: <Icon size={18} strokeWidth={1.8} />,
            };
        });
        const active = Number(mount.dataset.activeIndex ?? 0);

        createRoot(mount).render(
            <LiquidNavbar
                items={items}
                active={Number.isNaN(active) ? 0 : active}
                compact={false}
                tone="dark"
            />,
        );
    } catch {
        mount.remove();
    }
}

const notificationMount = document.querySelector<HTMLElement>(
    '[data-site-notification-center]',
);

if (notificationMount) {
    try {
        const notifications = JSON.parse(
            notificationMount.dataset.notifications ?? '[]',
        ) as NotificationItem[];
        const unreadCount = Number(
            notificationMount.dataset.notificationsUnread ?? 0,
        );
        const standalone = notificationMount.dataset.mode !== 'inertia';

        createRoot(notificationMount).render(
            <>
                <NotificationCenter
                    initialNotifications={notifications}
                    initialUnreadCount={unreadCount}
                    standalone={standalone}
                    endpoint={notificationMount.dataset.endpoint}
                    placement="header"
                />
                {standalone && <Toaster />}
            </>,
        );
    } catch {
        notificationMount.remove();
    }
}
