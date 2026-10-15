import { Head, Link, router } from '@inertiajs/react';
import { FolderOpen, Search, UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

interface PersonaFila {
    id: number;
    nombre: string;
    apellido: string;
    dni: string;
    edad: number | null;
    genero: string;
    localidad: string | null;
    barrio: string | null;
    telefono: string | null;
    email: string | null;
    legajos: string[];
}

interface PersonasIndexProps {
    personas: Paginador<PersonaFila>;
    filtros: { buscar: string | null };
    status?: string | null;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

const GENERO_LABEL: Record<string, string> = {
    M: 'Masculino',
    F: 'Femenino',
    X: 'No binario / X',
    NO_ESPECIFICA: 'No especifica',
};

const thClass =
    'px-4 py-3 text-left text-[0.68rem] font-bold tracking-[0.12em] text-[#6B8F71] uppercase whitespace-nowrap';
const tdClass = 'px-4 py-3.5 align-top text-sm text-[#2B2F36]';

export default function PersonasIndex({ personas, filtros, status }: PersonasIndexProps) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');

    useEffect(() => {
        if (buscar === (filtros.buscar ?? '')) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                '/personas',
                buscar ? { buscar } : {},
                { preserveState: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [buscar, filtros.buscar]);

    return (
        <>
            <Head title="Personas" />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                            <span>Personas</span>
                            <span className="text-[#B4C1B8]">/</span>
                            <span className="text-[#2B2F36]/45">Listado</span>
                        </div>
                        <h2
                            className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            Personas registradas
                        </h2>
                        <p className="mt-2 text-sm text-[#2B2F36]/65">
                            Buscá por nombre, apellido o DNI.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Link
                            href="/legajos"
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#D5D1C6] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            <FolderOpen size={16} />
                            Ver legajos
                        </Link>
                        <Link
                            href="/personas/nueva"
                            className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1F394B]"
                        >
                            <UserPlus size={16} />
                            Registrar persona
                        </Link>
                    </div>
                </div>

                {status && (
                    <div
                        role="status"
                        className="mb-6 rounded-lg border border-[#BDE3CB] bg-[#E8F7EF] px-4 py-3 text-sm font-medium text-[#267148]"
                    >
                        {status}
                    </div>
                )}

                <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                    <div className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:px-6">
                        <div className="relative max-w-md">
                            <Search
                                size={16}
                                className="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-[#2B2F36]/40"
                            />
                            <Input
                                value={buscar}
                                onChange={(event) => setBuscar(event.target.value)}
                                placeholder="Buscar por nombre, apellido o DNI…"
                                className="h-11 border-[#D5D1C6] bg-white pl-10 text-[#2B2F36] shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20"
                            />
                        </div>
                    </div>

                    <CardContent className="p-0">
                        {personas.data.length === 0 ? (
                            <div className="px-6 py-16 text-center">
                                <p className="text-sm font-semibold text-[#2B2F36]">
                                    {filtros.buscar
                                        ? 'No se encontraron personas con esa búsqueda.'
                                        : 'Todavía no hay personas registradas.'}
                                </p>
                                {!filtros.buscar && (
                                    <Link
                                        href="/personas/nueva"
                                        className="mt-3 inline-block text-sm font-semibold text-[#2C4A5E] underline"
                                    >
                                        Registrar la primera persona
                                    </Link>
                                )}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[820px] border-collapse">
                                    <thead className="bg-[#FAFBFA]">
                                        <tr className="border-b border-[#E4E8E5]">
                                            <th className={thClass}>Persona</th>
                                            <th className={thClass}>DNI</th>
                                            <th className={thClass}>Edad</th>
                                            <th className={thClass}>Domicilio</th>
                                            <th className={thClass}>Contacto</th>
                                            <th className={thClass}>Legajo</th>
                                            <th className={thClass}></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-[#EEF0ED]">
                                        {personas.data.map((persona) => (
                                            <tr
                                                key={persona.id}
                                                className="transition hover:bg-[#FAFBFA]"
                                            >
                                                <td className={tdClass}>
                                                    <p className="font-semibold text-[#173247]">
                                                        {persona.apellido}, {persona.nombre}
                                                    </p>
                                                    <p className="mt-0.5 text-xs text-[#2B2F36]/55">
                                                        {GENERO_LABEL[persona.genero] ??
                                                            persona.genero}
                                                    </p>
                                                </td>
                                                <td className={`${tdClass} font-mono`}>
                                                    {persona.dni}
                                                </td>
                                                <td className={tdClass}>
                                                    {persona.edad !== null
                                                        ? `${persona.edad} años`
                                                        : '—'}
                                                </td>
                                                <td className={tdClass}>
                                                    <p>{persona.localidad ?? '—'}</p>
                                                    {persona.barrio && (
                                                        <p className="mt-0.5 text-xs text-[#2B2F36]/55">
                                                            {persona.barrio}
                                                        </p>
                                                    )}
                                                </td>
                                                <td className={tdClass}>
                                                    <p>{persona.telefono ?? '—'}</p>
                                                    {persona.email && (
                                                        <p className="mt-0.5 text-xs text-[#2B2F36]/55">
                                                            {persona.email}
                                                        </p>
                                                    )}
                                                </td>
                                                <td className={tdClass}>
                                                    {persona.legajos.length > 0 ? (
                                                        <div className="flex flex-wrap gap-1.5">
                                                            {persona.legajos.map((numero) => (
                                                                <Badge
                                                                    key={numero}
                                                                    className="border-0 bg-[#EDF3EF] font-mono text-[#2C4A5E]"
                                                                >
                                                                    {numero}
                                                                </Badge>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <span className="text-xs text-[#2B2F36]/45">
                                                            Sin legajo
                                                        </span>
                                                    )}
                                                </td>
                                                <td className={`${tdClass} whitespace-nowrap text-right`}>
                                                    <Link
                                                        href={`/personas/${persona.id}`}
                                                        className="text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                    >
                                                        Ver
                                                    </Link>
                                                    <span className="mx-2 text-[#B4C1B8]">·</span>
                                                    <Link
                                                        href={`/personas/${persona.id}/edit`}
                                                        className="text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                    >
                                                        Editar
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        <Paginacion paginador={personas} unidad="personas" />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
