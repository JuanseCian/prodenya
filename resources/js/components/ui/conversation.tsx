'use client';

import type { ComponentProps } from 'react';
import { useCallback } from 'react';
import { ArrowDownIcon } from 'lucide-react';
import { StickToBottom, useStickToBottomContext } from 'use-stick-to-bottom';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type ConversationProps = ComponentProps<typeof StickToBottom>;

export const Conversation = ({ className, ...props }: ConversationProps) => (
    <StickToBottom
        className={cn('relative flex-1 overflow-y-auto', className)}
        initial="smooth"
        resize="smooth"
        role="log"
        {...props}
    />
);

export type ConversationContentProps = ComponentProps<
    typeof StickToBottom.Content
>;

export const ConversationContent = ({
    className,
    ...props
}: ConversationContentProps) => (
    <StickToBottom.Content className={cn('p-4', className)} {...props} />
);

export type ConversationScrollButtonProps = ComponentProps<typeof Button>;

export const ConversationScrollButton = ({
    className,
    ...props
}: ConversationScrollButtonProps) => {
    const { isAtBottom, scrollToBottom } = useStickToBottomContext();

    const handleScrollToBottom = useCallback(() => {
        scrollToBottom();
    }, [scrollToBottom]);

    return (
        !isAtBottom && (
            <Button
                className={cn(
                    'absolute bottom-4 left-1/2 z-10 -translate-x-1/2 rounded-full border-[#CFE1D4] bg-white text-[#2C4A5E] shadow-[0_8px_20px_rgba(23,50,71,0.15)] hover:bg-[#EAF3ED] hover:text-[#173247]',
                    className,
                )}
                onClick={handleScrollToBottom}
                size="icon"
                type="button"
                variant="outline"
                {...props}
            >
                <ArrowDownIcon className="size-4" />
                <span className="sr-only">Ir al mensaje más reciente</span>
            </Button>
        )
    );
};
