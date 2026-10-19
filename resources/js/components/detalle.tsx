import type { ReactNode } from 'react';

import { Badge } from '@/components/ui/badge';

export const FONT_HEADING = "'Lora', serif";
export const FONT_BODY = "'Public Sans', sans-serif";

export const NIVEL_ESTILO: Record<string, string> = {
    Bajo: 'bg-[#E3F0E6] text-[#41634A]',
    Medio: 'bg-[#FBF1DC] text-[#8A6420]',
    Alto: 'bg-[#FCE9DC] text-[#9A4E1C]',
    Critico: 'bg-[#F8E1DD] text-[#A84635]',
};

export function NivelBadge({ nivel }: { nivel: string }) {
    return (
        <Badge
            className={`border-0 ${NIVEL_ESTILO[nivel] ?? 'bg-[#EEF0ED] text-[#2B2F36]'}`}
        >
            {nivel === 'Critico' ? 'Crítico' : nivel}
        </Badge>
    );
}

export function Dato({
    etiqueta,
    children,
}: {
    etiqueta: string;
    children?: ReactNode;
}) {
    return (
        <div>
            <dt className="text-[0.68rem] font-bold tracking-[0.12em] text-[#6B8F71] uppercase">
                {etiqueta}
            </dt>
            <dd className="mt-1 text-sm whitespace-pre-line text-[#2B2F36]">
                {children || '—'}
            </dd>
        </div>
    );
}

export function AvisoEstado({ mensaje }: { mensaje?: string | null }) {
    if (!mensaje) {
return null;
}

    return (
        <div
            role="status"
            className="mb-6 rounded-lg border border-[#BDE3CB] bg-[#E8F7EF] px-4 py-3 text-sm font-medium text-[#267148]"
        >
            {mensaje}
        </div>
    );
}
