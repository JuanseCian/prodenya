import { Skeleton } from '@/components/ui/skeleton';
import Pattern from '@/components/ui/v-skeleton-10';

export function NavigationSkeleton() {
    return (
        <div
            className="fixed inset-0 z-50 overflow-y-auto bg-background/90 px-4 py-6 backdrop-blur-sm sm:px-6 sm:py-8"
            role="status"
            aria-live="polite"
            aria-label="Cargando sección"
        >
            <div className="mx-auto flex min-h-full w-full max-w-7xl items-start justify-center">
                <div className="w-full rounded-2xl border border-border bg-card/95 p-5 shadow-xl shadow-black/5 sm:p-8">
                    <div className="mb-8 flex items-center justify-between gap-4 border-b border-border pb-5">
                        <div className="space-y-2">
                            <Skeleton className="h-3 w-32" />
                            <Skeleton className="h-7 w-56" />
                        </div>
                        <Skeleton className="size-10 rounded-full" />
                    </div>

                    <div className="grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
                        <div className="space-y-5">
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-28" />
                                <Skeleton className="h-9 w-3/4" />
                                <Skeleton className="h-4 w-full max-w-xl" />
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2">
                                {Array.from({ length: 4 }).map((_, index) => (
                                    <div
                                        className="rounded-xl border border-border p-4"
                                        key={index}
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="space-y-2">
                                                <Skeleton className="h-3 w-24" />
                                                <Skeleton className="h-8 w-20" />
                                            </div>
                                            <Skeleton className="size-10 rounded-xl" />
                                        </div>
                                        <Skeleton className="mt-4 h-3 w-32" />
                                    </div>
                                ))}
                            </div>
                            <div className="rounded-xl border border-border p-5">
                                <Pattern />
                            </div>
                        </div>

                        <div className="space-y-4 rounded-xl border border-border p-5">
                            <div className="flex items-center justify-between gap-4">
                                <div className="space-y-2">
                                    <Skeleton className="h-5 w-36" />
                                    <Skeleton className="h-3 w-48" />
                                </div>
                                <Skeleton className="h-8 w-20 rounded-md" />
                            </div>
                            {Array.from({ length: 5 }).map((_, index) => (
                                <div
                                    className="flex items-center gap-3 rounded-lg border border-border p-3"
                                    key={index}
                                >
                                    <Skeleton className="size-9 rounded-full" />
                                    <div className="min-w-0 flex-1 space-y-2">
                                        <Skeleton className="h-3.5 w-2/3" />
                                        <Skeleton className="h-3 w-full" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
