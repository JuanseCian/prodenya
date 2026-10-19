import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { FormEventHandler } from 'react';

import { FONT_BODY, FONT_HEADING } from '@/components/detalle';
import ResumenErrores from '@/components/resumen-errores';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Opcion {
    id: number;
    nombre: string;
}

interface Profesional {
    id: number;
    nombre_usuario: string;
    email: string;
}

interface LegajoEditable {
    id: number;
    numero_legajo: string;
    persona: string | null;
    motivo_ingreso: string | null;
    motivo_ingreso_id: number | null;
    nivel_riesgo: string;
    origen_derivacion: string | null;
    profesional_id: number | null;
    observaciones: string | null;
    problematicas: number[];
}

interface Props {
    legajo: LegajoEditable;
    problematicas: Opcion[];
    motivosIngreso: Opcion[];
    profesionales: Profesional[];
}

const ETIQUETAS: Record<string, string> = {
    motivo_ingreso: 'Motivo de ingreso',
    problematicas: 'Problemáticas',
    nivel_riesgo: 'Nivel de riesgo',
    origen_derivacion: 'Origen de la derivación',
    profesional_id: 'Profesional asignado',
    observaciones: 'Observaciones',
};

const labelClass = 'text-sm font-semibold text-[#2B2F36]';
const errorClass = 'mt-1.5 text-xs font-medium text-[#A84635]';
const selectClass =
    'mt-2 block h-11 w-full rounded-md border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';
const textareaClass =
    'mt-2 block w-full resize-y rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

