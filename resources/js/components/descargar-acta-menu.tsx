import {
    ChevronDown,
    Download,
    FileText,
    FileType2,
    LoaderCircle,
} from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
    wordUrl: string;
    pdfUrl?: string | null;
    /** Nombre de respaldo del PDF si el servidor no lo informa. */
    etiqueta: string;
    variant?: 'default' | 'outline';
    /** Dónde se muestra el error de conversión respecto del botón. */
    errorAlineado?: 'izquierda' | 'derecha';
}

/**
 * «Descargar acta» con opción Word o PDF. El PDF se convierte en el servidor:
 * se baja con fetch para mostrar el progreso y, si falla, el motivo.
 */
export default function DescargarActaMenu({
    wordUrl,
    pdfUrl = null,
    etiqueta,
    variant = 'default',
    errorAlineado = 'izquierda',
}: Props) {
    const [estado, setEstado] = useState<'generando' | 'error' | null>(null);
    const [mensaje, setMensaje] = useState('');

    const descargarPdf = async () => {
        if (!pdfUrl) {
            return;
        }

        setEstado('generando');

        try {
            const response = await fetch(pdfUrl, {
                headers: {
                    Accept: '*/*',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                const cuerpo = (await response.json().catch(() => null)) as {
                    message?: string;
                } | null;
                setMensaje(
                    cuerpo?.message ??
                        'No se pudo generar el PDF. Probá descargarla en Word.',
                );
                setEstado('error');

                return;
            }

            const archivo = await response.blob();
            const nombre =
                /filename="?([^";]+)"?/i.exec(
                    response.headers.get('Content-Disposition') ?? '',
                )?.[1] ?? `${etiqueta}.pdf`;
            const enlace = document.createElement('a');
            enlace.href = URL.createObjectURL(archivo);
            enlace.download = nombre;
            enlace.click();
            window.setTimeout(() => URL.revokeObjectURL(enlace.href), 1000);
            setEstado(null);
        } catch {
            setMensaje(
                'No se pudo generar el PDF. Revisá la conexión y volvé a intentarlo.',
            );
            setEstado('error');
        }
    };

    const generando = estado === 'generando';

    return (
        <div
            className={`flex flex-wrap items-center gap-2 ${errorAlineado === 'izquierda' ? 'flex-row-reverse justify-start' : ''}`}
        >
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button size="sm" variant={variant} disabled={generando}>
                        {generando ? (
                            <LoaderCircle className="size-3.5 animate-spin" />
                        ) : (
                            <Download className="size-3.5" />
                        )}
                        {generando ? 'Generando PDF…' : 'Descargar acta'}
                        <ChevronDown className="size-3.5 opacity-70" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-52">
                    <DropdownMenuItem asChild>
                        <a href={wordUrl}>
                            <FileText className="size-4" />
                            Word (.docx)
                        </a>
                    </DropdownMenuItem>
                    {pdfUrl && (
                        <DropdownMenuItem onSelect={() => void descargarPdf()}>
                            <FileType2 className="size-4" />
                            PDF
                        </DropdownMenuItem>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
            {estado === 'error' && (
                <p role="alert" className="max-w-xs text-xs text-[#9B5964]">
                    {mensaje}
                </p>
            )}
        </div>
    );
}
