import { Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    KeyRound,
    Palette,
    UserRound,
    UsersRound,
} from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as teamsIndex } from '@/routes/teams';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Cuenta',
        href: edit(),
        icon: UserRound,
    },
    {
        title: 'Seguridad',
        href: editSecurity(),
        icon: KeyRound,
    },
    {
        title: 'Preferencias',
        href: editAppearance(),
        icon: Palette,
    },
    {
        title: 'Equipos',
        href: teamsIndex(),
        icon: UsersRound,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="account-settings-shell min-h-full bg-background px-4 py-6 text-foreground sm:px-6 sm:py-9 lg:px-10 lg:py-12">
            <div className="mx-auto max-w-[1280px]">
                <header className="mb-8 flex flex-col gap-5 border-b border-border/70 pb-8 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-secondary px-3 py-1.5 text-[length:calc(0.65rem_+_2px)] font-bold tracking-[0.15em] text-secondary-foreground uppercase">
                            <span className="size-1.5 rounded-full bg-primary" />
                            Centro de cuenta
                        </div>
                        <h1 className="text-3xl font-semibold tracking-[-0.04em] text-foreground sm:text-4xl">
                            Tu espacio personal
                        </h1>
                        <p className="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground sm:text-base">
                            Mantené actualizada tu identidad, protegé tus
                            accesos y elegí cómo querés trabajar en Prodenya.
                        </p>
                    </div>
                    <div className="hidden items-center gap-2 text-right text-xs text-muted-foreground sm:flex">
                        <span className="size-2 rounded-full bg-success" />
                        Cuenta protegida
                    </div>
                </header>

                <div className="mb-7 overflow-x-auto lg:hidden">
                    <nav
                        className="flex min-w-max gap-2 rounded-2xl border border-border bg-card p-2 shadow-sm"
                        aria-label="Secciones de mi cuenta"
                    >
                        {sidebarNavItems.map((item) => {
                            const isActive = isCurrentOrParentUrl(item.href);
                            const Icon = item.icon;

                            return (
                                <Link
                                    key={toUrl(item.href)}
                                    href={item.href}
                                    aria-current={isActive ? 'page' : undefined}
                                    className={cn(
                                        'inline-flex h-10 items-center gap-2 rounded-xl px-3.5 text-sm font-semibold text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground',
                                        isActive &&
                                            'bg-primary text-primary-foreground shadow-sm hover:bg-primary hover:text-primary-foreground',
                                    )}
                                >
                                    {Icon && <Icon className="size-4" />}
                                    {item.title}
                                </Link>
                            );
                        })}
                    </nav>
                </div>

                <div className="grid gap-8 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-10">
                    <aside className="hidden lg:block">
                        <div className="sticky top-24 space-y-5">
                            <div className="rounded-[1.35rem] border border-border bg-card p-2 shadow-[0_18px_50px_-34px_rgba(32,56,70,0.45)]">
                                <div className="px-3 pt-3 pb-4">
                                    <p className="text-sm font-semibold text-card-foreground">
                                        Navegación de cuenta
                                    </p>
                                    <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                        Todo lo que necesitás para trabajar con
                                        tranquilidad.
                                    </p>
                                </div>
                                <nav
                                    className="space-y-1"
                                    aria-label="Secciones de mi cuenta"
                                >
                                    {sidebarNavItems.map((item) => {
                                        const isActive = isCurrentOrParentUrl(
                                            item.href,
                                        );
                                        const Icon = item.icon;

                                        return (
                                            <Link
                                                key={toUrl(item.href)}
                                                href={item.href}
                                                aria-current={
                                                    isActive
                                                        ? 'page'
                                                        : undefined
                                                }
                                                className={cn(
                                                    'group flex min-h-12 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-muted-foreground transition-[background-color,color,transform] hover:translate-x-0.5 hover:bg-accent hover:text-accent-foreground',
                                                    isActive &&
                                                        'bg-primary text-primary-foreground shadow-sm hover:bg-primary hover:text-primary-foreground',
                                                )}
                                            >
                                                <span
                                                    className={cn(
                                                        'flex size-8 items-center justify-center rounded-lg bg-secondary text-primary transition-colors',
                                                        isActive &&
                                                            'bg-white/15 text-primary-foreground',
                                                    )}
                                                >
                                                    {Icon && (
                                                        <Icon className="size-4" />
                                                    )}
                                                </span>
                                                {item.title}
                                                <ArrowUpRight className="ml-auto size-3.5 opacity-0 transition-opacity group-hover:opacity-70" />
                                            </Link>
                                        );
                                    })}
                                </nav>
                            </div>
                            <div className="rounded-[1.35rem] border border-primary/15 bg-secondary/60 p-4">
                                <p className="text-sm font-semibold text-secondary-foreground">
                                    Una cuenta, un solo lugar
                                </p>
                                <p className="mt-2 text-xs leading-5 text-muted-foreground">
                                    Tus preferencias se guardan de forma segura
                                    y solo vos podés modificarlas.
                                </p>
                            </div>
                        </div>
                    </aside>

                    <main className="min-w-0">
                        <section className="mx-auto max-w-5xl space-y-6 sm:space-y-8">
                            {children}
                        </section>
                    </main>
                </div>
            </div>
        </div>
    );
}
