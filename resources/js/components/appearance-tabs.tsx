import type { LucideIcon } from 'lucide-react';
import { Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();

    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'Claro' },
        { value: 'dark', icon: Moon, label: 'Oscuro' },
        { value: 'system', icon: Monitor, label: 'Sistema' },
    ];

    return (
        <div
            className={cn(
                'grid w-full max-w-2xl gap-2 sm:grid-cols-3',
                className,
            )}
            aria-label="Modo de apariencia"
            {...props}
        >
            {tabs.map(({ value, icon: Icon, label }) => (
                <button
                    key={value}
                    type="button"
                    onClick={() => updateAppearance(value)}
                    aria-pressed={appearance === value}
                    className={cn(
                        'flex min-h-20 flex-col items-start justify-center gap-2 rounded-2xl border px-4 py-3 text-left transition-[border-color,background-color,box-shadow,transform] hover:-translate-y-0.5',
                        appearance === value
                            ? 'border-primary/40 bg-secondary font-semibold text-secondary-foreground shadow-sm'
                            : 'border-border bg-surface-soft text-muted-foreground hover:border-primary/30 hover:bg-card hover:text-foreground',
                    )}
                >
                    <Icon className="size-4 text-primary" />
                    <span className="text-sm">{label}</span>
                </button>
            ))}
        </div>
    );
}
