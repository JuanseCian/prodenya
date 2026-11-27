import { useState } from 'react';

interface Props {
    /** Quién firma: «Responsable», «Entrevistado»… */
    titulo: string;
    nombre: string | null;
    aclaracion: string | null;
    /** URL de la imagen de la firma; null si no tiene firma digital. */
    url: string | null;
    compacta?: boolean;
}

/**
 * Firma registrada de una intervención, de solo lectura.
 * Si el archivo no es una imagen (p. ej. un PDF subido), se ofrece abrirlo.
 */
export default function FirmaGuardada({
    titulo,
    nombre,
    aclaracion,
    url,
    compacta = false,
}: Props) {
    const [sinVista, setSinVista] = useState(false);

    return (
        <figure
            className={`m-0 rounded-lg border border-[#DCE3DF] bg-white ${compacta ? 'p-2.5' : 'p-3'}`}
        >
            <figcaption className="text-[length:calc(0.68rem_+_2px)] font-bold tracking-[0.08em] text-[#6B8F71] uppercase">
                {titulo}
            </figcaption>
            <div
                className={`mt-1 flex items-end ${compacta ? 'h-14' : 'h-20'}`}
            >
                {url && !sinVista ? (
                    <img
                        src={url}
                        alt={`Firma de ${nombre || titulo.toLowerCase()}`}
                        loading="lazy"
                        onError={() => setSinVista(true)}
                        className="h-full w-auto max-w-full object-contain object-left"
                    />
                ) : url ? (
                    <a
                        href={url}
                        className="text-xs font-semibold text-[#267148] underline"
                    >
                        Ver archivo de firma
                    </a>
                ) : (
                    <span className="text-xs text-[#697586] italic">
                        Sin firma digital
                    </span>
                )}
            </div>
            <div className="mt-1 border-t border-dashed border-[#2B2F36]/40 pt-1.5">
                <p className="text-sm font-semibold text-[#173247]">
                    {nombre || '—'}
                </p>
                {aclaracion && (
                    <p className="text-xs text-[#697586]">{aclaracion}</p>
                )}
            </div>
        </figure>
    );
}
