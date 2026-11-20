import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    ClipboardCheck,
    Home,
    LayoutGrid,
    Mail,
    Menu,
    UserRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import AppLogoIcon from '@/components/app-logo-icon';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { TeamSwitcher } from '@/components/team-switcher';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { UserMenuContent } from '@/components/user-menu-content';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useInitials } from '@/hooks/use-initials';
import { toUrl } from '@/lib/utils';
import { estadisticas as estadisticasRoute, home } from '@/routes';
import mailbox from '@/routes/mailbox';
import tareas from '@/routes/tareas';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const rightNavItems: NavItem[] = [
    {
        title: 'Inicio institucional',
        href: '/',
        icon: Home,
    },
    {
        title: 'Mi cuenta',
        href: '/settings/profile',
        icon: UserRound,
    },
];

export function AppHeader({ breadcrumbs = [] }: Props) {
    const page = usePage();
    const { auth } = page.props;
    const getInitials = useInitials();
    const { isCurrentUrl } = useCurrentUrl();
    const homeUrl = home().url;

    const mainNavItems: NavItem[] = [
        {
            title: 'Inicio',
            href: homeUrl,
            icon: LayoutGrid,
        },
        ...(page.props.can?.accessStatistics
            ? [
                  {
                      title: 'Estadísticas',
                      href: estadisticasRoute().url,
                      icon: BarChart3,
                  },
              ]
            : []),
        ...(page.props.can?.accessTasks
            ? [
                  {
                      title: 'Tareas',
                      href: tareas.index().url,
                      icon: ClipboardCheck,
                  },
              ]
            : []),
        ...(page.props.can?.accessMailbox
            ? [{ title: 'Mailbox', href: mailbox.index().url, icon: Mail }]
            : []),
    ];
    const activeNavIndex = mainNavItems.findIndex((item) =>
        isCurrentUrl(item.href),
    );

    return (
        <>
            <div className="border-b border-white/10 bg-brand-strong text-white shadow-[0_8px_24px_rgba(23,50,71,0.12)]">
                <div className="mx-auto flex h-16 items-center px-4 sm:px-6 md:max-w-[1440px]">
                    {/* Mobile Menu */}
                    <div className="lg:hidden">
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="mr-2 h-10 w-10 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                                >
                                    <Menu className="h-5 w-5" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent
                                side="left"
                                className="flex h-full w-64 flex-col items-stretch justify-between bg-sidebar"
                            >
                                <SheetTitle className="sr-only">
                                    Navigation menu
                                </SheetTitle>
                                <SheetHeader className="flex justify-start text-left">
                                    <AppLogoIcon className="h-6 w-6 fill-current text-white" />
                                </SheetHeader>
                                <div className="flex h-full flex-1 flex-col space-y-4 p-4">
                                    <div className="flex h-full flex-col justify-between text-sm">
                                        <div className="flex flex-col space-y-4">
                                            {mainNavItems.map((item) => (
                                                <Link
                                                    key={item.title}
                                                    href={item.href}
                                                    className="flex items-center space-x-2 font-medium"
                                                >
                                                    {item.icon && (
                                                        <item.icon className="h-5 w-5" />
                                                    )}
                                                    <span>{item.title}</span>
                                                </Link>
                                            ))}
                                        </div>

                                        <div className="flex flex-col space-y-4">
                                            {rightNavItems.map((item) => (
                                                <Link
                                                    key={item.title}
                                                    href={toUrl(item.href)}
                                                    className="flex items-center space-x-2 font-medium"
                                                >
                                                    {item.icon && (
                                                        <item.icon className="h-5 w-5" />
                                                    )}
                                                    <span>{item.title}</span>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    <Link
                        href={homeUrl}
                        prefetch
                        className="flex items-center space-x-2"
                    >
                        <AppLogo />
                    </Link>

                    {/* Desktop Navigation */}
                    <div className="ml-6 hidden h-full items-center gap-0.5 lg:flex">
                        {mainNavItems.map((item, index) => (
                            <Link
                                key={item.title}
                                href={item.href}
                                className={`inline-flex h-10 items-center gap-2 rounded-lg px-3 text-sm font-medium transition-[background-color,color,transform] duration-200 hover:-translate-y-px hover:bg-white/10 hover:text-white ${index === activeNavIndex ? 'bg-white/15 text-white' : 'text-white/72'}`}
                            >
                                {item.icon && <item.icon className="size-4" />}
                                {item.title}
                            </Link>
                        ))}
                    </div>

                    <div className="ml-auto flex items-center space-x-2">
                        <div className="hidden items-center gap-1 lg:flex">
                            {rightNavItems.map((item) => (
                                <TooltipProvider
                                    key={item.title}
                                    delayDuration={0}
                                >
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Link
                                                href={toUrl(item.href)}
                                                className="group inline-flex h-10 w-10 items-center justify-center rounded-lg bg-transparent p-0 text-sm font-medium text-white/80 transition-[background-color,color,transform] duration-200 hover:-translate-y-px hover:bg-white/10 hover:text-white focus-visible:ring-2 focus-visible:ring-white/60 focus-visible:outline-none"
                                            >
                                                <span className="sr-only">
                                                    {item.title}
                                                </span>
                                                {item.icon && (
                                                    <item.icon className="size-5 opacity-80 group-hover:opacity-100" />
                                                )}
                                            </Link>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{item.title}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            ))}
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="size-10 rounded-full p-1"
                                >
                                    <Avatar className="size-8 overflow-hidden rounded-full">
                                        <AvatarImage
                                            src={auth.user.avatar}
                                            alt={auth.user.name}
                                        />
                                        <AvatarFallback className="rounded-lg bg-white/15 text-white">
                                            {getInitials(auth.user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="end">
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <TeamSwitcher inHeader />
                    </div>
                </div>
            </div>
            {breadcrumbs.length > 1 && (
                <div className="flex w-full border-b border-sidebar-border/70">
                    <div className="mx-auto flex h-10 w-full items-center justify-start px-4 text-neutral-500 md:max-w-[1440px]">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                </div>
            )}
        </>
    );
}
