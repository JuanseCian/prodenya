import { createInertiaApp, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import {
    NavigationSkeleton,
    SaveLoadingOverlay,
} from '@/components/navigation-skeleton';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Prodenya';
type LoadingRequest = {
    kind: 'navigation' | 'mutation';
    targetUrl: string;
};

function AppShell({ children }: { children: React.ReactNode }) {
    const [loadingRequest, setLoadingRequest] = useState<LoadingRequest | null>(null);

    useEffect(() => {
        document.getElementById('initial-app-loader')?.remove();

        let timeout: number | undefined;

        const removeStartListener = router.on('start', (event) => {
            window.clearTimeout(timeout);
            const visit = event.detail.visit;
            const method = String(visit.method).toLowerCase();

            timeout = window.setTimeout(() => {
                setLoadingRequest({
                    kind: method === 'get' ? 'navigation' : 'mutation',
                    targetUrl: String(visit.url),
                });
            }, 250);
        });

        const removeFinishListener = router.on('finish', () => {
            window.clearTimeout(timeout);
            setLoadingRequest(null);
        });

        return () => {
            window.clearTimeout(timeout);
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    return (
        <>
            {children}
            {loadingRequest?.kind === 'navigation' && (
                <NavigationSkeleton targetUrl={loadingRequest.targetUrl} />
            )}
            {loadingRequest?.kind === 'mutation' && <SaveLoadingOverlay />}
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
            case name.startsWith('actas/'):
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
    progress: false,
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