export default function LegajoEdit({
    legajo,
    problematicas,
    motivosIngreso,
    profesionales,
}: Props) {
    const { data, setData, put, processing, errors } = useForm({
        motivo_ingreso: legajo.motivo_ingreso ?? '',
        motivo_ingreso_id: legajo.motivo_ingreso_id
            ? String(legajo.motivo_ingreso_id)
            : '',
        nivel_riesgo: legajo.nivel_riesgo ?? '',
        origen_derivacion: legajo.origen_derivacion ?? '',
        profesional_id: legajo.profesional_id
            ? String(legajo.profesional_id)
            : '',
        observaciones: legajo.observaciones ?? '',
        problematicas: legajo.problematicas as number[],
    });

    const alternarProblematica = (id: number) => {
        setData(
            'problematicas',
            data.problematicas.includes(id)
                ? data.problematicas.filter((actual) => actual !== id)
                : [...data.problematicas, id],
        );
    };

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        put(`/legajos/${legajo.id}`, { preserveScroll: true });
    };

    return (
        <>
            <Head title={`Editar legajo ${legajo.numero_legajo}`} />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <Link
                    href={`/legajos/${legajo.id}`}
                    className="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                >
                    <ArrowLeft size={15} />
                    Volver al legajo
                </Link>

                <h2
                    className="text-2xl font-semibold tracking-tight text-[#173247] sm:text-3xl"
                    style={{ fontFamily: FONT_HEADING }}
                >
                    Editar legajo
                </h2>
                <p className="mt-2 mb-8 text-sm text-[#2B2F36]/65">
                    <span className="font-mono font-semibold text-[#2C4A5E]">
                        {legajo.numero_legajo}
                    </span>
                    {legajo.persona && ` · ${legajo.persona}`}. El número y la
                    persona no se pueden modificar.
                </p>

                <form onSubmit={submit} className="max-w-4xl space-y-6">
                    <ResumenErrores
                        errors={errors as Record<string, string>}
                        etiquetas={ETIQUETAS}
                    />

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className="text-base text-[#2C4A5E]"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Datos del caso
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5 px-5 py-6">
                            <div>
                                <Label
                                    htmlFor="motivo_ingreso_id"
                                    className={labelClass}
                                >
                                    Tipo de motivo de ingreso
                                </Label>
                                <select
                                    id="motivo_ingreso_id"
                                    className={selectClass}
                                    value={data.motivo_ingreso_id}
                                    onChange={(event) =>
                                        setData(
                                            'motivo_ingreso_id',
                                            event.target.value,
                                        )
                                    }
                                >
                                    <option value="">Sin clasificar</option>
                                    {motivosIngreso.map((motivo) => (
                                        <option
                                            key={motivo.id}
                                            value={motivo.id}
                                        >
                                            {motivo.nombre}
                                        </option>
                                    ))}
                                </select>
                                {errors.motivo_ingreso_id && (
                                    <p className={errorClass}>
                                        {errors.motivo_ingreso_id}
                                    </p>
                                )}
                                <Label
                                    htmlFor="motivo_ingreso"
                                    className={`${labelClass} mt-4 block`}
                                >
                                    Detalle del motivo de ingreso{' '}
                                    <span className="text-[#A84635]">*</span>
                                </Label>
                                <textarea
                                    id="motivo_ingreso"
                                    rows={4}
                                    value={data.motivo_ingreso}
                                    onChange={(event) =>
                                        setData(
                                            'motivo_ingreso',
                                            event.target.value,
                                        )
                                    }
                                    className={textareaClass}
                                />
                                {errors.motivo_ingreso && (
                                    <p className={errorClass}>
                                        {errors.motivo_ingreso}
                                    </p>
                                )}
                            </div>

                            <div>
                                <Label className={labelClass}>
                                    Problemáticas{' '}
                                    <span className="text-[#A84635]">*</span>
                                </Label>
                                <div className="mt-2 grid gap-2 rounded-lg border border-[#D5D1C6] bg-white p-3 sm:grid-cols-2">
                                    {problematicas.map((problematica) => (
                                        <label
                                            key={problematica.id}
                                            className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-[#2B2F36] transition hover:bg-[#F4F6F5]"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={data.problematicas.includes(
                                                    problematica.id,
                                                )}
                                                onChange={() =>
                                                    alternarProblematica(
                                                        problematica.id,
                                                    )
                                                }
                                                className="size-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                            />
                                            {problematica.nombre}
                                        </label>
                                    ))}
                                </div>
                                {errors.problematicas && (
                                    <p className={errorClass}>
                                        {errors.problematicas}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <Label
                                        htmlFor="nivel_riesgo"
                                        className={labelClass}
                                    >
                                        Nivel de riesgo{' '}
                                        <span className="text-[#A84635]">
                                            *
                                        </span>
                                    </Label>
                                    <select
                                        id="nivel_riesgo"
                                        className={selectClass}
                                        value={data.nivel_riesgo}
                                        onChange={(event) =>
                                            setData(
                                                'nivel_riesgo',
                                                event.target.value,
                                            )
                                        }
                                    >
                                        <option value="">Seleccionar</option>
                                        <option value="Bajo">Bajo</option>
                                        <option value="Medio">Medio</option>
                                        <option value="Alto">Alto</option>
                                        <option value="Critico">Crítico</option>
                                    </select>
                                    {errors.nivel_riesgo && (
                                        <p className={errorClass}>
                                            {errors.nivel_riesgo}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <Label
                                        htmlFor="profesional_id"
                                        className={labelClass}
                                    >
                                        Profesional asignado
                                    </Label>
                                    <select
                                        id="profesional_id"
                                        className={selectClass}
                                        value={data.profesional_id}
                                        onChange={(event) =>
                                            setData(
                                                'profesional_id',
                                                event.target.value,
                                            )
                                        }
                                    >
                                        <option value="">Sin asignar</option>
                                        {profesionales.map((profesional) => (
                                            <option
                                                key={profesional.id}
                                                value={profesional.id}
                                            >
                                                {profesional.nombre_usuario}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.profesional_id && (
                                        <p className={errorClass}>
                                            {errors.profesional_id}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <Label
                                    htmlFor="origen_derivacion"
                                    className={labelClass}
                                >
                                    Origen de la derivación{' '}
                                    <span className="text-[#A84635]">*</span>
                                </Label>
                                <Input
                                    id="origen_derivacion"
                                    value={data.origen_derivacion}
                                    onChange={(event) =>
                                        setData(
                                            'origen_derivacion',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-2 h-11 border-[#D5D1C6] bg-white shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20"
                                />
                                {errors.origen_derivacion && (
                                    <p className={errorClass}>
                                        {errors.origen_derivacion}
                                    </p>
                                )}
                            </div>

                            <div>
                                <Label
                                    htmlFor="observaciones"
                                    className={labelClass}
                                >
                                    Observaciones{' '}
                                    <span className="font-normal text-[#2B2F36]/45">
                                        (opcional)
                                    </span>
                                </Label>
                                <textarea
                                    id="observaciones"
                                    rows={4}
                                    value={data.observaciones}
                                    onChange={(event) =>
                                        setData(
                                            'observaciones',
                                            event.target.value,
                                        )
                                    }
                                    className={textareaClass}
                                />
                                {errors.observaciones && (
                                    <p className={errorClass}>
                                        {errors.observaciones}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <Link
                            href={`/legajos/${legajo.id}`}
                            className="inline-flex h-11 items-center justify-center rounded-md px-5 text-sm font-semibold text-[#2B2F36]/65 transition hover:bg-white hover:text-[#2B2F36]"
                        >
                            Cancelar
                        </Link>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="h-11 bg-[#2C4A5E] px-6 text-sm font-semibold text-white shadow-sm hover:bg-[#1F394B]"
                        >
                            {processing ? 'Guardando…' : 'Guardar cambios'}
                        </Button>
                    </div>
                </form>
            </main>
        </>
    );
}
