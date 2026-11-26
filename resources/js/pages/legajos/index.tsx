import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowUpRight,
    CalendarDays,
    ChevronDown,
    ChevronUp,
    FileDown,
    Filter,
    FolderOpen,
    FolderPlus,
    MoreHorizontal,
    Search,
    UsersRound,
    X,
} from 'lucide-react';
import { Fragment, useEffect, useState } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import legajosRoutes from '@/routes/legajos';

interface LegajoFila {
    id: number;
    numero_legajo: string;
    edad: number | null;
    persona: { id: number; nombre_completo: string; dni: string } | null;
    problematicas: string[];
    nivel_riesgo: string | null;
    estado: string | null;
    origen_derivacion: string | null;
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
    puedeExportarWord: boolean;
    status?: string | null;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

const NIVELES = ['Bajo', 'Medio', 'Alto', 'Critico'];

const NIVEL_ESTILO: Record<string, string> = {
    Bajo: 'bg-[#E3F0E6] text-[#41634A] dark:bg-[#1c3a2a] dark:text-[#9ce0bf]',
    Medio: 'bg-[#FBF1DC] text-[#8A6420] dark:bg-[#3a2f14] dark:text-[#e5ba6c]',
    Alto: 'bg-[#FCE9DC] text-[#9A4E1C] dark:bg-[#3a2416] dark:text-[#e99b8e]',
    Critico:
        'bg-[#F8E1DD] text-[#A84635] dark:bg-[#3a1f1a] dark:text-[#d58070]',
};

const thClass =
    'px-4 py-3 text-left text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.12em] text-primary uppercase whitespace-nowrap';
const tdClass = 'px-4 py-3.5 align-top text-sm text-foreground';
const selectClass =
    'h-11 rounded-lg border border-input bg-background px-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20';

const formatDni = (dni: string): string => {
    const digits = dni.replace(/\D/g, '');

    if (digits.length <= 4) {
        return '••••';
    }

    return `${digits.slice(0, 2)}${'•'.repeat(Math.max(2, digits.length - 4))}${digits.slice(-2)}`;
};

function LegajoActions({
    legajo,
    puedeExportarWord,
    mobile = false,
}: {
    legajo: LegajoFila;
    puedeExportarWord: boolean;
    mobile?: boolean;
}) {
    return (
        <div
            className={`flex items-center gap-2 ${mobile ? 'justify-between' : ''}`}
        >
            <Link
                href={`/legajos/${legajo.id}`}
                aria-label={`Abrir legajo ${legajo.numero_legajo}`}
                className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#2C4A5E] px-3 text-xs font-bold text-white transition hover:bg-[#173247] focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:hover:bg-[#3a5873]"
            >
                <ArrowUpRight size={14} />
                Abrir legajo
            </Link>
            {puedeExportarWord && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            aria-label={`Más acciones para ${legajo.numero_legajo}`}
                            className="grid size-9 place-items-center rounded-lg border border-border bg-card text-primary transition hover:border-primary/30 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <MoreHorizontal size={17} />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-52">
                        <DropdownMenuItem asChild>
                            <a
                                href={legajosRoutes.previsualizarWord.url(
                                    legajo.id,
                                )}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <FileDown />
                                Previsualizar Word
                            </a>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
        </div>
    );
}

function ProblematicasResumen({
    problematicas,
    expanded,
    onToggle,
}: {
    problematicas: string[];
    expanded: boolean;
    onToggle: () => void;
}) {
    if (problematicas.length === 0) {
        return <span className="text-muted-foreground">—</span>;
    }

    return (
        <div className="flex max-w-[250px] flex-wrap items-center gap-1.5">
            <Badge className="max-w-full truncate border-0 bg-secondary text-primary">
                {problematicas[0]}
            </Badge>
            {problematicas.length > 1 && (
                <button
                    type="button"
                    onClick={onToggle}
                    aria-expanded={expanded}
                    className="rounded-full border border-border px-2 py-1 text-xs font-semibold text-primary transition hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    +{problematicas.length - 1}
                </button>
            )}
        </div>
    );
}

function DetalleExpandido({ legajo }: { legajo: LegajoFila }) {
    return (
        <div className="grid gap-4 border-t border-border bg-surface-soft px-5 py-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p className="text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.12em] text-primary uppercase">
                    Estado
                </p>
                <p className="mt-1 text-sm font-semibold text-foreground">
                    {legajo.estado ?? 'Reservado'}
                </p>
            </div>
            <div>
                <p className="text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.12em] text-primary uppercase">
                    Profesional responsable
                </p>
                <p className="mt-1 text-sm text-foreground">
                    {legajo.profesional ?? 'Sin profesional asignado'}
                </p>
            </div>
            <div>
                <p className="text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.12em] text-primary uppercase">
                    Derivación
                </p>
                <p className="mt-1 text-sm text-foreground">
                    {legajo.origen_derivacion ?? 'Sin informar'}
                </p>
            </div>
            <div className="sm:col-span-2 lg:col-span-1">
                <p className="text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.12em] text-primary uppercase">
                    Problemáticas completas
                </p>
                <div className="mt-2 flex flex-wrap gap-1.5">
                    {legajo.problematicas.length > 0 ? (
                        legajo.problematicas.map((problematica) => (
                            <Badge
                                key={problematica}
                                className="border-0 bg-card text-primary"
                            >
                                {problematica}
                            </Badge>
                        ))
                    ) : (
                        <span className="text-sm text-muted-foreground">
                            Sin problemáticas informadas
                        </span>
                    )}
                </div>
            </div>
        </div>
    );
}

export default function LegajosIndex({
    legajos,
    seccion,
    filtros,
    estados,
    problematicas,
    edades,
    puedeExportarWord,
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
    const [filtrosAbiertos, setFiltrosAbiertos] = useState(false);
    const [filaExpandida, setFilaExpandida] = useState<number | null>(null);
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
        buscar,
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
                className="admin-page-shell legajos-page min-h-screen bg-background px-4 py-6 sm:px-6 sm:py-8 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mx-auto max-w-7xl">
                    <header className="mb-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div className="flex items-center gap-2 text-sm font-medium text-primary">
                                {esMayores18 ? (
                                    <UsersRound size={16} />
                                ) : (
                                    <FolderOpen size={16} />
                                )}
                                <span>
                                    {esMayores18
                                        ? 'Personas +18'
                                        : 'Gestión de casos'}
                                </span>
                            </div>
                            <h1
                                className="mt-2 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                {esMayores18 ? '+18' : 'Legajos'}
                            </h1>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-foreground/65">
                                {esMayores18
                                    ? 'Personas que cumplieron 18 años y continúan abiertas en otra instancia de acompañamiento.'
                                    : 'Gestión y seguimiento de intervenciones.'}
                            </p>
                        </div>

                        <div className="flex flex-col items-stretch gap-3 sm:items-end">
                            <Link
                                href="/legajos/create"
                                className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#2C4A5E] px-5 text-sm font-bold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-[#173247] focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:hover:bg-[#3a5873]"
                            >
                                <FolderPlus size={16} />
                                Nuevo legajo
                            </Link>
                            <nav
                                aria-label="Secciones de legajos"
                                className="flex items-center rounded-xl border border-border bg-card p-1 shadow-[0_6px_18px_rgba(23,50,71,0.04)]"
                            >
                                <Link
                                    href="/legajos"
                                    aria-current={
                                        !esMayores18 ? 'page' : undefined
                                    }
                                    className={`inline-flex h-9 items-center gap-1.5 rounded-lg px-3 text-xs font-semibold transition ${!esMayores18 ? 'bg-[#2C4A5E] text-white' : 'text-muted-foreground hover:bg-accent hover:text-foreground'}`}
                                >
                                    <FolderOpen size={14} />
                                    Legajos
                                </Link>
                                <Link
                                    href="/mayores-18"
                                    aria-current={
                                        esMayores18 ? 'page' : undefined
                                    }
                                    className={`inline-flex h-9 items-center gap-1.5 rounded-lg px-3 text-xs font-semibold transition ${esMayores18 ? 'bg-[#2C4A5E] text-white' : 'text-muted-foreground hover:bg-accent hover:text-foreground'}`}
                                >
                                    <UsersRound size={14} />
                                    +18
                                </Link>
                            </nav>
                        </div>
                    </header>

                    {status && (
                        <div
                            role="status"
                            className="mb-5 rounded-xl border border-success/40 bg-success/10 px-4 py-3 text-sm font-medium text-success"
                        >
                            {status}
                        </div>
                    )}

                    <section className="mb-5 rounded-2xl border border-border bg-card p-4 shadow-[0_10px_26px_rgba(23,50,71,0.05)] sm:p-5">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
                            <div className="w-full">
                                <label
                                    htmlFor="buscar-legajos"
                                    className="text-sm font-semibold text-foreground"
                                >
                                    Buscar un legajo
                                </label>
                                <div className="relative mt-2">
                                    <Search className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-foreground/40" />
                                    <Input
                                        id="buscar-legajos"
                                        value={buscar}
                                        onChange={(event) =>
                                            setBuscar(event.target.value)
                                        }
                                        placeholder="Código, nombre, apellido, DNI o problemática"
                                        className="h-11 border-input bg-background pl-10 text-foreground shadow-none focus-visible:border-primary focus-visible:ring-primary/20"
                                    />
                                </div>
                            </div>
                            <button
                                type="button"
                                aria-expanded={filtrosAbiertos}
                                onClick={() =>
                                    setFiltrosAbiertos((abierto) => !abierto)
                                }
                                className={`inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none ${filtrosAbiertos || cantidadFiltros > 1 ? 'border-primary bg-accent text-primary' : 'border-input bg-card text-foreground/70 hover:border-primary/40 hover:text-primary'}`}
                            >
                                <Filter size={15} />
                                Filtros avanzados
                                {cantidadFiltros > 0 && (
                                    <span className="grid size-5 place-items-center rounded-full bg-primary text-[length:calc(0.68rem_+_2px)] text-primary-foreground">
                                        {cantidadFiltros}
                                    </span>
                                )}
                            </button>
                        </div>
                    </section>

                    <div
                        className={`prodenya-filter-layout ${filtrosAbiertos ? 'is-open' : 'is-closed'}`}
                    >
                        <aside
                            aria-hidden={!filtrosAbiertos}
                            inert={!filtrosAbiertos}
                            className={`prodenya-filter-panel h-fit rounded-2xl border border-border bg-card p-5 shadow-[0_10px_26px_rgba(23,50,71,0.04)] lg:sticky lg:top-6 ${filtrosAbiertos ? 'is-visible' : 'is-hidden'}`}
                        >
                            <div className="flex items-start justify-between gap-3 border-b border-border pb-4">
                                <div>
                                    <p className="text-sm font-semibold text-foreground">
                                        Filtros avanzados
                                    </p>
                                    <p className="mt-1 text-xs leading-5 text-foreground/55">
                                        Combiná criterios para encontrar un
                                        legajo puntual.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    aria-label="Ocultar filtros avanzados"
                                    onClick={() => setFiltrosAbiertos(false)}
                                    className="grid size-7 shrink-0 place-items-center rounded-lg text-foreground/45 transition hover:bg-accent hover:text-primary focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:outline-none"
                                >
                                    <X size={16} />
                                </button>
                            </div>
                            <div className="mt-4 space-y-4">
                                <label className="block text-xs font-semibold text-foreground/70">
                                    Número de legajo
                                    <Input
                                        value={numeroLegajo}
                                        onChange={(event) =>
                                            setNumeroLegajo(event.target.value)
                                        }
                                        placeholder="Ej. GON-MAR-123"
                                        className="mt-1.5 h-10 border-input bg-background text-sm font-normal text-foreground shadow-none focus-visible:border-primary focus-visible:ring-primary/20"
                                    />
                                </label>
                                <label className="block text-xs font-semibold text-foreground/70">
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
                                <label className="block text-xs font-semibold text-foreground/70">
                                    Edad
                                    <select
                                        value={edad}
                                        onChange={(event) =>
                                            setEdad(event.target.value)
                                        }
                                        className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                    >
                                        <option value="">
                                            Todas las edades
                                        </option>
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
                                <label className="block text-xs font-semibold text-foreground/70">
                                    Nivel de riesgo
                                    <select
                                        value={nivel}
                                        onChange={(event) =>
                                            setNivel(event.target.value)
                                        }
                                        className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                    >
                                        <option value="">
                                            Todos los riesgos
                                        </option>
                                        {NIVELES.map((opcion) => (
                                            <option key={opcion} value={opcion}>
                                                {opcion === 'Critico'
                                                    ? 'Crítico'
                                                    : opcion}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="block text-xs font-semibold text-foreground/70">
                                    Estado
                                    <select
                                        value={estado}
                                        onChange={(event) =>
                                            setEstado(event.target.value)
                                        }
                                        className={`${selectClass} mt-1.5 h-10 w-full font-normal`}
                                    >
                                        <option value="">
                                            Todos los estados
                                        </option>
                                        {estados.map((opcion) => (
                                            <option key={opcion} value={opcion}>
                                                {opcion}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                            </div>
                        </aside>

                        <Card className="overflow-hidden rounded-2xl border-border py-0 shadow-[0_12px_30px_rgba(23,50,71,0.05)]">
                            <div className="flex flex-col gap-2 border-b border-border bg-surface-soft px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div>
                                    <p className="text-sm font-semibold text-foreground">
                                        {esMayores18
                                            ? 'Personas en seguimiento'
                                            : 'Listado de legajos'}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {legajos.total === 0
                                            ? 'No hay registros para mostrar.'
                                            : `Mostrando ${legajos.from}–${legajos.to} de ${legajos.total} ${esMayores18 ? 'personas' : 'legajos'}.`}
                                    </p>
                                </div>
                                {filaExpandida !== null && (
                                    <button
                                        type="button"
                                        onClick={() => setFilaExpandida(null)}
                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <ChevronUp size={14} />
                                        Cerrar detalle
                                    </button>
                                )}
                            </div>

                            <CardContent className="p-0">
                                {legajos.data.length === 0 ? (
                                    <div className="px-6 py-16 text-center">
                                        <Search className="mx-auto size-7 text-primary" />
                                        <p className="mt-3 text-sm font-semibold text-foreground">
                                            {hayFiltros
                                                ? `No se encontraron personas ${esMayores18 ? '+18' : 'con legajos'} con esos filtros.`
                                                : esMayores18
                                                  ? 'Todavía no hay personas en la sección +18.'
                                                  : 'Todavía no hay legajos abiertos.'}
                                        </p>
                                        <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground">
                                            {hayFiltros
                                                ? 'Probá con otro dato o limpiá los filtros para ampliar la búsqueda.'
                                                : 'Cuando se cree un legajo, aparecerá en este listado.'}
                                        </p>
                                        {!hayFiltros && (
                                            <Link
                                                href="/legajos/create"
                                                className="mt-5 inline-flex h-10 items-center gap-2 rounded-xl bg-[#2C4A5E] px-4 text-sm font-bold text-white transition hover:bg-[#173247] focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:hover:bg-[#3a5873]"
                                            >
                                                <FolderPlus size={15} />
                                                {esMayores18
                                                    ? 'Abrir un legajo'
                                                    : 'Abrir el primer legajo'}
                                            </Link>
                                        )}
                                    </div>
                                ) : (
                                    <>
                                        <div className="divide-y divide-border md:hidden">
                                            {legajos.data.map((legajo) => {
                                                const expanded =
                                                    filaExpandida === legajo.id;

                                                return (
                                                    <article
                                                        key={legajo.id}
                                                        className="p-4"
                                                    >
                                                        <div className="flex items-start justify-between gap-3">
                                                            <div className="min-w-0">
                                                                <p className="text-xs font-medium text-primary">
                                                                    Legajo
                                                                </p>
                                                                <Link
                                                                    href={`/legajos/${legajo.id}`}
                                                                    className="mt-1 block truncate font-mono text-base font-semibold text-foreground hover:underline"
                                                                >
                                                                    {
                                                                        legajo.numero_legajo
                                                                    }
                                                                </Link>
                                                            </div>
                                                            <Badge
                                                                className={`shrink-0 border-0 ${legajo.nivel_riesgo ? (NIVEL_ESTILO[legajo.nivel_riesgo] ?? 'bg-muted text-foreground') : 'bg-muted text-foreground'}`}
                                                            >
                                                                {legajo.nivel_riesgo ===
                                                                'Critico'
                                                                    ? 'Crítico'
                                                                    : (legajo.nivel_riesgo ??
                                                                      'Reservado')}
                                                            </Badge>
                                                        </div>
                                                        <div className="mt-4 grid gap-3 border-t border-border pt-4">
                                                            <div>
                                                                <p className="text-xs font-medium text-primary">
                                                                    Persona
                                                                </p>
                                                                <p className="mt-1 text-sm font-semibold text-foreground">
                                                                    {legajo
                                                                        .persona
                                                                        ?.nombre_completo ??
                                                                        'Datos reservados'}
                                                                </p>
                                                                {legajo.persona && (
                                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                                        {legajo.edad !==
                                                                        null
                                                                            ? `${legajo.edad} años`
                                                                            : 'Edad sin informar'}{' '}
                                                                        · DNI{' '}
                                                                        {formatDni(
                                                                            legajo
                                                                                .persona
                                                                                .dni,
                                                                        )}
                                                                    </p>
                                                                )}
                                                            </div>
                                                            <div>
                                                                <p className="text-xs font-medium text-primary">
                                                                    Problemáticas
                                                                </p>
                                                                <div className="mt-2">
                                                                    <ProblematicasResumen
                                                                        problematicas={
                                                                            legajo.problematicas
                                                                        }
                                                                        expanded={
                                                                            expanded
                                                                        }
                                                                        onToggle={() =>
                                                                            setFilaExpandida(
                                                                                expanded
                                                                                    ? null
                                                                                    : legajo.id,
                                                                            )
                                                                        }
                                                                    />
                                                                </div>
                                                            </div>
                                                            <div className="grid grid-cols-2 gap-3">
                                                                <div>
                                                                    <p className="text-xs font-medium text-primary">
                                                                        Seguimiento
                                                                    </p>
                                                                    <p className="mt-1 text-sm font-semibold text-foreground">
                                                                        {legajo.estado ??
                                                                            'Reservado'}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <p className="text-xs font-medium text-primary">
                                                                        Apertura
                                                                    </p>
                                                                    <p className="mt-1 flex items-center gap-1.5 text-sm text-foreground">
                                                                        <CalendarDays
                                                                            size={
                                                                                14
                                                                            }
                                                                            className="text-primary"
                                                                        />
                                                                        {legajo.fecha_apertura ??
                                                                            '—'}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {expanded && (
                                                            <DetalleExpandido
                                                                legajo={legajo}
                                                            />
                                                        )}
                                                        <div className="mt-4 border-t border-border pt-4">
                                                            <LegajoActions
                                                                legajo={legajo}
                                                                puedeExportarWord={
                                                                    puedeExportarWord
                                                                }
                                                                mobile
                                                            />
                                                        </div>
                                                    </article>
                                                );
                                            })}
                                        </div>

                                        <div className="hidden overflow-x-auto md:block">
                                            <table className="w-full min-w-[960px] border-collapse">
                                                <colgroup>
                                                    <col className="w-[15%]" />
                                                    <col className="w-[21%]" />
                                                    <col className="w-[18%]" />
                                                    <col className="w-[10%]" />
                                                    <col className="w-[17%]" />
                                                    <col className="w-[11%]" />
                                                    <col className="w-[8%]" />
                                                </colgroup>
                                                <thead className="bg-surface-soft">
                                                    <tr className="border-b border-border">
                                                        {[
                                                            'Legajo',
                                                            'Persona',
                                                            'Problemáticas',
                                                            'Riesgo',
                                                            'Seguimiento',
                                                            'Apertura',
                                                            'Acciones',
                                                        ].map((titulo) => (
                                                            <th
                                                                key={titulo}
                                                                scope="col"
                                                                className={
                                                                    thClass
                                                                }
                                                            >
                                                                {titulo ===
                                                                'Acciones' ? (
                                                                    <span className="sr-only">
                                                                        Acciones
                                                                    </span>
                                                                ) : (
                                                                    titulo
                                                                )}
                                                            </th>
                                                        ))}
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-border">
                                                    {legajos.data.map(
                                                        (legajo) => {
                                                            const expanded =
                                                                filaExpandida ===
                                                                legajo.id;

                                                            return (
                                                                <Fragment
                                                                    key={
                                                                        legajo.id
                                                                    }
                                                                >
                                                                    <tr
                                                                        className={`transition hover:bg-muted/60 ${expanded ? 'bg-muted/60' : ''}`}
                                                                    >
                                                                        <td
                                                                            className={`${tdClass} font-mono font-semibold text-primary`}
                                                                        >
                                                                            <div className="flex items-start gap-2">
                                                                                <button
                                                                                    type="button"
                                                                                    aria-expanded={
                                                                                        expanded
                                                                                    }
                                                                                    aria-controls={`detalle-legajo-${legajo.id}`}
                                                                                    aria-label={`${expanded ? 'Ocultar' : 'Mostrar'} detalle de ${legajo.numero_legajo}`}
                                                                                    onClick={() =>
                                                                                        setFilaExpandida(
                                                                                            expanded
                                                                                                ? null
                                                                                                : legajo.id,
                                                                                        )
                                                                                    }
                                                                                    className="mt-0.5 grid size-6 shrink-0 place-items-center rounded-md text-primary transition hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                                                >
                                                                                    {expanded ? (
                                                                                        <ChevronUp
                                                                                            size={
                                                                                                15
                                                                                            }
                                                                                        />
                                                                                    ) : (
                                                                                        <ChevronDown
                                                                                            size={
                                                                                                15
                                                                                            }
                                                                                        />
                                                                                    )}
                                                                                </button>
                                                                                <Link
                                                                                    href={`/legajos/${legajo.id}`}
                                                                                    className="pt-0.5 hover:underline"
                                                                                >
                                                                                    {
                                                                                        legajo.numero_legajo
                                                                                    }
                                                                                </Link>
                                                                            </div>
                                                                            <span className="mt-1 block pl-8 font-sans text-xs font-normal text-muted-foreground">
                                                                                #
                                                                                {
                                                                                    legajo.id
                                                                                }
                                                                            </span>
                                                                        </td>
                                                                        <td
                                                                            className={
                                                                                tdClass
                                                                            }
                                                                        >
                                                                            {legajo.persona ? (
                                                                                <>
                                                                                    <p className="font-semibold text-foreground">
                                                                                        {
                                                                                            legajo
                                                                                                .persona
                                                                                                .nombre_completo
                                                                                        }
                                                                                    </p>
                                                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                                                        {legajo.edad !==
                                                                                        null
                                                                                            ? `${legajo.edad} años`
                                                                                            : 'Edad sin informar'}{' '}
                                                                                        ·
                                                                                        DNI{' '}
                                                                                        {formatDni(
                                                                                            legajo
                                                                                                .persona
                                                                                                .dni,
                                                                                        )}
                                                                                    </p>
                                                                                </>
                                                                            ) : (
                                                                                <span className="text-muted-foreground">
                                                                                    Datos
                                                                                    reservados
                                                                                </span>
                                                                            )}
                                                                        </td>
                                                                        <td
                                                                            className={
                                                                                tdClass
                                                                            }
                                                                        >
                                                                            <ProblematicasResumen
                                                                                problematicas={
                                                                                    legajo.problematicas
                                                                                }
                                                                                expanded={
                                                                                    expanded
                                                                                }
                                                                                onToggle={() =>
                                                                                    setFilaExpandida(
                                                                                        expanded
                                                                                            ? null
                                                                                            : legajo.id,
                                                                                    )
                                                                                }
                                                                            />
                                                                        </td>
                                                                        <td
                                                                            className={
                                                                                tdClass
                                                                            }
                                                                        >
                                                                            <Badge
                                                                                className={`border-0 ${legajo.nivel_riesgo ? (NIVEL_ESTILO[legajo.nivel_riesgo] ?? 'bg-muted text-foreground') : 'bg-muted text-foreground'}`}
                                                                            >
                                                                                {legajo.nivel_riesgo ===
                                                                                'Critico'
                                                                                    ? 'Crítico'
                                                                                    : (legajo.nivel_riesgo ??
                                                                                      'Reservado')}
                                                                            </Badge>
                                                                        </td>
                                                                        <td
                                                                            className={
                                                                                tdClass
                                                                            }
                                                                        >
                                                                            <p className="font-semibold text-foreground">
                                                                                {legajo.estado ??
                                                                                    'Reservado'}
                                                                            </p>
                                                                            <p className="mt-1 text-xs text-muted-foreground">
                                                                                {legajo.profesional ??
                                                                                    'Sin profesional asignado'}
                                                                            </p>
                                                                        </td>
                                                                        <td
                                                                            className={`${tdClass} whitespace-nowrap`}
                                                                        >
                                                                            <span className="inline-flex items-center gap-1.5">
                                                                                <CalendarDays
                                                                                    size={
                                                                                        14
                                                                                    }
                                                                                    className="text-primary"
                                                                                />
                                                                                {legajo.fecha_apertura ??
                                                                                    '—'}
                                                                            </span>
                                                                        </td>
                                                                        <td
                                                                            className={`${tdClass} text-right whitespace-nowrap`}
                                                                        >
                                                                            <LegajoActions
                                                                                legajo={
                                                                                    legajo
                                                                                }
                                                                                puedeExportarWord={
                                                                                    puedeExportarWord
                                                                                }
                                                                            />
                                                                        </td>
                                                                    </tr>
                                                                    {expanded && (
                                                                        <tr
                                                                            id={`detalle-legajo-${legajo.id}`}
                                                                            key={`${legajo.id}-detalle`}
                                                                        >
                                                                            <td
                                                                                colSpan={
                                                                                    7
                                                                                }
                                                                                className="p-0"
                                                                            >
                                                                                <DetalleExpandido
                                                                                    legajo={
                                                                                        legajo
                                                                                    }
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    )}
                                                                </Fragment>
                                                            );
                                                        },
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    </>
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
                </div>
            </main>
        </>
    );
}
