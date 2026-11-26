import { Head, Link } from '@inertiajs/react';
import {
    ArrowDownRight,
    ArrowUpRight,
    BarChart3,
    CalendarDays,
    CheckCircle2,
    ClipboardCheck,
    FolderOpen,
    HeartPulse,
    MapPinned,
    TriangleAlert,
    UserRound,
} from 'lucide-react';
import type { ReactNode } from 'react';

import {
    BarsChart,
    DonutChart,
    TrendChart,
} from '@/components/estadisticas-chart';
import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasMapa from '@/components/estadisticas-mapa';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';
import estadisticas from '@/routes/estadisticas';

function Panel({
    eyebrow,
    title,
    description,
    children,
    className = '',
}: {
    eyebrow?: string;
    title: string;
    description?: string;
    children: ReactNode;
    className?: string;
}) {
    return (
        <article
            className={`rounded-md border border-[#DCE3E6] bg-white p-5 shadow-[0_10px_28px_rgba(23,50,71,0.045)] dark:border-[#34424B] dark:bg-[#1D2A31] ${className}`}
        >
            <div>
                {eyebrow && (
                    <p className="text-[length:calc(0.66rem_+_2px)] font-bold tracking-[0.13em] text-[#39795B] uppercase dark:text-[#8BC9A9]">
                        {eyebrow}
                    </p>
                )}
                <h2 className="mt-1 text-lg font-semibold tracking-tight text-[#173247] dark:text-[#EDF6FB]">
                    {title}
                </h2>
                {description && (
                    <p className="mt-1 max-w-2xl text-sm leading-5 text-[#697586] dark:text-[#AFC2CC]">
                        {description}
                    </p>
                )}
            </div>
            <div className="mt-5">{children}</div>
        </article>
    );
}

function ComparisonNote({
    current,
    previous,
}: {
    current: number;
    previous: number;
}) {
    if (current === 0 && previous === 0) {
        return null;
    }

    if (previous === 0) {
        return (
            <span className="text-xs font-semibold text-[#39795B] dark:text-[#8BC9A9]">
                Nuevo registro
            </span>
        );
    }

    const percentage = Math.round(((current - previous) / previous) * 100);
    const positive = percentage >= 0;
    const Icon = positive ? ArrowUpRight : ArrowDownRight;

    return (
        <span
            className={`inline-flex items-center gap-1 text-xs font-semibold ${positive ? 'text-[#39795B] dark:text-[#8BC9A9]' : 'text-[#A8493C] dark:text-[#E4A49C]'}`}
        >
            <Icon size={13} />
            {Math.abs(percentage)}% vs. período anterior
        </span>
    );
}

function ActivityList({
    data,
}: {
    data: EstadisticasData['actividadTimeline'];
}) {
    if (data.length === 0) {
        return (
            <div className="rounded-md border border-dashed border-[#DCE3E6] bg-[#F8FAFA] px-4 py-10 text-center text-sm text-[#697586] dark:border-[#34424B] dark:bg-[#223039] dark:text-[#AFC2CC]">
                No hay actividad registrada en el período seleccionado.
            </div>
        );
    }

    return (
        <div className="divide-y divide-[#E8EEEA] dark:divide-[#34424B]">
            {data.slice(0, 8).map((item) => (
                <div
                    key={item.id}
                    className="flex items-start gap-3 py-3 first:pt-0 last:pb-0"
                >
                    <span className="mt-1.5 size-2 shrink-0 rounded-full bg-[#39795B]" />
                    <div className="min-w-0">
                        <p className="text-sm font-medium text-[#2C4A5E] dark:text-[#D8E2E8]">
                            {item.evento || 'Actividad'}
                            <span className="text-[#697586] dark:text-[#AFC2CC]">
                                {' · '}
                                {item.tipo || 'Registro'}
                            </span>
                        </p>
                        <time className="mt-1 block text-xs text-[#697586] dark:text-[#8EA5B0]">
                            {item.fecha
                                ? new Date(item.fecha).toLocaleDateString(
                                      'es-AR',
                                  )
                                : 'Fecha no disponible'}
                        </time>
                    </div>
                </div>
            ))}
        </div>
    );
}

