import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { Breadcrumbs } from '@/components/breadcrumbs';
import type { AppLayoutProps } from '@/types';

/**
 * The institutional header is rendered server-side by `layouts.frontend`,
 * so this layout only adds breadcrumbs and the page content.
 */
export default function AppHeaderLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="header">
            {breadcrumbs.length > 1 && (
                <div className="flex w-full border-b border-sidebar-border/70">
                    <div className="mx-auto flex h-10 w-full items-center justify-start px-4 text-neutral-500 md:max-w-[1440px]">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                </div>
            )}
            <AppContent variant="header">{children}</AppContent>
        </AppShell>
    );
}
