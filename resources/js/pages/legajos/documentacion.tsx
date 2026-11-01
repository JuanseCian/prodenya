import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarDays,
    Download,
    ShieldCheck,
    Trash2,
    Upload,
} from 'lucide-react';
import type { FormEvent } from 'react';

import ResumenErrores from '@/components/resumen-errores';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import legajosRoutes from '@/routes/legajos';
import cudRoutes from '@/routes/legajos/cud';

interface Archivo {
    id: number;
    tipo: 'frente' | 'dorso' | 'receta';
    nombre: string;
    mime: string;
    tamanio: number;
    descarga_url: string;
}

interface Cud {
    id: number;
    numero_cud: string;
    fecha_vigencia: string;
    fecha_vencimiento: string;
    observaciones: string | null;
    estado: string;
    activo: boolean;
    creado_en: string | null;
    archivos: Archivo[];
}

interface Props {
    legajo: {
        id: number;
        numero_legajo: string;
        persona: string | null;
    };
    cud: Cud | null;
    historial: Cud[];
    puedeGestionar: boolean;
    status?: string | null;
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

const inputClass =
    'mt-2 block h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

const formatDate = (value: string | null): string => {
    if (!value) {
        return 'Sin fecha';
    }

    return new Intl.DateTimeFormat('es-AR').format(new Date(`${value}T12:00:00`));
};

const formatSize = (bytes: number): string => {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const estadoClass: Record<string, string> = {
    Vigente: 'bg-[#EAF6EE] text-[#397461]',
    'Próximo a vencer': 'bg-[#FBF5EA] text-[#9A6C2D]',
    Vencido: 'bg-[#FCF1F3] text-[#9B5964]',
};

export default function Documentacion({
    legajo,
    cud,
    historial,
    puedeGestionar,
    status,
}: Props) {
    const errors = (usePage().props.errors ?? {}) as Record<string, string>;
    const form = useForm<FormData>({
        numero_cud: cud?.numero_cud ?? '',
        fecha_vigencia: cud?.fecha_vigencia ?? '',
        fecha_vencimiento: cud?.fecha_vencimiento ?? '',
        observaciones: cud?.observaciones ?? '',
        frente: null,
        dorso: null,
        recetas: [],
    });

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(cudRoutes.store.url(legajo.id), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const quitarArchivo = (archivo: Archivo) => {
        if (!window.confirm(`¿Quitar ${archivo.nombre} del CUD actual?`)) {
            return;
        }

        router.delete(
            cudRoutes.archivos.eliminar.url([legajo.id, archivo.id]),
            { preserveScroll: true },
        );
    };

    const archivos = (tipo: Archivo['tipo']): Archivo[] =>
        cud?.archivos.filter((archivo) => archivo.tipo === tipo) ?? [];

    return (
        <>
            <Head title={`Documentación · ${legajo.numero_legajo}`} />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <Link
                                href={legajosRoutes.show.url(legajo.id)}
                                className="inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                            >
                                <ArrowLeft className="size-4" />
                                Volver al legajo
                            </Link>
                            <p className="mt-4 text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                                Legajo {legajo.numero_legajo}
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                                Documentación
                            </h1>
                            <p className="mt-2 text-sm text-[#697586]">
                                {legajo.persona ?? 'Persona sin asociar'} · Certificado Único de Discapacidad
                            </p>
                        </div>
                        {cud && (
                            <span className={`rounded-full px-3 py-1.5 text-xs font-bold ${estadoClass[cud.estado] ?? 'bg-[#F1F5F3] text-[#60756B]'}`}>
                                {cud.estado}
                            </span>
                        )}
                    </div>

                    <ResumenErrores errors={errors} />
                    {status && <p className="mb-5 rounded-lg bg-[#EAF6EE] px-4 py-3 text-sm font-semibold text-[#397461]">{status}</p>}

                    <div className="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                <div className="flex items-center gap-2">
                                    <ShieldCheck className="size-5 text-[#6B8F71]" />
                                    <CardTitle className="text-base font-semibold text-[#2C4A5E]">CUD vigente</CardTitle>
                                </div>
                                <p className="mt-1 text-sm text-[#2B2F36]/60">Los archivos reemplazados quedan conservados en el historial.</p>
                            </CardHeader>
                            <CardContent className="px-5 py-5">
                                {cud ? (
                                    <dl className="grid gap-4 sm:grid-cols-2">
                                        <div><dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">Número CUD</dt><dd className="mt-1 font-mono text-sm font-semibold text-[#173247]">{cud.numero_cud}</dd></div>
                                        <div><dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">Vigencia</dt><dd className="mt-1 text-sm text-[#2B2F36]">{formatDate(cud.fecha_vigencia)} al {formatDate(cud.fecha_vencimiento)}</dd></div>
                                    </dl>
                                ) : (
                                    <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-5 text-sm text-[#697586]">Todavía no hay un CUD cargado.</p>
                                )}
                                {cud?.observaciones && <p className="mt-4 whitespace-pre-line text-sm leading-6 text-[#2B2F36]/75">{cud.observaciones}</p>}
                            </CardContent>
                        </Card>

                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                <div className="flex items-center gap-2"><CalendarDays className="size-5 text-[#6B8F71]" /><CardTitle className="text-base font-semibold text-[#2C4A5E]">Archivos actuales</CardTitle></div>
                            </CardHeader>
                            <CardContent className="space-y-3 px-5 py-5">
                                {(['frente', 'dorso', 'receta'] as const).map((tipo) => (
                                    <div key={tipo}>
                                        <p className="mb-1 text-xs font-bold tracking-wide text-[#697586] uppercase">{tipo === 'receta' ? 'Recetas' : `CUD ${tipo}`}</p>
                                        {archivos(tipo).length === 0 ? <p className="text-sm text-[#8A9692]">No adjuntado</p> : archivos(tipo).map((archivo) => (
                                            <div key={archivo.id} className="flex items-center justify-between gap-2 rounded-lg border border-[#E4E8E5] px-3 py-2 text-sm">
                                                <span className="min-w-0 truncate text-[#2B2F36]">{archivo.nombre}<small className="ml-2 text-xs text-[#8A9692]">{formatSize(archivo.tamanio)}</small></span>
                                                <span className="flex shrink-0 gap-1">
                                                    <a href={archivo.descarga_url} className="rounded-md p-1.5 text-[#2C4A5E] hover:bg-[#F4F6F5]" title="Descargar"><Download className="size-4" /></a>
                                                    {puedeGestionar && <button type="button" onClick={() => quitarArchivo(archivo)} className="rounded-md p-1.5 text-[#9B5964] hover:bg-[#FCF1F3]" title="Quitar"><Trash2 className="size-4" /></button>}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    {puedeGestionar && (
                        <Card className="mt-5 border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4"><CardTitle className="text-base font-semibold text-[#2C4A5E]">{cud ? 'Reemplazar o actualizar CUD' : 'Cargar CUD'}</CardTitle></CardHeader>
                            <CardContent className="px-5 py-5">
                                <form onSubmit={guardar} className="grid gap-4">
                                    <div className="grid gap-4 sm:grid-cols-3">
                                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Número CUD<Input required value={form.data.numero_cud} onChange={(event) => form.setData('numero_cud', event.target.value)} className={inputClass} placeholder="Número tal como figura" /></label>
                                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Fecha de vigencia<Input required type="date" value={form.data.fecha_vigencia} onChange={(event) => form.setData('fecha_vigencia', event.target.value)} className={inputClass} /></label>
                                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Fecha de vencimiento<Input required type="date" value={form.data.fecha_vencimiento} onChange={(event) => form.setData('fecha_vencimiento', event.target.value)} className={inputClass} /></label>
                                    </div>
                                    <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Observación<textarea value={form.data.observaciones} onChange={(event) => form.setData('observaciones', event.target.value)} rows={3} className={`${inputClass} h-auto py-2`} /></label>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Frente del CUD<Input type="file" accept="application/pdf,image/jpeg,image/png" onChange={(event) => form.setData('frente', event.target.files?.[0] ?? null)} className={`${inputClass} py-2`} /></label>
                                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Dorso del CUD<Input type="file" accept="application/pdf,image/jpeg,image/png" onChange={(event) => form.setData('dorso', event.target.files?.[0] ?? null)} className={`${inputClass} py-2`} /></label>
                                    </div>
                                    <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Recetas <span className="text-xs font-normal text-[#697586]">Opcionales, podés seleccionar varias.</span><Input type="file" multiple accept="application/pdf,image/jpeg,image/png" onChange={(event) => form.setData('recetas', Array.from(event.target.files ?? []))} className={`${inputClass} py-2`} /></label>
                                    <div className="flex justify-end"><Button type="submit" disabled={form.processing} className="bg-[#2C4A5E] hover:bg-[#173247]"><Upload className="size-4" />{form.processing ? 'Guardando…' : 'Guardar documentación'}</Button></div>
                                </form>
                            </CardContent>
                        </Card>
                    )}

                    <Card className="mt-5 border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4"><CardTitle className="text-base font-semibold text-[#2C4A5E]">Historial de certificados</CardTitle></CardHeader>
                        <CardContent className="px-5 py-5">
                            {historial.length === 0 ? <p className="text-sm text-[#697586]">No hay versiones anteriores.</p> : <div className="space-y-3">{historial.map((version) => <div key={version.id} className="rounded-lg border border-[#E4E8E5] p-4"><div className="flex flex-wrap justify-between gap-2"><p className="font-mono text-sm font-semibold text-[#173247]">{version.numero_cud}</p><span className="text-xs text-[#697586]">Cargado {formatDate(version.creado_en?.slice(0, 10) ?? null)}</span></div><p className="mt-1 text-xs text-[#697586]">Vigencia: {formatDate(version.fecha_vigencia)} al {formatDate(version.fecha_vencimiento)} · {version.archivos.length} archivo(s) conservado(s)</p></div>)}</div>}
                        </CardContent>
                    </Card>
                </div>
            </main>
        </>
    );
}
