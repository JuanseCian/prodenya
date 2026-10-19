import type { LucideIcon } from 'lucide-react';

const tones = {
    blue: {
        icon: 'bg-[#EEECF7] text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]',
        surface:
            'bg-white ring-[#DFE4EA] dark:bg-[#232A34] dark:ring-[#3B4552]',
        value: 'text-[#243140] dark:text-[#EDF1F5]',
    },
    green: {
        icon: 'bg-[#EAF4EE] text-[#39795B] dark:bg-[#304C3D] dark:text-[#8BC9A9]',
        surface:
            'bg-white ring-[#DFE4EA] dark:bg-[#232A34] dark:ring-[#3B4552]',
        value: 'text-[#243140] dark:text-[#EDF1F5]',
    },
    orange: {
        icon: 'bg-[#FFF3DF] text-[#A56D24] dark:bg-[#4A3D29] dark:text-[#E0B978]',
        surface:
            'bg-white ring-[#DFE4EA] dark:bg-[#232A34] dark:ring-[#3B4552]',
        value: 'text-[#243140] dark:text-[#EDF1F5]',
    },
    violet: {
        icon: 'bg-[#EEECF7] text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]',
        surface:
            'bg-white ring-[#DFE4EA] dark:bg-[#232A34] dark:ring-[#3B4552]',
        value: 'text-[#243140] dark:text-[#EDF1F5]',
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
        <article className={`rounded-lg p-4 ring-1 ${styles.surface}`}>
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-[0.68rem] font-bold tracking-[0.08em] text-[#2C4A5E]/60 uppercase dark:text-[#B7C9D4]">
                        {label}
                    </p>
                    <p
                        className={`mt-2 text-[2rem] font-bold tracking-[-0.04em] ${styles.value}`}
                    >
                        {value.toLocaleString('es-AR')}
                    </p>
                </div>
                <span
                    className={`grid size-10 shrink-0 place-items-center rounded-lg ${styles.icon}`}
                >
                    <Icon size={22} strokeWidth={1.8} />
                </span>
            </div>
            <p className="mt-4 text-xs font-medium text-[#2C4A5E]/55 dark:text-[#9BB2C1]">
                {detail}
            </p>
        </article>
    );
}
