import { createInertiaApp, router } from '@inertiajs/react';
import { NavigationFeedback } from '@/components/navigation-feedback';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Prodenya';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name === 'dashboard':
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
                <div className="prodenya-page-transition-shell">{app}</div>
                <Toaster />
                <NavigationFeedback />
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
