import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Printer } from 'lucide-react';
import type { ReactNode } from 'react';

import { FONT_BODY, FONT_HEADING } from '@/components/detalle';
import { BarsChart, TrendChart } from '@/components/estadisticas-chart';
import type { Intervalo, NombreTotal } from '@/components/estadisticas-types';
import legajosRoutes from '@/routes/legajos';

interface Estadisticas {
    resumen: {
        fecha_apertura: string | null;
        dias_abierto: number | null;
        intervenciones: number;
        intervenciones_ultimos_30_dias: number;
        ultima_intervencion: string | null;
        dias_desde_ultima: number | null;
        derivaciones: number;
        derivaciones_pendientes: number;
        firmas: { firmadas: number; total: number; porcentaje: number | null };
    };
    puntos_clave: { tema: string; texto: string }[];
    actividad: {
        por_mes: { intervalos: Intervalo[]; valores: number[] };
        por_tipo: NombreTotal[];
        por_profesional: NombreTotal[];
        promedio_mensual: number | null;
        dias_promedio_entre: number | null;
        primera: string | null;
        tareas: { pendientes: number; completadas: number };
    };
    circuito: {
        por_estado: NombreTotal[];
        dias_promedio_respuesta: number | null;
        pendiente_mas_antigua_dias: number | null;
        responsables: { nombre: string; principal: boolean }[];
        ultimas: {
            id: number;
            fecha: string | null;
            destinatario: string;
            estado: string;
            dias_respuesta: number | null;
        }[];
    };
    documentacion: {
        firmas: { responsable: number; entrevistado: number };
        documentos: number;
        mensajes: number;
        auh: {
            cargados: number;
            obligatorios: number;
            planillas_firmadas: number;
            planillas_total: number;
        } | null;
    };
    auditoria: {
        total: number;
        visualizaciones: number;
        descargas: number;
        usuarios_distintos: number;
        ultimo_acceso: string | null;
        puede_ver_detalle: boolean;
        por_usuario: NombreTotal[];
    };
}

interface Props {
    legajo: {
        id: number;
        numero_legajo: string;
        nombre: string | null;
        estado: string;
        nivel_riesgo: string | null;
    };
    estadisticas: Estadisticas;
}

const fecha = (valor: string | null): string =>
    valor
        ? new Intl.DateTimeFormat('es-AR').format(
              new Date(valor.length === 10 ? `${valor}T12:00:00` : valor),
          )
        : '—';

const hace = (dias: number | null): string => {
    if (dias === null) {
        return '';
    }

    if (dias === 0) {
        return 'hoy';
    }

    return dias === 1 ? 'ayer' : `hace ${dias} días`;
};

const dias = (valor: number | null): string =>
    valor === null
        ? '—'
        : `${valor.toLocaleString('es-AR', { maximumFractionDigits: 1 })} ${valor === 1 ? 'día' : 'días'}`;

/** Color del punto de estado de cada derivación. */
const colorEstado: Record<string, string> = {
    pendiente: 'bg-[#C98A1B]',
    aceptada: 'bg-[#39795B]',
    rechazada: 'bg-[#A8493C]',
    cancelada: 'bg-muted-foreground/50',
};

const estadoDesdeNombre: Record<string, string> = {
    Pendientes: 'pendiente',
    Aceptadas: 'aceptada',
    Rechazadas: 'rechazada',
    Canceladas: 'cancelada',
};

function Tarjeta({
    titulo,
    meta,
    children,
    className = '',
}: {
    titulo: string;
    meta?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section
            className={`break-inside-avoid rounded-2xl border border-border bg-card p-5 sm:p-6 ${className}`}
        >
            <div className="mb-5 flex items-baseline justify-between gap-4">
                <h2
                    className="text-base font-semibold text-foreground"
                    style={{ fontFamily: FONT_HEADING }}
                >
                    {titulo}
                </h2>
                {meta && (
                    <p className="text-xs text-muted-foreground">{meta}</p>
                )}
            </div>
            {children}
        </section>
    );
}

