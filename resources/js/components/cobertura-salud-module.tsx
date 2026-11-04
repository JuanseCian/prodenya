import { useForm } from '@inertiajs/react';
import {
    Download,
    HeartPulse,
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
import coberturaRoutes from '@/routes/legajos/cobertura';

type CoberturaTipo =
    | 'municipal'
    | 'obra_social'
    | 'prepaga'
    | 'pami_publica'
    | 'otra'
    | 'sin_cobertura';

type CoberturaOrigen =
    | 'municipal'
    | 'relacion_dependencia'
    | 'monotributo'
    | 'jubilacion_pension'
    | 'familiar_a_cargo'
    | 'voluntaria_particular'
    | 'otro'
    | 'no_aplica';

interface CoberturaArchivo {
    id: number;
    tipo: 'credencial' | 'constancia';
    nombre: string;
    tamanio: number;
    descarga_url: string;
}

export interface CoberturaResumen {
    id: number;
    tipo: CoberturaTipo;
    origen: CoberturaOrigen;
    programa: string | null;
    entidad: string | null;
    plan: string | null;
    numero_afiliado: string | null;
    persona_es_titular: boolean;
    titular_nombre: string | null;
    titular_dni: string | null;
    titular_cuil: string | null;
    parentesco_titular: string | null;
    fecha_desde: string | null;
    fecha_hasta: string | null;
    observaciones: string | null;
    estado: string;
    archivos: CoberturaArchivo[];
}

interface Props {
    legajoId: number;
    cobertura: CoberturaResumen | null;
    historial: CoberturaResumen[];
    puedeGestionar: boolean;
}

interface FormData {
    tipo: CoberturaTipo;
    origen: CoberturaOrigen;
    programa: string;
    entidad: string;
    plan: string;
    numero_afiliado: string;
    persona_es_titular: boolean;
    titular_nombre: string;
    titular_dni: string;
    titular_cuil: string;
    parentesco_titular: string;
    fecha_desde: string;
    fecha_hasta: string;
    observaciones: string;
    credencial: File | null;
    constancias: File[];
}

const tipoLabels: Record<CoberturaTipo, string> = {
    municipal: 'Obra Social Municipal de San Nicolás',
    obra_social: 'Obra social',
    prepaga: 'Medicina prepaga',
    pami_publica: 'PAMI / cobertura pública',
    otra: 'Otra cobertura',
    sin_cobertura: 'Sin cobertura',
};

const origenLabels: Record<CoberturaOrigen, string> = {
    municipal: 'Programa municipal',
    relacion_dependencia: 'Relación de dependencia',
    monotributo: 'Monotributo',
    jubilacion_pension: 'Jubilación o pensión',
    familiar_a_cargo: 'Familiar a cargo',
    voluntaria_particular: 'Voluntaria o particular',
    otro: 'Otro origen',
    no_aplica: 'No aplica',
};

const estadoClass: Record<string, string> = {
    Vigente: 'bg-[#EAF6EE] text-[#397461]',
    Programada: 'bg-[#F2F7FB] text-[#35627A]',
    Finalizada: 'bg-[#FCF1F3] text-[#9B5964]',
    'Sin cobertura': 'bg-[#F1F5F3] text-[#60756B]',
};

const inputClass =
    'mt-2 block h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

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

function FieldError({ message }: { message?: string }) {
    return message ? (
        <span className="text-xs font-normal text-[#9B5964]">{message}</span>
    ) : null;
}

export default function CoberturaSaludModule({
    legajoId,
    cobertura,
    historial,
    puedeGestionar,
}: Props) {
    const [open, setOpen] = useState(false);
    const form = useForm<FormData>({
        tipo: cobertura?.tipo ?? 'sin_cobertura',
        origen: cobertura?.origen ?? 'no_aplica',
        programa: cobertura?.programa ?? '',
        entidad: cobertura?.entidad ?? '',
        plan: cobertura?.plan ?? '',
        numero_afiliado: cobertura?.numero_afiliado ?? '',
        persona_es_titular: cobertura?.persona_es_titular ?? true,
        titular_nombre: cobertura?.titular_nombre ?? '',
        titular_dni: cobertura?.titular_dni ?? '',
        titular_cuil: cobertura?.titular_cuil ?? '',
        parentesco_titular: cobertura?.parentesco_titular ?? '',
        fecha_desde: cobertura?.fecha_desde ?? '',
        fecha_hasta: cobertura?.fecha_hasta ?? '',
        observaciones: cobertura?.observaciones ?? '',
        credencial: null,
        constancias: [],
    });

    useEffect(() => {
        form.setData('tipo', cobertura?.tipo ?? 'sin_cobertura');
        form.setData('origen', cobertura?.origen ?? 'no_aplica');
        form.setData('programa', cobertura?.programa ?? '');
        form.setData('entidad', cobertura?.entidad ?? '');
        form.setData('plan', cobertura?.plan ?? '');
        form.setData('numero_afiliado', cobertura?.numero_afiliado ?? '');
        form.setData('persona_es_titular', cobertura?.persona_es_titular ?? true);
        form.setData('titular_nombre', cobertura?.titular_nombre ?? '');
        form.setData('titular_dni', cobertura?.titular_dni ?? '');
        form.setData('titular_cuil', cobertura?.titular_cuil ?? '');
        form.setData('parentesco_titular', cobertura?.parentesco_titular ?? '');
        form.setData('fecha_desde', cobertura?.fecha_desde ?? '');
        form.setData('fecha_hasta', cobertura?.fecha_hasta ?? '');
        form.setData('observaciones', cobertura?.observaciones ?? '');
        // Each replacement creates a new historical coverage id.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [cobertura?.id]);

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(coberturaRoutes.store.url(legajoId), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('credencial', 'constancias');
                setOpen(true);
            },
            onError: () => setOpen(true),
        });
    };

    const cambiarTipo = (tipo: CoberturaTipo) => {
        form.setData('tipo', tipo);

        if (tipo === 'municipal') {
            form.setData('origen', 'municipal');
        } else if (tipo === 'sin_cobertura') {
            form.setData('origen', 'no_aplica');
        } else if (form.data.origen === 'municipal' || form.data.origen === 'no_aplica') {
            form.setData('origen', 'relacion_dependencia');
        }
    };

    const tieneCobertura = form.data.tipo !== 'sin_cobertura';

    return (
        <>
            <Card className="rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                            <HeartPulse size={17} className="text-[#6B8F71]" />
                            <CardTitle className="text-base font-semibold text-[#2C4A5E]">
                                Cobertura de salud
                            </CardTitle>
                        </div>
                        {cobertura && (
                            <Badge
                                className={`border-0 ${estadoClass[cobertura.estado] ?? 'bg-[#F1F5F3] text-[#60756B]'}`}
                            >
                                {cobertura.estado}
                            </Badge>
                        )}
                    </div>
                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                        Obra social, prepaga, cobertura pública o programa municipal.
                    </p>
                </CardHeader>
                <CardContent className="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                    {cobertura ? (
                        <dl className="grid gap-3 sm:grid-cols-3">
                            <div>
                                <dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                    Tipo
                                </dt>
                                <dd className="mt-1 text-sm font-semibold text-[#173247]">
                                    {tipoLabels[cobertura.tipo]}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                    Entidad / programa
                                </dt>
                                <dd className="mt-1 text-sm text-[#2B2F36]">
                                    {cobertura.programa ?? cobertura.entidad ?? 'Sin informar'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                    Desde
                                </dt>
                                <dd className="mt-1 text-sm text-[#2B2F36]">
                                    {formatDate(cobertura.fecha_desde)}
                                </dd>
                            </div>
                        </dl>
                    ) : (
                        <p className="text-sm text-[#697586]">
                            Todavía no hay información de cobertura cargada.
                        </p>
                    )}
                    <Button
                        type="button"
                        onClick={() => setOpen(true)}
                        className="shrink-0 bg-[#2C4A5E] hover:bg-[#173247]"
                    >
                        <HeartPulse className="size-4" />
                        Ver cobertura
                    </Button>
                </CardContent>
            </Card>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-3xl">
                    <DialogHeader>
                        <div className="flex items-center gap-2">
                            <HeartPulse size={18} className="text-[#6B8F71]" />
                            <DialogTitle className="text-base font-semibold text-[#2C4A5E]">
                                Cobertura de salud
                            </DialogTitle>
                        </div>
                        <DialogDescription className="text-sm text-[#2B2F36]/60">
                            Registrá la cobertura principal vigente y conservá sus cambios históricos.
                        </DialogDescription>
                    </DialogHeader>

                    {cobertura && (
                        <div className="space-y-4 px-1 py-2">
                            <div className="grid gap-4 rounded-xl border border-[#DCE3DF] bg-white p-4 sm:grid-cols-3">
                                <div>
                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">Tipo</p>
                                    <p className="mt-1 text-sm font-semibold text-[#173247]">{tipoLabels[cobertura.tipo]}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">Entidad / programa</p>
                                    <p className="mt-1 text-sm text-[#2B2F36]">{cobertura.programa ?? cobertura.entidad ?? 'Sin informar'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-bold tracking-wide text-[#697586] uppercase">Afiliado</p>
                                    <p className="mt-1 text-sm text-[#2B2F36]">{cobertura.numero_afiliado ?? 'Sin número informado'}</p>
                                </div>
                            </div>

                            <div className="grid gap-3 rounded-xl border border-[#E4E8E5] bg-white p-4 sm:grid-cols-2">
                                <p className="text-sm text-[#2B2F36]">Origen: <strong>{origenLabels[cobertura.origen]}</strong></p>
                                <p className="text-sm text-[#2B2F36]">Plan: <strong>{cobertura.plan ?? 'Sin plan informado'}</strong></p>
                                <p className="text-sm text-[#2B2F36]">Vigencia: <strong>{formatDate(cobertura.fecha_desde)} al {formatDate(cobertura.fecha_hasta)}</strong></p>
                                <p className="text-sm text-[#2B2F36]">Titular: <strong>{cobertura.persona_es_titular ? 'Destinatario' : cobertura.titular_nombre ?? 'Sin informar'}</strong></p>
                            </div>

                            {cobertura.archivos.length > 0 && (
                                <div>
                                    <h3 className="text-sm font-semibold text-[#2C4A5E]">Documentación actual</h3>
                                    <div className="mt-3 space-y-2">
                                        {cobertura.archivos.map((archivo) => (
                                            <div key={archivo.id} className="flex items-center justify-between gap-3 rounded-lg border border-[#E4E8E5] bg-white px-3 py-2.5">
                                                <span className="min-w-0 truncate text-sm text-[#2B2F36]">
                                                    {archivo.nombre}
                                                    <small className="ml-2 text-xs text-[#8A9692]">{formatSize(archivo.tamanio)}</small>
                                                </span>
                                                <a href={archivo.descarga_url} className="shrink-0 rounded-md p-2 text-[#2C4A5E] hover:bg-[#F4F6F5]" title={`Descargar ${archivo.nombre}`}>
                                                    <Download className="size-4" />
                                                </a>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {cobertura.observaciones && (
                                <p className="rounded-lg border border-[#E4E8E5] bg-white p-4 text-sm leading-6 whitespace-pre-line text-[#2B2F36]/75">{cobertura.observaciones}</p>
                            )}
                        </div>
                    )}

                    {historial.length > 0 && (
                        <div className="border-t border-[#E4E8E5] px-1 pt-5">
                            <h3 className="text-sm font-semibold text-[#2C4A5E]">Historial de coberturas</h3>
                            <div className="mt-3 space-y-2">
                                {historial.map((version) => (
                                    <div key={version.id} className="rounded-lg border border-[#E4E8E5] bg-white p-3">
                                        <div className="flex flex-wrap justify-between gap-2">
                                            <p className="text-sm font-semibold text-[#173247]">{tipoLabels[version.tipo]}</p>
                                            <span className="text-xs text-[#697586]">{formatDate(version.fecha_desde)} al {formatDate(version.fecha_hasta)}</span>
                                        </div>
                                        <p className="mt-1 text-xs text-[#697586]">{version.programa ?? version.entidad ?? 'Sin entidad'} · {version.archivos.length} archivo(s)</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {puedeGestionar && (
                        <form onSubmit={guardar} className="space-y-4 border-t border-[#E4E8E5] px-1 pt-5">
                            <div>
                                <h3 className="text-sm font-semibold text-[#2C4A5E]">{cobertura ? 'Reemplazar o actualizar cobertura' : 'Cargar cobertura'}</h3>
                                <p className="mt-1 text-xs text-[#697586]">Al guardar una nueva cobertura, la anterior queda en el historial.</p>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Tipo de cobertura
                                    <select value={form.data.tipo} onChange={(event) => cambiarTipo(event.target.value as CoberturaTipo)} className={inputClass}>
                                        {Object.entries(tipoLabels).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                                    </select>
                                    <FieldError message={form.errors.tipo} />
                                </label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Origen
                                    <select value={form.data.origen} onChange={(event) => form.setData('origen', event.target.value as CoberturaOrigen)} disabled={!tieneCobertura && form.data.tipo === 'sin_cobertura'} className={inputClass}>
                                        {Object.entries(origenLabels).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                                    </select>
                                    <FieldError message={form.errors.origen} />
                                </label>
                            </div>

                            {tieneCobertura && (
                                <>
                                    {form.data.tipo === 'municipal' ? (
                                        <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                            Programa municipal
                                            <Input required value={form.data.programa} onChange={(event) => form.setData('programa', event.target.value)} className={inputClass} placeholder="Ej. Obra Social Municipal" />
                                            <FieldError message={form.errors.programa} />
                                        </label>
                                    ) : (
                                        <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                            Entidad / obra social / prepaga
                                            <Input required value={form.data.entidad} onChange={(event) => form.setData('entidad', event.target.value)} className={inputClass} placeholder="Nombre de la entidad" />
                                            <FieldError message={form.errors.entidad} />
                                        </label>
                                    )}
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                            Plan
                                            <Input value={form.data.plan} onChange={(event) => form.setData('plan', event.target.value)} className={inputClass} placeholder="Nombre o código del plan" />
                                        </label>
                                        <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                            Número de afiliado
                                            <Input value={form.data.numero_afiliado} onChange={(event) => form.setData('numero_afiliado', event.target.value)} className={inputClass} />
                                        </label>
                                    </div>
                                    <label className="flex items-center gap-2 text-sm font-semibold text-[#2C4A5E]">
                                        <input type="checkbox" checked={form.data.persona_es_titular} onChange={(event) => form.setData('persona_es_titular', event.target.checked)} className="size-4 accent-[#2C4A5E]" />
                                        El destinatario es el titular
                                    </label>
                                    {!form.data.persona_es_titular && (
                                        <div className="grid gap-3 rounded-lg border border-[#E4E8E5] bg-white p-3 sm:grid-cols-2">
                                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Nombre del titular<Input required value={form.data.titular_nombre} onChange={(event) => form.setData('titular_nombre', event.target.value)} className={inputClass} /></label>
                                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Parentesco<Input required value={form.data.parentesco_titular} onChange={(event) => form.setData('parentesco_titular', event.target.value)} className={inputClass} placeholder="Madre, padre, tutor..." /></label>
                                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">DNI del titular<Input value={form.data.titular_dni} onChange={(event) => form.setData('titular_dni', event.target.value)} className={inputClass} /></label>
                                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">CUIL del titular<Input value={form.data.titular_cuil} onChange={(event) => form.setData('titular_cuil', event.target.value)} className={inputClass} /></label>
                                        </div>
                                    )}
                                </>
                            )}

                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Desde<Input required type="date" value={form.data.fecha_desde} onChange={(event) => form.setData('fecha_desde', event.target.value)} className={inputClass} /><FieldError message={form.errors.fecha_desde} /></label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Hasta (opcional)<Input type="date" value={form.data.fecha_hasta} onChange={(event) => form.setData('fecha_hasta', event.target.value)} className={inputClass} /><FieldError message={form.errors.fecha_hasta} /></label>
                            </div>
                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Observaciones<textarea value={form.data.observaciones} onChange={(event) => form.setData('observaciones', event.target.value)} rows={3} className={`${inputClass} h-auto py-2 text-sm font-normal`} /></label>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Credencial<Input type="file" accept="application/pdf,image/jpeg,image/png" onChange={(event) => form.setData('credencial', event.target.files?.[0] ?? null)} className={`${inputClass} py-2 text-xs`} /></label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">Constancias <span className="text-xs font-normal text-[#697586]">Podés seleccionar varias.</span><Input type="file" multiple accept="application/pdf,image/jpeg,image/png" onChange={(event) => form.setData('constancias', Array.from(event.target.files ?? []))} className={`${inputClass} py-2 text-xs`} /></label>
                            </div>
                            {form.progress && <progress value={form.progress.percentage} max="100" className="h-2 w-full accent-[#6B8F71]" />}
                            <div className="flex justify-end"><Button type="submit" disabled={form.processing} className="bg-[#2C4A5E] hover:bg-[#173247]"><Upload className="size-4" />{form.processing ? 'Guardando...' : 'Guardar cobertura'}</Button></div>
                        </form>
                    )}

                    {!cobertura && !puedeGestionar && (
                        <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-white px-4 py-5 text-sm text-[#697586]">No hay información de cobertura cargada.</p>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}
