import { Link, router } from '@inertiajs/react';
import { ChevronDown, KeyRound, Palette, UserRound } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
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
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const activeItem =
        sidebarNavItems.find((item) => isCurrentOrParentUrl(item.href)) ??
        sidebarNavItems[0];
    const activeHref = toUrl(activeItem.href);

    return (
        <div className="min-h-full bg-[#f5f9f7] px-4 py-6 text-[#173247] sm:px-6 sm:py-8 lg:px-8 lg:py-10">
            <div className="mx-auto max-w-7xl">
                <header className="mb-7 max-w-3xl">
                    <p className="mb-2 text-xs font-bold tracking-[0.16em] text-[#5d9b7a] uppercase">
                        Configuración de cuenta
                    </p>
                    <h1 className="text-3xl font-semibold tracking-[-0.03em] text-[#173247] sm:text-4xl">
                        Mi cuenta
                    </h1>
                    <p className="mt-3 text-sm leading-6 text-[#63757e] sm:text-base">
                        Administrá tu información, seguridad y preferencias desde un solo lugar.
                    </p>
                </header>

                <div className="mb-6 lg:hidden">
                    <label htmlFor="account-section" className="mb-2 block text-xs font-bold tracking-[0.12em] text-[#71828a] uppercase">
                        Sección actual
                    </label>
                    <div className="relative">
                        <select
                            id="account-section"
                            defaultValue={activeHref}
                            onChange={(event) => router.visit(event.target.value)}
                            className="h-12 w-full appearance-none rounded-xl border border-[#d7e5dd] bg-white px-4 pr-11 text-sm font-semibold text-[#173247] shadow-sm outline-none transition focus:border-[#78b58f] focus:ring-2 focus:ring-[#bde3ca]"
                            aria-label="Sección de mi cuenta"
                        >
                            {sidebarNavItems.map((item) => (
                                <option key={toUrl(item.href)} value={toUrl(item.href)}>
                                    {item.title}
                                </option>
                            ))}
                        </select>
                        <ChevronDown className="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-[#5d9b7a]" aria-hidden="true" />
                    </div>
                </div>

                <div className="flex flex-col gap-8 lg:flex-row lg:items-start lg:gap-10">
                    <aside className="hidden w-64 shrink-0 lg:sticky lg:top-24 lg:block">
                        <div className="overflow-hidden rounded-2xl border border-[#d7e5dd] bg-white p-2 shadow-[0_14px_35px_rgba(23,50,71,0.07)]">
                            <div className="px-3 pb-3 pt-2">
                                <p className="text-sm font-semibold text-[#173247]">Tu espacio</p>
                                <p className="mt-1 text-xs leading-5 text-[#819197]">Preferencias y protección de la cuenta</p>
                            </div>
                            <nav className="space-y-1" aria-label="Secciones de mi cuenta">
                                {sidebarNavItems.map((item) => {
                                    const isActive = isCurrentOrParentUrl(item.href);

                                    return (
                                        <Button
                                            key={toUrl(item.href)}
                                            size="sm"
                                            variant="ghost"
                                            asChild
                                            className={cn(
                                                'h-12 w-full justify-start gap-3 rounded-xl px-3 text-sm font-medium text-[#60757e] hover:bg-[#f0f8f3] hover:text-[#173247]',
                                                isActive && 'bg-[#e9f5ed] font-semibold text-[#173247] shadow-sm hover:bg-[#e9f5ed]',
                                            )}
                                        >
                                            <Link href={item.href} aria-current={isActive ? 'page' : undefined}>
                                                {item.icon && (
                                                    <span className={cn('flex size-8 items-center justify-center rounded-lg bg-[#f0f7f3] text-[#5d9b7a]', isActive && 'bg-[#cfead8] text-[#34775a]')}>
                                                        <item.icon className="size-4" />
                                                    </span>
                                                )}
                                                {item.title}
                                            </Link>
                                        </Button>
                                    );
                                })}
                            </nav>
                        </div>
                        <p className="px-3 pt-4 text-xs leading-5 text-[#819197]">
                            Tus datos y preferencias se administran de forma segura.
                        </p>
                    </aside>

                    <main className="min-w-0 flex-1">
                        <section className="mx-auto max-w-4xl space-y-8">
                            {children}
                        </section>
                    </main>
                </div>
            </div>
        </div>
    );
}