function Cifra({
    label,
    valor,
    detalle,
}: {
    label: string;
    valor: ReactNode;
    detalle: string;
}) {
    return (
        <div className="px-5 py-5">
            <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </p>
            <p
                className="mt-2 text-3xl font-semibold tracking-tight text-foreground tabular-nums"
                style={{ fontFamily: FONT_HEADING }}
            >
                {valor}
            </p>
            <p className="mt-1 text-xs text-muted-foreground">{detalle}</p>
        </div>
    );
}

function Fila({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex items-baseline justify-between gap-4 border-b border-border py-3 text-sm last:border-0">
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="text-right font-medium text-foreground tabular-nums">
                {children}
            </dd>
        </div>
    );
}

function Progreso({
    label,
    valor,
    total,
    detalle,
}: {
    label: string;
    valor: number;
    total: number;
    detalle?: string;
}) {
    const porcentaje = total > 0 ? Math.round((valor * 100) / total) : 0;

    return (
        <div>
            <div className="flex items-baseline justify-between gap-4 text-sm">
                <span className="text-muted-foreground">{label}</span>
                <span className="font-medium text-foreground tabular-nums">
                    {valor} de {total}
                </span>
            </div>
            <div
                className="mt-2 h-1.5 overflow-hidden rounded-full bg-muted"
                role="progressbar"
                aria-label={label}
                aria-valuenow={porcentaje}
                aria-valuemin={0}
                aria-valuemax={100}
            >
                <div
                    className="h-full rounded-full bg-[#39795B]"
                    style={{ width: `${porcentaje}%` }}
                />
            </div>
            {detalle && (
                <p className="mt-1.5 text-xs text-muted-foreground">
                    {detalle}
                </p>
            )}
        </div>
    );
}

function Subtitulo({ children }: { children: ReactNode }) {
    return (
        <h3 className="mb-4 text-xs font-medium tracking-wide text-muted-foreground uppercase">
            {children}
        </h3>
    );
}

