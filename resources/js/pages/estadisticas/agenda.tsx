import { Head } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';

import {
    BarsChart,
    DonutChart,
    TrendChart,
} from '@/components/estadisticas-chart';
import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';

export default function EstadisticasAgenda(props: EstadisticasData) {
    return (
        <>
            <Head title="Estadísticas de agenda" />
            <EstadisticasShell
                active="agenda"
                title="Agenda"
                subtitle={props.rango.descripcion}
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
                        <div className="grid gap-4 pt-7 sm:grid-cols-2 xl:grid-cols-3">
                            <EstadisticaKpi
                                label="Eventos"
                                value={props.kpis.agenda}
                                detail="Eventos visibles para tu rol"
                                icon={CalendarDays}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Tipos"
                                value={props.agendaPorTipo.length}
                                detail="Clasificaciones utilizadas"
                                icon={CalendarDays}
                                tone="violet"
                            />
                            <EstadisticaKpi
                                label="Programados"
                                value={
                                    props.agendaPorEstado.find(
                                        (item) => item.nombre === 'programado',
                                    )?.total ?? 0
                                }
                                detail="Pendientes de realización"
                                icon={CalendarDays}
                                tone="green"
                            />
                        </div>
                        <div className="grid gap-5 pt-6 xl:grid-cols-2">
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Evolución
                                </h2>
                                <div className="mt-5">
                                    <TrendChart
                                        intervals={props.intervalos}
                                        values={props.agendaPorPeriodo}
                                        label="Evolución de agenda"
                                    />
                                </div>
                            </article>
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Por tipo
                                </h2>
                                <div className="mt-5">
                                    <DonutChart items={props.agendaPorTipo} />
                                </div>
                            </article>
                        </div>
                        <div className="grid gap-5 pt-5 xl:grid-cols-2">
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Distribución por estado
                                </h2>
                                <div className="mt-5">
                                    <BarsChart items={props.agendaPorEstado} />
                                </div>
                            </article>
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Línea de tiempo
                                </h2>
                                <div className="mt-4 space-y-3">
                                    {props.agendaTimeline.length === 0 ? (
                                        <p className="text-sm text-[#697586]">
                                            No hay eventos en el período.
                                        </p>
                                    ) : (
                                        props.agendaTimeline.map((item) => (
                                            <div
                                                key={item.id}
                                                className="border-l-2 border-[#AAA4E5] pl-3 text-sm"
                                            >
                                                <strong className="text-[#2C4A5E] dark:text-[#D8E2E8]">
                                                    {item.titulo}
                                                </strong>
                                                <p className="text-xs text-[#697586]">
                                                    {item.tipo} · {item.estado}{' '}
                                                    ·{' '}
                                                    {item.fecha
                                                        ? new Date(
                                                              item.fecha,
                                                          ).toLocaleString(
                                                              'es-AR',
                                                          )
                                                        : ''}
                                                </p>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
