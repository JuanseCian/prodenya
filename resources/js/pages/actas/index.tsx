import { Head, Link } from '@inertiajs/react';
import { Download, FileSignature, Link2, PenLine } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import VincularActaDialog from '@/components/vincular-acta-dialog';
import actasRoutes from '@/routes/actas';

interface TipoActa {
    tipo: string;
    label: string;
    descripcion: string;
    firma: boolean;
}

interface ActaResumen {
    id: number;
    tipo: string;
    etiqueta: string;
    titulo: string;
    fecha: string | null;
    firmado: boolean;
    requiere_firma: boolean;
    creado_por: string | null;
    legajo: { id: number; numero_legajo: string; url: string } | null;
    descarga_url: string;
    vincular_url: string;
}

interface Props {
    tipos: { grupo: string; tipos: TipoActa[] }[];
    sinLegajo: ActaResumen[];
    sinLegajoTotal: number;
    recientes: ActaResumen[];
    status?: string | null;
}

const fecha = (valor: string | null): string =>
    valor
        ? new Intl.DateTimeFormat('es-AR').format(new Date(`${valor}T12:00:00`))
        : '—';

function FilaActa({
    acta,
    onVincular,
}: {
    acta: ActaResumen;
    onVincular?: (acta: ActaResumen) => void;
}) {
    return (
        <li className="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="min-w-0">
                <p className="truncate text-sm font-medium text-foreground">
                    {acta.titulo}
                </p>
                <p className="mt-0.5 text-xs text-muted-foreground">
                    {acta.etiqueta} · {fecha(acta.fecha)}
                    {acta.creado_por && ` · ${acta.creado_por}`}
                    {acta.requiere_firma &&
                        (acta.firmado ? ' · Firmada' : ' · Sin firmar')}
                </p>
            </div>
            <div className="flex shrink-0 items-center gap-2">
                {acta.legajo ? (
                    <Link
                        href={acta.legajo.url}
                        className="text-xs font-medium text-primary underline-offset-4 hover:underline"
                    >
                        Legajo {acta.legajo.numero_legajo}
                    </Link>
                ) : (
                    onVincular && (
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            onClick={() => onVincular(acta)}
                        >
                            <Link2 className="size-3.5" />
                            Vincular
                        </Button>
                    )
                )}
                <Button asChild size="sm" variant="ghost">
                    <a
                        href={acta.descarga_url}
                        aria-label={`Descargar ${acta.etiqueta}`}
                    >
                        <Download className="size-3.5" />
                        Descargar
                    </a>
                </Button>
            </div>
        </li>
    );
}

export default function ActasIndex({
    tipos,
    sinLegajo,
    sinLegajoTotal,
    recientes,
    status,
}: Props) {
    const [vinculando, setVinculando] = useState<ActaResumen | null>(null);

    return (
        <>
            <Head title="Actas rápidas" />
            <main className="admin-page-shell min-h-screen bg-background px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div className="mx-auto max-w-5xl space-y-10">
                    <header>
                        <p className="text-xs font-semibold tracking-[0.14em] text-primary uppercase">
                            Documentación
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                            Actas rápidas
                        </h1>
                        <p className="mt-2 max-w-2xl text-sm text-muted-foreground">
                            Completá y firmá un acta aunque la persona todavía
                            no tenga legajo. Si no la vinculás al guardarla,
                            queda acá para hacerlo después.
                        </p>
                    </header>

                    {status && (
                        <p className="rounded-xl border border-success/25 bg-success/10 px-4 py-3 text-sm text-success">
                            {status}
                        </p>
                    )}

                    <section aria-labelledby="nueva-acta" className="space-y-5">
                        <h2
                            id="nueva-acta"
                            className="text-base font-semibold text-foreground"
                        >
                            Nueva acta
                        </h2>
                        {tipos.map((grupo) => (
                            <div key={grupo.grupo}>
                                <h3 className="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    {grupo.grupo}
                                </h3>
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {grupo.tipos.map((tipo) => (
                                        <Link
                                            key={tipo.tipo}
                                            href={actasRoutes.create.url(
                                                tipo.tipo,
                                            )}
                                            className="group flex items-start gap-3 rounded-xl border border-border bg-card p-4 transition hover:border-primary/40 hover:shadow-sm"
                                        >
                                            <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-secondary text-secondary-foreground">
                                                {tipo.firma ? (
                                                    <FileSignature className="size-4" />
                                                ) : (
                                                    <PenLine className="size-4" />
                                                )}
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block text-sm font-semibold text-foreground group-hover:text-primary">
                                                    {tipo.label}
                                                </span>
                                                <span className="mt-0.5 block text-xs leading-5 text-muted-foreground">
                                                    {tipo.descripcion}
                                                    {tipo.firma &&
                                                        ' Se firma en pantalla.'}
                                                </span>
                                            </span>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </section>

                    <section aria-labelledby="sin-legajo">
                        <div className="flex items-baseline justify-between gap-3">
                            <h2
                                id="sin-legajo"
                                className="text-base font-semibold text-foreground"
                            >
                                Sin legajo
                            </h2>
                            <Link
                                href={actasRoutes.pendientes.url()}
                                className="text-xs font-medium text-primary underline-offset-4 hover:underline"
                            >
                                {sinLegajoTotal > 0
                                    ? `Ver las ${sinLegajoTotal} sin vincular`
                                    : 'Ver sección'}
                            </Link>
                        </div>
                        {sinLegajo.length > 0 ? (
                            <ul className="mt-2 divide-y divide-border rounded-xl border border-border bg-card px-4">
                                {sinLegajo.map((acta) => (
                                    <FilaActa
                                        key={acta.id}
                                        acta={acta}
                                        onVincular={setVinculando}
                                    />
                                ))}
                            </ul>
                        ) : (
                            <p className="mt-2 rounded-xl border border-dashed border-border px-4 py-6 text-center text-sm text-muted-foreground">
                                No hay actas esperando un legajo.
                            </p>
                        )}
                    </section>

                    {recientes.length > 0 && (
                        <section aria-labelledby="recientes">
                            <h2
                                id="recientes"
                                className="text-base font-semibold text-foreground"
                            >
                                Últimas vinculadas
                            </h2>
                            <ul className="mt-2 divide-y divide-border rounded-xl border border-border bg-card px-4">
                                {recientes.map((acta) => (
                                    <FilaActa key={acta.id} acta={acta} />
                                ))}
                            </ul>
                        </section>
                    )}
                </div>
            </main>

            <VincularActaDialog
                acta={vinculando}
                onClose={() => setVinculando(null)}
            />
        </>
    );
}
