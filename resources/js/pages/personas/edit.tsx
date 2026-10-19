import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEventHandler, ReactNode } from 'react';

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

interface PersonaEditable {
    id: number;
    nombre: string;
    apellido: string;
    dni: string;
    fecha_nacimiento: string | null;
    genero: string;
    direccion_calle: string | null;
    direccion_numero: string | null;
    direccion_piso_depto: string | null;
    provincia_id: number | null;
    localidad_id: number | null;
    barrio_id: number | null;
    telefono: string | null;
    email: string | null;
    observaciones: string | null;
}

interface Props {
    persona: PersonaEditable;
    provincias: Opcion[];
}

const ETIQUETAS: Record<string, string> = {
    nombre: 'Nombre',
    apellido: 'Apellido',
    dni: 'DNI',
    fecha_nacimiento: 'Fecha de nacimiento',
    genero: 'Género',
    telefono: 'Teléfono',
    email: 'Correo electrónico',
    direccion_calle: 'Calle',
    direccion_numero: 'Número',
    direccion_piso_depto: 'Piso / Depto',
    provincia_id: 'Provincia',
    localidad_id: 'Localidad',
    barrio_id: 'Barrio',
    observaciones: 'Observaciones',
};

const inputClass =
    'mt-2 h-11 border-[#D5D1C6] bg-white text-[#2B2F36] shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20';
const selectClass = `${inputClass} block w-full rounded-md border px-3 text-sm`;
const labelClass = 'text-sm font-semibold text-[#2B2F36]';
const errorClass = 'mt-1.5 text-xs font-medium text-[#A84635]';

