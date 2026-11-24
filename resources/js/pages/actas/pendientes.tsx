import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    Download,
    FileText,
    Link2,
    Search,
} from 'lucide-react';
import { useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import VincularActaDialog from '@/components/vincular-acta-dialog';
import actasRoutes from '@/routes/actas';

interface Seccion {
    titulo: string;
    campos: { label: string; valor: string }[];
    texto: boolean;
}

interface ActaPendiente {
    id: number;
    etiqueta: string;
    titulo: string;
    fecha: string | null;
    firmado: boolean;
    requiere_firma: boolean;
    creado_por: string | null;
    creado_en: string | null;
    firmas: { rol_label: string; nombre: string }[];
    secciones: Seccion[];
    descarga_url: string;
    vincular_url: string;
}

interface Paginador<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    actas: Paginador<ActaPendiente>;
    total: number;
    filtros: { buscar: string; tipo: string };
    tipos: { tipo: string; label: string }[];
    status?: string | null;
}

const fecha = (valor: string | null): string =>
    valor
        ? new Intl.DateTimeFormat('es-AR').format(
              new Date(valor.length === 10 ? `${valor}T12:00:00` : valor),
          )
        : '—';

const haceDias = (valor: string | null): string => {
    if (!valor) {
        return '';
    }

    const dias = Math.floor(
        (Date.now() - new Date(valor).getTime()) / 86_400_000,
    );

    if (dias <= 0) {
        return 'hoy';
    }

    return dias === 1 ? 'ayer' : `hace ${dias} días`;
};

