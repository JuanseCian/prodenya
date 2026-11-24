import { useId, useMemo, useState } from 'react';
import type { KeyboardEvent } from 'react';

interface Props {
    value: string;
    onChange: (barrio: string) => void;
    barrios: string[];
    opciones?: string[];
    etiqueta?: string;
    placeholder?: string;
    ancho?: string;
    error?: string;
    /** «hoja»: espacio punteado dentro de un acta; «campo»: campo de formulario común. */
    variante?: 'hoja' | 'campo';
    id?: string;
}

const normalizar = (texto: string): string =>
    texto
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .trim();

/**
 * Campo en línea para elegir un barrio de la lista: se puede escribir para
 * buscarlo, pero solo se acepta un barrio cargado en el sistema.
 */
export default function SelectorBarrio({
    value,
    onChange,
    barrios,
    opciones,
    etiqueta = 'barrio',
    placeholder = 'barrio',
    ancho = 'w-48',
    error,
    variante = 'hoja',
    id,
}: Props) {
    const opcionesDisponibles = opciones ?? barrios;
    const listaId = useId();
    const [texto, setTexto] = useState(value);
    const [valorPrevio, setValorPrevio] = useState(value);
    const [abierto, setAbierto] = useState(false);
    const [activo, setActivo] = useState(0);

    // Si el barrio cambia desde afuera (p. ej. al elegir una persona), se refleja en el campo.
    if (value !== valorPrevio) {
        setValorPrevio(value);
        setTexto(value);
    }

    const coincidencias = useMemo(() => {
        const termino = normalizar(texto);

        if (termino === '' || termino === normalizar(value)) {
            return opcionesDisponibles;
        }

        return opcionesDisponibles.filter((barrio) => normalizar(barrio).includes(termino));
    }, [opcionesDisponibles, texto, value]);

    const elegir = (barrio: string) => {
        setTexto(barrio);
        setAbierto(false);
        onChange(barrio);
    };

    const confirmar = () => {
        setAbierto(false);
        const termino = normalizar(texto);

        if (termino === '') {
            onChange('');

            return;
        }

        const exacto = opcionesDisponibles.find((barrio) => normalizar(barrio) === termino);

        if (exacto) {
            elegir(exacto);

            return;
        }

        // Lo escrito no es un barrio de la lista: se vuelve al último válido.
        setTexto(value);
    };

    const alPresionarTecla = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            setAbierto(true);
            setActivo((actual) => {
                const total = coincidencias.length;

                if (total === 0) {
                    return 0;
                }

                return event.key === 'ArrowDown'
                    ? (actual + 1) % total
                    : (actual - 1 + total) % total;
            });

            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();

            if (abierto && coincidencias[activo]) {
                elegir(coincidencias[activo]);
            } else {
                confirmar();
            }

            return;
        }

        if (event.key === 'Escape') {
            setAbierto(false);
            setTexto(value);
        }
    };

    return (
        <span
            className={
                variante === 'campo'
                    ? 'relative block w-full'
                    : 'relative mx-1 inline-block max-w-full align-baseline'
            }
        >
            <input
                type="text"
                role="combobox"
                aria-expanded={abierto}
                aria-controls={listaId}
                aria-autocomplete="list"
                aria-activedescendant={
                    abierto && coincidencias[activo]
                        ? `${listaId}-${activo}`
                        : undefined
                }
                aria-label={placeholder}
                aria-invalid={error ? true : undefined}
                title={
                    error ?? `Escribí para buscar y elegí ${etiqueta} de la lista`
                }
                value={texto}
                placeholder={placeholder}
                autoComplete="off"
                onChange={(event) => {
                    setTexto(event.target.value);
                    setActivo(0);
                    setAbierto(true);
                }}
                onFocus={(event) => {
                    event.target.select();
                    setActivo(0);
                    setAbierto(true);
                }}
                onBlur={confirmar}
                onKeyDown={alPresionarTecla}
                id={id}
                className={
                    variante === 'campo'
                        ? `h-10 w-full rounded-lg border bg-white px-3 text-sm text-[#2B2F36] outline-none placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20 ${error ? 'border-[#D59A8E]' : 'border-[#D5D1C6]'}`
                        : `inline-block max-w-full ${ancho} rounded-sm border-0 border-b-2 border-dotted px-1 py-0 align-baseline font-[inherit] leading-snug text-[#173247] text-[inherit] outline-none placeholder:font-normal placeholder:text-[#8A6A35]/60 focus:border-solid focus:border-[#2C4A5E] focus:bg-[#FFF3C4] ${
                              error
                                  ? 'border-[#9B5964] bg-[#FCF1F3]'
                                  : 'border-[#2B2F36]/50 bg-[#FFFBEA]'
                          }`
                }
            />
            {abierto && (
                <ul
                    id={listaId}
                    role="listbox"
                    className="absolute top-full left-0 z-30 mt-1 max-h-60 w-64 max-w-[80vw] overflow-y-auto rounded-lg border border-[#DCE3DF] bg-white p-1 font-sans text-sm leading-5 font-normal text-[#2B2F36] shadow-lg"
                >
                    {coincidencias.length === 0 ? (
                        <li className="px-3 py-2 text-[#697586]">
                            No hay {etiqueta}s con «{texto}».
                        </li>
                    ) : (
                        coincidencias.map((barrio, index) => (
                            <li
                                key={barrio}
                                id={`${listaId}-${index}`}
                                role="option"
                                aria-selected={index === activo}
                                onMouseDown={(event) => {
                                    event.preventDefault();
                                    elegir(barrio);
                                }}
                                onMouseEnter={() => setActivo(index)}
                                className={`cursor-pointer rounded-md px-3 py-1.5 ${
                                    index === activo
                                        ? 'bg-[#EAF2EE] text-[#173247]'
                                        : ''
                                } ${barrio === value ? 'font-semibold' : ''}`}
                            >
                                {barrio}
                            </li>
                        ))
                    )}
                </ul>
            )}
        </span>
    );
}
