import { Check, Search, X } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { KeyboardEvent } from 'react';

interface Opcion {
    id: number;
    nombre: string;
}

interface Props {
    opciones: Opcion[];
    seleccionadas: number[];
    onChange: (seleccionadas: number[]) => void;
    error?: string;
    errorId?: string;
    /** Id de «Otros»: al elegirla se pide especificar cuál es. */
    idOtros?: number | null;
    detalleOtros?: string;
    onDetalleOtrosChange?: (detalle: string) => void;
    errorOtros?: string;
}

const normalizar = (texto: string): string =>
    texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

/**
 * Selector de problemáticas: pastillas que se marcan con un toque y un buscador
 * para encontrarlas rápido.
 */
export default function SelectorProblematicas({
    opciones,
    seleccionadas,
    onChange,
    error,
    errorId,
    idOtros = null,
    detalleOtros = '',
    onDetalleOtrosChange,
    errorOtros,
}: Props) {
    const [busqueda, setBusqueda] = useState('');
    const campoOtros = useRef<HTMLInputElement>(null);
    const eligioOtros = idOtros !== null && seleccionadas.includes(idOtros);
    const eligioOtrosAntes = useRef(eligioOtros);

    useEffect(() => {
        if (eligioOtros && !eligioOtrosAntes.current) {
            campoOtros.current?.focus();
        }

        eligioOtrosAntes.current = eligioOtros;
    }, [eligioOtros]);

    const ordenadas = useMemo(
        () =>
            [...opciones].sort(
                (a, b) => Number(a.id === idOtros) - Number(b.id === idOtros),
            ),
        [opciones, idOtros],
    );

    const visibles = useMemo(() => {
        const termino = normalizar(busqueda);

        return termino === ''
            ? ordenadas
            : ordenadas.filter((opcion) =>
                  normalizar(opcion.nombre).includes(termino),
              );
    }, [busqueda, ordenadas]);

    const ocultasSeleccionadas = seleccionadas.filter(
        (id) => !visibles.some((opcion) => opcion.id === id),
    ).length;

    const alternar = (id: number) =>
        onChange(
            seleccionadas.includes(id)
                ? seleccionadas.filter((actual) => actual !== id)
                : [...seleccionadas, id],
        );

    const alPresionarTecla = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();

            if (visibles.length > 0) {
                alternar(visibles[0].id);
                setBusqueda('');
            }
        }

        if (event.key === 'Escape' && busqueda !== '') {
            event.preventDefault();
            setBusqueda('');
        }
    };

    return (
        <div
            className={`rounded-xl border bg-white transition ${error ? 'border-[#D59A8E] bg-[#FFF9F7]' : 'border-[#D5D1C6] focus-within:border-[#2C4A5E] focus-within:ring-2 focus-within:ring-[#2C4A5E]/15'}`}
            aria-describedby={error ? errorId : undefined}
        >
            <div className="flex items-center gap-3 border-b border-[#E4E8E5] px-3.5 py-2.5">
                <Search className="size-4 shrink-0 text-[#2B2F36]/40" />
                <input
                    type="search"
                    value={busqueda}
                    onChange={(event) => setBusqueda(event.target.value)}
                    onKeyDown={alPresionarTecla}
                    placeholder="Buscar problemática…"
                    aria-label="Buscar problemática"
                    className="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-[#2B2F36] outline-none placeholder:text-[#2B2F36]/35 focus:ring-0"
                />
                <span
                    className="shrink-0 text-xs text-[#2B2F36]/55 tabular-nums"
                    aria-live="polite"
                >
                    {seleccionadas.length === 0
                        ? 'Ninguna elegida'
                        : `${seleccionadas.length} ${seleccionadas.length === 1 ? 'elegida' : 'elegidas'}`}
                </span>
                {seleccionadas.length > 0 && (
                    <button
                        type="button"
                        onClick={() => onChange([])}
                        className="shrink-0 rounded-md px-1.5 py-0.5 text-xs font-medium text-[#2C4A5E] transition hover:bg-[#EEF3F0]"
                    >
                        Quitar todas
                    </button>
                )}
            </div>

            <div className="p-3">
                {visibles.length > 0 ? (
                    <ul
                        className="flex flex-wrap gap-2"
                        aria-label="Problemáticas"
                    >
                        {visibles.map((opcion) => {
                            const elegida = seleccionadas.includes(opcion.id);

                            return (
                                <li key={opcion.id}>
                                    <button
                                        type="button"
                                        onClick={() => alternar(opcion.id)}
                                        aria-pressed={elegida}
                                        className={`inline-flex h-9 items-center gap-1.5 rounded-full border px-3.5 text-sm transition focus-visible:ring-2 focus-visible:ring-[#2C4A5E]/30 focus-visible:outline-none ${
                                            elegida
                                                ? 'border-[#2C4A5E] bg-[#2C4A5E] text-white shadow-sm hover:bg-[#173247]'
                                                : 'border-[#D5D1C6] bg-white text-[#2B2F36] hover:border-[#2C4A5E]/50 hover:bg-[#F4F7F5]'
                                        }`}
                                    >
                                        {elegida && (
                                            <Check
                                                className="size-3.5"
                                                strokeWidth={2.5}
                                            />
                                        )}
                                        {opcion.nombre}
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                ) : (
                    <p className="px-1 py-2 text-sm text-[#2B2F36]/55">
                        No hay problemáticas que coincidan con «{busqueda}».
                    </p>
                )}

                {eligioOtros && onDetalleOtrosChange && (
                    <label className="mt-3 block rounded-lg border border-[#DCE3DF] bg-[#F7FAF8] px-3 py-2.5">
                        <span className="text-xs font-medium text-[#2B2F36]/70">
                            ¿Cuál es la otra problemática?
                        </span>
                        <input
                            ref={campoOtros}
                            type="text"
                            value={detalleOtros}
                            onChange={(event) =>
                                onDetalleOtrosChange(event.target.value)
                            }
                            maxLength={255}
                            placeholder="Ej.: conflicto en la escuela"
                            aria-invalid={errorOtros ? true : undefined}
                            className={`mt-1.5 block w-full rounded-md border bg-white px-3 py-2 text-sm text-[#2B2F36] outline-none placeholder:text-[#2B2F36]/35 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20 ${errorOtros ? 'border-[#D59A8E]' : 'border-[#D5D1C6]'}`}
                        />
                        {errorOtros && (
                            <span className="mt-1.5 block text-xs text-[#B1553F]">
                                {errorOtros}
                            </span>
                        )}
                    </label>
                )}

                {busqueda !== '' && (
                    <div className="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-[#2B2F36]/55">
                        <span>
                            {ocultasSeleccionadas > 0
                                ? `${ocultasSeleccionadas} elegida${ocultasSeleccionadas === 1 ? '' : 's'} fuera de esta búsqueda. `
                                : ''}
                            Enter elige la primera.
                        </span>
                        <button
                            type="button"
                            onClick={() => setBusqueda('')}
                            className="inline-flex items-center gap-1 font-medium text-[#2C4A5E] hover:underline"
                        >
                            <X className="size-3" />
                            Ver todas
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