function DetalleActa({
    acta,
    onVincular,
    onVolver,
}: {
    acta: ActaPendiente;
    onVincular: () => void;
    onVolver: () => void;
}) {
    return (
        <article className="flex h-full flex-col">
            <header className="border-b border-border px-5 py-4 sm:px-6">
                <button
                    type="button"
                    onClick={onVolver}
                    className="mb-3 inline-flex items-center gap-1 text-xs font-medium text-muted-foreground hover:text-foreground lg:hidden"
                >
                    <ChevronLeft className="size-4" />
                    Lista
                </button>
                <p className="text-xs font-semibold tracking-[0.12em] text-primary uppercase">
                    {acta.etiqueta}
                </p>
                <h2 className="mt-1 text-xl font-semibold tracking-tight break-words text-foreground">
                    {acta.titulo}
                </h2>
                <p className="mt-1 text-xs text-muted-foreground">
                    Del {fecha(acta.fecha)} · cargada {haceDias(acta.creado_en)}
                    {acta.creado_por && ` por ${acta.creado_por}`}
                </p>
                <div className="mt-4 flex flex-wrap gap-2">
                    <Button type="button" size="sm" onClick={onVincular}>
                        <Link2 className="size-3.5" />
                        Vincular a un legajo
                    </Button>
                    <Button asChild size="sm" variant="outline">
                        <a href={acta.descarga_url}>
                            <Download className="size-3.5" />
                            Descargar Word
                        </a>
                    </Button>
                </div>
            </header>

            <div className="flex-1 space-y-6 px-5 py-5 sm:px-6 lg:overflow-y-auto">
                {acta.requiere_firma && (
                    <section>
                        <h3 className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Firmas
                        </h3>
                        <p className="mt-2 text-sm text-foreground">
                            {acta.firmas.length > 0
                                ? acta.firmas
                                      .map(
                                          (firma) =>
                                              `${firma.nombre} (${firma.rol_label})`,
                                      )
                                      .join(' · ')
                                : 'Sin firmar en el sistema.'}
                        </p>
                    </section>
                )}

                {acta.secciones.length === 0 && (
                    <p className="text-sm text-muted-foreground">
                        El acta se guardó sin datos completados.
                    </p>
                )}

                {acta.secciones.map((seccion) => (
                    <section key={seccion.titulo}>
                        <h3 className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            {seccion.titulo}
                        </h3>
                        {seccion.texto ? (
                            <p className="mt-2 rounded-lg bg-surface-soft px-4 py-3 text-sm leading-6 whitespace-pre-wrap text-foreground">
                                {seccion.campos[0].valor}
                            </p>
                        ) : (
                            <dl className="mt-2 grid gap-x-8 sm:grid-cols-2">
                                {seccion.campos.map((campo) => (
                                    <div
                                        key={campo.label}
                                        className="flex items-baseline justify-between gap-4 border-b border-border py-2 text-sm"
                                    >
                                        <dt className="text-muted-foreground">
                                            {campo.label}
                                        </dt>
                                        <dd className="text-right font-medium break-words text-foreground">
                                            {campo.valor}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        )}
                    </section>
                ))}
            </div>
        </article>
    );
}

export default function ActasPendientes({
    actas,
    total,
    filtros,
    tipos,
    status,
}: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar);
    const [tipo, setTipo] = useState(filtros.tipo);
    const [seleccionadaId, setSeleccionadaId] = useState<number | null>(
        actas.data[0]?.id ?? null,
    );
    const [verDetalleEnMovil, setVerDetalleEnMovil] = useState(false);
    const [vinculando, setVinculando] = useState<ActaPendiente | null>(null);

    const seleccionada =
        actas.data.find((acta) => acta.id === seleccionadaId) ??
        actas.data[0] ??
        null;

    useEffect(() => {
        if (buscar === filtros.buscar && tipo === filtros.tipo) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                actasRoutes.pendientes.url({
                    query: {
                        ...(buscar ? { buscar } : {}),
                        ...(tipo ? { tipo } : {}),
                    },
                }),
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [buscar, tipo, filtros]);

    const irA = (url: string | null) =>
        url &&
        router.get(url, {}, { preserveState: true, preserveScroll: true });

    return (
        <>
            <Head title="Actas sin vincular" />
            <main className="admin-page-shell min-h-screen bg-background px-4 py-6 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-6xl space-y-5">
                    <Link
                        href={actasRoutes.index.url()}
                        className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        Actas rápidas
                    </Link>
                    <header className="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                                Actas sin vincular
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {total === 0
                                    ? 'No hay actas esperando un legajo.'
                                    : `${total} ${total === 1 ? 'acta espera' : 'actas esperan'} ser vinculada${total === 1 ? '' : 's'} a un legajo.`}
                            </p>
                        </div>
                    </header>

                    {status && (
                        <p className="rounded-xl border border-success/25 bg-success/10 px-4 py-3 text-sm text-success">
                            {status}
                        </p>
                    )}

                    <section className="grid overflow-hidden rounded-2xl border border-border bg-card lg:h-[calc(100svh-14rem)] lg:min-h-[520px] lg:grid-cols-[360px_minmax(0,1fr)]">
                        <div
                            className={`min-w-0 flex-col border-border lg:flex lg:min-h-0 lg:border-r ${verDetalleEnMovil ? 'hidden' : 'flex'}`}
                        >
                            <div className="space-y-2 border-b border-border p-3">
                                <div className="flex h-10 items-center gap-2 rounded-xl border border-border bg-background px-3 focus-within:border-ring focus-within:ring-2 focus-within:ring-ring/30">
                                    <Search className="size-4 shrink-0 text-muted-foreground" />
                                    <input
                                        type="search"
                                        value={buscar}
                                        onChange={(event) =>
                                            setBuscar(event.target.value)
                                        }
                                        placeholder="Buscar por nombre o DNI…"
                                        aria-label="Buscar actas"
                                        className="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-foreground outline-none placeholder:text-muted-foreground focus:ring-0"
                                    />
                                </div>
                                <select
                                    value={tipo}
                                    onChange={(event) =>
                                        setTipo(event.target.value)
                                    }
                                    aria-label="Tipo de acta"
                                    className="h-9 w-full rounded-xl border-input bg-background px-3 text-sm text-foreground outline-none focus:border-ring focus:ring-2 focus:ring-ring/30"
                                >
                                    <option value="">Todos los tipos</option>
                                    {tipos.map((opcion) => (
                                        <option
                                            key={opcion.tipo}
                                            value={opcion.tipo}
                                        >
                                            {opcion.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <ul className="min-h-[240px] flex-1 space-y-1 p-2 lg:min-h-0 lg:overflow-y-auto">
                                {actas.data.length === 0 ? (
                                    <li className="px-4 py-12 text-center">
                                        <FileText className="mx-auto size-7 text-muted-foreground" />
                                        <p className="mt-3 text-sm font-medium text-foreground">
                                            No hay actas para mostrar.
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {filtros.buscar || filtros.tipo
                                                ? 'Probá con otra búsqueda o tipo.'
                                                : 'Todas las actas están vinculadas.'}
                                        </p>
                                    </li>
                                ) : (
                                    actas.data.map((acta) => (
                                        <li key={acta.id}>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setSeleccionadaId(acta.id);
                                                    setVerDetalleEnMovil(true);
                                                }}
                                                aria-current={
                                                    acta.id === seleccionada?.id
                                                        ? 'true'
                                                        : undefined
                                                }
                                                className={`block w-full rounded-xl px-3.5 py-3 text-left transition-colors hover:bg-accent ${acta.id === seleccionada?.id ? 'bg-secondary ring-1 ring-primary/20' : ''}`}
                                            >
                                                <span className="flex items-start justify-between gap-2">
                                                    <span className="truncate text-sm font-semibold text-foreground">
                                                        {acta.titulo}
                                                    </span>
                                                    <time className="shrink-0 text-[11px] text-muted-foreground">
                                                        {fecha(acta.fecha)}
                                                    </time>
                                                </span>
                                                <span className="mt-0.5 block truncate text-xs text-muted-foreground">
                                                    {acta.etiqueta}
                                                    {acta.requiere_firma &&
                                                        (acta.firmado
                                                            ? ' · Firmada'
                                                            : ' · Sin firmar')}
                                                </span>
                                            </button>
                                        </li>
                                    ))
                                )}
                            </ul>

                            {actas.last_page > 1 && (
                                <div className="flex items-center justify-between border-t border-border px-4 py-2.5 text-xs text-muted-foreground">
                                    <span>
                                        {actas.from}-{actas.to} de {actas.total}
                                    </span>
                                    <div className="flex items-center gap-1">
                                        <button
                                            type="button"
                                            disabled={!actas.prev_page_url}
                                            onClick={() =>
                                                irA(actas.prev_page_url)
                                            }
                                            className="flex size-8 items-center justify-center rounded-lg hover:bg-accent disabled:opacity-40"
                                            aria-label="Página anterior"
                                        >
                                            <ChevronLeft className="size-4" />
                                        </button>
                                        <button
                                            type="button"
                                            disabled={!actas.next_page_url}
                                            onClick={() =>
                                                irA(actas.next_page_url)
                                            }
                                            className="flex size-8 items-center justify-center rounded-lg hover:bg-accent disabled:opacity-40"
                                            aria-label="Página siguiente"
                                        >
                                            <ChevronRight className="size-4" />
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        <div
                            className={`min-w-0 lg:block lg:min-h-0 ${verDetalleEnMovil ? 'block' : 'hidden'}`}
                        >
                            {seleccionada ? (
                                <DetalleActa
                                    acta={seleccionada}
                                    onVincular={() =>
                                        setVinculando(seleccionada)
                                    }
                                    onVolver={() => setVerDetalleEnMovil(false)}
                                />
                            ) : (
                                <div className="flex h-full min-h-[320px] items-center justify-center px-6 text-center text-sm text-muted-foreground">
                                    Elegí un acta para ver su contenido.
                                </div>
                            )}
                        </div>
                    </section>
                </div>
            </main>

            <VincularActaDialog
                acta={vinculando}
                onClose={() => setVinculando(null)}
            />
        </>
    );
}