function Campo({
    id,
    etiqueta,
    requerido,
    error,
    className,
    children,
}: {
    id: string;
    etiqueta: string;
    requerido?: boolean;
    error?: string;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={className}>
            <Label htmlFor={id} className={labelClass}>
                {etiqueta}{' '}
                {requerido && <span className="text-[#A84635]">*</span>}
            </Label>
            {children}
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}

export default function PersonaEdit({ persona, provincias }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        nombre: persona.nombre ?? '',
        apellido: persona.apellido ?? '',
        dni: persona.dni ?? '',
        fecha_nacimiento: persona.fecha_nacimiento ?? '',
        genero: persona.genero ?? 'NO_ESPECIFICA',
        telefono: persona.telefono ?? '',
        email: persona.email ?? '',
        direccion_calle: persona.direccion_calle ?? '',
        direccion_numero: persona.direccion_numero ?? '',
        direccion_piso_depto: persona.direccion_piso_depto ?? '',
        provincia_id: persona.provincia_id ? String(persona.provincia_id) : '',
        localidad_id: persona.localidad_id ? String(persona.localidad_id) : '',
        barrio_id: persona.barrio_id ? String(persona.barrio_id) : '',
        observaciones: persona.observaciones ?? '',
    });

    const [localidades, setLocalidades] = useState<Opcion[]>([]);
    const [barrios, setBarrios] = useState<Opcion[]>([]);

    useEffect(() => {
        if (!data.provincia_id) {
            return;
        }

        fetch(`/api/localidades?provincia_id=${data.provincia_id}`)
            .then((response) => response.json())
            .then(setLocalidades);
    }, [data.provincia_id]);

    useEffect(() => {
        if (!data.localidad_id) {
            return;
        }

        fetch(`/api/barrios?localidad_id=${data.localidad_id}`)
            .then((response) => response.json())
            .then(setBarrios);
    }, [data.localidad_id]);

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        put(`/personas/${persona.id}`, { preserveScroll: true });
    };

    const texto = (campo: keyof typeof data) => ({
        id: campo,
        value: String(data[campo] ?? ''),
        onChange: (event: { target: { value: string } }) =>
            setData(campo, event.target.value as never),
        className: inputClass,
    });

    return (
        <>
            <Head title="Editar persona" />

            <main
                    className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <Link
                    href={`/personas/${persona.id}`}
                    className="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                >
                    <ArrowLeft size={15} />
                    Volver a la persona
                </Link>

                <h2
                    className="mb-8 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                    style={{ fontFamily: FONT_HEADING }}
                >
                    Editar persona
                </h2>

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
                                Datos personales
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-5 px-5 py-6 sm:grid-cols-2">
                            <Campo
                                id="nombre"
                                etiqueta="Nombre"
                                requerido
                                error={errors.nombre}
                            >
                                <Input {...texto('nombre')} />
                            </Campo>
                            <Campo
                                id="apellido"
                                etiqueta="Apellido"
                                requerido
                                error={errors.apellido}
                            >
                                <Input {...texto('apellido')} />
                            </Campo>
                            <Campo
                                id="dni"
                                etiqueta="DNI"
                                requerido
                                error={errors.dni}
                            >
                                <Input {...texto('dni')} inputMode="numeric" />
                            </Campo>
                            <Campo
                                id="fecha_nacimiento"
                                etiqueta="Fecha de nacimiento"
                                requerido
                                error={errors.fecha_nacimiento}
                            >
                                <Input
                                    {...texto('fecha_nacimiento')}
                                    type="date"
                                />
                            </Campo>
                            <Campo
                                id="genero"
                                etiqueta="Género"
                                error={errors.genero}
                            >
                                <select
                                    id="genero"
                                    className={selectClass}
                                    value={data.genero}
                                    onChange={(event) =>
                                        setData('genero', event.target.value)
                                    }
                                >
                                    <option value="NO_ESPECIFICA">
                                        Prefiere no especificar
                                    </option>
                                    <option value="F">Femenino</option>
                                    <option value="M">Masculino</option>
                                    <option value="X">No binario / X</option>
                                </select>
                            </Campo>
                            <Campo
                                id="telefono"
                                etiqueta="Teléfono"
                                error={errors.telefono}
                            >
                                <Input {...texto('telefono')} />
                            </Campo>
                            <Campo
                                id="email"
                                etiqueta="Correo electrónico"
                                error={errors.email}
                                className="sm:col-span-2"
                            >
                                <Input {...texto('email')} type="email" />
                            </Campo>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className="text-base text-[#2C4A5E]"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Domicilio
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-5 px-5 py-6 sm:grid-cols-6">
                            <Campo
                                id="direccion_calle"
                                etiqueta="Calle"
                                error={errors.direccion_calle}
                                className="sm:col-span-4"
                            >
                                <Input {...texto('direccion_calle')} />
                            </Campo>
                            <Campo
                                id="direccion_numero"
                                etiqueta="Número"
                                error={errors.direccion_numero}
                                className="sm:col-span-2"
                            >
                                <Input {...texto('direccion_numero')} />
                            </Campo>
                            <Campo
                                id="direccion_piso_depto"
                                etiqueta="Piso / Depto"
                                error={errors.direccion_piso_depto}
                                className="sm:col-span-2"
                            >
                                <Input {...texto('direccion_piso_depto')} />
                            </Campo>
                            <Campo
                                id="provincia_id"
                                etiqueta="Provincia"
                                requerido
                                error={errors.provincia_id}
                                className="sm:col-span-2"
                            >
                                <select
                                    id="provincia_id"
                                    className={selectClass}
                                    value={data.provincia_id}
                                    onChange={(event) =>
                                        (() => {
                                            setLocalidades([]);
                                            setBarrios([]);
                                            setData({
                                                ...data,
                                                provincia_id:
                                                    event.target.value,
                                                localidad_id: '',
                                                barrio_id: '',
                                            });
                                        })()
                                    }
                                >
                                    <option value="">Seleccionar</option>
                                    {provincias.map((provincia) => (
                                        <option
                                            key={provincia.id}
                                            value={provincia.id}
                                        >
                                            {provincia.nombre}
                                        </option>
                                    ))}
                                </select>
                            </Campo>
                            <Campo
                                id="localidad_id"
                                etiqueta="Localidad"
                                requerido
                                error={errors.localidad_id}
                                className="sm:col-span-2"
                            >
                                <select
                                    id="localidad_id"
                                    className={selectClass}
                                    value={data.localidad_id}
                                    onChange={(event) =>
                                        (() => {
                                            setBarrios([]);
                                            setData({
                                                ...data,
                                                localidad_id:
                                                    event.target.value,
                                                barrio_id: '',
                                            });
                                        })()
                                    }
                                    disabled={!data.provincia_id}
                                >
                                    <option value="">Seleccionar</option>
                                    {localidades.map((localidad) => (
                                        <option
                                            key={localidad.id}
                                            value={localidad.id}
                                        >
                                            {localidad.nombre}
                                        </option>
                                    ))}
                                </select>
                            </Campo>
                            <Campo
                                id="barrio_id"
                                etiqueta="Barrio"
                                error={errors.barrio_id}
                                className="sm:col-span-2"
                            >
                                <select
                                    id="barrio_id"
                                    className={selectClass}
                                    value={data.barrio_id}
                                    onChange={(event) =>
                                        setData('barrio_id', event.target.value)
                                    }
                                    disabled={!data.localidad_id}
                                >
                                    <option value="">Sin barrio</option>
                                    {barrios.map((barrio) => (
                                        <option
                                            key={barrio.id}
                                            value={barrio.id}
                                        >
                                            {barrio.nombre}
                                        </option>
                                    ))}
                                </select>
                            </Campo>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className="text-base text-[#2C4A5E]"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Observaciones
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-6">
                            <textarea
                                id="observaciones"
                                rows={4}
                                value={data.observaciones}
                                onChange={(event) =>
                                    setData('observaciones', event.target.value)
                                }
                                className="block w-full resize-y rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                            />
                            {errors.observaciones && (
                                <p className={errorClass}>
                                    {errors.observaciones}
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <Link
                            href={`/personas/${persona.id}`}
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
