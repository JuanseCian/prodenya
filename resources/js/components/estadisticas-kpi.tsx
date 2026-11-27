import type { LucideIcon } from 'lucide-react';

const tones = {
    blue: {
        accent: 'border-l-[#2C4A5E]',
        icon: 'bg-[#E8F0F3] text-[#2C4A5E] dark:bg-[#29404D] dark:text-[#B9D5E1]',
    },
    green: {
        accent: 'border-l-[#39795B]',
        icon: 'bg-[#EAF4EE] text-[#39795B] dark:bg-[#304C3D] dark:text-[#8BC9A9]',
    },
    orange: {
        accent: 'border-l-[#A16A00]',
        icon: 'bg-[#FFF3DF] text-[#A16A00] dark:bg-[#4A3D29] dark:text-[#E0B978]',
    },
    violet: {
        accent: 'border-l-[#5B7080]',
        icon: 'bg-[#EDF1F3] text-[#5B7080] dark:bg-[#35424A] dark:text-[#B7C9D4]',
    },
    red: {
        accent: 'border-l-[#A8493C]',
        icon: 'bg-[#FBECEA] text-[#A8493C] dark:bg-[#4A302F] dark:text-[#E4A49C]',
    },
};

export function EstadisticaKpi({
    label,
    value,
    detail,
    icon: Icon,
    tone,
}: {
    label: string;
    value: number;
    detail: string;
    icon: LucideIcon;
    tone: keyof typeof tones;
}) {
    const styles = tones[tone];

    return (
        <article
            className={`rounded-md border border-l-4 border-[#DCE3E6] bg-white px-4 py-4 shadow-[0_8px_22px_rgba(23,50,71,0.04)] dark:border-[#34424B] dark:bg-[#1D2A31] ${styles.accent}`}
        >
            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <p className="truncate text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.1em] text-[#5A7180] uppercase dark:text-[#AFC2CC]">
                        {label}
                    </p>
                    <p className="mt-2 text-[length:calc(2rem_+_2px)] leading-none font-semibold tracking-[-0.05em] text-[#173247] dark:text-[#EDF6FB]">
                        {value.toLocaleString('es-AR')}
                    </p>
                </div>
                <span
                    className={`grid size-9 shrink-0 place-items-center rounded-md ${styles.icon}`}
                >
                    <Icon size={19} strokeWidth={1.8} />
                </span>
            </div>
            <p className="mt-3 text-xs leading-5 text-[#697586] dark:text-[#9BB2C1]">
                {detail}
            </p>
        </article>
    );
}
