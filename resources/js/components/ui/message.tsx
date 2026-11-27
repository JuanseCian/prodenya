import * as React from 'react';

import { cn } from '@/lib/utils';

type MessageProps = React.ComponentProps<'div'> & {
    align?: 'start' | 'end';
};

function Message({ align = 'start', className, ...props }: MessageProps) {
    return (
        <div
            data-slot="message"
            data-align={align}
            className={cn(
                'group/message flex w-full items-end gap-3',
                align === 'end' ? 'justify-end' : 'justify-start',
                className,
            )}
            {...props}
        />
    );
}

function MessageAvatar({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="message-avatar"
            className={cn('shrink-0 pb-5', className)}
            {...props}
        />
    );
}

function MessageContent({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="message-content"
            className={cn(
                'flex max-w-[min(42rem,88%)] min-w-0 flex-col gap-1',
                className,
            )}
            {...props}
        />
    );
}

function MessageHeader({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="message-header"
            className={cn('px-1 text-xs font-semibold text-[#2C4A5E]', className)}
            {...props}
        />
    );
}

function MessageFooter({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="message-footer"
            className={cn('flex items-center px-1 text-[length:calc(0.68rem_+_2px)] text-[#697586]', className)}
            {...props}
        />
    );
}

export { Message, MessageAvatar, MessageContent, MessageFooter, MessageHeader };
