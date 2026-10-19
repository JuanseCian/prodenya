import { Head, router } from '@inertiajs/react';
import { Check, Mail, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

interface Mensaje {
    id: number;
    subject: string;
    from_name: string | null;
    from_email: string;
    received_at: string | null;
    is_read: boolean;
    preview: string;
}

interface Props {
    mensajes: Paginador<Mensaje>;
    filtros: { buscar: string | null; no_leidos: boolean };
    noLeidos: number;
}

const formatDate = (value: string | null) => {
    if (!value) {
        return 'Sin fecha';
    }

    return new Intl.DateTimeFormat('es-AR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

export default function Mailbox({ mensajes, filtros, noLeidos }: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [noLeidosFiltro, setNoLeidosFiltro] = useState(filtros.no_leidos);

    useEffect(() => {
        if (
            buscar === (filtros.buscar ?? '') &&
            noLeidosFiltro === filtros.no_leidos
        ) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                '/mailbox',
                {
                    ...(buscar ? { buscar } : {}),
                    ...(noLeidosFiltro ? { no_leidos: 1 } : {}),
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [buscar, noLeidosFiltro, filtros.buscar, filtros.no_leidos]);

    const marcarLeido = (id: number) => {
        router.patch(`/mailbox/${id}/read`, {}, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Mailbox" />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                                Administración
                            </p>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                                Mailbox
                            </h1>
                            <p className="mt-2 text-sm text-[#697586]">
                                Bandeja institucional para la gestión de correos.
                            </p>
                        </div>
                        {noLeidos > 0 && (
                            <Badge className="w-fit border-0 bg-[#E8F7EF] text-[#267148]">
                                {noLeidos}{' '}
                                {noLeidos === 1
                                    ? 'correo nuevo'
                                    : 'correos nuevos'}
                            </Badge>
                        )}
                    </div>

                    <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <div className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                            <div className="relative w-full sm:max-w-lg">
                                <Search className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#2B2F36]/40" />
                                <Input
                                    value={buscar}
                                    onChange={(event) =>
                                        setBuscar(event.target.value)
                                    }
                                    placeholder="Buscar por remitente, asunto o contenido…"
                                    className="h-11 border-[#D5D1C6] bg-white pl-10 shadow-none"
                                />
                            </div>
                            <label className="flex h-11 items-center gap-2 px-1 text-sm font-medium text-[#2B2F36]">
                                <input
                                    type="checkbox"
                                    checked={noLeidosFiltro}
                                    onChange={(event) =>
                                        setNoLeidosFiltro(event.target.checked)
                                    }
                                    className="size-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                />
                                Solo no leídos
                            </label>
                        </div>

                        <CardContent className="p-0">
                            {mensajes.data.length === 0 ? (
                                <div className="px-6 py-16 text-center">
                                    <Mail className="mx-auto size-8 text-[#6B8F71]" />
                                    <p className="mt-3 text-sm font-semibold text-[#2B2F36]">
                                        No hay correos para mostrar.
                                    </p>
                                </div>
                            ) : (
                                <div className="divide-y divide-[#EEF0ED]">
                                    {mensajes.data.map((mensaje) => (
                                        <article
                                            key={mensaje.id}
                                            className={`flex gap-4 px-5 py-5 transition hover:bg-[#FAFBFA] sm:px-6 ${mensaje.is_read ? '' : 'bg-[#F8FAF9]'}`}
                                        >
                                            <div className="mt-1 shrink-0 text-[#2C4A5E]">
                                                <Mail className="size-5" />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                                    <div className="min-w-0">
                                                        <p
                                                            className={`truncate text-sm ${mensaje.is_read ? 'font-medium' : 'font-bold'} text-[#173247]`}
                                                        >
                                                            {mensaje.subject}
                                                        </p>
                                                        <p className="mt-1 truncate text-xs text-[#2B2F36]/60">
                                                            {mensaje.from_name
                                                                ? `${mensaje.from_name} · `
                                                                : ''}
                                                            {mensaje.from_email}
                                                        </p>
                                                    </div>
                                                    <time className="shrink-0 text-xs text-[#2B2F36]/50">
                                                        {formatDate(
                                                            mensaje.received_at,
                                                        )}
                                                    </time>
                                                </div>
                                                <p className="mt-3 line-clamp-2 text-sm leading-6 text-[#2B2F36]/70">
                                                    {mensaje.preview ||
                                                        'Sin contenido de texto.'}
                                                </p>
                                            </div>
                                            {!mensaje.is_read && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        marcarLeido(mensaje.id)
                                                    }
                                                    className="inline-flex h-9 shrink-0 items-center gap-1.5 self-start rounded-md border border-[#D5D1C6] bg-white px-3 text-xs font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                                                >
                                                    <Check className="size-3.5" />
                                                    Leído
                                                </button>
                                            )}
                                        </article>
                                    ))}
                                </div>
                            )}
                            <Paginacion paginador={mensajes} unidad="correos" />
                        </CardContent>
                    </Card>
                </div>
            </main>
        </>
    );
}
