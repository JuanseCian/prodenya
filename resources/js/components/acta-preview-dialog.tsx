import { FileText, LoaderCircle, TriangleAlert } from 'lucide-react';
import { useEffect, useState } from 'react';

import ActaHojaLectura from '@/components/acta-hoja-lectura';
import type { HojaActaGuardada } from '@/components/acta-hoja-lectura';
import DescargarActaMenu from '@/components/descargar-acta-menu';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';

interface Props {
    /** URL que devuelve la hoja del acta en JSON; null cierra el diálogo. */
    hojaUrl: string | null;
    onClose: () => void;
}

/** Las hojas ya pedidas se reutilizan mientras la página siga abierta. */
const hojasCargadas = new Map<string, HojaActaGuardada>();

const fechaLegible = (valor: string | null | undefined): string | null =>
    valor
        ? new Intl.DateTimeFormat('es-AR').format(
              new Date(`${valor.slice(0, 10)}T12:00:00`),
          )
        : null;

/**
 * Muestra el acta tal como quedó en el Word, con sus firmas, y permite descargarla.
 */
export default function ActaPreviewDialog({ hojaUrl, onClose }: Props) {
    /** Último resultado de carga, asociado a la URL que lo pidió. */
    const [carga, setCarga] = useState<{
        url: string;
        hoja: HojaActaGuardada | null;
        error: boolean;
    } | null>(null);

    const hoja = hojaUrl
        ? (hojasCargadas.get(hojaUrl) ??
          (carga?.url === hojaUrl ? carga.hoja : null))
        : null;
    const error = hojaUrl !== null && carga?.url === hojaUrl && carga.error;

    useEffect(() => {
        if (!hojaUrl || hojasCargadas.has(hojaUrl)) {
            return;
        }

        const controller = new AbortController();

        fetch(hojaUrl, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(String(response.status));
                }

                return response.json() as Promise<HojaActaGuardada>;
            })
            .then((datos) => {
                hojasCargadas.set(hojaUrl, datos);
                setCarga({ url: hojaUrl, hoja: datos, error: false });
            })
            .catch((motivo: unknown) => {
                if (!(
                    motivo instanceof DOMException &&
                    motivo.name === 'AbortError'
                )) {
                    setCarga({ url: hojaUrl, hoja: null, error: true });
                }
            });

        return () => controller.abort();
    }, [hojaUrl]);

    const firmantes = hoja ? Object.entries(hoja.firmantes) : [];

    return (
        <Dialog
            open={hojaUrl !== null}
            onOpenChange={(abierto) => !abierto && onClose()}
        >
            <DialogContent className="flex max-h-[calc(100dvh-2rem)] flex-col gap-0 overflow-hidden p-0 sm:max-w-4xl">
                <DialogHeader className="border-b border-[#E4E8E5] px-5 py-4 text-left sm:px-6">
                    <div className="flex items-start gap-3">
                        <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-[#EDF3EF] text-[#2C4A5E]">
                            <FileText className="size-4.5" />
                        </span>
                        <div className="min-w-0">
                            <DialogTitle className="text-base text-[#173247]">
                                {hoja?.etiqueta ?? 'Acta'}
                            </DialogTitle>
                            <DialogDescription className="mt-0.5 text-xs text-[#697586]">
                                {hoja
                                    ? [
                                          hoja.titulo,
                                          fechaLegible(hoja.fecha),
                                          hoja.legajo_numero
                                              ? `Legajo ${hoja.legajo_numero}`
                                              : null,
                                          hoja.creado_por
                                              ? `Cargada por ${hoja.creado_por}`
                                              : null,
                                      ]
                                          .filter(Boolean)
                                          .join(' · ')
                                    : 'Cargando el acta…'}
                            </DialogDescription>
                        </div>
                    </div>

                    {hoja && hoja.requiere_firma && (
                        <ul className="mt-3 flex flex-wrap gap-1.5">
                            {firmantes.map(([rol, etiqueta]) => {
                                const firmado = hoja.firmados.includes(rol);

                                return (
                                    <li
                                        key={rol}
                                        className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[length:calc(0.7rem_+_2px)] font-semibold ${
                                            firmado
                                                ? 'bg-[#DDEBE1] text-[#31765b]'
                                                : 'bg-[#F4F6F5] text-[#697586]'
                                        }`}
                                    >
                                        {firmado ? '✓' : '○'} {etiqueta}
                                        {firmado && hoja.firmas[rol]
                                            ? `: ${hoja.firmas[rol]}`
                                            : ' · sin firmar'}
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </DialogHeader>

                <div className="min-h-0 flex-1 overflow-y-auto bg-[#EEF1EF] px-2 py-5 sm:px-6">
                    {error ? (
                        <div className="mx-auto flex max-w-md flex-col items-center gap-3 py-16 text-center text-sm text-[#697586]">
                            <TriangleAlert className="size-6 text-[#9B5964]" />
                            No se pudo cargar el acta. Cerrá y volvé a
                            intentarlo.
                        </div>
                    ) : hoja ? (
                        <ActaHojaLectura hoja={hoja} />
                    ) : (
                        <div
                            className="mx-auto max-w-[794px] space-y-4 rounded-sm bg-white px-8 py-10 shadow-sm"
                            aria-hidden="true"
                        >
                            <Skeleton className="mx-auto h-12 w-40" />
                            <Skeleton className="h-4 w-2/3" />
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-5/6" />
                            <Skeleton className="h-32 w-full" />
                            <Skeleton className="h-4 w-1/2" />
                        </div>
                    )}
                </div>

                <DialogFooter className="flex-row items-center justify-end gap-2 border-t border-[#E4E8E5] px-5 py-3 sm:px-6">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={onClose}
                    >
                        Cerrar
                    </Button>
                    {hoja ? (
                        <DescargarActaMenu
                            wordUrl={hoja.descarga_url}
                            pdfUrl={hoja.descarga_pdf_url}
                            etiqueta={hoja.etiqueta}
                        />
                    ) : (
                        <Button size="sm" disabled>
                            <LoaderCircle className="size-3.5 animate-spin" />
                            Descargar acta
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
