import type { InertiaLinkProps } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import {
    BarChart3,
    CalendarDays,
    CheckSquare,
    FolderOpen,
    Home,
    LogIn,
    MessageCircle,
    Settings,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import { dashboard, home, login, estadisticas } from '@/routes';
import agenda from '@/routes/agenda';
import comunicacion from '@/routes/comunicacion';
import legajos from '@/routes/legajos';
import profile from '@/routes/profile';
import tareas from '@/routes/tareas';

export type FooterAccess = {
    dashboard: boolean;
    legajos: boolean;
    agenda: boolean;
    tareas: boolean;
    comunicacion: boolean;
    estadisticas: boolean;
    profile: boolean;
};

type FooterLink = {
    href: InertiaLinkProps['href'];
    icon: LucideIcon;
    label: string;
};

type Props = {
    access?: Partial<FooterAccess>;
    authenticated?: boolean;
};

const defaultAccess: FooterAccess = {
    agenda: false,
    comunicacion: false,
    dashboard: false,
    estadisticas: false,
    legajos: false,
    profile: false,
    tareas: false,
};

function FooterLinkItem({ href, icon: Icon, label }: FooterLink) {
    return (
        <li>
            <Link
                href={href}
                className="group inline-flex items-center gap-2 text-sm text-slate-600 transition-colors hover:text-[#173247] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#5b9d7b] dark:text-slate-300 dark:hover:text-white"
            >
                <Icon className="size-4 text-[#6b8f71] transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                {label}
            </Link>
        </li>
    );
}

export function Footer02({ access, authenticated = false }: Props) {
    const enabledAccess = { ...defaultAccess, ...access };
    const systemLinks: FooterLink[] = [
        { href: home(), icon: Home, label: 'Inicio institucional' },
        ...(enabledAccess.dashboard
            ? [{ href: dashboard(), icon: BarChart3, label: 'Panel principal' }]
            : []),
        ...(enabledAccess.legajos
            ? [{ href: legajos.index(), icon: FolderOpen, label: 'Legajos' }]
            : []),
    ];
    const workLinks: FooterLink[] = [
        ...(enabledAccess.agenda
            ? [{ href: agenda.index(), icon: CalendarDays, label: 'Agenda' }]
            : []),
        ...(enabledAccess.tareas
            ? [{ href: tareas.index(), icon: CheckSquare, label: 'Tareas' }]
            : []),
        ...(enabledAccess.comunicacion
            ? [
                  {
                      href: comunicacion.index(),
                      icon: MessageCircle,
                      label: 'Comunicación',
                  },
              ]
            : []),
        ...(enabledAccess.estadisticas
            ? [{ href: estadisticas(), icon: BarChart3, label: 'Estadísticas' }]
            : []),
    ];
    const accountLinks: FooterLink[] = enabledAccess.profile
        ? [{ href: profile.edit(), icon: Settings, label: 'Configuración' }]
        : !authenticated
          ? [{ href: login(), icon: LogIn, label: 'Ingresar' }]
          : [];

    return (
        <footer className="rounded-t-[2rem] border-t border-[#dbe9e1] bg-[#f7fbf9] pb-[calc(78px+env(safe-area-inset-bottom))] text-slate-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 lg:pb-0">
            <div className="mx-auto grid w-full max-w-7xl gap-10 px-5 py-12 sm:px-8 lg:grid-cols-[1.35fr_1fr_1fr_1fr] lg:gap-12 lg:px-10 lg:py-16">
                <div className="space-y-5">
                    <Link
                        href={home()}
                        className="inline-flex items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#5b9d7b]"
                    >
                        <img
                            src="/assets/img/SN.png"
                            alt="San Nicolás"
                            className="size-12 rounded-xl object-contain"
                        />
                        <span>
                            <span className="block text-base font-bold tracking-[0.16em] text-[#173247] dark:text-white">
                                PRODENYA
                            </span>
                            <span className="mt-1 block text-xs text-slate-500 dark:text-slate-400">
                                Promoción y Protección de Derechos
                            </span>
                        </span>
                    </Link>
                    <p className="max-w-sm text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Sistema institucional para acompañar la promoción y
                        protección integral de los derechos de niñas, niños y
                        adolescentes.
                    </p>
                    <p className="text-xs font-medium uppercase tracking-[0.14em] text-[#6b8f71]">
                        Municipalidad de San Nicolás de los Arroyos
                    </p>
                </div>

                <div>
                    <h2 className="text-sm font-semibold text-[#173247] dark:text-white">
                        Sistema
                    </h2>
                    <ul className="mt-4 grid gap-3">
                        {systemLinks.map((link) => (
                            <FooterLinkItem key={link.label} {...link} />
                        ))}
                    </ul>
                </div>

                {workLinks.length > 0 && (
                    <div>
                        <h2 className="text-sm font-semibold text-[#173247] dark:text-white">
                            Gestión
                        </h2>
                        <ul className="mt-4 grid gap-3">
                            {workLinks.map((link) => (
                                <FooterLinkItem key={link.label} {...link} />
                            ))}
                        </ul>
                    </div>
                )}

                {accountLinks.length > 0 && (
                    <div>
                        <h2 className="text-sm font-semibold text-[#173247] dark:text-white">
                            Cuenta
                        </h2>
                        <ul className="mt-4 grid gap-3">
                            {accountLinks.map((link) => (
                                <FooterLinkItem key={link.label} {...link} />
                            ))}
                        </ul>
                    </div>
                )}
            </div>
            <div className="border-t border-[#dbe9e1] dark:border-slate-800">
                <div
                    className={cn(
                        'mx-auto flex w-full max-w-7xl flex-col gap-3 px-5 py-5 text-xs text-slate-500 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10',
                    )}
                >
                    <span>© {new Date().getFullYear()} PRODENYA</span>
                    <span>Uso institucional · San Nicolás de los Arroyos</span>
                </div>
            </div>
        </footer>
    );
}
