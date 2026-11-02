import { Link, useForm } from '@inertiajs/react';
import {
    Download,
    ExternalLink,
    FileCheck2,
    ShieldCheck,
    Upload,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import legajosRoutes from '@/routes/legajos';
import cudRoutes from '@/routes/legajos/cud';

interface CudArchivo {
    id: number;
    tipo: 'frente' | 'dorso' | 'receta';
    nombre: string;
    mime: string;
    tamanio: number;
    descarga_url: string;
}

export interface CudResumen {
    id: number;
    numero_cud: string;
    fecha_vigencia: string | null;
    fecha_vencimiento: string | null;
    observaciones: string | null;
    estado: string;
    archivos: CudArchivo[];
}

interface Props {
    legajoId: number;
    cud: CudResumen | null;
    puedeGestionar: boolean;
}

interface FormData {
    numero_cud: string;
    fecha_vigencia: string;
    fecha_vencimiento: string;
    observaciones: string;
    frente: File | null;
    dorso: File | null;
    recetas: File[];
}

const estadoClass: Record<string, string> = {
    Vigente: 'bg-[#EAF6EE] text-[#397461]',
    'Próximo a vencer': 'bg-[#FBF5EA] text-[#9A6C2D]',
    Vencido: 'bg-[#FCF1F3] text-[#9B5964]',
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return 'Sin fecha';
    }

    return new Intl.DateTimeFormat('es-AR').format(
        new Date(`${value}T12:00:00`),
    );
};

