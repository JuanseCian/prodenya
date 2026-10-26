import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    CalendarDays,
    ClipboardCheck,
    FolderOpen,
    HeartPulse,
    Map,
    Pencil,
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

function Card({ title, children }: { title: string; children: ReactNode }) {
    return (
        <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 sm:p-6 dark:border-[#3B4552] dark:bg-[#232A34]">
            <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                {title}
            </h2>
            {children}
        </article>
    );
}

export default function EstadisticasResumen(props: EstadisticasData) {
    return (
        <>
            <Head title="Estadísticas" />
            <EstadisticasShell
                active="resumen"
                title="Estadísticas"
                subtitle={`Actualización automática cada 60 s · ${props.rango.descripcion}`}
                modulosDisponibles={props.modulosDisponibles}
            >
                <div className="stats-page-surface min-h-[calc(100vh-77px)] px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <div className="mx-auto max-w-7xl">
                        <EstadisticasFiltros
                            filtros={props.filtros}
                            rango={props.rango}
                            problematicas={props.problematicas}
                            catalogos={props.catalogos}
                        />

                        <section className="grid gap-4 pt-7 sm:grid-cols-2 xl:grid-cols-4">
                            <EstadisticaKpi
                                label="Personas altas"
                                value={props.kpis.personasAltas}
                                detail="Persona.creado_en"
                                icon={UserRound}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Legajos abiertos"
                                value={props.kpis.legajosAltas}
                                detail="Legajo.fecha_apertura"
                                icon={FolderOpen}
                                tone="green"
                            />
                            <EstadisticaKpi
                                label="Legajos dados de baja"
                                value={props.kpis.legajosBajas}
                                detail="Cierres en el período"
                                icon={ArrowUpRight}
                                tone="violet"
                            />
                            <EstadisticaKpi
                                label="Modificaciones"
                                value={props.kpis.modificaciones}
                                detail="Timestamps + eventos de auditoría"
                                icon={Pencil}
                                tone="orange"
                            />
                        </section>
                        <section className="grid gap-4 pt-4 sm:grid-cols-2 xl:grid-cols-4">
                            <EstadisticaKpi
                                label="Intervenciones"
                                value={props.kpis.intervenciones}
                                detail="Por fecha de intervención"
                                icon={HeartPulse}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Tareas"
                                value={props.kpis.tareas}
                                detail={`${props.kpis.tareasCompletadas} completadas`}
                                icon={ClipboardCheck}
                                tone="green"
                            />
                            <EstadisticaKpi
                                label="Agenda"
                                value={props.kpis.agenda}
                                detail="Eventos visibles"
                                icon={CalendarDays}
                                tone="orange"
                            />
                            <EstadisticaKpi
                                label="Problemáticas"
                                value={props.kpis.problematicasConCasos}
                                detail="Con casos asociados"
                                icon={TriangleAlert}
                                tone="violet"
                            />
                        </section>

                        {props.comparacion && (
                            <p className="mt-5 rounded-md bg-[#EEECF7] px-4 py-3 text-sm text-[#4F498E] dark:bg-[#38385A] dark:text-[#D8D3FF]">
                                Comparando con {props.comparacion.rango.desde}{' '}
                                al {props.comparacion.rango.hasta}. Los
                                indicadores principales se mantienen disponibles
                                para lectura rápida.
                            </p>
                        )}

                        <section className="grid gap-5 pt-6 xl:grid-cols-2">
                            <Card title="Altas de personas">
                                <p className="mt-1 text-xs text-[#697586]">
                                    Registro de persona, independiente de la
                                    apertura del legajo.
                                </p>
                                <div className="mt-5">
                                    <TrendChart
                                        intervals={props.intervalos}
                                        values={props.personasPorPeriodo}
                                        label="Altas de personas"
                                    />
                                </div>
                            </Card>
                            <Card title="Aperturas y bajas de legajos">
                                <div className="mt-5 grid gap-5 sm:grid-cols-2">
                                    <TrendChart
                                        intervals={props.intervalos}
                                        values={props.legajosPorPeriodo}
                                        label="Aperturas de legajos"
                                    />
                                    <TrendChart
                                        intervals={props.intervalos}
                                        values={props.bajasPorPeriodo}
                                        label="Bajas de legajos"
                                    />
                                </div>
                            </Card>
                        </section>

                        <section className="grid gap-5 pt-5 xl:grid-cols-3">
                            <Card title="Problemáticas">
                                <div className="mt-5">
                                    <BarsChart items={props.problematicas} />
                                </div>
                            </Card>
                            <Card title="Intervenciones por tipo">
                                <div className="mt-5">
                                    <DonutChart
                                        items={props.intervencionesPorTipo}
                                    />
                                </div>
                            </Card>
                            <Card title="Estado de legajos">
                                <div className="mt-5">
                                    <DonutChart
                                        items={[
                                            {
                                                nombre: 'Abiertos',
                                                total: props.legajosPorEstado
                                                    .abiertos,
                                            },
                                            {
                                                nombre: 'Cerrados',
                                                total: props.legajosPorEstado
                                                    .cerrados,
                                            },
                                            {
                                                nombre: 'Archivados',
                                                total: props.legajosPorEstado
                                                    .archivados,
                                            },
                                        ]}
                                    />
                                </div>
                            </Card>
                        </section>

                        <section className="pt-5">
                            <Card title="Mapa de zonas más recurridas">
                                <p className="mt-1 text-xs text-[#697586] dark:text-[#B7C9D4]">
                                    Distribución agregada por zona y problemática, respetando los filtros seleccionados.
                                </p>
                                <div className="mt-5">
                                    <EstadisticasMapa
                                        data={props.mapaBarrios}
                                        problematicas={props.problematicas}
                                    />
                                </div>
                            </Card>
                        </section>

                        <section className="grid gap-5 pt-5 xl:grid-cols-2">
                            <Card title="Distribución por barrio · San Nicolás">
                                <div className="mt-5">
                                    <BarsChart items={props.barrios} />
                                </div>
                                <div className="mt-5 flex items-start gap-3 rounded-md bg-[#F7F8FA] p-3 text-xs text-[#697586] dark:bg-[#2C333E] dark:text-[#B7C9D4]">
                                    <Map
                                        size={17}
                                        className="mt-0.5 shrink-0 text-[#5B559E]"
                                    />
                                    <span>
                                        Se expone el ranking de barrios como complemento del mapa agregado.
                                    </span>
                                </div>
                            </Card>
                             <Card title="Altas, bajas y modificaciones">
                                 <div className="mt-4 space-y-3">
                                     {props.actividadTimeline.length ===
                                     0 ? (
                                         <p className="text-sm text-[#697586]">
                                             No hay actividad en el
                                             período.
                                         </p>
                                     ) : (
                                         props.actividadTimeline.map(
                                             (item) => (
                                                 <div
                                                     key={item.id}
                                                     className="border-l-2 border-[#AAA4E5] pb-3 pl-3 text-sm last:border-0 dark:border-[#3B4552]"
                                                 >
                                                     <p className="font-medium text-[#2C4A5E] dark:text-[#D8E2E8]">
                                                         {item.evento ||
                                                             'Actividad'}{' '}
                                                         · {item.tipo || 'Registro'}
                                                     </p>
                                                     <time className="text-xs text-[#697586]">
                                                         {item.fecha
                                                             ? new Date(
                                                                   item.fecha,
                                                               ).toLocaleDateString(
                                                                   'es-AR',
                                                               )
                                                             : ''}
                                                         {item.usuario
                                                             ? ` · ${item.usuario}`
                                                             : ''}
                                                     </time>
                                                 </div>
                                            ),
                                        )
                                    )}
                                </div>
                            </Card>
                        </section>

                        <section className="grid gap-4 pt-5 sm:grid-cols-2">
                            {props.modulosDisponibles.includes('tareas') && (
                                <Link
                                    href={estadisticas.tareas.url()}
                                    className="group rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]"
                                >
                                    <ClipboardCheck className="text-[#5B559E]" />
                                    <h2 className="mt-3 font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                        Tareas operativas
                                    </h2>
                                    <span className="mt-3 inline-flex items-center gap-1 text-sm font-bold text-[#5B559E]">
                                        Ver detalle{' '}
                                        <ArrowUpRight
                                            size={15}
                                            className="transition group-hover:translate-x-0.5"
                                        />
                                    </span>
                                </Link>
                            )}
                            {props.modulosDisponibles.includes('agenda') && (
                                <Link
                                    href={estadisticas.agenda.url()}
                                    className="group rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]"
                                >
                                    <CalendarDays className="text-[#5B559E]" />
                                    <h2 className="mt-3 font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                        Agenda y próximos eventos
                                    </h2>
                                    <span className="mt-3 inline-flex items-center gap-1 text-sm font-bold text-[#5B559E]">
                                        Ver detalle{' '}
                                        <ArrowUpRight
                                            size={15}
                                            className="transition group-hover:translate-x-0.5"
                                        />
                                    </span>
                                </Link>
                            )}
                        </section>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