export default function LegajoEstadisticas({ legajo, estadisticas }: Props) {
    const { resumen, actividad, circuito, documentacion, auditoria } =
        estadisticas;
    const total = resumen.intervenciones;
    const cerrado = legajo.estado === 'Cerrado';
    const datosLegajo = [
        `N° ${legajo.numero_legajo}`,
        legajo.nivel_riesgo && `Riesgo ${legajo.nivel_riesgo.toLowerCase()}`,
        resumen.fecha_apertura &&
            `Abierto el ${fecha(resumen.fecha_apertura)}, ${hace(resumen.dias_abierto)}`,
    ].filter(Boolean);
    const ritmo = [
        total > 0 &&
            `${actividad.promedio_mensual?.toLocaleString('es-AR')} por mes`,
        actividad.dias_promedio_entre !== null &&
            `una cada ${dias(actividad.dias_promedio_entre)}`,
        `${actividad.tareas.pendientes} tareas pendientes`,
    ].filter(Boolean);
    const primerMes = actividad.por_mes.intervalos[0]?.label;

    return (
        <>
            <Head title={`Estadísticas · ${legajo.numero_legajo}`} />
            <main
                className="stats-module-shell min-h-screen px-4 py-6 sm:px-6 sm:py-10 lg:px-8 print:p-0"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mx-auto max-w-5xl space-y-6">
                    <div className="flex items-center justify-between gap-4 print:hidden">
                        <Link
                            href={legajosRoutes.show.url(legajo.id)}
                            className="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            <ArrowLeft size={14} />
                            Volver al legajo
                        </Link>
                        <button
                            type="button"
                            onClick={() => window.print()}
                            className="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-card px-3 text-sm text-foreground transition hover:bg-accent"
                        >
                            <Printer size={14} />
                            Imprimir
                        </button>
                    </div>

                    <header className="pt-2 pb-2">
                        <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Estadísticas del legajo
                        </p>
                        <h1
                            className="mt-2 text-3xl font-semibold tracking-tight break-words text-foreground"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            {legajo.nombre ?? `Legajo ${legajo.numero_legajo}`}
                        </h1>
                        <p className="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted-foreground">
                            <span className="inline-flex items-center gap-1.5 font-medium text-foreground">
                                <span
                                    className={`size-2 rounded-full ${cerrado ? 'bg-muted-foreground/50' : 'bg-[#39795B]'}`}
                                />
                                {legajo.estado}
                            </span>
                            {datosLegajo.map((dato) => (
                                <span key={dato as string}>· {dato}</span>
                            ))}
                        </p>
                    </header>

                    <div className="grid grid-cols-2 divide-border overflow-hidden rounded-2xl border border-border bg-card sm:grid-cols-4 sm:divide-x [&>*]:border-border max-sm:[&>*:nth-child(-n+2)]:border-b">
                        <Cifra
                            label="Intervenciones"
                            valor={total}
                            detalle={`${resumen.intervenciones_ultimos_30_dias} en los últimos 30 días`}
                        />
                        <Cifra
                            label="Última"
                            valor={fecha(resumen.ultima_intervencion)}
                            detalle={
                                resumen.ultima_intervencion
                                    ? hace(resumen.dias_desde_ultima)
                                    : 'Sin intervenciones'
                            }
                        />
                        <Cifra
                            label="Derivaciones"
                            valor={resumen.derivaciones}
                            detalle={
                                resumen.derivaciones_pendientes === 1
                                    ? '1 sin respuesta'
                                    : `${resumen.derivaciones_pendientes} sin respuesta`
                            }
                        />
                        <Cifra
                            label="Firmadas"
                            valor={
                                resumen.firmas.porcentaje === null
                                    ? '—'
                                    : `${resumen.firmas.porcentaje}%`
                            }
                            detalle={`${resumen.firmas.firmadas} de ${resumen.firmas.total} intervenciones`}
                        />
                    </div>

                    {estadisticas.puntos_clave.length > 0 && (
                        <section className="break-inside-avoid rounded-2xl bg-surface-soft px-5 py-5 sm:px-6">
                            <h2 className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                En resumen
                            </h2>
                            <ul className="mt-3 space-y-2">
                                {estadisticas.puntos_clave.map((punto) => (
                                    <li
                                        key={punto.texto}
                                        className="flex gap-3 text-sm leading-6 text-foreground/90"
                                    >
                                        <span className="mt-2.5 size-1.5 shrink-0 rounded-full bg-[#39795B]" />
                                        {punto.texto}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}

                    <Tarjeta
                        titulo="Actividad"
                        meta={primerMes ? `Desde ${primerMes}` : undefined}
                    >
                        <TrendChart
                            intervals={actividad.por_mes.intervalos}
                            values={actividad.por_mes.valores}
                            label="Intervenciones"
                        />
                        <p className="mt-3 text-sm text-muted-foreground">
                            {ritmo.join(' · ')}
                        </p>
                        {total > 0 && (
                            <div className="mt-6 grid gap-8 border-t border-border pt-6 md:grid-cols-2">
                                <div>
                                    <Subtitulo>Por tipo</Subtitulo>
                                    <BarsChart items={actividad.por_tipo} />
                                </div>
                                <div>
                                    <Subtitulo>Por profesional</Subtitulo>
                                    <BarsChart
                                        items={actividad.por_profesional}
                                    />
                                </div>
                            </div>
                        )}
                    </Tarjeta>

                    <div className="grid gap-6 lg:grid-cols-2">
                        <Tarjeta
                            titulo="Derivaciones"
                            meta={
                                circuito.dias_promedio_respuesta !== null
                                    ? `Responden en ${dias(circuito.dias_promedio_respuesta)}`
                                    : undefined
                            }
                        >
                            {resumen.derivaciones === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    El legajo todavía no tuvo derivaciones.
                                </p>
                            ) : (
                                <>
                                    <div className="flex flex-wrap gap-x-6 gap-y-3">
                                        {circuito.por_estado.map((estado) => (
                                            <div key={estado.nombre}>
                                                <p
                                                    className="text-2xl font-semibold text-foreground tabular-nums"
                                                    style={{
                                                        fontFamily:
                                                            FONT_HEADING,
                                                    }}
                                                >
                                                    {estado.total}
                                                </p>
                                                <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                    <span
                                                        className={`size-1.5 rounded-full ${colorEstado[estadoDesdeNombre[estado.nombre]] ?? 'bg-muted-foreground/50'}`}
                                                    />
                                                    {estado.nombre}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                    {circuito.pendiente_mas_antigua_dias !==
                                        null && (
                                        <p className="mt-4 text-sm text-muted-foreground">
                                            La pendiente más antigua lleva{' '}
                                            <span className="font-medium text-foreground">
                                                {dias(
                                                    circuito.pendiente_mas_antigua_dias,
                                                )}
                                            </span>
                                            .
                                        </p>
                                    )}
                                    <ul className="mt-5 divide-y divide-border border-t border-border">
                                        {circuito.ultimas.map((derivacion) => (
                                            <li
                                                key={derivacion.id}
                                                className="flex items-center justify-between gap-3 py-3 text-sm"
                                            >
                                                <span className="flex min-w-0 items-center gap-2.5">
                                                    <span
                                                        className={`size-2 shrink-0 rounded-full ${colorEstado[derivacion.estado] ?? 'bg-muted-foreground/50'}`}
                                                        title={
                                                            derivacion.estado
                                                        }
                                                    />
                                                    <span className="truncate text-foreground">
                                                        {
                                                            derivacion.destinatario
                                                        }
                                                    </span>
                                                </span>
                                                <span className="shrink-0 text-xs text-muted-foreground">
                                                    {fecha(derivacion.fecha)}
                                                    {derivacion.dias_respuesta !==
                                                        null &&
                                                        ` · ${dias(derivacion.dias_respuesta)}`}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                </>
                            )}
                            <div className="mt-5 border-t border-border pt-4">
                                <Subtitulo>Responsables</Subtitulo>
                                {circuito.responsables.length > 0 ? (
                                    <ul className="space-y-1.5 text-sm">
                                        {circuito.responsables.map(
                                            (responsable) => (
                                                <li
                                                    key={responsable.nombre}
                                                    className="flex items-center justify-between gap-3 text-foreground"
                                                >
                                                    {responsable.nombre}
                                                    {responsable.principal && (
                                                        <span className="text-xs text-muted-foreground">
                                                            Principal
                                                        </span>
                                                    )}
                                                </li>
                                            ),
                                        )}
                                    </ul>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        Sin responsables asignados.
                                    </p>
                                )}
                            </div>
                        </Tarjeta>

                        <Tarjeta titulo="Documentación y accesos">
                            <div className="space-y-5">
                                <Progreso
                                    label="Firma del responsable"
                                    valor={documentacion.firmas.responsable}
                                    total={total}
                                />
                                <Progreso
                                    label="Firma del entrevistado"
                                    valor={documentacion.firmas.entrevistado}
                                    total={total}
                                />
                                {documentacion.auh && (
                                    <Progreso
                                        label="Documentación AUH"
                                        valor={documentacion.auh.cargados}
                                        total={documentacion.auh.obligatorios}
                                        detalle={`${documentacion.auh.planillas_firmadas} de ${documentacion.auh.planillas_total} planillas firmadas`}
                                    />
                                )}
                            </div>
                            <dl className="mt-5 border-t border-border">
                                <Fila label="Documentos adjuntos">
                                    {documentacion.documentos}
                                </Fila>
                                <Fila label="Correos vinculados">
                                    {documentacion.mensajes}
                                </Fila>
                                <Fila label="Consultas al legajo">
                                    {auditoria.total}
                                    <span className="ml-1 font-normal text-muted-foreground">
                                        ({auditoria.visualizaciones} vistas,{' '}
                                        {auditoria.descargas} descargas)
                                    </span>
                                </Fila>
                                <Fila label="Usuarios distintos">
                                    {auditoria.usuarios_distintos}
                                </Fila>
                                <Fila label="Último acceso">
                                    {fecha(auditoria.ultimo_acceso)}
                                </Fila>
                            </dl>
                            {auditoria.puede_ver_detalle &&
                                auditoria.por_usuario.length > 0 && (
                                    <div className="mt-5 border-t border-border pt-5">
                                        <Subtitulo>Quién consultó</Subtitulo>
                                        <BarsChart
                                            items={auditoria.por_usuario}
                                        />
                                    </div>
                                )}
                        </Tarjeta>
                    </div>
                </div>
            </main>
        </>
    );
}