const formatSize = (bytes: number): string => {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const etiquetaArchivo = (tipo: CudArchivo['tipo']): string =>
    tipo === 'receta' ? 'Recetas' : `CUD ${tipo}`;

export default function CudModule({ legajoId, cud, puedeGestionar }: Props) {
    const [open, setOpen] = useState(false);
    const form = useForm<FormData>({
        numero_cud: cud?.numero_cud ?? '',
        fecha_vigencia: cud?.fecha_vigencia ?? '',
        fecha_vencimiento: cud?.fecha_vencimiento ?? '',
        observaciones: cud?.observaciones ?? '',
        frente: null,
        dorso: null,
        recetas: [],
    });

    useEffect(() => {
        form.setData('numero_cud', cud?.numero_cud ?? '');
        form.setData('fecha_vigencia', cud?.fecha_vigencia ?? '');
        form.setData('fecha_vencimiento', cud?.fecha_vencimiento ?? '');
        form.setData('observaciones', cud?.observaciones ?? '');
        // The certificate id changes after every replacement.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [cud?.id]);

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(cudRoutes.store.url(legajoId), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('frente', 'dorso', 'recetas');
                setOpen(true);
            },
            onError: () => setOpen(true),
        });
    };

    const inputClass =
        'mt-2 block h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

    return (
        <>
            <Card className="rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                            <ShieldCheck size={17} className="text-[#6B8F71]" />
                            <CardTitle className="text-base font-semibold text-[#2C4A5E]">
                                Certificado Único de Discapacidad (CUD)
                            </CardTitle>
                        </div>
                        {cud && (
                            <Badge
                                className={`border-0 ${estadoClass[cud.estado] ?? 'bg-[#F1F5F3] text-[#60756B]'}`}
                            >
                                {cud.estado}
                            </Badge>
                        )}
                    </div>
                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                        Documentación vigente asociada al legajo.
                    </p>
                </CardHeader>
                <CardContent className="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                    {cud ? (
                        <dl className="grid gap-3 sm:grid-cols-3">
                            <div>
                                <dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                    Número CUD
                                </dt>
                                <dd className="mt-1 font-mono text-sm font-semibold text-[#173247]">
                                    {cud.numero_cud}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                    Vencimiento
                                </dt>
                                <dd className="mt-1 text-sm text-[#2B2F36]">
                                    {formatDate(cud.fecha_vencimiento)}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                    Archivos
                                </dt>
                                <dd className="mt-1 text-sm text-[#2B2F36]">
                                    {cud.archivos.length} adjunto(s)
                                </dd>
                            </div>
                        </dl>
                    ) : (
                        <p className="text-sm text-[#697586]">
                            Todavía no hay un CUD vigente cargado.
                        </p>
                    )}
                    <Button
                        type="button"
                        onClick={() => setOpen(true)}
                        className="shrink-0 bg-[#2C4A5E] hover:bg-[#173247]"
                    >
                        <FileCheck2 className="size-4" />
                        Ver documentación
                    </Button>
                </CardContent>
            </Card>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-2xl">
                    <DialogHeader>
                        <div className="flex items-center gap-2">
                            <ShieldCheck size={18} className="text-[#6B8F71]" />
                            <DialogTitle className="text-base font-semibold text-[#2C4A5E]">
                                Documentación CUD
                            </DialogTitle>
                        </div>
                        <DialogDescription className="text-sm text-[#2B2F36]/60">
                            Consultá el certificado vigente y sus archivos
                            asociados.
                        </DialogDescription>
                    </DialogHeader>

                    {cud ? (
                        <div className="space-y-5 px-1 py-2">
                            <div className="grid gap-4 rounded-xl border border-[#DCE3DF] bg-white p-4 sm:grid-cols-3">
                                <div>
                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                        Número CUD
                                    </p>
                                    <p className="mt-1 font-mono text-sm font-semibold text-[#173247]">
                                        {cud.numero_cud}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                        Vigencia
                                    </p>
                                    <p className="mt-1 text-sm text-[#2B2F36]">
                                        {formatDate(cud.fecha_vigencia)} al{' '}
                                        {formatDate(cud.fecha_vencimiento)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                        Estado
                                    </p>
                                    <Badge
                                        className={`mt-1 border-0 ${estadoClass[cud.estado] ?? 'bg-[#F1F5F3] text-[#60756B]'}`}
                                    >
                                        {cud.estado}
                                    </Badge>
                                </div>
                            </div>

                            <div>
                                <h3 className="text-sm font-semibold text-[#2C4A5E]">
                                    Archivos actuales
                                </h3>
                                <div className="mt-3 space-y-3">
                                    {cud.archivos.length > 0 ? (
                                        cud.archivos.map((archivo) => (
                                            <div
                                                key={archivo.id}
                                                className="flex items-center justify-between gap-3 rounded-lg border border-[#E4E8E5] bg-white px-3 py-2.5"
                                            >
                                                <div className="min-w-0">
                                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                                        {etiquetaArchivo(
                                                            archivo.tipo,
                                                        )}
                                                    </p>
                                                    <p className="truncate text-sm text-[#2B2F36]">
                                                        {archivo.nombre}
                                                        <span className="ml-2 text-xs text-[#8A9692]">
                                                            {formatSize(
                                                                archivo.tamanio,
                                                            )}
                                                        </span>
                                                    </p>
                                                </div>
                                                <a
                                                    href={archivo.descarga_url}
                                                    className="shrink-0 rounded-md p-2 text-[#2C4A5E] transition hover:bg-[#F4F6F5]"
                                                    title={`Descargar ${archivo.nombre}`}
                                                >
                                                    <Download className="size-4" />
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-white px-4 py-4 text-sm text-[#697586]">
                                            No hay archivos adjuntos al CUD
                                            vigente.
                                        </p>
                                    )}
                                </div>
                            </div>

                            {cud.observaciones && (
                                <div className="rounded-lg border border-[#E4E8E5] bg-white p-4">
                                    <h3 className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                        Observaciones
                                    </h3>
                                    <p className="mt-2 text-sm leading-6 whitespace-pre-line text-[#2B2F36]/75">
                                        {cud.observaciones}
                                    </p>
                                </div>
                            )}
                        </div>
                    ) : (
                        <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-white px-4 py-5 text-sm text-[#697586]">
                            Todavía no hay un CUD vigente cargado.
                        </p>
                    )}

                    {puedeGestionar && (
                        <form
                            onSubmit={guardar}
                            className="space-y-4 border-t border-[#E4E8E5] px-1 pt-5"
                        >
                            <div>
                                <h3 className="text-sm font-semibold text-[#2C4A5E]">
                                    {cud
                                        ? 'Reemplazar o actualizar CUD'
                                        : 'Cargar CUD'}
                                </h3>
                                <p className="mt-1 text-xs text-[#697586]">
                                    Los archivos reemplazados quedan conservados
                                    en el historial.
                                </p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-3">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Número CUD
                                    <Input
                                        required
                                        value={form.data.numero_cud}
                                        onChange={(event) =>
                                            form.setData(
                                                'numero_cud',
                                                event.target.value,
                                            )
                                        }
                                        className={inputClass}
                                        placeholder="Número tal como figura"
                                    />
                                    {form.errors.numero_cud && (
                                        <span className="text-xs font-normal text-[#9B5964]">
                                            {form.errors.numero_cud}
                                        </span>
                                    )}
                                </label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Fecha de vigencia
                                    <Input
                                        required
                                        type="date"
                                        value={form.data.fecha_vigencia}
                                        onChange={(event) =>
                                            form.setData(
                                                'fecha_vigencia',
                                                event.target.value,
                                            )
                                        }
                                        className={inputClass}
                                    />
                                    {form.errors.fecha_vigencia && (
                                        <span className="text-xs font-normal text-[#9B5964]">
                                            {form.errors.fecha_vigencia}
                                        </span>
                                    )}
                                </label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Fecha de vencimiento
                                    <Input
                                        required
                                        type="date"
                                        value={form.data.fecha_vencimiento}
                                        onChange={(event) =>
                                            form.setData(
                                                'fecha_vencimiento',
                                                event.target.value,
                                            )
                                        }
                                        className={inputClass}
                                    />
                                    {form.errors.fecha_vencimiento && (
                                        <span className="text-xs font-normal text-[#9B5964]">
                                            {form.errors.fecha_vencimiento}
                                        </span>
                                    )}
                                </label>
                            </div>
                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                Observaciones
                                <textarea
                                    value={form.data.observaciones}
                                    onChange={(event) =>
                                        form.setData(
                                            'observaciones',
                                            event.target.value,
                                        )
                                    }
                                    rows={3}
                                    className={`${inputClass} h-auto py-2 text-sm font-normal`}
                                />
                            </label>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Frente del CUD
                                    <Input
                                        type="file"
                                        accept="application/pdf,image/jpeg,image/png"
                                        onChange={(event) =>
                                            form.setData(
                                                'frente',
                                                event.target.files?.[0] ?? null,
                                            )
                                        }
                                        className={`${inputClass} py-2 text-xs`}
                                    />
                                </label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Dorso del CUD
                                    <Input
                                        type="file"
                                        accept="application/pdf,image/jpeg,image/png"
                                        onChange={(event) =>
                                            form.setData(
                                                'dorso',
                                                event.target.files?.[0] ?? null,
                                            )
                                        }
                                        className={`${inputClass} py-2 text-xs`}
                                    />
                                </label>
                            </div>
                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                Recetas{' '}
                                <span className="text-xs font-normal text-[#697586]">
                                    Opcionales, podés seleccionar varias.
                                </span>
                                <Input
                                    type="file"
                                    multiple
                                    accept="application/pdf,image/jpeg,image/png"
                                    onChange={(event) =>
                                        form.setData(
                                            'recetas',
                                            Array.from(
                                                event.target.files ?? [],
                                            ),
                                        )
                                    }
                                    className={`${inputClass} py-2 text-xs`}
                                />
                            </label>
                            {form.progress && (
                                <progress
                                    value={form.progress.percentage}
                                    max="100"
                                    className="h-2 w-full accent-[#6B8F71]"
                                />
                            )}
                            <div className="flex justify-end">
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                    className="bg-[#2C4A5E] hover:bg-[#173247]"
                                >
                                    <Upload className="size-4" />
                                    {form.processing
                                        ? 'Guardando...'
                                        : 'Guardar documentación'}
                                </Button>
                            </div>
                        </form>
                    )}

                    <div className="flex justify-end border-t border-[#E4E8E5] pt-4">
                        <Link
                            href={legajosRoutes.documentacion.url(legajoId)}
                            className="inline-flex items-center gap-2 rounded-md bg-[#2C4A5E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#173247]"
                        >
                            Abrir documentación completa
                            <ExternalLink className="size-4" />
                        </Link>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
