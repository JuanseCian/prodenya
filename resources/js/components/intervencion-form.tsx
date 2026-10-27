import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Clock3,
    FileUp,
    Paperclip,
    Save,
    ShieldCheck,
    UserRound,
    UsersRound,
} from 'lucide-react';
import type { FormEvent } from 'react';

import FirmaDigital from '@/components/firma-digital';
import ResumenErrores from '@/components/resumen-errores';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';

interface LegajoResumen {
    id: number;
    persona_id: number;
    numero_legajo: string;
    persona: string | null;
}

interface Opcion {
    id: number;
    nombre: string;
}

interface Profesional {
    id: number;
    name: string;
    profesion: string | null;
}

interface Adjunto {
    id: number;
    nombre: string;
    tamanio: number | null;
}

interface LegajoRelacionado {
    id: number;
    numero_legajo: string;
    persona: string | null;
}

export interface IntervencionInicial {
    id: number;
    fecha_intervencion: string;
    hora_intervencion: string;
    tipo_intervencion_id: number;
    profesional_id: number;
    detalle: string;
    firma_nombre: string;
    firma_aclaracion: string;
    tiene_firma_responsable: boolean;
    firma_entrevistado_nombre: string | null;
    firma_entrevistado_aclaracion: string | null;
    tiene_firma_entrevistado: boolean;
}

interface Props {
    legajo: LegajoResumen;
    tipos: Opcion[];
    profesionales: Profesional[];
    profesionalPorDefecto?: number;
    hoy: string;
    horaAhora: string;
    intervencion?: IntervencionInicial;
    adjuntos?: Adjunto[];
    legajosRelacionados?: LegajoRelacionado[];
}

interface FormData {
    fecha_intervencion: string;
    hora_intervencion: string;
    tipo_intervencion_id: string;
    profesional_id: string;
    detalle: string;
    firma_nombre: string;
    firma_aclaracion: string;
    firma_responsable_dibujo: string;
    firma_responsable_archivo: File | null;
    firma_entrevistado_nombre: string;
    firma_entrevistado_aclaracion: string;
    firma_entrevistado_dibujo: string;
    firma_entrevistado_archivo: File | null;
    archivos: File[];
    legajos_destino_ids: string[];
}

const ETIQUETAS: Record<string, string> = {
    fecha_intervencion: 'Fecha',
    hora_intervencion: 'Horario',
    tipo_intervencion_id: 'Tipo',
    profesional_id: 'Profesional',
    detalle: 'Detalle',
    firma_nombre: 'Firma',
    firma_aclaracion: 'Aclaración',
    firma_responsable_digital: 'Firma digital del responsable',
    firma_entrevistado_digital: 'Firma digital del entrevistado',
};

const inputClass =
    'mt-2 block h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

