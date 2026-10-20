import { Head } from '@inertiajs/react';
import { CheckSquare } from 'lucide-react';

import {
    BarsChart,
    DonutChart,
    TrendChart,
} from '@/components/estadisticas-chart';
import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';

export default function EstadisticasTareas(props: EstadisticasData) {
    return (
        <>
            <Head title="Estadísticas de tareas" />
            <EstadisticasShell
                active="tareas"
                title="Tareas"
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
                                label="Tareas creadas"
                                value={props.kpis.tareas}
                                detail="Por created_at"
                                icon={CheckSquare}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Completadas"
                                value={props.kpis.tareasCompletadas}
                                detail="Estado completada"
                                icon={CheckSquare}
                                tone="green"
                            />
                            <EstadisticaKpi
                                label="Pendientes"
                                value={props.tareasPorEstado
                                    .filter(
                                        (item) => item.nombre === 'pendiente',
                                    )
                                    .reduce((sum, item) => sum + item.total, 0)}
                                detail="Requieren seguimiento"
                                icon={CheckSquare}
                                tone="orange"
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
                                        values={props.tareasPorPeriodo}
                                        label="Evolución de tareas"
                                    />
                                </div>
                            </article>
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Por estado
                                </h2>
                                <div className="mt-5">
                                    <DonutChart items={props.tareasPorEstado} />
                                </div>
                            </article>
                        </div>
                        <article className="mt-5 rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                            <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                Carga por estado
                            </h2>
                            <div className="mt-5">
                                <BarsChart items={props.tareasPorEstado} />
                            </div>
                        </article>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
