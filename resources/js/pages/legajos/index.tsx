import { Head, Link, router } from '@inertiajs/react';
import {
    Eye,
    Filter,
    FolderOpen,
    FolderPlus,
    Pencil,
    Search,
    UsersRound,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

interface LegajoFila {
    id: number;
    numero_legajo: string;
    edad: number | null;
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
    numero_legajo: string | null;
    problematica: number | null;
    edad: string | null;
    nivel_riesgo: string | null;
    estado: string | null;
}

interface OpcionFiltro {
    id?: number;
    value?: string;
    nombre?: string;
    label?: string;
}

interface LegajosIndexProps {
    legajos: Paginador<LegajoFila>;
    seccion: 'legajos' | 'mayores18';
    filtros: Filtros;
    estados: string[];
    problematicas: OpcionFiltro[];
    edades: OpcionFiltro[];
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

export default function LegajosIndex({
    legajos,
    seccion,
    filtros,
    estados,
    problematicas,
    edades,
    status,
}: LegajosIndexProps) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [numeroLegajo, setNumeroLegajo] = useState(
        filtros.numero_legajo ?? '',
    );
    const [problematica, setProblematica] = useState(
        filtros.problematica ? String(filtros.problematica) : '',
    );
    const [edad, setEdad] = useState(filtros.edad ?? '');
    const [nivel, setNivel] = useState(filtros.nivel_riesgo ?? '');
    const [estado, setEstado] = useState(filtros.estado ?? '');
    const [filtrosAbiertos, setFiltrosAbiertos] = useState(true);
    const esMayores18 = seccion === 'mayores18';
    const rutaListado = esMayores18 ? '/mayores-18' : '/legajos';

    useEffect(() => {
        const sinCambios =
            buscar === (filtros.buscar ?? '') &&
            numeroLegajo === (filtros.numero_legajo ?? '') &&
            problematica ===
                (filtros.problematica ? String(filtros.problematica) : '') &&
            edad === (filtros.edad ?? '') &&
            nivel === (filtros.nivel_riesgo ?? '') &&
            estado === (filtros.estado ?? '');

        if (sinCambios) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                rutaListado,
                {
                    ...(buscar ? { buscar } : {}),
                    ...(numeroLegajo ? { numero_legajo: numeroLegajo } : {}),
                    ...(problematica ? { problematica } : {}),
                    ...(edad ? { edad } : {}),
                    ...(nivel ? { nivel_riesgo: nivel } : {}),
                    ...(estado ? { estado } : {}),
                },
                { preserveState: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [
        buscar,
        numeroLegajo,
        problematica,
        edad,
        nivel,
        estado,
        rutaListado,
        filtros.buscar,
        filtros.numero_legajo,
        filtros.problematica,
        filtros.edad,
        filtros.nivel_riesgo,
        filtros.estado,
    ]);

    const hayFiltros = Boolean(
        buscar || numeroLegajo || problematica || edad || nivel || estado,
    );
    const cantidadFiltros = [
        numeroLegajo,
        problematica,
        edad,
        nivel,
        estado,
    ].filter(Boolean).length;

    return (
        <>
            <Head title={esMayores18 ? '+18' : 'Legajos'} />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="flex items-start gap-3">
                        <div className="mt-1 grid size-10 shrink-0 place-items-center rounded-lg border border-[#DCE3DF] bg-[#EDF3EF] text-[#2C4A5E]">
                            {esMayores18 ? (
                                <UsersRound size={20} strokeWidth={1.8} />
                            ) : (
                                <FolderOpen size={20} strokeWidth={1.8} />
                            )}
                        </div>
                        <div>
                            <div className="flex items-center gap-2 text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                                <span>{esMayores18 ? '+18' : 'Legajos'}</span>
                                <span className="text-[#B4C1B8]">/</span>
                                <span className="text-[#2B2F36]/45">
                                    {esMayores18
                                        ? 'Personas mayores de edad'
                                        : 'Listado'}
                                </span>
                            </div>
                            <h2
                                className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                {esMayores18 ? '+18' : 'Legajos'}
                            </h2>
                            <p className="mt-2 text-sm text-[#2B2F36]/65">
                                {esMayores18
                                    ? 'Personas que cumplieron 18 años y continúan abiertas en otra instancia de acompañamiento.'
                                    : 'Buscá por número de legajo, problemática, nombre, apellido o DNI.'}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                            <nav
                            aria-label="Secciones de legajos"
                            className="flex items-center rounded-lg border border-[#DCE3DF] bg-white p-1"
                        >
                            <Link
                                href="/legajos"
                                aria-current={!esMayores18 ? 'page' : undefined}
                                 className={`inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-xs font-semibold transition ${!esMayores18 ? 'bg-[#2C4A5E] text-white' : 'text-[#697586] hover:bg-[#F4F6F5] hover:text-[#1F394B]'}`}
                            >
                                <FolderOpen size={14} />
                                Legajos
                            </Link>
                            <Link
                                href="/mayores-18"
                                aria-current={esMayores18 ? 'page' : undefined}
                                 className={`inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-xs font-semibold transition ${esMayores18 ? 'bg-[#2C4A5E] text-white' : 'text-[#697586] hover:bg-[#F4F6F5] hover:text-[#1F394B]'}`}
                            >
                                <UsersRound size={14} />
                                +18
                            </Link>
                        </nav>
                        <Link
                            href="/legajos/create"
                            className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
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

                <div
                    className={`prodenya-filter-layout ${filtrosAbiertos ? 'is-open' : 'is-closed'}`}
                >
                    <aside
                        aria-hidden={!filtrosAbiertos}
                        inert={!filtrosAbiertos}
                        className={`prodenya-filter-panel h-fit rounded-lg border border-[#DCE3DF] bg-white p-5 lg:sticky lg:top-6 ${filtrosAbiertos ? 'is-visible' : 'is-hidden'}`}
                    >
                        <div className="flex items-start justify-between gap-3 border-b border-[#E4E8E5] pb-4">
                            <div>
                                <p className="text-sm font-semibold text-[#173247]">
                                    Filtrar listado
                                </p>
                                <p className="mt-1 text-xs leading-5 text-[#2B2F36]/55">
                                    Combiná criterios para encontrar un legajo
                                    puntual.
                                </p>
                            </div>
                            <button
                                type="button"
                                aria-label="Ocultar filtros"
                                onClick={() => setFiltrosAbiertos(false)}
                                className="grid size-7 shrink-0 place-items-center rounded-lg text-[#2B2F36]/45 transition hover:bg-[#F4F6F5] hover:text-[#2C4A5E] focus-visible:ring-2 focus-visible:ring-[#2C4A5E]/30 focus-visible:outline-none"
                            >
                                <X size={16} />
                            </button>
                        </div>
                        <div className="mt-4 space-y-4">
                            <label className="block text-xs font-semibold text-[#2B2F36]/70">
                                Número de legajo
                                <Input
                                    value={numeroLegajo}
                                    onChange={(event) =>
                                        setNumeroLegajo(event.target.value)
                                    }
                                    placeholder="Ej. GON-MAR-123"
                                    className="mt-1.5 h-10 border-[#D5D1C6] bg-white text-sm font-normal text-[#2B2F36] shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20"
                                />
                            </label>
                            <label className="block text-xs font-semibold text-[#2B2F36]/70">
                                Problemática
                                <select
                                    value={problematica}
                                    onChange={(event) =>
                                        setProblematica(event.target.value)
                                    }
                                    className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                >
                                    <option value="">
                                        Todas las problemáticas
                                    </option>
                                    {problematicas.map((opcion) => (
                                        <option
                                            key={opcion.id}
                                            value={opcion.id}
                                        >
                                            {opcion.nombre}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-xs font-semibold text-[#2B2F36]/70">
                                Edad
                                <select
                                    value={edad}
                                    onChange={(event) =>
                                        setEdad(event.target.value)
                                    }
                                    className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                >
                                    <option value="">Todas las edades</option>
                                    {edades.map((opcion) => (
                                        <option
                                            key={opcion.value}
                                            value={opcion.value}
                                        >
                                            {opcion.label}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-xs font-semibold text-[#2B2F36]/70">
                                Nivel de riesgo
                                <select
                                    value={nivel}
                                    onChange={(event) =>
                                        setNivel(event.target.value)
                                    }
                                    className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                >
                                    <option value="">Todos los riesgos</option>
                                    {NIVELES.map((n) => (
                                        <option key={n} value={n}>
                                            {n === 'Critico' ? 'Crítico' : n}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-xs font-semibold text-[#2B2F36]/70">
                                Estado
                                <select
                                    value={estado}
                                    onChange={(event) =>
                                        setEstado(event.target.value)
                                    }
                                    className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                >
                                    <option value="">Todos los estados</option>
                                    {estados.map((e) => (
                                        <option key={e} value={e}>
                                            {e}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        </div>
                        {cantidadFiltros > 0 && (
                            <button
                                type="button"
                                onClick={() => {
                                    setBuscar('');
                                    setNumeroLegajo('');
                                    setProblematica('');
                                    setEdad('');
                                    setNivel('');
                                    setEstado('');
                                }}
                                className="mt-5 inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-[#E3B7AE] px-3 py-2 text-xs font-semibold text-[#A84635] transition hover:bg-[#FBEFEC]"
                            >
                                <X size={13} />
                                Limpiar filtros
                            </button>
                        )}
                    </aside>

                    <Card className="overflow-hidden rounded-xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <div className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                            <div className="relative w-full sm:max-w-md">
                                <Search
                                    size={16}
                                    className="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-[#2B2F36]/40"
                                />
                                <Input
                                    value={buscar}
                                    onChange={(event) =>
                                        setBuscar(event.target.value)
                                    }
                                    placeholder="Buscar legajo, problemática, nombre o DNI…"
                                    className="h-11 border-[#D5D1C6] bg-white pl-10 text-[#2B2F36] shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20"
                                />
                            </div>
                            <button
                                type="button"
                                aria-expanded={filtrosAbiertos}
                                onClick={() =>
                                    setFiltrosAbiertos((abierto) => !abierto)
                                }
                                className={`inline-flex h-11 items-center justify-center gap-2 rounded-md border px-3.5 text-sm font-semibold transition ${filtrosAbiertos || cantidadFiltros > 0 ? 'border-[#2C4A5E] bg-[#EEF3F1] text-[#2C4A5E]' : 'border-[#D5D1C6] bg-white text-[#2B2F36]/70 hover:border-[#2C4A5E]/40 hover:text-[#2C4A5E]'}`}
                            >
                                <Filter size={15} />
                                {filtrosAbiertos
                                    ? 'Ocultar filtros'
                                    : 'Mostrar filtros'}
                                {cantidadFiltros > 0 && (
                                    <span className="grid size-5 place-items-center rounded-full bg-[#6B8F71] text-[0.68rem] text-white">
                                        {cantidadFiltros}
                                    </span>
                                )}
                            </button>
                        </div>

                        <CardContent className="p-0">
                            {legajos.data.length === 0 ? (
                                <div className="px-6 py-16 text-center">
                                    <p className="text-sm font-semibold text-[#2B2F36]">
                                        {hayFiltros
                                            ? `No se encontraron personas ${esMayores18 ? '+18' : 'con legajos'} con esos filtros.`
                                            : esMayores18
                                              ? 'Todavía no hay personas en la sección +18.'
                                              : 'Todavía no hay legajos abiertos.'}
                                    </p>
                                    {!hayFiltros && (
                                        <Link
                                            href="/legajos/create"
                                            className="mt-3 inline-block text-sm font-semibold text-[#2C4A5E] underline"
                                        >
                                            {esMayores18
                                                ? 'Abrir un legajo'
                                                : 'Abrir el primer legajo'}
                                        </Link>
                                    )}
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[980px] border-collapse">
                                        <thead className="bg-[#FAFBFA]">
                                            <tr className="border-b border-[#E4E8E5]">
                                                <th className={thClass}>
                                                    Legajo
                                                </th>
                                                <th className={thClass}>
                                                    Persona
                                                </th>
                                                <th className={thClass}>
                                                    Edad
                                                </th>
                                                <th className={thClass}>
                                                    Problemáticas
                                                </th>
                                                <th className={thClass}>
                                                    Riesgo
                                                </th>
                                                <th className={thClass}>
                                                    Estado
                                                </th>
                                                <th className={thClass}>
                                                    Derivación
                                                </th>
                                                <th className={thClass}>
                                                    Profesional
                                                </th>
                                                <th className={thClass}>
                                                    Apertura
                                                </th>
                                                <th className={thClass}></th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-[#EEF0ED]">
                                            {legajos.data.map((legajo) => (
                                                <tr
                                                    key={legajo.id}
                                                    className="transition hover:bg-[#F7FAF8]"
                                                >
                                                    <td
                                                        className={`${tdClass} font-mono font-semibold text-[#2C4A5E]`}
                                                    >
                                                        {legajo.numero_legajo}
                                                    </td>
                                                    <td className={tdClass}>
                                                        {legajo.persona ? (
                                                            <>
                                                                <p className="font-semibold text-[#173247]">
                                                                    {
                                                                        legajo
                                                                            .persona
                                                                            .nombre_completo
                                                                    }
                                                                </p>
                                                                <p className="mt-0.5 font-mono text-xs text-[#2B2F36]/55">
                                                                    DNI{' '}
                                                                    {
                                                                        legajo
                                                                            .persona
                                                                            .dni
                                                                    }
                                                                </p>
                                                            </>
                                                        ) : (
                                                            '—'
                                                        )}
                                                    </td>
                                                    <td
                                                        className={`${tdClass} whitespace-nowrap`}
                                                    >
                                                        {legajo.edad !== null
                                                            ? `${legajo.edad} años`
                                                            : '—'}
                                                    </td>
                                                    <td className={tdClass}>
                                                        <div className="flex max-w-[260px] flex-wrap gap-1.5">
                                                            {legajo
                                                                .problematicas
                                                                .length > 0
                                                                ? legajo.problematicas.map(
                                                                      (
                                                                          nombre,
                                                                      ) => (
                                                                          <Badge
                                                                              key={
                                                                                  nombre
                                                                              }
                                                                              className="border-0 bg-[#EDF3EF] text-[#2C4A5E]"
                                                                          >
                                                                              {
                                                                                  nombre
                                                                              }
                                                                          </Badge>
                                                                      ),
                                                                  )
                                                                : '—'}
                                                        </div>
                                                    </td>
                                                    <td className={tdClass}>
                                                        <Badge
                                                            className={`border-0 ${NIVEL_ESTILO[legajo.nivel_riesgo] ?? 'bg-[#EEF0ED] text-[#2B2F36]'}`}
                                                        >
                                                            {legajo.nivel_riesgo ===
                                                            'Critico'
                                                                ? 'Crítico'
                                                                : legajo.nivel_riesgo}
                                                        </Badge>
                                                    </td>
                                                    <td className={tdClass}>
                                                        {legajo.estado}
                                                    </td>
                                                    <td className={tdClass}>
                                                        {
                                                            legajo.origen_derivacion
                                                        }
                                                    </td>
                                                    <td className={tdClass}>
                                                        {legajo.profesional ??
                                                            '—'}
                                                    </td>
                                                    <td
                                                        className={`${tdClass} whitespace-nowrap`}
                                                    >
                                                        {legajo.fecha_apertura ??
                                                            '—'}
                                                    </td>
                                                    <td
                                                        className={`${tdClass} text-right whitespace-nowrap`}
                                                    >
                                                        <div className="inline-flex items-center gap-1.5">
                                                            <Link
                                                                href={`/legajos/${legajo.id}`}
                                                                aria-label={`Ver legajo ${legajo.numero_legajo}`}
                                                                title="Ver legajo"
                                                                className="grid size-9 place-items-center rounded-md border border-[#DCE3DF] bg-white text-[#2C4A5E] transition hover:border-[#2C4A5E]/40 hover:bg-[#F4F6F5] focus-visible:ring-2 focus-visible:ring-[#2C4A5E]/30 focus-visible:outline-none"
                                                            >
                                                                <Eye
                                                                    size={16}
                                                                    strokeWidth={
                                                                        1.9
                                                                    }
                                                                />
                                                            </Link>
                                                            <Link
                                                                href={`/legajos/${legajo.id}/edit`}
                                                                aria-label={`Editar legajo ${legajo.numero_legajo}`}
                                                                title="Editar legajo"
                                                                className="grid size-9 place-items-center rounded-md border border-[#DCE3DF] bg-white text-[#2C4A5E] transition hover:border-[#2C4A5E]/40 hover:bg-[#F4F6F5] focus-visible:ring-2 focus-visible:ring-[#2C4A5E]/30 focus-visible:outline-none"
                                                            >
                                                                <Pencil
                                                                    size={15}
                                                                    strokeWidth={
                                                                        2
                                                                    }
                                                                />
                                                            </Link>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}

                            <Paginacion
                                paginador={legajos}
                                unidad={
                                    esMayores18 ? 'personas +18' : 'legajos'
                                }
                            />
                        </CardContent>
                    </Card>
                </div>
            </main>
        </>
    );
}
