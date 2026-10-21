import { usePage } from '@inertiajs/react';
import * as React from 'react';
import { SidebarInset } from '@/components/ui/sidebar';
import type { AppVariant } from '@/types';

type Props = React.ComponentProps<'main'> & {
    variant?: AppVariant;
};

export function AppContent({ variant = 'sidebar', children, ...props }: Props) {
    const { url } = usePage();
    const pageTransition = (
        <div key={url} data-page-transition className="prodenya-page-transition">
            {children}
        </div>
    );

    if (variant === 'sidebar') {
        return <SidebarInset {...props}>{pageTransition}</SidebarInset>;
    }

    return (
        <main
            className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl"
            {...props}
        >
            {pageTransition}
        </main>
    );
}
