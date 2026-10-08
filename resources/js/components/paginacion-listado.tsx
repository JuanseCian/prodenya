import { Link } from '@inertiajs/react';

export interface PaginadorLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginador<T> {
    data: T[];
    links: PaginadorLink[];
    from: number | null;
    to: number | null;
    total: number;
}

const limpiarEtiqueta = (label: string) =>
    label
        .replace('&laquo; Previous', 'Anterior')
        .replace('Next &raquo;', 'Siguiente')
        .replace('pagination.previous', 'Anterior')
        .replace('pagination.next', 'Siguiente')
        .replace(/&laquo;|&raquo;/g, '')
        .trim();

export default function Paginacion<T>({
    paginador,
    unidad,
}: {
    paginador: Paginador<T>;
    unidad: string;
}) {
    const { links, from, to, total } = paginador;

    return (
        <div className="flex flex-col gap-3 border-t border-[#E4E8E5] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p className="text-xs text-[#2B2F36]/60">
                {total === 0
                    ? `Sin ${unidad}`
                    : `Mostrando ${from}–${to} de ${total} ${unidad}`}
            </p>

            {links.length > 3 && (
                <nav className="flex flex-wrap gap-1" aria-label="Paginación">
                    {links.map((link, index) => {
                        const etiqueta = limpiarEtiqueta(link.label);
                        const base =
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2.5 text-xs font-semibold transition';

                        if (!link.url) {
                            return (
                                <span
                                    key={`${etiqueta}-${index}`}
                                    className={`${base} cursor-not-allowed text-[#2B2F36]/30`}
                                >
                                    {etiqueta}
                                </span>
                            );
                        }

                        return (
                            <Link
                                key={`${etiqueta}-${index}`}
                                href={link.url}
                                preserveScroll
                                preserveState
                                className={`${base} ${
                                    link.active
                                        ? 'bg-[#2C4A5E] text-white'
                                        : 'text-[#2B2F36]/70 hover:bg-[#EDF3EF]'
                                }`}
                            >
                                {etiqueta}
                            </Link>
                        );
                    })}
                </nav>
            )}
        </div>
    );
}
