import { Head, Link } from '@inertiajs/react';
import { ChevronRight, CircleHelp, TriangleAlert } from 'lucide-react';

import { TrendChart } from '@/components/estadisticas-chart';
import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';

const FONT_HEADING = 'inherit';

function queryFor(filtros: EstadisticasData['filtros'], problematica?: number) {
    const query = new URLSearchParams({
        periodo: filtros.periodo,
        anio: String(filtros.anio),
        mes: String(filtros.mes),
    });

    if (problematica) {
        query.set('problematica', String(problematica));
    }

    return `/estadisticas/problematicas?${query.toString()}`;
}

export default function EstadisticasProblematicas(props: EstadisticasData) {
    const max = Math.max(...props.problematicas.map((item) => item.total), 1);

    return (
        <>
            <Head title="Estadísticas de problemáticas" />
            <EstadisticasShell
                active="problematicas"
                title="Problemáticas"
                subtitle="Casos por categoría"
            >
                <div className="stats-page-surface min-h-[calc(100vh-77px)] px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <div className="mx-auto max-w-7xl">
                        <div className="mb-6">
                            <h1
                                className="text-2xl font-semibold tracking-tight text-[#173247] sm:text-3xl"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Problemáticas
                            </h1>
                        </div>

                        <EstadisticasFiltros
                            filtros={props.filtros}
                            problematicas={props.problematicas}
                        />

                        <div className="grid gap-4 pt-7 sm:grid-cols-2 xl:grid-cols-3">
                            <EstadisticaKpi
                                label="Problemáticas con casos"
                                value={props.kpis.problematicasConCasos}
                                detail="En el período"
                                icon={TriangleAlert}
                                tone="orange"
                            />
                            <EstadisticaKpi
                                label="Legajos vinculados"
                                value={props.kpis.totalLegajos}
                                detail="Casos vinculados"
                                icon={CircleHelp}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Problemática seleccionada"
                                value={
                                    props.problematicaSeleccionada
                                        ? (props.problematicas.find(
                                              (item) =>
                                                  item.id ===
                                                  props.problematicaSeleccionada
                                                      ?.id,
                                          )?.total ?? 0)
                                        : 0
                                }
                                detail={
                                    props.problematicaSeleccionada?.nombre ??
                                    'Sin selección'
                                }
                                icon={TriangleAlert}
                                tone="violet"
                            />
                        </div>

                        <article className="mt-6 rounded-lg border border-[#DFE4EA] bg-white p-5 sm:p-6">
                            <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                                <div>
                                    <h2
                                        className="text-xl font-semibold text-[#173247]"
                                        style={{ fontFamily: FONT_HEADING }}
                                    >
                                        Problemáticas
                                    </h2>
                                </div>
                                {props.problematicaSeleccionada && (
                                    <Link
                                        href={queryFor(props.filtros)}
                                        className="inline-flex w-fit rounded-xl bg-[#FFF3DB] px-3 py-2 text-xs font-bold text-[#A16A00] hover:bg-[#FFE9C7]"
                                    >
                                        Quitar filtro
                                    </Link>
                                )}
                            </div>
                            {props.problematicaSeleccionada && (
                                <div className="mt-5 flex items-center gap-2 rounded-2xl border border-[#F8E5C9] bg-[#FFFAF2] px-4 py-3 text-sm text-[#A16A00]">
                                    <CircleHelp size={17} />{' '}
                                    <strong>
                                        {props.problematicaSeleccionada.nombre}
                                    </strong>
                                    .
                                </div>
                            )}

                            <div className="mt-6 grid gap-6 lg:grid-cols-[1fr_1.15fr]">
                                <div className="space-y-3">
                                    {props.problematicas
                                        .slice(0, 12)
                                        .map((problematica) => (
                                            <Link
                                                key={problematica.id}
                                                href={queryFor(
                                                    props.filtros,
                                                    problematica.id,
                                                )}
                                                preserveScroll
                                                className={`group block rounded-md border p-3.5 transition ${props.problematicaSeleccionada?.id === problematica.id ? 'border-[#AAA4E5] bg-[#F8F7FC]' : 'border-[#DFE4EA] bg-[#FBFBFC] hover:border-[#AAA4E5]'}`}
                                            >
                                                <div className="flex items-center justify-between gap-3">
                                                    <span className="min-w-0 truncate text-sm font-semibold text-[#2C4A5E]">
                                                        {problematica.nombre}
                                                    </span>
                                                    <span className="flex shrink-0 items-center gap-1 text-sm font-bold text-[#C66A10]">
                                                        {problematica.total.toLocaleString(
                                                            'es-AR',
                                                        )}
                                                        <ChevronRight
                                                            size={15}
                                                            className="transition group-hover:translate-x-0.5"
                                                        />
                                                    </span>
                                                </div>
                                                <div className="mt-2 h-2 overflow-hidden rounded-full bg-[#F3EADB]">
                                                    <div
                                                        className="h-full rounded-full bg-[#C66A10]"
                                                        style={{
                                                            width: `${(problematica.total / max) * 100}%`,
                                                        }}
                                                    />
                                                </div>
                                            </Link>
                                        ))}
                                </div>
                                <div className="rounded-lg border border-[#DFE4EA] bg-[#FBFBFC] p-5">
                                    {props.problematicaSeleccionada ? (
                                        <>
                                            <h3 className="mt-1 text-lg font-semibold text-[#173247]">
                                                {
                                                    props
                                                        .problematicaSeleccionada
                                                        .nombre
                                                }
                                            </h3>
                                            <div className="mt-6">
                                                <TrendChart
                                                    intervals={props.intervalos}
                                                    values={
                                                        props.legajosPorPeriodo
                                                    }
                                                />
                                            </div>
                                        </>
                                    ) : (
                                        <div className="flex min-h-64 flex-col items-center justify-center text-center">
                                            <span className="grid size-14 place-items-center rounded-2xl bg-[#FFE9C7] text-[#C66A10]">
                                                <TriangleAlert size={25} />
                                            </span>
                                            <h3 className="mt-4 text-lg font-semibold text-[#173247]">
                                                Seleccioná una problemática
                                            </h3>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
