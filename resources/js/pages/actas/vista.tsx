import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, FileText } from 'lucide-react';

import ActaContenido from '@/components/acta-contenido';
import DescargarActaMenu from '@/components/descargar-acta-menu';
import actasRoutes from '@/routes/actas';

interface ActaVista {
    id: number;
    etiqueta: string;
    titulo: string;
    fecha: string | null;
    firmado: boolean;
    requiere_firma: boolean;
    creado_por: string | null;
    creado_en: string | null;
    legajo: { numero_legajo: string; url: string } | null;
    descarga_url: string;
    descarga_pdf_url: string;
    firmas: { rol_label: string; nombre: string }[];
    secciones: {
        titulo: string;
        campos: { label: string; valor: string }[];
        texto: boolean;
    }[];
}

interface Props {
    acta: ActaVista;
}

const fecha = (valor: string | null): string =>
    valor
        ? new Intl.DateTimeFormat('es-AR').format(new Date(`${valor}T12:00:00`))
        : '—';

export default function ActaVista({ acta }: Props) {
    return (
        <>
            <Head title={`Ver ${acta.etiqueta}`} />
            <main className="admin-page-shell min-h-screen bg-background px-4 py-6 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-5xl space-y-5">
                    <Link
                        href={acta.legajo?.url ?? actasRoutes.pendientes.url()}
                        className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        Volver
                    </Link>

                    <article className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                        <header className="border-b border-border px-5 py-5 sm:px-6">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div className="flex gap-3">
                                    <div className="grid size-10 shrink-0 place-items-center rounded-xl bg-secondary text-primary">
                                        <FileText className="size-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.12em] text-primary uppercase">
                                            {acta.etiqueta}
                                        </p>
                                        <h1 className="mt-1 text-2xl font-semibold tracking-tight text-foreground">
                                            {acta.titulo}
                                        </h1>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Fecha: {fecha(acta.fecha)}
                                            {acta.legajo &&
                                                ` · Legajo ${acta.legajo.numero_legajo}`}
                                            {acta.creado_por &&
                                                ` · Cargada por ${acta.creado_por}`}
                                        </p>
                                    </div>
                                </div>
                                <DescargarActaMenu
                                    wordUrl={acta.descarga_url}
                                    pdfUrl={acta.descarga_pdf_url}
                                    etiqueta={acta.etiqueta}
                                    variant="outline"
                                />
                            </div>
                            <p className="mt-4 text-xs font-medium text-muted-foreground">
                                {acta.requiere_firma
                                    ? acta.firmado
                                        ? 'Acta firmada digitalmente.'
                                        : 'Acta sin todas las firmas requeridas.'
                                    : 'Acta sin firma digital requerida.'}
                            </p>
                        </header>

                        <ActaContenido acta={acta} />
                    </article>
                </div>
            </main>
        </>
    );
}
