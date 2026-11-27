import { ArrowUpRight, CirclePlus, X } from 'lucide-react';
import { useState, type CSSProperties } from 'react';
import type { LucideIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

export type RadialOrbitalTimelineItem = {
    id: string;
    title: string;
    date: string;
    content: string;
    category: string;
    icon: LucideIcon;
    href: string;
};

type RadialOrbitalTimelineProps = {
    timelineData: RadialOrbitalTimelineItem[];
    className?: string;
};

const itemColor =
    'bg-[#eaf3ed] text-[#397461] ring-[#cfe3d6] hover:bg-[#dcece2] dark:bg-[#1b303f] dark:text-[#9ccfb2] dark:ring-[#294252] dark:hover:bg-[#223b4b]';

function getItemPosition(index: number, total: number): CSSProperties {
    const angle = (index / total) * 360 - 90;
    const radians = (angle * Math.PI) / 180;
    const x = Math.cos(radians) * 86;
    const y = Math.sin(radians) * 86;

    return {
        transform: `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`,
    };
}

export function RadialOrbitalTimeline({ timelineData, className }: RadialOrbitalTimelineProps) {
    const [activeId, setActiveId] = useState(timelineData[0]?.id ?? '');
    const [isOpen, setIsOpen] = useState(false);
    const activeItem = timelineData.find((item) => item.id === activeId) ?? timelineData[0];

    if (!timelineData.length || !activeItem) {
        return null;
    }

    return (
        <section
            aria-label="Nueva acta rápida"
            className={cn(
                'relative p-2',
                className,
            )}
        >
            <div className="relative mx-auto h-[210px] w-full max-w-[300px]" aria-label="Tipos de acta">
                <div className="absolute top-1/2 left-1/2 h-[172px] w-[172px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-dashed border-[#cbded4] dark:border-[#36525b]" />
                <div className="absolute top-1/2 left-1/2 h-[112px] w-[112px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#f4f8f5] shadow-inner dark:bg-[#1b303f]" />

                <button
                    type="button"
                    aria-expanded={isOpen}
                    aria-controls="quick-acta-orbital-items"
                    aria-label={isOpen ? 'Ocultar tipos de acta' : 'Mostrar tipos de acta'}
                    className="absolute top-1/2 left-1/2 z-10 flex h-[92px] w-[92px] -translate-x-1/2 -translate-y-1/2 flex-col items-center justify-center rounded-full border border-[#b9e8cc] bg-[#173247] text-center text-white shadow-[0_12px_24px_rgba(23,50,71,0.2)] transition duration-300 hover:scale-105 focus-visible:ring-2 focus-visible:ring-[#397461] focus-visible:ring-offset-2 dark:border-[#397461] dark:focus-visible:ring-offset-[#152532]"
                    onClick={() => setIsOpen((open) => !open)}
                >
                    {isOpen ? <X className="mb-1 h-5 w-5 text-[#b9e8cc]" aria-hidden="true" /> : <CirclePlus className="mb-1 h-5 w-5 text-[#b9e8cc]" aria-hidden="true" />}
                    <span className="max-w-[74px] text-[length:calc(0.66rem_+_2px)] leading-tight font-bold">{isOpen ? 'Cerrar actas' : 'Ver actas'}</span>
                </button>

                <div id="quick-acta-orbital-items" className="contents">
                    {timelineData.map((item, index) => {
                    const Icon = item.icon;
                    const isActive = item.id === activeItem.id;

                    return (
                        <button
                            key={item.id}
                            type="button"
                            title={item.title}
                            aria-label={`Seleccionar ${item.title}`}
                            aria-pressed={isActive}
                            className={cn(
                                'group absolute top-1/2 left-1/2 flex h-10 w-10 items-center justify-center rounded-full border border-white shadow-md ring-1 transition duration-200 hover:scale-110 focus-visible:z-20 focus-visible:ring-2 focus-visible:ring-[#397461] focus-visible:ring-offset-2 dark:border-[#152532] dark:focus-visible:ring-offset-[#152532]',
                                itemColor,
                                isActive && isOpen && 'scale-110 shadow-lg ring-2 ring-[#397461] ring-offset-2 dark:ring-offset-[#152532]',
                            )}
                            style={{
                                ...getItemPosition(index, timelineData.length),
                                opacity: isOpen ? 1 : 0,
                                pointerEvents: isOpen ? 'auto' : 'none',
                                transitionDelay: isOpen ? `${index * 35}ms` : '0ms',
                                transform: isOpen ? getItemPosition(index, timelineData.length).transform : 'translate(-50%, -50%) scale(0.35)',
                            }}
                            onClick={() => setActiveId(item.id)}
                            onDoubleClick={() => window.location.assign(item.href)}
                        >
                            <Icon className="h-4 w-4" aria-hidden="true" />
                            <span className="pointer-events-none absolute bottom-full left-1/2 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-[#173247] px-2 py-1 text-[length:calc(0.65rem_+_2px)] font-semibold text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100 dark:bg-[#edf6fb] dark:text-[#173247]">
                                {item.title}
                            </span>
                        </button>
                    );
                    })}
                </div>
            </div>

            <div className="mt-1 flex h-10 items-center justify-between gap-3 rounded-xl border border-[#dce7ed] bg-white/70 px-3 dark:border-[#294252] dark:bg-[#152532]/70">
                {isOpen ? (
                    <>
                        <span className="min-w-0 truncate text-xs font-semibold text-[#244357] dark:text-[#d9e7ee]">
                            {activeItem.title}
                        </span>
                        <a
                            href={activeItem.href}
                            className="inline-flex shrink-0 items-center gap-1 rounded-lg bg-[#173247] px-2.5 py-1 text-[length:calc(0.7rem_+_2px)] font-bold text-white no-underline transition hover:bg-[#244357] focus-visible:ring-2 focus-visible:ring-[#397461] focus-visible:ring-offset-2 dark:bg-[#397461] dark:hover:bg-[#4a8a74]"
                        >
                            Crear
                            <ArrowUpRight className="h-3 w-3" aria-hidden="true" />
                        </a>
                    </>
                ) : (
                    <span className="truncate text-xs text-[#5d727e] dark:text-[#9bb2c1]">
                        Tocá «Ver actas» y elegí un tipo
                    </span>
                )}
            </div>

            <div className="sr-only" aria-live="polite">
                {isOpen ? `${activeItem.title} seleccionado` : 'Tipos de acta ocultos'}
            </div>
        </section>
    );
}
