import { Link } from '@inertiajs/react';
import { KeyRound, Palette, UserRound, UsersRound } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as teams } from '@/routes/teams';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Información personal',
        href: edit(),
        icon: UserRound,
    },
    {
        title: 'Seguridad y contraseña',
        href: editSecurity(),
        icon: KeyRound,
    },
    {
        title: 'Equipos',
        href: teams(),
        icon: UsersRound,
    },
    {
        title: 'Preferencias',
        href: editAppearance(),
        icon: Palette,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="min-h-full bg-background px-4 py-6 sm:px-6 lg:px-8">
            <Heading
                title="Mi cuenta"
                description="Administrá tu información, seguridad y preferencias."
            />

            <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-10">
                <aside className="w-full lg:w-56 lg:shrink-0">
                    <nav
                        className="grid grid-cols-1 gap-1 rounded-xl border bg-card p-2 shadow-sm sm:grid-cols-2 lg:flex lg:flex-col"
                        aria-label="Secciones de mi cuenta"
                    >
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-secondary font-semibold text-secondary-foreground hover:bg-secondary/80':
                                        isCurrentOrParentUrl(item.href),
                                })}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="min-w-0 flex-1">
                    <section className="max-w-3xl space-y-8">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
