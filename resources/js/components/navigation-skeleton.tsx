import { Skeleton } from '@/components/ui/skeleton';

type SkeletonVariant =
    | 'auth'
    | 'calendar'
    | 'dashboard'
    | 'detail'
    | 'form'
    | 'mailbox'
    | 'settings'
    | 'table'
    | 'thread';

function variantForUrl(targetUrl = ''): SkeletonVariant {
    const path = targetUrl.toLowerCase();

    if (/login|register|password/.test(path)) {
        return 'auth';
    }

    if (path.includes('mailbox')) {
        return 'mailbox';
    }

    if (path.includes('comunicacion')) {
        return 'thread';
    }

    if (path.includes('agenda')) {
        return 'calendar';
    }

    if (path.includes('settings') || path.includes('teams')) {
        return 'settings';
    }

    if (/\/create$|\/edit$|documentacion|intervenciones\/create/.test(path)) {
        return 'form';
    }

    if (/estadisticas|dashboard/.test(path) || path === '/') {
        return 'dashboard';
    }

    if (/\/legajos\/\d+|\/personas\/\d+|word-preview/.test(path)) {
        return 'detail';
    }

    return 'table';
}

function LoadingProgress() {
    return (
        <div
            className="fixed inset-x-0 top-0 z-[70] h-1 overflow-hidden bg-primary/15"
            aria-hidden="true"
        >
            <div className="h-full w-1/3 animate-[loading-progress_1.2s_ease-in-out_infinite] rounded-full bg-primary motion-reduce:animate-none" />
        </div>
    );
}

function LoadingBrand({ compact = false }: { compact?: boolean }) {
    return (
        <div className={`flex items-center ${compact ? 'gap-3' : 'gap-4'}`}>
            <span className="flex size-11 items-center justify-center rounded-xl border border-primary/20 bg-primary/8 p-2 shadow-sm">
                <img src="/assets/img/SN.png" alt="" className="size-full object-contain" />
            </span>
            <div className="space-y-1">
                <p className="text-[0.65rem] font-bold tracking-[0.16em] text-primary uppercase">
                    PRODENYA
                </p>
                {!compact && (
                    <p className="text-xs text-muted-foreground">
                        Cargando informacion
                    </p>
                )}
            </div>
        </div>
    );
}

function PageHeader() {
    return (
        <div className="flex items-end justify-between gap-4 border-b border-border pb-5">
            <div className="space-y-3">
                <Skeleton className="h-3 w-28" />
                <Skeleton className="h-8 w-56 max-w-[70vw]" />
                <Skeleton className="h-3 w-80 max-w-[80vw]" />
            </div>
            <Skeleton className="h-10 w-28 rounded-lg" />
        </div>
    );
}

function StatsRow() {
    return (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            {Array.from({ length: 4 }).map((_, index) => (
                <div className="rounded-xl border border-border bg-card p-4" key={index}>
                    <div className="flex items-start justify-between gap-3">
                        <div className="space-y-2">
                            <Skeleton className="h-3 w-24" />
                            <Skeleton className="h-8 w-20" />
                        </div>
                        <Skeleton className="size-9 rounded-lg" />
                    </div>
                    <Skeleton className="mt-4 h-3 w-32" />
                </div>
            ))}
        </div>
    );
}

function TableRows() {
    return (
        <div className="overflow-hidden rounded-xl border border-border bg-card">
            <div className="hidden gap-4 border-b border-border bg-muted/40 px-4 py-3 sm:grid sm:grid-cols-[1.3fr_1fr_0.8fr_0.6fr]">
                <Skeleton className="h-3 w-28" />
                <Skeleton className="h-3 w-24" />
                <Skeleton className="h-3 w-20" />
                <Skeleton className="h-3 w-14" />
            </div>
            {Array.from({ length: 7 }).map((_, index) => (
                <div
                    className="grid gap-3 border-b border-border px-4 py-4 last:border-0 sm:grid-cols-[1.3fr_1fr_0.8fr_0.6fr] sm:items-center"
                    key={index}
                >
                    <div className="space-y-2">
                        <Skeleton className="h-3.5 w-3/4" />
                        <Skeleton className="h-3 w-1/2" />
                    </div>
                    <Skeleton className="h-3 w-2/3" />
                    <Skeleton className="h-6 w-20 rounded-full" />
                    <Skeleton className="h-8 w-8 rounded-md sm:ml-auto" />
                </div>
            ))}
        </div>
    );
}

