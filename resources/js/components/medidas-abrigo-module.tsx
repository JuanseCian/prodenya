import { useForm } from '@inertiajs/react';
import { Download, HeartHandshake, Plus, Upload, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import medidasAbrigoRoutes from '@/routes/legajos/medidas-abrigo';

type Ubicacion = 'familia_ampliada' | 'institucion';
type TipoActa = 'per' | 'solicitud_vacante' | 'informe';

interface Acta {
    id: number;
    tipo: TipoActa;
    tipo_label: string;
    nombre: string;
    tamanio: number;
    descarga_url: string;
}

export interface MedidaAbrigoResumen {
    id: number;
    ubicacion: Ubicacion;
    ubicacion_label: string;
    duracion_dias: number;
    fecha_inicio: string;
    fecha_fin: string;
    estado: string;
    dias_restantes: number;
    observaciones: string | null;
    actas: Acta[];
}

interface ActaForm {
    tipo: TipoActa;
    archivo: File | null;
}

interface FormData {
    ubicacion: Ubicacion;
    duracion_dias: number;
    fecha_inicio: string;
    observaciones: string;
    actas: ActaForm[];
}

interface Props {
    legajoId: number;
    medidas: MedidaAbrigoResumen[];
    puedeGestionar: boolean;
}

const tipoActaLabels: Record<TipoActa, string> = {
    per: 'PER',
    solicitud_vacante: 'Solicitud de vacante',
    informe: 'Informe',
};

const ubicacionLabels: Record<Ubicacion, string> = {
    familia_ampliada: 'Familia ampliada',
    institucion: 'Institución',
};

const inputClass =
    'mt-2 block h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat('es-AR').format(new Date(`${value}T12:00:00`));

const formatSize = (bytes: number): string =>
    bytes < 1024 * 1024
        ? `${Math.max(1, Math.round(bytes / 1024))} KB`
        : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;

const calculateEndDate = (start: string, duration: number): string => {
    if (!start) {
        return 'Seleccioná la fecha de inicio';
    }

    const date = new Date(`${start}T12:00:00`);
    date.setDate(date.getDate() + duration - 1);

    return new Intl.DateTimeFormat('es-AR').format(date);
};

export default function MedidasAbrigoModule({
    legajoId,
    medidas,
    puedeGestionar,
}: Props) {
    const [open, setOpen] = useState(false);
    const form = useForm<FormData>({
        ubicacion: 'familia_ampliada',
        duracion_dias: 90,
        fecha_inicio: new Date().toISOString().slice(0, 10),
        observaciones: '',
        actas: [{ tipo: 'per', archivo: null }],
    });

    const tiposDisponibles = useMemo(() => {
        const seleccionados = new Set(form.data.actas.map((acta) => acta.tipo));

        return (Object.keys(tipoActaLabels) as TipoActa[]).filter(
            (tipo) => !seleccionados.has(tipo),
        );
    }, [form.data.actas]);

    const addActa = () => {
        const tipo = tiposDisponibles[0];

        if (!tipo) {
            return;
        }

        form.setData('actas', [...form.data.actas, { tipo, archivo: null }]);
    };

    const removeActa = (index: number) => {
        form.setData(
            'actas',
            form.data.actas.filter((_, actaIndex) => actaIndex !== index),
        );
    };

    const updateActa = (index: number, changes: Partial<ActaForm>) => {
        form.setData(
            'actas',
            form.data.actas.map((acta, actaIndex) =>
                actaIndex === index ? { ...acta, ...changes } : acta,
            ),
        );
    };

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(medidasAbrigoRoutes.store.url(legajoId), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
            onError: () => setOpen(true),
        });
    };

    return (
        <Card className="rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                        <HeartHandshake size={18} className="text-[#6B8F71]" />
                        <CardTitle className="text-base font-semibold text-[#2C4A5E]">
                            Medidas de abrigo
                        </CardTitle>
                    </div>
                    {puedeGestionar && (
                        <Button
                            type="button"
                            onClick={() => setOpen(true)}
                            className="bg-[#2C4A5E] hover:bg-[#173247]"
                        >
                            <Plus className="size-4" />
                            Registrar medida
                        </Button>
                    )}
                </div>
                <p className="mt-1 text-sm text-[#2B2F36]/60">
                    Registro de medidas de protección y documentación
                    respaldatoria.
                </p>
            </CardHeader>

            <CardContent className="space-y-4 px-5 py-5">
                {medidas.length > 0 ? (
                    medidas.map((medida) => (
                        <article
                            key={medida.id}
                            className="rounded-xl border border-[#DCE3DF] bg-[#FBFCFB] p-4"
                        >
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge
                                            className={
                                                medida.estado === 'Vigente'
                                                    ? 'border-0 bg-[#EAF6EE] text-[#397461]'
                                                    : 'border-0 bg-[#FCF1F3] text-[#9B5964]'
                                            }
                                        >
                                            {medida.estado}
                                        </Badge>
                                        <span className="text-sm font-semibold text-[#173247]">
                                            {medida.ubicacion_label}
                                        </span>
                                    </div>
                                    <p className="mt-2 text-sm text-[#2B2F36]">
                                        Del {formatDate(medida.fecha_inicio)} al{' '}
                                        {formatDate(medida.fecha_fin)} ·{' '}
                                        {medida.duracion_dias} días
                                    </p>
                                    {medida.estado === 'Vigente' && (
                                        <p className="mt-1 text-xs text-[#6B8F71]">
                                            {medida.dias_restantes} días
                                            restantes
                                        </p>
                                    )}
                                </div>
                                <Badge className="w-fit border-0 bg-[#F2F7FB] text-[#35627A]">
                                    {medida.actas.length} acta(s)
                                </Badge>
                            </div>

                            {medida.observaciones && (
                                <p className="mt-3 text-sm leading-6 whitespace-pre-line text-[#2B2F36]/75">
                                    {medida.observaciones}
                                </p>
                            )}

                            <div className="mt-4 flex flex-wrap gap-2">
                                {medida.actas.map((acta) => (
                                    <a
                                        key={acta.id}
                                        href={acta.descarga_url}
                                        className="inline-flex items-center gap-2 rounded-lg border border-[#DCE3DF] bg-white px-3 py-2 text-xs font-semibold text-[#2C4A5E] transition hover:bg-[#F4F6F5]"
                                    >
                                        <Download className="size-3.5" />
                                        {acta.tipo_label}
                                        <span className="font-normal text-[#697586]">
                                            {formatSize(acta.tamanio)}
                                        </span>
                                    </a>
                                ))}
                            </div>
                        </article>
                    ))
                ) : (
                    <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-5 text-sm text-[#697586]">
                        Todavía no hay medidas de abrigo registradas.
                    </p>
                )}
            </CardContent>

            {puedeGestionar && (
                <div
                    className={`fixed inset-0 z-50 ${open ? 'flex' : 'hidden'} items-center justify-center bg-[#173247]/35 p-4`}
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="medida-abrigo-title"
                >
                    <div className="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-[#FBFCFB] p-5 shadow-2xl sm:p-6">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <h2
                                    id="medida-abrigo-title"
                                    className="text-lg font-semibold text-[#2C4A5E]"
                                >
                                    Registrar medida de abrigo
                                </h2>
                                <p className="mt-1 text-sm text-[#697586]">
                                    La medida se registrará automáticamente como
                                    intervención.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="rounded-lg p-2 text-[#697586] hover:bg-[#F1F5F3]"
                                aria-label="Cerrar formulario"
                            >
                                <X className="size-5" />
                            </button>
                        </div>

                        <form onSubmit={guardar} className="mt-5 space-y-4">
                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Lugar de cumplimiento
                                    <select
                                        value={form.data.ubicacion}
                                        onChange={(event) =>
                                            form.setData(
                                                'ubicacion',
                                                event.target.value as Ubicacion,
                                            )
                                        }
                                        className={inputClass}
                                    >
                                        {Object.entries(ubicacionLabels).map(
                                            ([value, label]) => (
                                                <option
                                                    key={value}
                                                    value={value}
                                                >
                                                    {label}
                                                </option>
                                            ),
                                        )}
                                    </select>
                                </label>
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Duración
                                    <select
                                        value={form.data.duracion_dias}
                                        onChange={(event) =>
                                            form.setData(
                                                'duracion_dias',
                                                Number(event.target.value),
                                            )
                                        }
                                        className={inputClass}
                                    >
                                        <option value={90}>90 días</option>
                                        <option value={180}>180 días</option>
                                    </select>
                                </label>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                    Fecha de inicio
                                    <input
                                        required
                                        type="date"
                                        value={form.data.fecha_inicio}
                                        onChange={(event) =>
                                            form.setData(
                                                'fecha_inicio',
                                                event.target.value,
                                            )
                                        }
                                        className={inputClass}
                                    />
                                </label>
                                <div className="rounded-lg border border-[#DCE3DF] bg-white px-3 py-2.5 text-sm text-[#2B2F36]">
                                    <p className="text-xs font-semibold text-[#697586]">
                                        Fecha de finalización calculada
                                    </p>
                                    <p className="mt-1 font-semibold text-[#173247]">
                                        {calculateEndDate(
                                            form.data.fecha_inicio,
                                            form.data.duracion_dias,
                                        )}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <h3 className="text-sm font-semibold text-[#2C4A5E]">
                                            Actas
                                        </h3>
                                        <p className="mt-1 text-xs text-[#697586]">
                                            Podés cargar una, dos o las tres
                                            actas.
                                        </p>
                                    </div>
                                    {tiposDisponibles.length > 0 && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={addActa}
                                        >
                                            <Plus className="size-4" />
                                            Agregar acta
                                        </Button>
                                    )}
                                </div>

                                <div className="mt-3 space-y-3">
                                    {form.data.actas.map((acta, index) => (
                                        <div
                                            key={`${acta.tipo}-${index}`}
                                            className="grid gap-3 rounded-lg border border-[#DCE3DF] bg-white p-3 sm:grid-cols-[190px_1fr_auto] sm:items-end"
                                        >
                                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                                Tipo de acta
                                                <select
                                                    value={acta.tipo}
                                                    onChange={(event) =>
                                                        updateActa(index, {
                                                            tipo: event.target
                                                                .value as TipoActa,
                                                        })
                                                    }
                                                    className={inputClass}
                                                >
                                                    {(
                                                        Object.keys(
                                                            tipoActaLabels,
                                                        ) as TipoActa[]
                                                    )
                                                        .filter(
                                                            (tipo) =>
                                                                tipo ===
                                                                    acta.tipo ||
                                                                !form.data.actas.some(
                                                                    (
                                                                        item,
                                                                        itemIndex,
                                                                    ) =>
                                                                        itemIndex !==
                                                                            index &&
                                                                        item.tipo ===
                                                                            tipo,
                                                                ),
                                                        )
                                                        .map((tipo) => (
                                                            <option
                                                                key={tipo}
                                                                value={tipo}
                                                            >
                                                                {
                                                                    tipoActaLabels[
                                                                        tipo
                                                                    ]
                                                                }
                                                            </option>
                                                        ))}
                                                </select>
                                            </label>
                                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                                Archivo
                                                <input
                                                    required
                                                    type="file"
                                                    accept="application/pdf,image/jpeg,image/png"
                                                    onChange={(event) =>
                                                        updateActa(index, {
                                                            archivo:
                                                                event.target
                                                                    .files?.[0] ??
                                                                null,
                                                        })
                                                    }
                                                    className={`${inputClass} py-2 text-xs`}
                                                />
                                            </label>
                                            {form.data.actas.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        removeActa(index)
                                                    }
                                                    className="rounded-lg p-2 text-[#9B5964] hover:bg-[#FCF1F3]"
                                                    aria-label="Quitar acta"
                                                >
                                                    <X className="size-4" />
                                                </button>
                                            )}
                                            {form.errors[
                                                `actas.${index}.archivo`
                                            ] && (
                                                <span className="text-xs text-[#9B5964] sm:col-span-3">
                                                    {
                                                        form.errors[
                                                            `actas.${index}.archivo`
                                                        ]
                                                    }
                                                </span>
                                            )}
                                        </div>
                                    ))}
                                </div>
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

                            {(form.errors as Record<string, string>)
                                .medida_abrigo && (
                                <p className="text-sm text-[#9B5964]">
                                    {
                                        (form.errors as Record<string, string>)
                                            .medida_abrigo
                                    }
                                </p>
                            )}
                            <div className="flex justify-end gap-2 border-t border-[#E4E8E5] pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setOpen(false)}
                                >
                                    Cancelar
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                    className="bg-[#2C4A5E] hover:bg-[#173247]"
                                >
                                    <Upload className="size-4" />
                                    {form.processing
                                        ? 'Guardando...'
                                        : 'Guardar medida'}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </Card>
    );
}
