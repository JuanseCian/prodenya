import { CheckCircle2, Info, TriangleAlert, X } from 'lucide-react';
import type { ReactNode } from 'react';

import type { FlashToast } from '@/types/ui';

type ProdenyaToastProps = FlashToast & {
    onDismiss: () => void;
};

const styles: Record<
    FlashToast['type'],
    { title: string; icon: ReactNode; iconClass: string; accentClass: string }
> = {
    success: {
        title: 'Listo',
        icon: <CheckCircle2 size={20} strokeWidth={2.2} />,
        iconClass: 'bg-[#E3F0E6] text-[#41634A]',
        accentClass: 'bg-[#6B8F71]',
    },
    info: {
        title: 'Información',
        icon: <Info size={20} strokeWidth={2.2} />,
        iconClass: 'bg-[#E4EEF3] text-[#2C4A5E]',
        accentClass: 'bg-[#2C4A5E]',
    },
    warning: {
        title: 'Atención',
        icon: <TriangleAlert size={20} strokeWidth={2.2} />,
        iconClass: 'bg-[#FBF1DC] text-[#8A6420]',
        accentClass: 'bg-[#C9963E]',
    },
    error: {
        title: 'No se pudo completar',
        icon: <TriangleAlert size={20} strokeWidth={2.2} />,
        iconClass: 'bg-[#F8E1DD] text-[#A84635]',
        accentClass: 'bg-[#A84635]',
    },
};

export function ProdenyaToast({
    type,
    title,
    message,
    onDismiss,
}: ProdenyaToastProps) {
    const variant = styles[type];

    return (
        <div
            role={type === 'error' ? 'alert' : 'status'}
            className="pointer-events-auto relative flex w-[min(calc(100vw-2rem),390px)] items-start gap-3 overflow-hidden rounded-2xl border border-[#DCE3DF] bg-white p-4 text-[#173247] shadow-[0_18px_45px_rgba(23,50,71,0.16)] dark:border-[#414262] dark:bg-[#292A45] dark:text-[#F8F8FF]"
        >
            <span
                aria-hidden="true"
                className={`absolute inset-y-0 left-0 w-1 ${variant.accentClass}`}
            />
            <span
                aria-hidden="true"
                className={`mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl ${variant.iconClass}`}
            >
                {variant.icon}
            </span>
            <div className="min-w-0 flex-1 pr-1">
                <p className="text-sm leading-5 font-semibold">
                    {title ?? variant.title}
                </p>
                <p className="mt-0.5 text-sm leading-5 text-[#2B2F36]/65 dark:text-white/65">
                    {message}
                </p>
            </div>
            <button
                type="button"
                aria-label="Cerrar notificación"
                onClick={onDismiss}
                className="-mt-1 -mr-1 grid size-7 shrink-0 place-items-center rounded-lg text-[#2B2F36]/45 transition hover:bg-[#F4F6F5] hover:text-[#173247] focus-visible:ring-2 focus-visible:ring-[#6B8F71]/50 focus-visible:outline-none dark:text-white/45 dark:hover:bg-white/10 dark:hover:text-white"
            >
                <X size={16} />
            </button>
        </div>
    );
}
