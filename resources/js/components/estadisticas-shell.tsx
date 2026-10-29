import { Link, router } from '@inertiajs/react';
import {
    BarChart3,
    ChevronRight,
    ClipboardCheck,
    Download,
    FolderOpen,
    HeartPulse,
    LayoutDashboard,
    Menu,
    PanelLeftClose,
    PanelLeftOpen,
    Moon,
    Sun,
    TriangleAlert,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';

import type { ReactNode } from 'react';

import { useAppearance } from '@/hooks/use-appearance';
import { estadisticas as estadisticasRoute } from '@/routes';
import estadisticas from '@/routes/estadisticas';

export type EstadisticasModulo =
    | 'resumen'
    | 'legajos'
    | 'problematicas'
    | 'intervenciones'
    | 'tareas'
    | 'agenda';

const modulos: Array<{
    key: EstadisticasModulo;
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
}> = [
    {
        key: 'resumen',
        label: 'Resumen',
        href: estadisticasRoute().url,
        icon: LayoutDashboard,
    },
    {
        key: 'legajos',
        label: 'Legajos',
        href: estadisticas.legajos.url(),
        icon: FolderOpen,
    },
    {
        key: 'problematicas',
        label: 'Problemáticas',
        href: estadisticas.problematicas.url(),
        icon: TriangleAlert,
    },
    {
        key: 'intervenciones',
        label: 'Intervenciones',
        href: estadisticas.intervenciones.url(),
        icon: HeartPulse,
    },
    {
        key: 'tareas',
        label: 'Tareas',
        href: estadisticas.tareas.url(),
        icon: ClipboardCheck,
    },
    {
        key: 'agenda',
        label: 'Agenda',
        href: estadisticas.agenda.url(),
        icon: LayoutDashboard,
    },
];

export default function EstadisticasShell({
    active,
    title,
    subtitle,
    modulosDisponibles,
    children,
}: {
    active: EstadisticasModulo;
    title: string;
    subtitle: string;
    modulosDisponibles?: EstadisticasModulo[];
    children: ReactNode;
}) {
    const [collapsed, setCollapsed] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    useEffect(() => {
        const refresh = window.setInterval(() => {
            router.reload({ preserveUrl: true });
        }, 60_000);

        return () => window.clearInterval(refresh);
    }, []);

    return (
        <div className="stats-module-shell min-h-screen bg-[#F7F8FA] text-[#243140] dark:bg-[#1B2028] dark:text-[#EDF1F5]">
            <div
                className={`fixed inset-0 z-30 bg-[#243140]/35 lg:hidden dark:bg-black/55 ${mobileOpen ? 'opacity-100' : 'pointer-events-none opacity-0'}`}
                onClick={() => setMobileOpen(false)}
                aria-hidden="true"
            />

            <aside
                className={`fixed inset-y-0 left-0 z-40 flex flex-col border-r border-[#DFE4EA] bg-white transition-[width,transform] duration-300 ease-out dark:border-[#3B4552] dark:bg-[#20262F] ${collapsed ? 'lg:w-[82px]' : 'lg:w-[260px]'} ${mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'} w-[260px]`}
            >
                <div className="flex items-center justify-between border-b border-[#DFE4EA] px-4 py-5 dark:border-[#3B4552]">
                    <Link
                        href={estadisticasRoute().url}
                        className={`flex min-w-0 items-center gap-3 ${collapsed ? 'lg:mx-auto' : ''}`}
                        onClick={() => setMobileOpen(false)}
                    >
                        <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-[#EEECF7] text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]">
                            <BarChart3 size={21} strokeWidth={1.8} />
                        </span>
                        <span className={`${collapsed ? 'lg:hidden' : ''}`}>
                            <strong className="block text-sm font-bold tracking-[0.08em] text-[#173247] uppercase dark:text-[#EDF6FB]">
                                Estadísticas
                            </strong>
                        </span>
                    </Link>
                    <button
                        type="button"
                        className="grid size-8 place-items-center rounded-md text-[#697586] transition hover:bg-[#F1F3F5] hover:text-[#5B559E] lg:hidden dark:text-[#ADB7C3] dark:hover:bg-[#323845] dark:hover:text-[#AAA4E5]"
                        onClick={() => setMobileOpen(false)}
                        aria-label="Cerrar menú de estadísticas"
                    >
                        <X size={18} />
                    </button>
                </div>

                <div className={`px-3 pt-6 ${collapsed ? 'lg:px-2' : ''}`}>
                    <p
                        className={`px-3 text-[0.65rem] font-bold tracking-[0.14em] text-[#697586] uppercase dark:text-[#ADB7C3] ${collapsed ? 'lg:hidden' : ''}`}
                    >
                        Módulos
                    </p>
                    <nav
                        className="mt-3 space-y-1.5"
                        aria-label="Módulos de estadísticas"
                    >
                        {modulos
                            .filter(
                                (modulo) =>
                                    !modulosDisponibles ||
                                    modulosDisponibles.includes(modulo.key),
                            )
                            .map((modulo) => {
                            const Icon = modulo.icon;
                            const isActive = active === modulo.key;

                            return (
                                <Link
                                    key={modulo.key}
                                    href={modulo.href}
                                    onClick={() => setMobileOpen(false)}
                                    className={`group relative flex items-center gap-3 rounded-lg px-3 py-2.5 transition ${collapsed ? 'lg:justify-center lg:px-0' : ''} ${isActive ? 'bg-[#EEECF7] text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]' : 'text-[#697586] hover:bg-[#F1F3F5] hover:text-[#5B559E] dark:text-[#ADB7C3] dark:hover:bg-[#323845] dark:hover:text-[#AAA4E5]'}`}
                                    title={collapsed ? modulo.label : undefined}
                                >
                                    <span
                                        className={`grid size-8 shrink-0 place-items-center rounded-md transition ${isActive ? 'bg-[#DDD9F1] text-[#5B559E] dark:bg-[#4A456C] dark:text-[#AAA4E5]' : 'bg-[#F1F3F5] text-[#697586] group-hover:bg-[#EEECF7] group-hover:text-[#5B559E] dark:bg-[#323845] dark:text-[#ADB7C3] dark:group-hover:bg-[#4A456C] dark:group-hover:text-[#AAA4E5]'}`}
                                    >
                                        <Icon size={18} strokeWidth={1.8} />
                                    </span>
                                    <span
                                        className={`min-w-0 ${collapsed ? 'lg:hidden' : ''}`}
                                    >
                                        <strong className="block truncate text-sm font-semibold">
                                            {modulo.label}
                                        </strong>
                                    </span>
                                    {isActive && !collapsed && (
                                        <ChevronRight
                                            className="ml-auto shrink-0"
                                            size={15}
                                        />
                                    )}
                                </Link>
                            );
                            })}
                    </nav>
                </div>

                <div className="mt-auto space-y-3 border-t border-[#DFE4EA] p-3 dark:border-[#3B4552]">
                    <Link
                        href="/legajos"
                        className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-[#697586] transition hover:bg-[#F1F3F5] hover:text-[#5B559E] dark:text-[#ADB7C3] dark:hover:bg-[#323845] dark:hover:text-[#AAA4E5] ${collapsed ? 'lg:justify-center lg:px-0' : ''}`}
                        title={collapsed ? 'Volver a legajos' : undefined}
                    >
                        <FolderOpen size={19} strokeWidth={1.8} />
                        <span className={`${collapsed ? 'lg:hidden' : ''}`}>
                            Volver a Legajos
                        </span>
                    </Link>
                    <button
                        type="button"
                        onClick={() => setCollapsed((value) => !value)}
                        className="hidden w-full items-center justify-center gap-2 rounded-md border border-[#DFE4EA] bg-[#F7F8FA] px-3 py-2.5 text-xs font-semibold text-[#697586] transition hover:border-[#AAA4E5] hover:text-[#5B559E] lg:flex dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#ADB7C3] dark:hover:border-[#625CAA] dark:hover:text-[#AAA4E5]"
                        aria-label={
                            collapsed ? 'Expandir panel' : 'Plegar panel'
                        }
                    >
                        {collapsed ? (
                            <PanelLeftOpen size={16} />
                        ) : (
                            <PanelLeftClose size={16} />
                        )}
                        <span className={collapsed ? 'lg:hidden' : ''}>
                            {collapsed ? 'Expandir' : 'Plegar panel'}
                        </span>
                    </button>
                </div>
            </aside>

            <div
                className={`min-h-screen transition-[margin] duration-300 ${collapsed ? 'lg:ml-[82px]' : 'lg:ml-[260px]'}`}
            >
                <header className="sticky top-0 z-20 border-b border-[#DFE4EA] bg-white dark:border-[#3B4552] dark:bg-[#20262F]">
                    <div className="flex items-center justify-between gap-4 px-4 py-4 sm:px-7 lg:px-10">
                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                className="grid size-10 place-items-center rounded-md border border-[#DFE4EA] bg-white text-[#5B559E] lg:hidden dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#AAA4E5]"
                                onClick={() => setMobileOpen(true)}
                                aria-label="Abrir menú de estadísticas"
                            >
                                <Menu size={19} />
                            </button>
                            <div>
                                <h1 className="mt-0.5 text-xl font-semibold tracking-tight text-[#173247] sm:text-2xl dark:text-[#EDF6FB]">
                                    {title}
                                </h1>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <a
                                href={`/estadisticas/exportar${window.location.search}`}
                                className="hidden size-10 place-items-center rounded-md border border-[#DFE4EA] bg-white text-[#5B559E] transition hover:border-[#AAA4E5] sm:grid dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#AAA4E5]"
                                aria-label="Exportar estadísticas en CSV"
                                title="Exportar CSV"
                            >
                                <Download size={17} />
                            </a>
                            <button
                                type="button"
                                onClick={() => window.print()}
                                className="hidden size-10 place-items-center rounded-md border border-[#DFE4EA] bg-white text-[#5B559E] transition hover:border-[#AAA4E5] sm:grid dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#AAA4E5]"
                                aria-label="Imprimir estadísticas"
                                title="Vista imprimible / PDF"
                            >
                                <span className="text-sm font-bold">PDF</span>
                            </button>
                            <button
                                type="button"
                                onClick={() =>
                                    updateAppearance(isDark ? 'light' : 'dark')
                                }
                                className="grid size-10 place-items-center rounded-md border border-[#DFE4EA] bg-white text-[#5B559E] transition hover:border-[#AAA4E5] dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#AAA4E5]"
                                aria-label={
                                    isDark
                                        ? 'Activar modo claro'
                                        : 'Activar modo oscuro'
                                }
                                title={isDark ? 'Modo claro' : 'Modo oscuro'}
                            >
                                {isDark ? (
                                    <Sun size={17} />
                                ) : (
                                    <Moon size={17} />
                                )}
                            </button>
                            <p className="hidden max-w-sm text-right text-sm text-[#2C4A5E]/60 sm:block dark:text-[#B7C9D4]">
                                {subtitle}
                            </p>
                        </div>
                    </div>
                </header>

                <main className="pb-[calc(6rem+env(safe-area-inset-bottom))] lg:pb-0">
                    {children}
                </main>
            </div>
        </div>
    );
}
