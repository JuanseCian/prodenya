import { ChevronDown, Filter, RotateCcw, SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';

import type { ChangeEventHandler, ReactNode } from 'react';

import type { EstadisticasData, Preset } from '@/components/estadisticas-types';

const inputClass =
    'mt-1.5 h-10 w-full rounded-md border border-[#D5DCE4] bg-white px-3 text-sm font-medium text-[#243140] outline-none transition focus:border-[#5B559E] focus:ring-2 focus:ring-[#AAA4E5]/20 dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#EDF1F5]';

function SelectField({
    label,
    name,
    defaultValue,
    value,
    onChange,
    children,
}: {
    label: string;
    name: string;
    defaultValue: string | number | null;
    value?: string;
    onChange?: ChangeEventHandler<HTMLSelectElement>;
    children: ReactNode;
}) {
    return (
        <label className="text-xs font-bold tracking-wide text-[#697586] uppercase dark:text-[#ADB7C3]">
            {label}
            <select
                name={name}
                {...(value !== undefined
                    ? { value, onChange }
                    : { defaultValue: defaultValue ?? '' })}
                className={inputClass}
            >
                {children}
            </select>
        </label>
    );
}

function DateField({
    label,
    name,
    value,
    onChange,
}: {
    label: string;
    name: string;
    value: string;
    onChange: () => void;
}) {
    return (
        <label className="text-xs font-bold tracking-wide text-[#697586] uppercase dark:text-[#ADB7C3]">
            {label}
            <input
                type="date"
                name={name}
                defaultValue={value}
                onChange={onChange}
                className={inputClass}
            />
        </label>
    );
}

export default function EstadisticasFiltros({
    filtros,
    rango,
    problematicas,
    catalogos,
}: Pick<
    EstadisticasData,
    'filtros' | 'rango' | 'problematicas' | 'catalogos'
>) {
    const [preset, setPreset] = useState<Preset>(filtros.preset);
    const [advancedOpen, setAdvancedOpen] = useState(
        Boolean(
            filtros.estado ||
                filtros.problematica ||
                filtros.profesional ||
                filtros.responsable ||
                filtros.edad ||
                filtros.genero ||
                filtros.tipo_intervencion ||
                filtros.tarea_estado ||
                filtros.agenda_estado,
        ),
    );

    const activeFilters = [
        filtros.comparar !== 'ninguno',
        filtros.estado,
        filtros.problematica,
        filtros.profesional,
        filtros.responsable,
        filtros.edad,
        filtros.genero,
        filtros.tipo_intervencion,
        filtros.tarea_estado,
        filtros.agenda_estado,
    ].filter(Boolean).length;

    const dateValue = (value: string | null, fallback: string) =>
        value ?? fallback;

    return (
        <form
            method="get"
            action={window.location.pathname}
            className="overflow-hidden rounded-xl border border-[#DFE4EA] bg-white shadow-[0_8px_24px_rgba(23,50,71,0.04)] dark:border-[#3B4552] dark:bg-[#232A34]"
        >
            <div className="border-b border-[#EDF0F3] px-4 py-4 sm:px-5 dark:border-[#3B4552]">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                        <span className="grid size-8 place-items-center rounded-md bg-[#EEECF7] text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]">
                            <Filter size={16} />
                        </span>
                        <div>
                            <h2 className="text-sm font-bold text-[#243140] dark:text-[#EDF1F5]">
                                Filtros de análisis
                            </h2>
                            <p className="mt-0.5 text-xs text-[#697586] dark:text-[#ADB7C3]">
                                Elegí el período y refiná los resultados.
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {activeFilters > 0 && (
                            <span className="rounded-full bg-[#EEECF7] px-2.5 py-1 text-[11px] font-bold text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]">
                                {activeFilters} filtro{activeFilters === 1 ? '' : 's'} activo{activeFilters === 1 ? '' : 's'}
                            </span>
                        )}
                        <a
                            href={window.location.pathname}
                            className="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-semibold text-[#697586] transition hover:bg-[#F5F6F8] hover:text-[#5B559E] dark:hover:bg-[#2C333E] dark:hover:text-[#AAA4E5]"
                        >
                            <RotateCcw size={14} /> Limpiar
                        </a>
                    </div>
                </div>
            </div>

            <div className="px-4 py-4 sm:px-5">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1.25fr_1fr_1fr_1.1fr_auto] lg:items-end">
                    <SelectField
                        label="Período de análisis"
                        name="preset"
                        defaultValue={null}
                        value={preset}
                        onChange={(event) =>
                            setPreset(event.target.value as Preset)
                        }
                    >
                        <option value="hoy">Día actual</option>
                        <option value="mes_actual">Mes actual</option>
                        <option value="anio_actual">Año actual</option>
                        <option value="personalizado">Rango personalizado</option>
                    </SelectField>
                    <DateField
                        label="Desde"
                        name="desde"
                        value={dateValue(filtros.desde, rango.desde)}
                        onChange={() => setPreset('personalizado')}
                    />
                    <DateField
                        label="Hasta"
                        name="hasta"
                        value={dateValue(filtros.hasta, rango.hasta)}
                        onChange={() => setPreset('personalizado')}
                    />
                    <SelectField
                        label="Comparar con"
                        name="comparar"
                        defaultValue={filtros.comparar}
                    >
                        <option value="ninguno">Sin comparación</option>
                        <option value="anterior">Período anterior</option>
                        <option value="anio_anterior">Año anterior</option>
                    </SelectField>
                    <button
                        type="submit"
                        className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#5B559E] px-5 text-sm font-semibold text-white transition hover:bg-[#4F498E] focus:ring-2 focus:ring-[#AAA4E5]/40 focus:outline-none"
                    >
                        <Filter size={16} /> Aplicar
                    </button>
                </div>

                <div className="mt-4 border-t border-[#EDF0F3] pt-3 dark:border-[#3B4552]">
                    <button
                        type="button"
                        onClick={() => setAdvancedOpen((open) => !open)}
                        className="inline-flex items-center gap-2 rounded-md py-1 text-sm font-semibold text-[#5B559E] transition hover:text-[#4F498E] focus:ring-2 focus:ring-[#AAA4E5]/40 focus:outline-none dark:text-[#AAA4E5]"
                        aria-expanded={advancedOpen}
                    >
                        <SlidersHorizontal size={16} />
                        Más filtros
                        <ChevronDown
                            size={16}
                            className={`transition-transform ${advancedOpen ? 'rotate-180' : ''}`}
                        />
                    </button>

                    {advancedOpen && (
                        <div className="mt-4 grid gap-5 border-t border-[#EDF0F3] pt-4 dark:border-[#3B4552]">
                            <fieldset>
                                <legend className="mb-3 text-xs font-bold tracking-[0.12em] text-[#697586] uppercase dark:text-[#ADB7C3]">
                                    Casos y personas
                                </legend>
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                                    <SelectField
                                        label="Estado de legajo"
                                        name="estado"
                                        defaultValue={filtros.estado}
                                    >
                                        <option value="">Todos</option>
                                        <option value="Abierto">Abierto</option>
                                        <option value="Cerrado">Cerrado</option>
                                        <option value="Archivado">Archivado</option>
                                    </SelectField>
                                    <SelectField
                                        label="Problemática"
                                        name="problematica"
                                        defaultValue={filtros.problematica}
                                    >
                                        <option value="">Todas</option>
                                        {problematicas.map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.nombre}
                                            </option>
                                        ))}
                                    </SelectField>
                                    <SelectField
                                        label="Profesional"
                                        name="profesional"
                                        defaultValue={filtros.profesional}
                                    >
                                        <option value="">Todos</option>
                                        {catalogos.profesionales.map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.name}{item.profesion ? ` · ${item.profesion}` : ''}
                                            </option>
                                        ))}
                                    </SelectField>
                                    <SelectField
                                        label="Edad"
                                        name="edad"
                                        defaultValue={filtros.edad}
                                    >
                                        <option value="">Todas</option>
                                        {catalogos.edades.map((item) => (
                                            <option key={item.value} value={item.value}>
                                                {item.label}
                                            </option>
                                        ))}
                                    </SelectField>
                                    <SelectField
                                        label="Género"
                                        name="genero"
                                        defaultValue={filtros.genero}
                                    >
                                        <option value="">Todos</option>
                                        {catalogos.generos.map((item) => (
                                            <option key={item.value} value={item.value}>
                                                {item.label}
                                            </option>
                                        ))}
                                    </SelectField>
                                    <SelectField
                                        label="Responsable"
                                        name="responsable"
                                        defaultValue={filtros.responsable}
                                    >
                                        <option value="">Todos</option>
                                        {catalogos.responsables.map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.name}{item.profesion ? ` · ${item.profesion}` : ''}
                                            </option>
                                        ))}
                                    </SelectField>
                                </div>
                            </fieldset>

                            <fieldset className="border-t border-[#EDF0F3] pt-4 dark:border-[#3B4552]">
                                <legend className="mb-3 text-xs font-bold tracking-[0.12em] text-[#697586] uppercase dark:text-[#ADB7C3]">
                                    Operación
                                </legend>
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <SelectField
                                        label="Tipo de intervención"
                                        name="tipo_intervencion"
                                        defaultValue={filtros.tipo_intervencion}
                                    >
                                        <option value="">Todos</option>
                                        {catalogos.tiposIntervencion.map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.nombre}
                                            </option>
                                        ))}
                                    </SelectField>
                                    <SelectField
                                        label="Estado de tarea"
                                        name="tarea_estado"
                                        defaultValue={filtros.tarea_estado}
                                    >
                                        <option value="">Todos</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_progreso">En progreso</option>
                                        <option value="completada">Completada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </SelectField>
                                    <SelectField
                                        label="Estado de agenda"
                                        name="agenda_estado"
                                        defaultValue={filtros.agenda_estado}
                                    >
                                        <option value="">Todos</option>
                                        <option value="programado">Programado</option>
                                        <option value="realizado">Realizado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </SelectField>
                                </div>
                            </fieldset>
                        </div>
                    )}
                </div>
            </div>
        </form>
    );
}
