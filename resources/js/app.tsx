import { createInertiaApp, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { NavigationSkeleton } from '@/components/navigation-skeleton';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Prodenya';
const skeletonPreviewDuration = import.meta.env.DEV ? 3000 : 0;

function AppShell({ children }: { children: React.ReactNode }) {
    const [showSkeleton, setShowSkeleton] = useState(false);

    useEffect(() => {
        let timeout: number | undefined;
        let hideTimeout: number | undefined;
        let startedAt = 0;

        const removeStartListener = router.on('start', () => {
            startedAt = performance.now();
            window.clearTimeout(timeout);
            window.clearTimeout(hideTimeout);
            timeout = window.setTimeout(() => setShowSkeleton(true), 120);
        });
        const removeFinishListener = router.on('finish', () => {
            window.clearTimeout(timeout);
            const elapsed = performance.now() - startedAt;
            const remaining = Math.max(0, skeletonPreviewDuration - elapsed);

            hideTimeout = window.setTimeout(
                () => setShowSkeleton(false),
                remaining,
            );
        });

        return () => {
            window.clearTimeout(timeout);
            window.clearTimeout(hideTimeout);
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    return (
        <>
            {children}
            {showSkeleton && <NavigationSkeleton />}
            <Toaster />
        </>
    );
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('estadisticas/'):
            case name === 'mailbox/index':
            case name === 'tareas/index':
            case name === 'agenda/index':
            case name === 'auditoria/index':
            case name.startsWith('comunicacion/'):
            case name.startsWith('personas/'):
            case name.startsWith('legajos/'):
            case name.startsWith('intervenciones/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
            case name.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                <AppShell>{app}</AppShell>
            </TooltipProvider>
        );
    },
    progress: {
        color: '#9ce0bf',
        delay: 120,
        showSpinner: false,
    },
});

router.on('start', () => {
    document.documentElement.classList.add('is-navigating');
});

router.on('finish', () => {
    const root = document.documentElement;
    root.classList.remove('is-navigating', 'is-page-entering');
    requestAnimationFrame(() => {
        root.classList.add('is-page-entering');
        window.setTimeout(() => root.classList.remove('is-page-entering'), 220);
    });
});

// This will set light / dark mode on load...
initializeTheme();
