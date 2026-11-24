import { LoaderCircle, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

import actasRoutes from '@/routes/actas';

export interface PersonaEncontrada {
    id: number;
    nombre: string;
    dni: string;
    fecha_nacimiento: string;
    domicilio: string;
    barrio: string;
    telefono: string;
    legajo: { id: number; numero_legajo: string; url: string } | null;
}

interface Props {
    onElegir: (persona: PersonaEncontrada) => void;
    /** Solo permite elegir personas que ya tienen legajo. */
    soloConLegajo?: boolean;
    placeholder?: string;
}

/**
 * Busca personas del sistema por DNI o nombre.
 */
export default function BuscadorPersonas({
    onElegir,
    soloConLegajo = false,
    placeholder = 'Buscar por DNI o nombre…',
}: Props) {
    const [termino, setTermino] = useState('');
    const [resultados, setResultados] = useState<PersonaEncontrada[]>([]);
    const [buscando, setBuscando] = useState(false);
    const [abierto, setAbierto] = useState(false);
    const consultaValida = termino.trim().length >= 2;

    useEffect(() => {
        if (!consultaValida) {
            return;
        }

        const controller = new AbortController();
        const timeout = setTimeout(() => {
            setBuscando(true);
            fetch(actasRoutes.personas.url({ query: { q: termino.trim() } }), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            })
                .then((response) => response.json())
                .then((datos: PersonaEncontrada[]) => setResultados(datos))
                .catch(() => undefined)
                .finally(() => setBuscando(false));
        }, 250);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [termino, consultaValida]);

    const visibles = consultaValida ? resultados : [];

    return (
        <div className="relative">
            <div className="flex h-10 items-center gap-2 rounded-xl border border-border bg-background px-3 focus-within:border-ring focus-within:ring-2 focus-within:ring-ring/30">
                {buscando ? (
                    <LoaderCircle className="size-4 shrink-0 animate-spin text-muted-foreground" />
                ) : (
                    <Search className="size-4 shrink-0 text-muted-foreground" />
                )}
                <input
                    type="search"
                    value={termino}
                    onChange={(event) => {
                        setTermino(event.target.value);
                        setAbierto(true);
                    }}
                    onFocus={() => setAbierto(true)}
                    onBlur={() => setTimeout(() => setAbierto(false), 150)}
                    placeholder={placeholder}
                    aria-label="Buscar persona"
                    className="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-foreground outline-none placeholder:text-muted-foreground focus:ring-0"
                />
            </div>

            {abierto && consultaValida && (
                <ul className="absolute inset-x-0 top-full z-30 mt-1 max-h-72 overflow-y-auto rounded-xl border border-border bg-card p-1 shadow-lg">
                    {visibles.length === 0 ? (
                        <li className="px-3 py-2.5 text-sm text-muted-foreground">
                            {buscando
                                ? 'Buscando…'
                                : 'No hay personas que coincidan.'}
                        </li>
                    ) : (
                        visibles.map((persona) => {
                            const deshabilitada =
                                soloConLegajo && !persona.legajo;

                            return (
                                <li key={persona.id}>
                                    <button
                                        type="button"
                                        disabled={deshabilitada}
                                        onMouseDown={(event) =>
                                            event.preventDefault()
                                        }
                                        onClick={() => {
                                            onElegir(persona);
                                            setTermino('');
                                            setAbierto(false);
                                        }}
                                        className="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <span className="min-w-0">
                                            <span className="block truncate font-medium text-foreground">
                                                {persona.nombre}
                                            </span>
                                            <span className="block text-xs text-muted-foreground">
                                                DNI {persona.dni || '—'}
                                            </span>
                                        </span>
                                        <span className="shrink-0 text-xs text-muted-foreground">
                                            {persona.legajo
                                                ? `Legajo ${persona.legajo.numero_legajo}`
                                                : 'Sin legajo'}
                                        </span>
                                    </button>
                                </li>
                            );
                        })
                    )}
                </ul>
            )}
        </div>
    );
}
