import { Link } from '@inertiajs/react';
import {
    ChevronRight,
    ClipboardCheck,
    Download,
    FolderOpen,
    HeartPulse,
    House,
    LayoutDashboard,
    Menu,
    Moon,
    Sun,
    TriangleAlert,
    X,
} from 'lucide-react';
import { useState } from 'react';

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
        label: 'Resumen institucional',
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
    modulosDisponibles,
    children,
}: {
    active: EstadisticasModulo;
    title: string;
    modulosDisponibles?: EstadisticasModulo[];
    children: ReactNode;
}) {
    const [collapsed, setCollapsed] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    const visibleModules = modulos.filter(
        (modulo) =>
            !modulosDisponibles || modulosDisponibles.includes(modulo.key),
    );

    return (
        <div className="stats-module-shell min-h-screen bg-background text-foreground">
            <div
                className={`fixed inset-0 z-30 bg-[#173247]/45 transition-opacity lg:hidden dark:bg-black/60 ${mobileOpen ? 'opacity-100' : 'pointer-events-none opacity-0'}`}
                onClick={() => setMobileOpen(false)}
                aria-hidden="true"
            />

            <aside
                className={`fixed inset-y-0 left-0 z-40 flex flex-col border-r border-white/10 bg-brand-strong transition-[width,transform] duration-300 ease-out ${collapsed ? 'lg:w-20' : 'lg:w-60'} ${mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'} w-72`}
            >
                <div className="flex items-center justify-between border-b border-white/10 px-4 py-5">
                    <button
                        type="button"
                        className={`group flex min-w-0 items-center gap-3 rounded-md text-left outline-none ${collapsed ? 'lg:mx-auto' : ''}`}
                        onClick={() => {
                            if (
                                window.matchMedia('(min-width: 1024px)').matches
                            ) {
                                setCollapsed((value) => !value);
                            }

                            setMobileOpen(false);
                        }}
                        aria-label={
                            collapsed
                                ? 'Expandir navegación'
                                : 'Contraer navegación'
                        }
                        aria-expanded={!collapsed}
                    >
                        <span className="grid size-10 shrink-0 place-items-center rounded-md bg-[#CDE8D6] p-1 shadow-[0_5px_18px_rgba(0,0,0,0.12)] transition group-hover:bg-white">
                            <img
                                src="/assets/img/SN.png"
                                alt=""
                                className="size-full rounded object-contain"
                            />
                        </span>
                        <span className={`${collapsed ? 'lg:hidden' : ''}`}>
                            <strong className="block text-sm font-bold tracking-[0.08em] text-white uppercase">
                                PRODENYA
                            </strong>
                            <span className="mt-0.5 block text-xs text-white/55">
                                Estadísticas
                            </span>
                        </span>
                    </button>
                    <button
                        type="button"
                        className="grid size-8 place-items-center rounded-md text-white/65 transition hover:bg-white/10 hover:text-white lg:hidden"
                        onClick={() => setMobileOpen(false)}
                        aria-label="Cerrar menú de estadísticas"
                    >
                        <X size={18} />
                    </button>
                </div>

                <div
                    className={`flex-1 px-3 pt-6 ${collapsed ? 'lg:px-2' : ''}`}
                >
                    <p
                        className={`px-3 text-[0.65rem] font-bold tracking-[0.14em] text-[#B9D5E1]/60 uppercase ${collapsed ? 'lg:hidden' : ''}`}
                    >
                        Análisis
                    </p>
                    <nav
                        className="mt-3 space-y-1.5"
                        aria-label="Módulos de estadísticas"
                    >
                        {visibleModules.map((modulo) => {
                            const Icon = modulo.icon;
                            const isActive = active === modulo.key;

                            return (
                                <Link
                                    key={modulo.key}
                                    href={modulo.href}
                                    onClick={() => setMobileOpen(false)}
                                    className={`group relative flex items-center gap-3 rounded-lg px-3 py-2.5 transition ${collapsed ? 'lg:justify-center lg:px-0' : ''} ${isActive ? 'bg-secondary text-secondary-foreground' : 'text-white/68 hover:bg-white/10 hover:text-white'}`}
                                    title={collapsed ? modulo.label : undefined}
                                >
                                    <span
                                        className={`grid size-8 shrink-0 place-items-center rounded-md transition ${isActive ? 'bg-card/70 text-primary' : 'bg-white/8 text-white/70 group-hover:bg-white/15 group-hover:text-white'}`}
                                    >
                                        <Icon size={17} strokeWidth={1.8} />
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

                <div className="border-t border-white/10 p-3">
                    <a
                        href="/"
                        onClick={() => setMobileOpen(false)}
                        className={`flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-white/70 transition hover:bg-white/10 hover:text-white ${collapsed ? 'lg:justify-center lg:px-0' : ''}`}
                        title={collapsed ? 'Inicio' : undefined}
                    >
                        <House size={18} strokeWidth={1.8} />
                        <span className={`${collapsed ? 'lg:hidden' : ''}`}>
                            Inicio
                        </span>
                    </a>
                </div>
            </aside>

            <div
                className={`min-h-screen transition-[margin] duration-300 ${collapsed ? 'lg:ml-20' : 'lg:ml-60'}`}
            >
                <header className="sticky top-0 z-20 border-b border-border bg-background/95 backdrop-blur">
                    <div className="flex min-h-16 items-center justify-between gap-4 px-4 py-3 sm:px-7 lg:px-10">
                        <div className="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                className="grid size-10 shrink-0 place-items-center rounded-lg border border-border bg-card text-foreground lg:hidden"
                                onClick={() => setMobileOpen(true)}
                                aria-label="Abrir menú de estadísticas"
                            >
                                <Menu size={19} />
                            </button>
                            <div className="min-w-0">
                                <h1 className="truncate text-xl font-semibold tracking-tight text-foreground sm:text-2xl">
                                    {title}
                                </h1>
                            </div>
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                            <a
                                href={`/estadisticas/exportar${window.location.search}`}
                                className="hidden size-10 place-items-center rounded-md border border-[#DCE3E6] bg-white text-[#2C4A5E] transition hover:border-[#7EAD8B] hover:bg-[#F2F8F3] sm:grid dark:border-[#29404D] dark:bg-[#1D303B] dark:text-[#B9D5E1]"
                                aria-label="Exportar estadísticas en CSV"
                                title="Exportar CSV"
                            >
                                <Download size={17} />
                            </a>
                            <button
                                type="button"
                                onClick={() => window.print()}
                                className="hidden h-10 items-center justify-center rounded-md border border-[#DCE3E6] bg-white px-3 text-xs font-bold text-[#2C4A5E] transition hover:border-[#7EAD8B] hover:bg-[#F2F8F3] sm:flex dark:border-[#29404D] dark:bg-[#1D303B] dark:text-[#B9D5E1]"
                                aria-label="Imprimir estadísticas"
                                title="Vista imprimible / PDF"
                            >
                                PDF
                            </button>
                            <button
                                type="button"
                                onClick={() =>
                                    updateAppearance(isDark ? 'light' : 'dark')
                                }
                                className="grid size-10 place-items-center rounded-md border border-[#DCE3E6] bg-white text-[#2C4A5E] transition hover:border-[#7EAD8B] dark:border-[#29404D] dark:bg-[#1D303B] dark:text-[#B9D5E1]"
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
