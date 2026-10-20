import { Head } from '@inertiajs/react';
import { HeartPulse } from 'lucide-react';

import {
    BarsChart,
    DonutChart,
    TrendChart,
} from '@/components/estadisticas-chart';
import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';

export default function EstadisticasIntervenciones(props: EstadisticasData) {
    return (
        <>
            <Head title="Estadísticas de intervenciones" />
            <EstadisticasShell
                active="intervenciones"
                title="Intervenciones"
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
                        <div className="grid gap-4 pt-7 sm:grid-cols-2 xl:grid-cols-4">
                            <EstadisticaKpi
                                label="Intervenciones"
                                value={props.kpis.intervenciones}
                                detail="Con fecha registrada"
                                icon={HeartPulse}
                                tone="violet"
                            />
                            <EstadisticaKpi
                                label="Tipos utilizados"
                                value={props.intervencionesPorTipo.length}
                                detail="Catálogo en el período"
                                icon={HeartPulse}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Profesionales"
                                value={
                                    props.intervencionesPorProfesional.length
                                }
                                detail="Con intervenciones en el período"
                                icon={HeartPulse}
                                tone="green"
                            />
                            <EstadisticaKpi
                                label="Legajos con casos"
                                value={props.kpis.totalLegajos}
                                detail="Aperturas en el período"
                                icon={HeartPulse}
                                tone="orange"
                            />
                        </div>
                        <div className="grid gap-5 pt-6 xl:grid-cols-[1.2fr_0.8fr]">
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Evolución de intervenciones
                                </h2>
                                <div className="mt-5">
                                    <TrendChart
                                        intervals={props.intervalos}
                                        values={props.intervencionesPorPeriodo}
                                        label="Evolución de intervenciones"
                                    />
                                </div>
                            </article>
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                                <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                    Por tipo
                                </h2>
                                <div className="mt-5">
                                    <DonutChart
                                        items={props.intervencionesPorTipo}
                                    />
                                </div>
                            </article>
                        </div>
                        <article className="mt-5 rounded-lg border border-[#DFE4EA] bg-white p-5 dark:border-[#3B4552] dark:bg-[#232A34]">
                            <h2 className="text-lg font-semibold text-[#173247] dark:text-[#EDF6FB]">
                                Ranking de tipos
                            </h2>
                            <div className="mt-5">
                                <BarsChart
                                    items={props.intervencionesPorTipo}
                                />
                            </div>
                        </article>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
