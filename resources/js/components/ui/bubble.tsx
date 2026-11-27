import * as React from 'react';

import { cn } from '@/lib/utils';

function Bubble({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="bubble"
            className={cn('overflow-hidden rounded-2xl border shadow-sm', className)}
            {...props}
        />
    );
}

function BubbleContent({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="bubble-content"
            className={cn('px-4 py-3 text-sm leading-6', className)}
            {...props}
        />
    );
}

export { Bubble, BubbleContent };
