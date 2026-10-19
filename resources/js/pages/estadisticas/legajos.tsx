import { Head } from '@inertiajs/react';
import { BarChart3, CheckCircle2, Clock3, FolderOpen } from 'lucide-react';

import { TrendChart } from '@/components/estadisticas-chart';
import EstadisticasFiltros from '@/components/estadisticas-filtros';
import { EstadisticaKpi } from '@/components/estadisticas-kpi';
import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasData } from '@/components/estadisticas-types';

const FONT_HEADING = 'inherit';

export default function EstadisticasLegajos(props: EstadisticasData) {
    const totalEstados = Object.values(props.legajosPorEstado).reduce(
        (total, value) => total + value,
        0,
    );
    const estados = [
        {
            label: 'Abiertos',
            value: props.legajosPorEstado.abiertos,
            color: 'bg-[#23845B]',
            text: 'text-[#23845B]',
        },
        {
            label: 'Cerrados',
            value: props.legajosPorEstado.cerrados,
            color: 'bg-[#1670AD]',
            text: 'text-[#1670AD]',
        },
        {
            label: 'Archivados',
            value: props.legajosPorEstado.archivados,
            color: 'bg-[#7652C7]',
            text: 'text-[#7652C7]',
        },
    ];

    return (
        <>
            <Head title="Estadísticas de legajos" />
            <EstadisticasShell
                active="legajos"
                title="Legajos"
                subtitle="Aperturas y estados"
            >
                <div className="stats-page-surface min-h-[calc(100vh-77px)] px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <div className="mx-auto max-w-7xl">
                        <div className="mb-6">
                            <h1
                                className="text-2xl font-semibold tracking-tight text-[#173247] sm:text-3xl"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Legajos
                            </h1>
                        </div>

                        <EstadisticasFiltros
                            filtros={props.filtros}
                            problematicas={props.problematicas}
                        />

                        <div className="grid gap-4 pt-7 sm:grid-cols-2 xl:grid-cols-4">
                            <EstadisticaKpi
                                label="Registrados"
                                value={props.kpis.totalLegajos}
                                detail={props.rango.descripcion}
                                icon={FolderOpen}
                                tone="blue"
                            />
                            <EstadisticaKpi
                                label="Abiertos"
                                value={props.kpis.legajosAbiertos}
                                detail="Seguimiento activo"
                                icon={CheckCircle2}
                                tone="green"
                            />
                            <EstadisticaKpi
                                label="Cerrados"
                                value={props.kpis.legajosCerrados}
                                detail="Finalizados en el período"
                                icon={Clock3}
                                tone="violet"
                            />
                            <EstadisticaKpi
                                label="Con problemática"
                                value={props.kpis.problematicasConCasos}
                                detail="Categorías con casos"
                                icon={BarChart3}
                                tone="orange"
                            />
                        </div>

                        <div className="grid gap-5 pt-6 xl:grid-cols-[1.55fr_0.85fr]">
                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 sm:p-6">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2
                                            className="mt-1 text-xl font-semibold text-[#173247]"
                                            style={{ fontFamily: FONT_HEADING }}
                                        >
                                            Aperturas
                                        </h2>
                                    </div>
                                    <span className="rounded-full bg-[#E8F5FF] px-3 py-1.5 text-xs font-semibold text-[#1670AD]">
                                        {props.rango.descripcion}
                                    </span>
                                </div>
                                <div className="mt-6">
                                    <TrendChart
                                        intervals={props.intervalos}
                                        values={props.legajosPorPeriodo}
                                    />
                                </div>
                            </article>

                            <article className="rounded-lg border border-[#DFE4EA] bg-white p-5 sm:p-6">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-[0.68rem] font-bold tracking-[0.12em] text-[#23845B] uppercase">
                                            Distribución
                                        </p>
                                        <h2
                                            className="mt-1 text-xl font-semibold text-[#173247]"
                                            style={{ fontFamily: FONT_HEADING }}
                                        >
                                            Estado actual
                                        </h2>
                                    </div>
                                    <BarChart3
                                        className="text-[#23845B]"
                                        size={22}
                                    />
                                </div>
                                <div className="mt-7 space-y-5">
                                    {estados.map((estado) => {
                                        const percentage = totalEstados
                                            ? (estado.value / totalEstados) *
                                              100
                                            : 0;

                                        return (
                                            <div key={estado.label}>
                                                <div className="mb-1.5 flex items-center justify-between text-sm">
                                                    <span className="font-semibold text-[#2C4A5E]">
                                                        {estado.label}
                                                    </span>
                                                    <span
                                                        className={`font-bold ${estado.text}`}
                                                    >
                                                        {estado.value.toLocaleString(
                                                            'es-AR',
                                                        )}
                                                    </span>
                                                </div>
                                                <div className="h-2 overflow-hidden rounded-full bg-[#EAF3F7]">
                                                    <div
                                                        className={`h-full rounded-full ${estado.color}`}
                                                        style={{
                                                            width: `${percentage}%`,
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                                <div className="mt-7 rounded-2xl bg-[#F3FCF7] p-4 text-sm text-[#2C4A5E]/70">
                                    <strong className="text-[#23845B]">
                                        {totalEstados.toLocaleString('es-AR')}
                                    </strong>{' '}
                                    legajos contabilizados.
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