function FormFields({ count = 8 }: { count?: number }) {
    return (
        <div className="grid gap-5 sm:grid-cols-2">
            {Array.from({ length: count }).map((_, index) => (
                <div
                    className={
                        index === count - 1
                            ? 'space-y-2 sm:col-span-2'
                            : 'space-y-2'
                    }
                    key={index}
                >
                    <Skeleton className="h-3 w-28" />
                    <Skeleton
                        className={`w-full rounded-lg ${index === count - 1 ? 'h-24' : 'h-10'}`}
                    />
                </div>
            ))}
        </div>
    );
}

function MainSkeleton({ variant }: { variant: SkeletonVariant }) {
    if (variant === 'auth') {
        return (
            <div className="mx-auto w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8">
                <div className="mb-8 flex justify-center">
                    <LoadingBrand />
                </div>
                <Skeleton className="h-8 w-44" />
                <Skeleton className="mt-3 h-4 w-64 max-w-full" />
                <div className="mt-8 space-y-5">
                    <FormFields count={3} />
                    <Skeleton className="h-11 w-full rounded-lg" />
                </div>
            </div>
        );
    }

    if (variant === 'calendar') {
        return (
            <>
                <PageHeader />
                <div className="grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.75fr)]">
                    <div className="rounded-xl border border-border bg-card p-4 sm:p-6">
                        <div className="mb-5 flex items-center justify-between">
                            <Skeleton className="h-6 w-40" />
                            <div className="flex gap-2">
                                <Skeleton className="size-9 rounded-md" />
                                <Skeleton className="size-9 rounded-md" />
                            </div>
                        </div>
                        <div className="grid grid-cols-7 gap-px overflow-hidden rounded-lg border border-border">
                            {Array.from({ length: 42 }).map((_, index) => (
                                <Skeleton className="h-20 rounded-none sm:h-24" key={index} />
                            ))}
                        </div>
                    </div>
                    <div className="rounded-xl border border-border bg-card p-5">
                        <Skeleton className="h-6 w-44" />
                        <div className="mt-5 space-y-3">
                            {Array.from({ length: 4 }).map((_, index) => (
                                <div className="rounded-lg border border-border p-3" key={index}>
                                    <Skeleton className="h-4 w-2/3" />
                                    <Skeleton className="mt-3 h-3 w-1/2" />
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </>
        );
    }

    if (variant === 'dashboard') {
        return (
            <>
                <PageHeader />
                <StatsRow />
                <div className="grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
                    <div className="h-72 rounded-xl border border-border bg-card p-5">
                        <Skeleton className="h-5 w-40" />
                        <Skeleton className="mt-8 h-48 w-full rounded-lg" />
                    </div>
                    <div className="rounded-xl border border-border bg-card p-5">
                        <Skeleton className="h-5 w-32" />
                        <div className="mt-5 space-y-4">
                            {Array.from({ length: 5 }).map((_, index) => (
                                <div className="flex gap-3" key={index}>
                                    <Skeleton className="size-9 rounded-full" />
                                    <div className="flex-1 space-y-2">
                                        <Skeleton className="h-3 w-2/3" />
                                        <Skeleton className="h-3 w-full" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </>
        );
    }

    if (variant === 'form' || variant === 'settings') {
        return (
            <>
                <PageHeader />
                <div className="grid gap-5 lg:grid-cols-[220px_1fr]">
                    {variant === 'settings' && (
                        <div className="space-y-2 rounded-xl border border-border bg-card p-3">
                            {Array.from({ length: 5 }).map((_, index) => (
                                <Skeleton className="h-10 w-full rounded-lg" key={index} />
                            ))}
                        </div>
                    )}
                    <div className="rounded-xl border border-border bg-card p-5 sm:p-7 lg:col-span-1">
                        <Skeleton className="h-6 w-48" />
                        <Skeleton className="mt-2 h-3 w-72 max-w-full" />
                        <div className="mt-7">
                            <FormFields count={variant === 'settings' ? 5 : 8} />
                        </div>
                        <div className="mt-7 flex justify-end gap-3">
                            <Skeleton className="h-10 w-24 rounded-lg" />
                            <Skeleton className="h-10 w-32 rounded-lg" />
                        </div>
                    </div>
                </div>
            </>
        );
    }

    if (variant === 'detail') {
        return (
            <>
                <PageHeader />
                <div className="grid gap-5 lg:grid-cols-[0.75fr_1.25fr]">
                    <div className="space-y-5">
                        <div className="rounded-xl border border-border bg-card p-5">
                            <Skeleton className="mx-auto size-20 rounded-full" />
                            <Skeleton className="mx-auto mt-4 h-5 w-40" />
                            <Skeleton className="mx-auto mt-2 h-3 w-52" />
                        </div>
                        <div className="rounded-xl border border-border bg-card p-5">
                            <Skeleton className="h-5 w-32" />
                            <div className="mt-5 space-y-4">
                                <Skeleton className="h-3 w-full" />
                                <Skeleton className="h-3 w-4/5" />
                                <Skeleton className="h-3 w-3/5" />
                            </div>
                        </div>
                    </div>
                    <div className="rounded-xl border border-border bg-card p-5 sm:p-7">
                        <div className="flex gap-2 border-b border-border pb-4">
                            <Skeleton className="h-8 w-24 rounded-md" />
                            <Skeleton className="h-8 w-28 rounded-md" />
                            <Skeleton className="h-8 w-20 rounded-md" />
                        </div>
                        <div className="mt-6">
                            <FormFields count={6} />
                        </div>
                    </div>
                </div>
            </>
        );
    }

    if (variant === 'mailbox' || variant === 'thread') {
        return (
            <>
                <PageHeader />
                <div className="grid min-h-[34rem] gap-5 lg:grid-cols-[0.7fr_1.3fr]">
                    <div className="rounded-xl border border-border bg-card p-4">
                        <Skeleton className="h-10 w-full rounded-lg" />
                        <div className="mt-4 space-y-3">
                            {Array.from({ length: 7 }).map((_, index) => (
                                <div className="flex gap-3 rounded-lg border border-border p-3" key={index}>
                                    <Skeleton className="size-10 rounded-full" />
                                    <div className="min-w-0 flex-1 space-y-2">
                                        <Skeleton className="h-3.5 w-3/4" />
                                        <Skeleton className="h-3 w-full" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="rounded-xl border border-border bg-card p-5 sm:p-7">
                        <div className="flex items-center gap-3 border-b border-border pb-4">
                            <Skeleton className="size-10 rounded-full" />
                            <Skeleton className="h-5 w-40" />
                        </div>
                        <Skeleton className="mt-6 h-48 w-full rounded-lg" />
                        <Skeleton className="mt-5 h-11 w-full rounded-lg" />
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <PageHeader />
            <div className="mb-5 flex flex-wrap gap-3">
                <Skeleton className="h-10 w-40 rounded-lg" />
                <Skeleton className="h-10 w-32 rounded-lg" />
                <Skeleton className="h-10 w-28 rounded-lg" />
            </div>
            <TableRows />
        </>
    );
}

export function NavigationSkeleton({ targetUrl }: { targetUrl?: string }) {
    return (
        <div
            className="fixed inset-0 z-50 overflow-y-auto bg-background/92 px-4 py-6 backdrop-blur-sm sm:px-6 sm:py-8"
            role="status"
            aria-live="polite"
            aria-label="Cargando seccion"
        >
            <LoadingProgress />
            <div className="mx-auto flex min-h-full w-full max-w-7xl items-start justify-center pt-2 sm:pt-4">
                <div className="w-full space-y-5 rounded-2xl border border-border bg-card/98 p-5 shadow-2xl shadow-black/8 sm:p-8">
                    <LoadingBrand />
                    <MainSkeleton variant={variantForUrl(targetUrl)} />
                </div>
            </div>
        </div>
    );
}

export function SaveLoadingOverlay() {
    return (
        <div
            className="fixed inset-0 z-[80] flex items-center justify-center bg-background/72 p-6 backdrop-blur-[2px]"
            role="status"
            aria-live="polite"
            aria-label="Guardando cambios"
        >
            <LoadingProgress />
            <div className="flex w-full max-w-xs flex-col items-center rounded-2xl border border-border bg-card p-8 text-center shadow-2xl shadow-black/10">
                <div className="relative flex size-20 items-center justify-center rounded-2xl border border-primary/20 bg-primary/8 p-4">
                    <span className="absolute inset-1 rounded-xl border-2 border-primary/20 border-t-primary motion-safe:animate-spin motion-reduce:border-t-primary" />
                    <img src="/assets/img/SN.png" alt="" className="relative size-full object-contain" />
                </div>
                <p className="mt-5 text-sm font-semibold text-foreground">Guardando cambios</p>
                <p className="mt-1 text-xs text-muted-foreground">No cierres esta ventana.</p>
            </div>
        </div>
    );
}