export default function EstadisticasResumen(props: EstadisticasData) {
    const estadoLegajos = [
        { nombre: 'Abiertos', total: props.legajosPorEstado.abiertos },
        { nombre: 'Cerrados', total: props.legajosPorEstado.cerrados },
        { nombre: 'Archivados', total: props.legajosPorEstado.archivados },
    ];
    const tieneTareas = props.modulosDisponibles.includes('tareas');
    const tieneAgenda = props.modulosDisponibles.includes('agenda');

    return (
        <>
            <Head title="Estadísticas" />
            <EstadisticasShell
                active="resumen"
                title="Estadísticas"
                modulosDisponibles={props.modulosDisponibles}
            >
                <div className="stats-page-surface min-h-[calc(100vh-4rem)] px-4 py-6 sm:px-7 sm:py-8 lg:px-10">
                    <div className="mx-auto max-w-[1480px]">
                        <section className="border-b border-[#DCE3E6] pb-7 dark:border-[#34424B]">
                            <div className="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
                                <div>
                                    <p className="text-sm font-semibold text-[#39795B] dark:text-[#8BC9A9]">
                                        Inteligencia institucional
                                    </p>
                                    <h2 className="mt-2 max-w-3xl text-3xl font-semibold tracking-[-0.035em] text-[#173247] sm:text-4xl dark:text-[#EDF6FB]">
                                        Análisis y seguimiento de la actividad
                                        institucional.
                                    </h2>
                                    <p className="mt-3 max-w-2xl text-sm leading-6 text-[#5A7180] dark:text-[#AFC2CC]">
                                        Una lectura consolidada de aperturas,
                                        seguimiento, problemáticas e
                                        intervención para orientar las
                                        prioridades del equipo.
                                    </p>
                                </div>
                                <div className="shrink-0 border-l-2 border-[#7EAD8B] pl-4">
                                    <p className="text-[length:calc(0.66rem_+_2px)] font-bold tracking-[0.13em] text-[#697586] uppercase dark:text-[#8EA5B0]">
                                        Período analizado
                                    </p>
                                    <p className="mt-1 text-sm font-semibold text-[#2C4A5E] dark:text-[#D8E2E8]">
                                        {props.rango.descripcion}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <div className="pt-6">
                            <EstadisticasFiltros
                                filtros={props.filtros}
                                rango={props.rango}
                                problematicas={props.problematicas}
                                catalogos={props.catalogos}
                            />
                        </div>

                        <section
                            className="pt-8"
                            aria-labelledby="resumen-title"
                        >
                            <div className="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                                <div>
                                    <p className="text-[length:calc(0.66rem_+_2px)] font-bold tracking-[0.13em] text-[#39795B] uppercase dark:text-[#8BC9A9]">
                                        Resumen ejecutivo
                                    </p>
                                    <h2
                                        id="resumen-title"
                                        className="mt-1 text-xl font-semibold tracking-tight text-[#173247] dark:text-[#EDF6FB]"
                                    >
                                        Señales para la toma de decisiones
                                    </h2>
                                </div>
                                <p className="text-xs text-[#697586] dark:text-[#AFC2CC]">
                                    Los valores corresponden al período
                                    seleccionado.
                                </p>
                            </div>

                            <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                <EstadisticaKpi
                                    label="Legajos abiertos"
                                    value={props.kpis.legajosAbiertos}
                                    detail="Aperturas dentro del período"
                                    icon={FolderOpen}
                                    tone="blue"
                                />
                                <EstadisticaKpi
                                    label="Riesgo crítico"
                                    value={props.kpis.casosRiesgoCritico}
                                    detail="Requieren lectura prioritaria"
                                    icon={TriangleAlert}
                                    tone="red"
                                />
                                <EstadisticaKpi
                                    label="Sin asignación"
                                    value={props.kpis.casosSinAsignar}
                                    detail="Sin profesional responsable"
                                    icon={UserRound}
                                    tone="orange"
                                />
                                <EstadisticaKpi
                                    label="Cierres"
                                    value={props.kpis.legajosBajas}
                                    detail="Cierres dentro del período"
                                    icon={CheckCircle2}
                                    tone="green"
                                />
                            </div>

                            <div className="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                <EstadisticaKpi
                                    label="Personas incorporadas"
                                    value={props.kpis.personasAltas}
                                    detail="Altas registradas"
                                    icon={UserRound}
                                    tone="violet"
                                />
                                <EstadisticaKpi
                                    label="Intervenciones"
                                    value={props.kpis.intervenciones}
                                    detail="Con fecha registrada"
                                    icon={HeartPulse}
                                    tone="blue"
                                />
                                {tieneTareas && (
                                    <EstadisticaKpi
                                        label="Tareas"
                                        value={props.kpis.tareas}
                                        detail={`${props.kpis.tareasCompletadas} completadas`}
                                        icon={ClipboardCheck}
                                        tone="green"
                                    />
                                )}
                                <EstadisticaKpi
                                    label="Problemáticas"
                                    value={props.kpis.problematicasConCasos}
                                    detail="Categorías con casos"
                                    icon={BarChart3}
                                    tone="violet"
                                />
                            </div>
                        </section>

                        {props.comparacion && (
                            <section className="mt-5 rounded-md border border-[#DCE3E6] bg-[#F8FAFA] px-4 py-4 dark:border-[#34424B] dark:bg-[#1D2A31]">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                            Comparación de períodos
                                        </p>
                                        <p className="mt-1 text-xs text-[#697586] dark:text-[#AFC2CC]">
                                            Frente al período{' '}
                                            {props.comparacion.rango.desde} al{' '}
                                            {props.comparacion.rango.hasta}.
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap gap-x-5 gap-y-2">
                                        <ComparisonNote
                                            current={props.kpis.legajosAltas}
                                            previous={
                                                props.comparacion.kpis
                                                    .legajosAltas
                                            }
                                        />
                                        <ComparisonNote
                                            current={props.kpis.intervenciones}
                                            previous={
                                                props.comparacion.kpis
                                                    .intervenciones
                                            }
                                        />
                                        {tieneTareas && (
                                            <ComparisonNote
                                                current={props.kpis.tareas}
                                                previous={
                                                    props.comparacion.kpis
                                                        .tareas
                                                }
                                            />
                                        )}
                                    </div>
                                </div>
                            </section>
                        )}

                        <section className="mt-7 grid gap-5 xl:grid-cols-[1.55fr_0.85fr]">
                            <Panel
                                eyebrow="Evolución temporal"
                                title="Movimiento de legajos"
                                description="Aperturas y cierres registrados dentro del período seleccionado."
                            >
                                <TrendChart
                                    intervals={props.intervalos}
                                    values={props.legajosPorPeriodo}
                                    series={[
                                        {
                                            label: 'Aperturas',
                                            values: props.legajosPorPeriodo,
                                            color: '#2C4A5E',
                                        },
                                        {
                                            label: 'Cierres',
                                            values: props.bajasPorPeriodo,
                                            color: '#39795B',
                                        },
                                    ]}
                                    label="Movimiento de legajos"
                                />
                            </Panel>
                            <Panel
                                eyebrow="Clasificación"
                                title="Estado de los legajos"
                                description="Distribución de los estados dentro del universo filtrado."
                            >
                                <DonutChart items={estadoLegajos} />
                            </Panel>
                        </section>

                        <section className="mt-5 grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                            <Panel
                                eyebrow="Distribución"
                                title="Problemáticas más frecuentes"
                                description="Casos vinculados a cada categoría, ordenados de mayor a menor."
                            >
                                <BarsChart items={props.problematicas} />
                            </Panel>
                            <Panel
                                eyebrow="Respuesta institucional"
                                title="Intervenciones por tipo"
                                description="Tipos utilizados durante el período seleccionado."
                            >
                                <BarsChart
                                    items={props.intervencionesPorTipo}
                                />
                            </Panel>
                        </section>

                        <section className="mt-5">
                            <Panel
                                eyebrow="Información territorial agregada"
                                title="Distribución por zona"
                                description="La visualización agrupa casos y omite domicilios e identidades individuales."
                            >
                                <EstadisticasMapa
                                    data={props.mapaBarrios}
                                    problematicas={props.problematicas}
                                />
                            </Panel>
                        </section>

                        <section className="mt-5 grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
                            <Panel
                                eyebrow="Detalle"
                                title="Ranking por barrio"
                                description="Casos agrupados en San Nicolás para el período seleccionado."
                            >
                                <BarsChart items={props.barrios} />
                            </Panel>
                            <Panel
                                eyebrow="Seguimiento"
                                title="Actividad reciente"
                                description="Aperturas, cierres y modificaciones agrupadas sin exponer nombres de personas."
                            >
                                <ActivityList data={props.actividadTimeline} />
                            </Panel>
                        </section>

                        {(tieneTareas || tieneAgenda) && (
                            <section className="mt-5 grid gap-3 sm:grid-cols-2">
                                {tieneTareas && (
                                    <Link
                                        href={estadisticas.tareas.url()}
                                        className="group flex items-center justify-between rounded-md border border-[#DCE3E6] bg-white px-5 py-4 transition hover:border-[#7EAD8B] hover:bg-[#F8FAFA] dark:border-[#34424B] dark:bg-[#1D2A31] dark:hover:bg-[#223039]"
                                    >
                                        <span className="flex items-center gap-3">
                                            <ClipboardCheck
                                                className="text-[#39795B]"
                                                size={19}
                                            />
                                            <span>
                                                <strong className="block text-sm text-[#173247] dark:text-[#EDF6FB]">
                                                    Detalle de tareas operativas
                                                </strong>
                                                <span className="mt-0.5 block text-xs text-[#697586] dark:text-[#AFC2CC]">
                                                    Estados y carga de trabajo
                                                </span>
                                            </span>
                                        </span>
                                        <ArrowUpRight
                                            className="text-[#39795B] transition group-hover:translate-x-0.5"
                                            size={17}
                                        />
                                    </Link>
                                )}
                                {tieneAgenda && (
                                    <Link
                                        href={estadisticas.agenda.url()}
                                        className="group flex items-center justify-between rounded-md border border-[#DCE3E6] bg-white px-5 py-4 transition hover:border-[#7EAD8B] hover:bg-[#F8FAFA] dark:border-[#34424B] dark:bg-[#1D2A31] dark:hover:bg-[#223039]"
                                    >
                                        <span className="flex items-center gap-3">
                                            <CalendarDays
                                                className="text-[#39795B]"
                                                size={19}
                                            />
                                            <span>
                                                <strong className="block text-sm text-[#173247] dark:text-[#EDF6FB]">
                                                    Detalle de agenda
                                                </strong>
                                                <span className="mt-0.5 block text-xs text-[#697586] dark:text-[#AFC2CC]">
                                                    Eventos visibles y estados
                                                </span>
                                            </span>
                                        </span>
                                        <ArrowUpRight
                                            className="text-[#39795B] transition group-hover:translate-x-0.5"
                                            size={17}
                                        />
                                    </Link>
                                )}
                            </section>
                        )}

                        <p className="mt-7 flex items-start gap-2 text-xs leading-5 text-[#697586] dark:text-[#8EA5B0]">
                            <MapPinned size={14} className="mt-0.5 shrink-0" />
                            La información se muestra de forma agregada y está
                            limitada por los permisos de tu usuario.
                        </p>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
