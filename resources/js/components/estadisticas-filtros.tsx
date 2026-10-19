import { Filter } from 'lucide-react';

import type { EstadisticasData } from '@/components/estadisticas-types';

const meses = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

export default function EstadisticasFiltros({
    filtros,
    problematicas,
}: Pick<EstadisticasData, 'filtros' | 'problematicas'>) {
    return (
        <form
            method="get"
            action={window.location.pathname}
            className="rounded-lg border border-[#DFE4EA] bg-white p-4 sm:p-5 dark:border-[#3B4552] dark:bg-[#232A34]"
        >
            <div className="mb-4 flex items-center gap-2 text-sm font-semibold text-[#243140] dark:text-[#EDF1F5]">
                <Filter
                    size={17}
                    className="text-[#5B559E] dark:text-[#AAA4E5]"
                />
                Filtros
            </div>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1.4fr_auto] lg:items-end">
                <label className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                    Agrupar por
                    <select
                        name="periodo"
                        defaultValue={filtros.periodo}
                        className="mt-1.5 h-10 w-full rounded-md border border-[#D5DCE4] bg-white px-3 text-sm font-medium text-[#243140] normal-case transition outline-none focus:border-[#5B559E] focus:ring-2 focus:ring-[#AAA4E5]/20 dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#EDF1F5] dark:focus:border-[#AAA4E5]"
                    >
                        <option value="dia">Día</option>
                        <option value="mes">Mes</option>
                        <option value="anio">Año</option>
                    </select>
                </label>
                <label className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                    Año
                    <select
                        name="anio"
                        defaultValue={filtros.anio}
                        className="mt-1.5 h-10 w-full rounded-md border border-[#D5DCE4] bg-white px-3 text-sm font-medium text-[#243140] normal-case transition outline-none focus:border-[#5B559E] focus:ring-2 focus:ring-[#AAA4E5]/20 dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#EDF1F5] dark:focus:border-[#AAA4E5]"
                    >
                        {Array.from(
                            { length: new Date().getFullYear() - 2019 },
                            (_, index) => new Date().getFullYear() - index,
                        ).map((year) => (
                            <option key={year} value={year}>
                                {year}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                    Mes
                    <select
                        name="mes"
                        defaultValue={filtros.mes}
                        disabled={filtros.periodo !== 'dia'}
                        className="mt-1.5 h-10 w-full rounded-md border border-[#D5DCE4] bg-white px-3 text-sm font-medium text-[#243140] normal-case transition outline-none focus:border-[#5B559E] focus:ring-2 focus:ring-[#AAA4E5]/20 disabled:cursor-not-allowed disabled:opacity-45 dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#EDF1F5] dark:focus:border-[#AAA4E5]"
                    >
                        {meses.map((mes, index) => (
                            <option key={mes} value={index + 1}>
                                {mes}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                    Problemática
                    <select
                        name="problematica"
                        defaultValue={filtros.problematica ?? ''}
                        className="mt-1.5 h-10 w-full rounded-md border border-[#D5DCE4] bg-white px-3 text-sm font-medium text-[#243140] normal-case transition outline-none focus:border-[#5B559E] focus:ring-2 focus:ring-[#AAA4E5]/20 dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#EDF1F5] dark:focus:border-[#AAA4E5]"
                    >
                        <option value="">Todas las problemáticas</option>
                        {problematicas.map((problematica) => (
                            <option
                                key={problematica.id}
                                value={problematica.id}
                            >
                                {problematica.nombre}
                            </option>
                        ))}
                    </select>
                </label>
                <button
                    type="submit"
                    className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#5B559E] px-5 text-sm font-semibold text-white transition hover:bg-[#4F498E]"
                >
                    <Filter size={16} /> Aplicar
                </button>
            </div>
        </form>
    );
}
