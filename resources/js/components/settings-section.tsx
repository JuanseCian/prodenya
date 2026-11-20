import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    icon: LucideIcon;
    eyebrow?: string;
    title: string;
    description: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
};

export default function SettingsSection({
    icon: Icon,
    eyebrow,
    title,
    description,
    action,
    children,
    className = '',
}: Props) {
    return (
        <section
            className={`overflow-hidden rounded-[1.5rem] border border-border bg-card shadow-[0_18px_50px_-34px_rgba(32,56,70,0.45)] ${className}`}
            aria-labelledby={`${title.toLowerCase().replaceAll(' ', '-')}-title`}
        >
            <div className="flex flex-col gap-5 border-b border-border/80 px-5 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-7 sm:py-6">
                <div className="flex items-start gap-3.5">
                    <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-secondary text-primary ring-1 ring-primary/10">
                        <Icon className="size-[1.1rem]" aria-hidden="true" />
                    </span>
                    <div>
                        {eyebrow && (
                            <p className="mb-1 text-[0.65rem] font-bold tracking-[0.16em] text-primary uppercase">
                                {eyebrow}
                            </p>
                        )}
                        <h2
                            id={`${title.toLowerCase().replaceAll(' ', '-')}-title`}
                            className="text-base font-semibold tracking-[-0.01em] text-card-foreground sm:text-lg"
                        >
                            {title}
                        </h2>
                        <p className="mt-1 max-w-2xl text-sm leading-6 text-muted-foreground">
                            {description}
                        </p>
                    </div>
                </div>
                {action}
            </div>
            <div className="px-5 py-5 sm:px-7 sm:py-7">{children}</div>
        </section>
    );
}
