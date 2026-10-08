import { useEffect, useRef } from 'react';

export default function ResumenErrores({
    errors,
    etiquetas = {},
}: {
    errors: Record<string, string>;
    etiquetas?: Record<string, string>;
}) {
    const referencia = useRef<HTMLDivElement>(null);
    const entradas = Object.entries(errors ?? {});
    const clave = JSON.stringify(errors ?? {});

    useEffect(() => {
        if (entradas.length > 0) {
            referencia.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [clave]);

    if (entradas.length === 0) return null;

    const etiquetaDe = (campo: string) => {
        const base = campo.replace(/\.\d+$/, '');
        return etiquetas[base] ?? base;
    };

    return (
        <div
            ref={referencia}
            role="alert"
            className="rounded-lg border border-[#E3B7AE] bg-[#FBEFEC] p-4 sm:p-5"
        >
            <p className="text-sm font-semibold text-[#A84635]">
                No se pudo completar la acción.{' '}
                {entradas.length === 1
                    ? 'Revisá este punto:'
                    : `Revisá estos ${entradas.length} puntos:`}
            </p>
            <ul className="mt-2 list-disc space-y-1 pl-5 text-sm text-[#7A2E21]">
                {entradas.map(([campo, mensaje]) => (
                    <li key={campo}>
                        {campo === 'general' ? (
                            mensaje
                        ) : (
                            <>
                                <strong>{etiquetaDe(campo)}:</strong> {mensaje}
                            </>
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}