export default function IntervencionForm({
    legajo,
    tipos,
    profesionales,
    profesionalPorDefecto,
    hoy,
    horaAhora,
    intervencion,
    adjuntos = [],
    legajosRelacionados = [],
}: Props) {
    const editando = Boolean(intervencion);
    const { data, setData, post, put, processing, errors } = useForm<FormData>({
        fecha_intervencion: intervencion?.fecha_intervencion ?? hoy,
        hora_intervencion: intervencion?.hora_intervencion ?? horaAhora,
        tipo_intervencion_id: intervencion
            ? String(intervencion.tipo_intervencion_id)
            : '',
        profesional_id: intervencion
            ? String(intervencion.profesional_id)
            : String(profesionalPorDefecto ?? ''),
        detalle: intervencion?.detalle ?? '',
        firma_nombre: intervencion?.firma_nombre ?? '',
        firma_aclaracion: intervencion?.firma_aclaracion ?? '',
        firma_responsable_dibujo: '',
        firma_responsable_archivo: null,
        firma_entrevistado_nombre:
            intervencion?.firma_entrevistado_nombre ?? '',
        firma_entrevistado_aclaracion:
            intervencion?.firma_entrevistado_aclaracion ?? '',
        firma_entrevistado_dibujo: '',
        firma_entrevistado_archivo: null,
        archivos: [],
        legajos_destino_ids: [],
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const options = {
            forceFormData: true,
            preserveScroll: true,
        } as const;

        if (intervencion) {
            put(
                `/legajos/${legajo.id}/intervenciones/${intervencion.id}`,
                options,
            );

            return;
        }

        post(`/legajos/${legajo.id}/intervenciones`, options);
    };

    return (
        <>
            <Head title={`${editando ? 'Editar' : 'Nueva'} intervención`} />

            <main className="min-h-screen bg-[#F4F6F5] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <Link
                                href={`/legajos/${legajo.persona_id}`}
                                className="mb-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] transition hover:text-[#173247] hover:underline"
                            >
                                <ArrowLeft size={15} />
                                Volver al legajo
                            </Link>
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="rounded-full bg-[#DDEBE1] px-2.5 py-1 text-[0.68rem] font-bold tracking-[0.12em] text-[#41634A] uppercase">
                                    Registro del legajo
                                </span>
                                <span className="font-mono text-xs font-semibold text-[#697586]">
                                    {legajo.numero_legajo}
                                </span>
                            </div>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                                {editando
                                    ? 'Editar intervención'
                                    : 'Nueva intervención'}
                            </h1>
                            <p className="mt-2 flex items-center gap-1.5 text-sm text-[#2B2F36]/65">
                                <UserRound size={15} />
                                {legajo.persona ?? 'Persona sin asociar'}
                            </p>
                        </div>
                        <div className="hidden rounded-xl border border-[#DCE3DF] bg-white px-4 py-3 text-right shadow-sm sm:block">
                            <p className="text-[0.65rem] font-bold tracking-[0.12em] text-[#6B8F71] uppercase">
                                Estado del registro
                            </p>
                            <p className="mt-1 text-sm font-semibold text-[#2C4A5E]">
                                {editando ? 'En edición' : 'Borrador nuevo'}
                            </p>
                        </div>
                    </div>

                    <ResumenErrores
                        errors={errors as Record<string, string>}
                        etiquetas={ETIQUETAS}
                        floating
                    />

                    <form onSubmit={submit} className="space-y-5">
                        <div className="grid items-start gap-5 lg:grid-cols-[minmax(0,1.65fr)_minmax(270px,0.75fr)]">
                            <div className="space-y-5">
                                <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_18px_45px_rgba(23,50,71,0.07)]">
                                    <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:px-6">
                                        <div className="flex items-start justify-between gap-4">
                                            <div>
                                                <CardTitle className="text-base text-[#2C4A5E]">
                                                    Datos de la intervención
                                                </CardTitle>
                                                <p className="mt-1 text-xs text-[#697586]">
                                                    Ubicá la actuación en el
                                                    historial del legajo.
                                                </p>
                                            </div>
                                            <div className="rounded-lg bg-[#EAF3ED] p-2 text-[#267148]">
                                                <CheckCircle2 size={18} />
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="px-5 py-5 sm:px-6">
                                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                            <div>
                                                <Label htmlFor="fecha_intervencion">
                                                    Fecha
                                                </Label>
                                                <div className="relative">
                                                    <CalendarDays
                                                        size={15}
                                                        className="pointer-events-none absolute top-3 left-3 text-[#697586]"
                                                    />
                                                    <input
                                                        id="fecha_intervencion"
                                                        type="date"
                                                        max={hoy}
                                                        value={data.fecha_intervencion}
                                                        onChange={(event) =>
                                                            setData(
                                                                'fecha_intervencion',
                                                                event.target.value,
                                                            )
                                                        }
                                                        className={`${inputClass} pl-9`}
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <Label htmlFor="hora_intervencion">
                                                    Horario
                                                </Label>
                                                <div className="relative">
                                                    <Clock3
                                                        size={15}
                                                        className="pointer-events-none absolute top-3 left-3 text-[#697586]"
                                                    />
                                                    <input
                                                        id="hora_intervencion"
                                                        type="time"
                                                        value={data.hora_intervencion}
                                                        onChange={(event) =>
                                                            setData(
                                                                'hora_intervencion',
                                                                event.target.value,
                                                            )
                                                        }
                                                        className={`${inputClass} pl-9`}
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <Label htmlFor="tipo_intervencion_id">
                                                    Tipo de registro
                                                </Label>
                                                <select
                                                    id="tipo_intervencion_id"
                                                    value={data.tipo_intervencion_id}
                                                    onChange={(event) =>
                                                        setData(
                                                            'tipo_intervencion_id',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className={inputClass}
                                                >
                                                    <option value="">
                                                        Seleccionar tipo…
                                                    </option>
                                                    {tipos.map((tipo) => (
                                                        <option
                                                            key={tipo.id}
                                                            value={tipo.id}
                                                        >
                                                            {tipo.nombre}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div>
                                                <Label htmlFor="profesional_id">
                                                    Profesional responsable
                                                </Label>
                                                <select
                                                    id="profesional_id"
                                                    value={data.profesional_id}
                                                    onChange={(event) =>
                                                        setData(
                                                            'profesional_id',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className={inputClass}
                                                >
                                                    <option value="">
                                                        Seleccionar profesional…
                                                    </option>
                                                    {profesionales.map(
                                                        (profesional) => (
                                                            <option
                                                                key={profesional.id}
                                                                value={profesional.id}
                                                            >
                                                                {profesional.name}{profesional.profesion ? ` · ${profesional.profesion}` : ''}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </div>
                                        </div>

                                        <div className="mt-5 border-t border-[#E4E8E5] pt-5">
                                            <div className="mb-2 flex items-center justify-between gap-3">
                                                <Label htmlFor="detalle">
                                                    Detalle / evolución
                                                </Label>
                                                <span className="text-xs text-[#697586]">
                                                    Campo obligatorio
                                                </span>
                                            </div>
                                            <textarea
                                                id="detalle"
                                                rows={8}
                                                value={data.detalle}
                                                onChange={(event) =>
                                                    setData(
                                                        'detalle',
                                                        event.target.value,
                                                    )
                                                }
                                                className="block min-h-44 w-full resize-y rounded-lg border border-[#D5D1C6] bg-white px-3.5 py-3 text-sm leading-6 text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                                placeholder="Describí la actuación, comunicación, acuerdo o situación registrada."
                                            />
                                        </div>
                                    </CardContent>
                                </Card>

                                {!editando && legajosRelacionados.length > 0 && (
                                    <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_18px_45px_rgba(23,50,71,0.07)]">
                                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:px-6">
                                            <div className="flex items-start gap-3">
                                                <UsersRound size={19} className="mt-0.5 text-[#6B8F71]" />
                                                <div>
                                                    <CardTitle className="text-base text-[#2C4A5E]">
                                                        Copiar en otros legajos
                                                    </CardTitle>
                                                    <p className="mt-1 text-xs leading-5 text-[#697586]">
                                                        Se creará una copia independiente, sin sus firmas.
                                                    </p>
                                                </div>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="px-5 py-5 sm:px-6">
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                {legajosRelacionados.map((relacionado) => {
                                                    const value = String(relacionado.id);
                                                    const checked = data.legajos_destino_ids.includes(value);

                                                    return (
                                                        <label key={relacionado.id} className="flex cursor-pointer items-center gap-3 rounded-lg border border-[#DCE3DF] bg-[#FBFCFB] px-3 py-3 transition hover:border-[#2C4A5E]/40">
                                                            <input
                                                                type="checkbox"
                                                                checked={checked}
                                                                onChange={() =>
                                                                    setData(
                                                                        'legajos_destino_ids',
                                                                        checked
                                                                            ? data.legajos_destino_ids.filter((id) => id !== value)
                                                                            : [...data.legajos_destino_ids, value],
                                                                    )
                                                                }
                                                                className="h-4 w-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                                            />
                                                            <span className="min-w-0">
                                                                <span className="block truncate text-sm font-semibold text-[#2C4A5E]">
                                                                    {relacionado.persona ?? 'Persona sin nombre'}
                                                                </span>
                                                                <span className="mt-0.5 block font-mono text-xs text-[#697586]">
                                                                    {relacionado.numero_legajo}
                                                                </span>
                                                            </span>
                                                        </label>
                                                    );
                                                })}
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}

                                <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_18px_45px_rgba(23,50,71,0.07)]">
                                    <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:px-6">
                                        <div className="flex items-start justify-between gap-4">
                                            <div>
                                                <CardTitle className="text-base text-[#2C4A5E]">
                                                    Firmas digitales
                                                </CardTitle>
                                                <p className="mt-1 text-xs text-[#697586]">
                                                    Dibujá con el dedo o cargá un
                                                    archivo de firma.
                                                </p>
                                            </div>
                                            <ShieldCheck
                                                size={20}
                                                className="text-[#6B8F71]"
                                            />
                                        </div>
                                    </CardHeader>
                                    <CardContent className="grid gap-5 px-5 py-5 sm:px-6 lg:grid-cols-2">
                                        <section className="rounded-xl border border-[#DCE3DF] bg-[#FBFCFB] p-4 sm:p-5">
                                            <div className="mb-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <h3 className="text-sm font-semibold text-[#2C4A5E]">
                                                        Responsable
                                                    </h3>
                                                    <p className="mt-1 text-xs leading-5 text-[#697586]">
                                                        Obligatoria para registrar
                                                        la intervención.
                                                    </p>
                                                </div>
                                                <span className="rounded-full bg-[#FCEBD7] px-2 py-1 text-[0.62rem] font-bold tracking-[0.08em] text-[#8A6420] uppercase">
                                                    Obligatoria
                                                </span>
                                            </div>
                                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                                <div>
                                                    <Label htmlFor="firma_nombre">
                                                        Nombre completo
                                                    </Label>
                                                    <input
                                                        id="firma_nombre"
                                                        value={data.firma_nombre}
                                                        onChange={(event) =>
                                                            setData(
                                                                'firma_nombre',
                                                                event.target.value,
                                                            )
                                                        }
                                                        className={inputClass}
                                                        placeholder="Nombre y apellido"
                                                    />
                                                </div>
                                                <div>
                                                    <Label htmlFor="firma_aclaracion">
                                                        Cargo / aclaración
                                                    </Label>
                                                    <input
                                                        id="firma_aclaracion"
                                                        value={data.firma_aclaracion}
                                                        onChange={(event) =>
                                                            setData(
                                                                'firma_aclaracion',
                                                                event.target.value,
                                                            )
                                                        }
                                                        className={inputClass}
                                                        placeholder="Cargo o función"
                                                    />
                                                </div>
                                            </div>
                                            <div className="mt-4">
                                                <FirmaDigital
                                                    label="Firmar con touch o mouse"
                                                    value={data.firma_responsable_dibujo}
                                                    onChange={(value) =>
                                                        setData(
                                                            'firma_responsable_dibujo',
                                                            value,
                                                        )
                                                    }
                                                />
                                            </div>
                                            <label className="mt-3 flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-[#DCE3DF] bg-white px-3 text-xs font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40 hover:bg-[#F5F8F6]">
                                                <FileUp size={15} />
                                                <span className="truncate">
                                                    {data.firma_responsable_archivo
                                                        ? data.firma_responsable_archivo
                                                              .name
                                                        : 'Subir archivo de firma'}
                                                </span>
                                                <input
                                                    type="file"
                                                    accept=".png,.jpg,.jpeg,.pdf"
                                                    className="sr-only"
                                                    onChange={(event) =>
                                                        setData(
                                                            'firma_responsable_archivo',
                                                            event.target.files?.[0] ??
                                                                null,
                                                        )
                                                    }
                                                />
                                            </label>
                                            {intervencion?.tiene_firma_responsable && (
                                                <a
                                                    href={`/legajos/${legajo.id}/intervenciones/${intervencion.id}/firma/responsable/descargar`}
                                                    className="mt-3 inline-block text-xs font-semibold text-[#267148] underline"
                                                >
                                                    Ver firma guardada
                                                </a>
                                            )}
                                        </section>

                                        <section className="rounded-xl border border-[#DCE3DF] bg-[#FBFCFB] p-4 sm:p-5">
                                            <div className="mb-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <h3 className="text-sm font-semibold text-[#2C4A5E]">
                                                        Entrevistado
                                                    </h3>
                                                    <p className="mt-1 text-xs leading-5 text-[#697586]">
                                                        Se completa cuando la
                                                        persona puede firmar.
                                                    </p>
                                                </div>
                                                <span className="rounded-full bg-[#EEF0ED] px-2 py-1 text-[0.62rem] font-bold tracking-[0.08em] text-[#697586] uppercase">
                                                    Opcional
                                                </span>
                                            </div>
                                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                                <div>
                                                    <Label htmlFor="firma_entrevistado_nombre">
                                                        Nombre completo
                                                    </Label>
                                                    <input
                                                        id="firma_entrevistado_nombre"
                                                        value={
                                                            data.firma_entrevistado_nombre
                                                        }
                                                        onChange={(event) =>
                                                            setData(
                                                                'firma_entrevistado_nombre',
                                                                event.target.value,
                                                            )
                                                        }
                                                        className={inputClass}
                                                        placeholder="Nombre y apellido"
                                                    />
                                                </div>
                                                <div>
                                                    <Label htmlFor="firma_entrevistado_aclaracion">
                                                        Vínculo / aclaración
                                                    </Label>
                                                    <input
                                                        id="firma_entrevistado_aclaracion"
                                                        value={
                                                            data.firma_entrevistado_aclaracion
                                                        }
                                                        onChange={(event) =>
                                                            setData(
                                                                'firma_entrevistado_aclaracion',
                                                                event.target.value,
                                                            )
                                                        }
                                                        className={inputClass}
                                                        placeholder="Entrevistado/a, familiar…"
                                                    />
                                                </div>
                                            </div>
                                            <div className="mt-4">
                                                <FirmaDigital
                                                    label="Firmar con touch o mouse"
                                                    value={
                                                        data.firma_entrevistado_dibujo
                                                    }
                                                    onChange={(value) =>
                                                        setData(
                                                            'firma_entrevistado_dibujo',
                                                            value,
                                                        )
                                                    }
                                                />
                                            </div>
                                            <label className="mt-3 flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-[#DCE3DF] bg-white px-3 text-xs font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40 hover:bg-[#F5F8F6]">
                                                <FileUp size={15} />
                                                <span className="truncate">
                                                    {data.firma_entrevistado_archivo
                                                        ? data.firma_entrevistado_archivo
                                                              .name
                                                        : 'Subir archivo de firma'}
                                                </span>
                                                <input
                                                    type="file"
                                                    accept=".png,.jpg,.jpeg,.pdf"
                                                    className="sr-only"
                                                    onChange={(event) =>
                                                        setData(
                                                            'firma_entrevistado_archivo',
                                                            event.target.files?.[0] ??
                                                                null,
                                                        )
                                                    }
                                                />
                                            </label>
                                            {intervencion?.tiene_firma_entrevistado && (
                                                <a
                                                    href={`/legajos/${legajo.id}/intervenciones/${intervencion.id}/firma/entrevistado/descargar`}
                                                    className="mt-3 inline-block text-xs font-semibold text-[#267148] underline"
                                                >
                                                    Ver firma guardada
                                                </a>
                                            )}
                                        </section>
                                    </CardContent>
                                </Card>
                            </div>

                            <aside className="space-y-5 lg:sticky lg:top-5">
                                <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_18px_45px_rgba(23,50,71,0.07)]">
                                    <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                        <div className="flex items-center gap-2">
                                            <Paperclip size={17} className="text-[#6B8F71]" />
                                            <CardTitle className="text-base text-[#2C4A5E]">
                                                Documentación
                                            </CardTitle>
                                        </div>
                                        <p className="mt-1 text-xs text-[#697586]">
                                            Actas, oficios, mails u otro respaldo.
                                        </p>
                                    </CardHeader>
                                    <CardContent className="px-5 py-5">
                                        <label className="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-[#B9C5BD] bg-[#FAFBFA] px-4 py-7 text-center transition hover:border-[#2C4A5E]/50 hover:bg-[#F4F6F5]">
                                            <span className="flex size-10 items-center justify-center rounded-full bg-[#EAF3ED] text-[#267148]">
                                                <FileUp size={19} />
                                            </span>
                                            <span className="mt-3 text-sm font-semibold text-[#2C4A5E]">
                                                {data.archivos.length > 0
                                                    ? `${data.archivos.length} archivo(s) seleccionado(s)`
                                                    : 'Agregar archivos'}
                                            </span>
                                            <span className="mt-1 text-xs leading-5 text-[#697586]">
                                                PDF, JPG o PNG
                                                <br />
                                                Hasta 10 MB por archivo
                                            </span>
                                            <input
                                                id="archivos"
                                                type="file"
                                                multiple
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                className="sr-only"
                                                onChange={(event) =>
                                                    setData(
                                                        'archivos',
                                                        Array.from(
                                                            event.target.files ?? [],
                                                        ),
                                                    )
                                                }
                                            />
                                        </label>
                                        {adjuntos.length > 0 && (
                                            <div className="mt-5 border-t border-[#E4E8E5] pt-4">
                                                <p className="mb-2 text-xs font-bold tracking-[0.1em] text-[#6B8F71] uppercase">
                                                    Ya asociados
                                                </p>
                                                <div className="space-y-2">
                                                    {adjuntos.map((adjunto) => (
                                                        <a
                                                            key={adjunto.id}
                                                            href={`/intervenciones/documentos/${adjunto.id}/descargar`}
                                                            className="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs text-[#2C4A5E] transition hover:bg-[#F4F6F5] hover:underline"
                                                        >
                                                            <Paperclip
                                                                size={13}
                                                                className="shrink-0 text-[#6B8F71]"
                                                            />
                                                            <span className="truncate">
                                                                {adjunto.nombre}
                                                            </span>
                                                        </a>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>

                                <div className="rounded-xl border border-[#CFE1D4] bg-[#EAF3ED] p-5">
                                    <div className="flex gap-3">
                                        <ShieldCheck
                                            size={19}
                                            className="mt-0.5 shrink-0 text-[#267148]"
                                        />
                                        <div>
                                            <p className="text-sm font-semibold text-[#41634A]">
                                                Registro seguro
                                            </p>
                                            <p className="mt-1 text-xs leading-5 text-[#41634A]/80">
                                                Se guardan el usuario, la fecha y
                                                cada firma asociada al registro.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </aside>
                        </div>

                        <div className="sticky bottom-4 z-10 flex flex-col-reverse gap-3 rounded-xl border border-[#DCE3DF] bg-white/95 p-3 shadow-[0_15px_40px_rgba(23,50,71,0.14)] backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:px-4">
                            <p className="hidden text-xs text-[#697586] sm:block">
                                Revisá los datos antes de confirmar el registro.
                            </p>
                            <div className="flex flex-col-reverse gap-2 sm:flex-row">
                                <Button asChild type="button" variant="outline">
                                    <Link href={`/legajos/${legajo.persona_id}`}>
                                        Cancelar
                                    </Link>
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-[#2C4A5E] text-white shadow-sm hover:bg-[#1F394B]"
                                >
                                    {processing ? (
                                        'Guardando…'
                                    ) : (
                                        <>
                                            <Save size={16} />
                                            {editando
                                                ? 'Guardar cambios'
                                                : 'Registrar intervención'}
                                        </>
                                    )}
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </>
    );
}
