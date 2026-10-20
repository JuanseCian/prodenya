import { Head, router } from '@inertiajs/react';
import { ClipboardList, Search } from 'lucide-react';
import { useState } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

interface Evento {
    id: number;
    evento: string;
    recurso: string;
    recurso_id: number | null;
    usuario: string;
    creado_en: string | null;
    datos_antes: Record<string, unknown> | null;
    datos_despues: Record<string, unknown> | null;
    ip_origen: string | null;
}

interface Props {
    eventos: Paginador<Evento>;
    filtros: { buscar: string | null };
}

const formatDate = (value: string | null) => value ? new Intl.DateTimeFormat('es-AR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : 'Sin fecha';

export default function Auditoria({ eventos, filtros }: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const buscarEventos = (value: string) => {
        setBuscar(value);
        router.get('/backend/auditoria', value ? { buscar: value } : {}, { preserveState: true, replace: true });
    };

    return (
        <>
            <Head title="Auditoría" />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-8"><p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">Control y trazabilidad</p><h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">Auditoría</h1><p className="mt-2 text-sm text-[#697586]">Historial de accesos, cambios y acciones realizadas en el sistema.</p></div>
                    <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]"><div className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:px-6"><div className="relative max-w-lg"><Search className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#2B2F36]/40" /><Input value={buscar} onChange={(event) => buscarEventos(event.target.value)} placeholder="Buscar por acción, recurso o usuario" className="h-11 border-[#D5D1C6] bg-white pl-10 shadow-none" /></div></div><CardContent className="p-0">{eventos.data.length === 0 ? <div className="px-6 py-20 text-center"><ClipboardList className="mx-auto size-9 text-[#6B8F71]" /><p className="mt-3 text-sm font-semibold text-[#2B2F36]">No hay eventos de auditoría.</p></div> : <div className="divide-y divide-[#EEF0ED]">{eventos.data.map((evento) => <article key={evento.id} className="px-5 py-4 sm:px-6"><div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div className="flex flex-wrap items-center gap-2"><span className="rounded-full bg-[#EAF3EF] px-2.5 py-1 text-[11px] font-bold text-[#397461]">{evento.evento}</span><strong className="text-sm text-[#173247]">{evento.recurso}{evento.recurso_id ? ` #${evento.recurso_id}` : ''}</strong></div><time className="text-xs text-[#697586]">{formatDate(evento.creado_en)}</time></div><p className="mt-2 text-xs text-[#697586]">Por {evento.usuario}{evento.ip_origen ? ` · ${evento.ip_origen}` : ''}</p></article>)}</div>}<Paginacion paginador={eventos} unidad="eventos" /></CardContent></Card>
                </div>
            </main>
        </>
    );
}
