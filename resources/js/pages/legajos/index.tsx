import { Head, Link, router } from '@inertiajs/react';
import { FolderPlus, Search, Users } from 'lucide-react';
import { useEffect, useState } from 'react';

import Paginacion, { type Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

interface LegajoFila {
    id: number;
    numero_legajo: string;
    persona: { id: number; nombre_completo: string; dni: string } | null;
    problematicas: string[];
    nivel_riesgo: string;
    estado: string;
    origen_derivacion: string;
    profesional: string | null;
    fecha_apertura: string | null;
}

interface Filtros {
    buscar: string | null;
    nivel_riesgo: string | null;
    estado: string | null;
}

interface LegajosIndexProps {
    legajos: Paginador<LegajoFila>;
    filtros: Filtros;
    estados: string[];
    status?: string | null;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

const NIVELES = ['Bajo', 'Medio', 'Alto', 'Critico'];

const NIVEL_ESTILO: Record<string, string> = {
    Bajo: 'bg-[#E3F0E6] text-[#41634A]',
    Medio: 'bg-[#FBF1DC] text-[#8A6420]',
    Alto: 'bg-[#FCE9DC] text-[#9A4E1C]',
    Critico: 'bg-[#F8E1DD] text-[#A84635]',
};

const thClass =
    'px-4 py-3 text-left text-[0.68rem] font-bold tracking-[0.12em] text-[#6B8F71] uppercase whitespace-nowrap';
const tdClass = 'px-4 py-3.5 align-top text-sm text-[#2B2F36]';
const selectClass =
    'h-11 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

export default function LegajosIndex({ legajos, filtros, estados, status }: LegajosIndexProps) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [nivel, setNivel] = useState(filtros.nivel_riesgo ?? '');
    const [estado, setEstado] = useState(filtros.estado ?? '');

    useEffect(() => {
        const sinCambios =
            buscar === (filtros.buscar ?? '') &&
            nivel === (filtros.nivel_riesgo ?? '') &&
            estado === (filtros.estado ?? '');

        if (sinCambios) return;

        const timeout = setTimeout(() => {
            router.get(
                '/legajos',
                {
                    ...(buscar ? { buscar } : {}),
                    ...(nivel ? { nivel_riesgo: nivel } : {}),
                    ...(estado ? { estado } : {}),
                },
                { preserveState: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [buscar, nivel, estado, filtros.buscar, filtros.nivel_riesgo, filtros.estado]);

    const hayFiltros = Boolean(filtros.buscar || filtros.nivel_riesgo || filtros.estado);

    return (
        <>
            <Head title="Legajos" />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                            <span>Legajos</span>
                            <span className="text-[#B4C1B8]">/</span>
                            <span className="text-[#2B2F36]/45">Listado</span>
                        </div>
                        <h2
                            className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            Legajos
                        </h2>
                        <p className="mt-2 text-sm text-[#2B2F36]/65">
                            Buscá por número de legajo, nombre, apellido o DNI.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Link
                            href="/personas"
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#D5D1C6] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            <Users size={16} />
                            Ver personas
                        </Link>
                        <Link
                            href="/legajos/create"
                            className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1F394B]"
                        >
                            <FolderPlus size={16} />
                            Nuevo legajo
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
                    <div className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                        <div className="relative w-full sm:max-w-md">
                            <Search
                                size={16}
                                className="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-[#2B2F36]/40"
                            />
                            <Input
                                value={buscar}
                                onChange={(event) => setBuscar(event.target.value)}
                                placeholder="Buscar legajo, nombre o DNI…"
                                className="h-11 border-[#D5D1C6] bg-white pl-10 text-[#2B2F36] shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20"
                            />
                        </div>
                        <select
                            value={nivel}
                            onChange={(event) => setNivel(event.target.value)}
                            className={selectClass}
                            aria-label="Filtrar por nivel de riesgo"
                        >
                            <option value="">Todos los riesgos</option>
                            {NIVELES.map((n) => (
                                <option key={n} value={n}>
                                    {n === 'Critico' ? 'Crítico' : n}
                                </option>
                            ))}
                        </select>
                        <select
                            value={estado}
                            onChange={(event) => setEstado(event.target.value)}
                            className={selectClass}
                            aria-label="Filtrar por estado"
                        >
                            <option value="">Todos los estados</option>
                            {estados.map((e) => (
                                <option key={e} value={e}>
                                    {e}
                                </option>
                            ))}
                        </select>
                    </div>

                    <CardContent className="p-0">
                        {legajos.data.length === 0 ? (
                            <div className="px-6 py-16 text-center">
                                <p className="text-sm font-semibold text-[#2B2F36]">
                                    {hayFiltros
                                        ? 'No se encontraron legajos con esos filtros.'
                                        : 'Todavía no hay legajos abiertos.'}
                                </p>
                                {!hayFiltros && (
                                    <Link
                                        href="/legajos/create"
                                        className="mt-3 inline-block text-sm font-semibold text-[#2C4A5E] underline"
                                    >
                                        Abrir el primer legajo
                                    </Link>
                                )}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[980px] border-collapse">
                                    <thead className="bg-[#FAFBFA]">
                                        <tr className="border-b border-[#E4E8E5]">
                                            <th className={thClass}>Legajo</th>
                                            <th className={thClass}>Persona</th>
                                            <th className={thClass}>Problemáticas</th>
                                            <th className={thClass}>Riesgo</th>
                                            <th className={thClass}>Estado</th>
                                            <th className={thClass}>Derivación</th>
                                            <th className={thClass}>Profesional</th>
                                            <th className={thClass}>Apertura</th>
                                            <th className={thClass}></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-[#EEF0ED]">
                                        {legajos.data.map((legajo) => (
                                            <tr
                                                key={legajo.id}
                                                className="transition hover:bg-[#FAFBFA]"
                                            >
                                                <td className={`${tdClass} font-mono font-semibold text-[#2C4A5E]`}>
                                                    {legajo.numero_legajo}
                                                </td>
                                                <td className={tdClass}>
                                                    {legajo.persona ? (
                                                        <>
                                                            <p className="font-semibold text-[#173247]">
                                                                {legajo.persona.nombre_completo}
                                                            </p>
                                                            <p className="mt-0.5 font-mono text-xs text-[#2B2F36]/55">
                                                                DNI {legajo.persona.dni}
                                                            </p>
                                                        </>
                                                    ) : (
                                                        '—'
                                                    )}
                                                </td>
                                                <td className={tdClass}>
                                                    <div className="flex max-w-[260px] flex-wrap gap-1.5">
                                                        {legajo.problematicas.length > 0
                                                            ? legajo.problematicas.map((nombre) => (
                                                                  <Badge
                                                                      key={nombre}
                                                                      className="border-0 bg-[#EDF3EF] text-[#2C4A5E]"
                                                                  >
                                                                      {nombre}
                                                                  </Badge>
                                                              ))
                                                            : '—'}
                                                    </div>
                                                </td>
                                                <td className={tdClass}>
                                                    <Badge
                                                        className={`border-0 ${NIVEL_ESTILO[legajo.nivel_riesgo] ?? 'bg-[#EEF0ED] text-[#2B2F36]'}`}
                                                    >
                                                        {legajo.nivel_riesgo === 'Critico'
                                                            ? 'Crítico'
                                                            : legajo.nivel_riesgo}
                                                    </Badge>
                                                </td>
                                                <td className={tdClass}>{legajo.estado}</td>
                                                <td className={tdClass}>{legajo.origen_derivacion}</td>
                                                <td className={tdClass}>{legajo.profesional ?? '—'}</td>
                                                <td className={`${tdClass} whitespace-nowrap`}>
                                                    {legajo.fecha_apertura ?? '—'}
                                                </td>
                                                <td className={`${tdClass} whitespace-nowrap text-right`}>
                                                    <Link
                                                        href={`/legajos/${legajo.id}`}
                                                        className="text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                    >
                                                        Ver
                                                    </Link>
                                                    <span className="mx-2 text-[#B4C1B8]">·</span>
                                                    <Link
                                                        href={`/legajos/${legajo.id}/edit`}
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

                        <Paginacion paginador={legajos} unidad="legajos" />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
