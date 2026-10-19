import { CircleAlert, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function ResumenErrores({
    errors,
    etiquetas = {},
    floating = false,
}: {
    errors: Record<string, string>;
    etiquetas?: Record<string, string>;
    floating?: boolean;
}) {
    const entradas = Object.entries(errors ?? {});
    const clave = JSON.stringify(errors ?? {});
    const [claveCerrada, setClaveCerrada] = useState<string | null>(null);
    const [cerrando, setCerrando] = useState(false);
    const visible = entradas.length > 0 && claveCerrada !== clave;

    useEffect(() => {
        if (entradas.length === 0) {
            return;
        }

        const primerCampo = entradas.find(
            ([campo]) => campo !== 'general',
        )?.[0];
        const objetivo = primerCampo
            ? (document.getElementById(primerCampo) ??
              document.querySelector(`[name="${primerCampo}"]`))
            : null;

        objetivo?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [clave]);

    if (entradas.length === 0 || !visible) {
        return null;
    }

    const etiquetaDe = (campo: string) => {
        const base = campo.replace(/\.\d+$/, '');

        return etiquetas[base] ?? base;
    };

    const cerrar = () => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setClaveCerrada(clave);

            return;
        }

        setCerrando(true);
    };

    const finalizarCierre = () => {
        if (cerrando) {
            setClaveCerrada(clave);
        }
    };

    return (
        <div
            role="alert"
            aria-live="assertive"
            onAnimationEnd={floating ? finalizarCierre : undefined}
            className={
                floating
                    ? `prodenya-toast-motion fixed top-4 right-4 z-50 w-[min(calc(100vw-2rem),420px)] overflow-hidden rounded-lg border border-[#E3B7AE] bg-white text-[#173247]${cerrando ? 'prodenya-toast-motion-out' : ''}`
                    : 'rounded-lg border border-[#E3B7AE] bg-[#FBEFEC] p-4 sm:p-5'
            }
        >
            {floating ? (
                <>
                    <div className="flex items-start gap-3 border-b border-[#F0D8D2] px-4 py-3.5">
                        <span className="mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl bg-[#F8E1DD] text-[#A84635]">
                            <CircleAlert size={20} strokeWidth={2.2} />
                        </span>
                        <div className="min-w-0 flex-1">
                            <p className="text-sm font-semibold text-[#173247]">
                                Revisá los datos antes de continuar
                            </p>
                            <p className="mt-0.5 text-xs text-[#2B2F36]/60">
                                {entradas.length === 1
                                    ? 'Hay un campo pendiente.'
                                    : `Hay ${entradas.length} campos pendientes.`}
                            </p>
                        </div>
                        <button
                            type="button"
                            aria-label="Cerrar aviso de errores"
                            onClick={cerrar}
                            className="-mt-1 -mr-1 grid size-7 shrink-0 place-items-center rounded-lg text-[#2B2F36]/45 transition hover:bg-[#FBEFEC] hover:text-[#A84635] focus-visible:ring-2 focus-visible:ring-[#A84635]/30 focus-visible:outline-none"
                        >
                            <X size={16} />
                        </button>
                    </div>
                    <ul className="max-h-52 space-y-2 overflow-y-auto px-4 py-3 text-xs text-[#7A2E21]">
                        {entradas.map(([campo, mensaje]) => (
                            <li key={campo} className="flex gap-2 leading-5">
                                <span className="mt-1.5 size-1.5 shrink-0 rounded-full bg-[#A84635]" />
                                <span>
                                    {campo === 'general' ? (
                                        mensaje
                                    ) : (
                                        <>
                                            <strong>
                                                {etiquetaDe(campo)}:
                                            </strong>{' '}
                                            {mensaje}
                                        </>
                                    )}
                                </span>
                            </li>
                        ))}
                    </ul>
                </>
            ) : (
                <>
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
                                        <strong>{etiquetaDe(campo)}:</strong>{' '}
                                        {mensaje}
                                    </>
                                )}
                            </li>
                        ))}
                    </ul>
                </>
            )}
        </div>
    );
}
