import { Head, Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    BarChart3,
    FolderOpen,
    HeartPulse,
    TriangleAlert,
} from 'lucide-react';

import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';

const FONT_HEADING = 'inherit';

export default function EstadisticasResumen(props: EstadisticasData) {
    return (
        <>
            <Head title="Estadísticas" />
            <EstadisticasShell
                active="resumen"
                title="Estadísticas"
                subtitle="Resumen"
            >
                <div className="stats-page-surface min-h-[calc(100vh-77px)] px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <div className="mx-auto max-w-7xl">
                        <EstadisticasFiltros
                            filtros={props.filtros}
                            problematicas={props.problematicas}
                        />

                        <section className="pt-7">
                            <div className="mb-4">
                                <h2
                                    className="mt-1 text-xl font-semibold text-[#173247]"
                                    style={{ fontFamily: FONT_HEADING }}
                                >
                                    Indicadores
                                </h2>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <EstadisticaKpi
                                    label="Legajos registrados"
                                    value={props.kpis.totalLegajos}
                                    detail="Aperturas dentro del período"
                                    icon={FolderOpen}
                                    tone="blue"
                                />
                                <EstadisticaKpi
                                    label="Legajos abiertos"
                                    value={props.kpis.legajosAbiertos}
                                    detail="Casos con seguimiento activo"
                                    icon={BarChart3}
                                    tone="green"
                                />
                                <EstadisticaKpi
                                    label="Legajos cerrados"
                                    value={props.kpis.legajosCerrados}
                                    detail="Casos finalizados"
                                    icon={ArrowUpRight}
                                    tone="violet"
                                />
                                <EstadisticaKpi
                                    label="Problemáticas con casos"
                                    value={props.kpis.problematicasConCasos}
                                    detail="Con legajos asociados"
                                    icon={TriangleAlert}
                                    tone="orange"
                                />
                            </div>
                        </section>

                        <section className="grid gap-5 pt-7 md:grid-cols-3">
                            <Link
                                href="/estadisticas/legajos"
                                className="group rounded-lg border border-[#DFE4EA] bg-white p-5 transition hover:border-[#AAA4E5]"
                            >
                                <span className="grid size-12 place-items-center rounded-2xl bg-[#DDF1FF] text-[#1670AD]">
                                    <FolderOpen size={23} />
                                </span>
                                <h2
                                    className="mt-5 text-xl font-semibold text-[#173247]"
                                    style={{ fontFamily: FONT_HEADING }}
                                >
                                    Legajos
                                </h2>
                                <span className="mt-5 inline-flex items-center gap-1.5 text-sm font-bold text-[#1670AD]">
                                    Abrir{' '}
                                    <ArrowUpRight
                                        size={16}
                                        className="transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                    />
                                </span>
                            </Link>
                            <Link
                                href="/estadisticas/problematicas"
                                className="group rounded-lg border border-[#DFE4EA] bg-white p-5 transition hover:border-[#AAA4E5]"
                            >
                                <span className="grid size-12 place-items-center rounded-2xl bg-[#FFE9C7] text-[#C66A10]">
                                    <TriangleAlert size={23} />
                                </span>
                                <h2
                                    className="mt-5 text-xl font-semibold text-[#173247]"
                                    style={{ fontFamily: FONT_HEADING }}
                                >
                                    Problemáticas
                                </h2>
                                <span className="mt-5 inline-flex items-center gap-1.5 text-sm font-bold text-[#C66A10]">
                                    Abrir{' '}
                                    <ArrowUpRight
                                        size={16}
                                        className="transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                    />
                                </span>
                            </Link>
                            <Link
                                href="/estadisticas/intervenciones"
                                className="group rounded-lg border border-[#DFE4EA] bg-white p-5 transition hover:border-[#AAA4E5]"
                            >
                                <span className="grid size-12 place-items-center rounded-2xl bg-[#EAE1FF] text-[#7652C7]">
                                    <HeartPulse size={23} />
                                </span>
                                <h2
                                    className="mt-5 text-xl font-semibold text-[#173247]"
                                    style={{ fontFamily: FONT_HEADING }}
                                >
                                    Intervenciones
                                </h2>
                                <span className="mt-5 inline-flex items-center gap-1.5 text-sm font-bold text-[#7652C7]">
                                    Abrir{' '}
                                    <ArrowUpRight
                                        size={16}
                                        className="transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                    />
                                </span>
                            </Link>
                        </section>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
