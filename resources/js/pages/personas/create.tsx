import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowDown,
    Check,
    ClipboardPlus,
    Home,
    MapPin,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEventHandler, ReactNode } from 'react';

import ResumenErrores from '@/components/resumen-errores';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';

interface Opcion {
    id: number;
    nombre: string;
}

interface Profesional {
    id: number;
    nombre_usuario: string;
    email: string;
    profesion: string | null;
}

interface CreatePersonaProps {
    provincias: Opcion[];
    problematicas: Opcion[];
    profesionales: Profesional[];
    modoLegajo?: boolean;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

const inputClass =
    'mt-2 h-11 border-[#D5D1C6] bg-white text-[#2B2F36] shadow-none placeholder:text-[#2B2F36]/35 focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20';
const labelClass = 'text-sm font-semibold text-[#2B2F36]';
const errorClass = 'mt-1.5 text-xs font-medium text-[#A84635]';

function FieldError({ message }: { message?: string }) {
    return message ? <p className={errorClass}>{message}</p> : null;
}

const FIELD_LABELS: Record<string, string> = {
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
    abrir_legajo: 'Abrir legajo',
    legajo_problematicas: 'Problemática',
    legajo_nivel_riesgo: 'Nivel de riesgo',
    legajo_profesional_id: 'Profesional asignado',
    legajo_origen_derivacion: 'Origen de la derivación',
    legajo_observaciones: 'Observaciones del legajo',
};

function SectionHeading({
    icon,
    eyebrow,
    title,
    description,
}: {
    icon: ReactNode;
    eyebrow: string;
    title: string;
    description: string;
}) {
    return (
        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-5 sm:px-6">
            <div className="flex items-start gap-3">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#2C4A5E] text-white">
                    {icon}
                </div>
                <div>
                    <p className="text-[0.65rem] font-bold tracking-[0.16em] text-[#6B8F71] uppercase">
                        {eyebrow}
                    </p>
                    <CardTitle
                        className="mt-1 text-lg font-semibold text-[#2C4A5E]"
                        style={{ fontFamily: FONT_HEADING }}
                    >
                        {title}
                    </CardTitle>
                    <CardDescription className="mt-1 text-sm text-[#697586]">
                        {description}
                    </CardDescription>
                </div>
            </div>
        </CardHeader>
    );
}

export default function CreatePersona({
    provincias,
    problematicas,
    profesionales,
    modoLegajo = false,
}: CreatePersonaProps) {
    const { data, setData, post, processing, errors } = useForm({
        nombre: '',
        apellido: '',
        dni: '',
        fecha_nacimiento: '',
        genero: 'NO_ESPECIFICA',
        direccion_calle: '',
        direccion_numero: '',
        direccion_piso_depto: '',
        provincia_id: '',
        localidad_id: '',
        barrio_id: '',
        telefono: '',
        email: '',
        observaciones: '',
        abrir_legajo: modoLegajo,
        legajo_nivel_riesgo: '',
        legajo_origen_derivacion: '',
        legajo_profesional_id: '',
        legajo_problematicas: [] as number[],
        legajo_observaciones: '',
    });

    const [localidades, setLocalidades] = useState<Opcion[]>([]);
    const [barrios, setBarrios] = useState<Opcion[]>([]);
    const [cargandoLocalidades, setCargandoLocalidades] = useState(false);
    const [cargandoBarrios, setCargandoBarrios] = useState(false);

    useEffect(() => {
        const controller = new AbortController();

        if (!data.provincia_id) {
            return () => controller.abort();
        }

        fetch(`/api/localidades?provincia_id=${data.provincia_id}`, {
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('No se pudieron cargar las localidades.');
                }

                return response.json();
            })
            .then(setLocalidades)
            .catch((error: unknown) => {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                setLocalidades([]);
            })
            .finally(() => {
                if (!controller.signal.aborted) {
                    setCargandoLocalidades(false);
                }
            });

        return () => controller.abort();
    }, [data.provincia_id]);

    useEffect(() => {
        const controller = new AbortController();

        if (!data.localidad_id) {
            return () => controller.abort();
        }

        fetch(`/api/barrios?localidad_id=${data.localidad_id}`, {
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('No se pudieron cargar los barrios.');
                }

                return response.json();
            })
            .then(setBarrios)
            .catch((error: unknown) => {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }

                setBarrios([]);
            })
            .finally(() => {
                if (!controller.signal.aborted) {
                    setCargandoBarrios(false);
                }
            });

        return () => controller.abort();
    }, [data.localidad_id]);

    const toggleProblematica = (id: number) => {
        setData(
            'legajo_problematicas',
            data.legajo_problematicas.includes(id)
                ? data.legajo_problematicas.filter(
                      (problematica) => problematica !== id,
                  )
                : [...data.legajo_problematicas, id],
        );
    };

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(modoLegajo ? '/legajos' : '/personas', {
            preserveScroll: true,
        });
    };

    const previaLegajo = (() => {
        const soloLetras = (value: string) =>
            value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toUpperCase()
                .replace(/[^A-Z]/g, '');
        const apellido = soloLetras(data.apellido).padEnd(3, 'X').slice(0, 3);
        const nombre = soloLetras(data.nombre).padEnd(3, 'X').slice(0, 3);
        const dni = data.dni.replace(/\D/g, '');
        const ultimosDigitos =
            dni.length >= 3 ? dni.slice(-3) : dni.padStart(3, '0');

        if (!data.apellido || !data.nombre || !data.dni) {
            return null;
        }

        return `${apellido}-${nombre}-${ultimosDigitos}`;
    })();

    const secciones = [
        {
            id: 'datos-personales',
            label: 'Identificación',
            complete: Boolean(
                data.nombre &&
                data.apellido &&
                data.dni &&
                data.fecha_nacimiento,
            ),
        },
        {
            id: 'domicilio',
            label: 'Domicilio',
            complete: Boolean(data.provincia_id && data.localidad_id),
        },
        {
            id: 'informacion-complementaria',
            label: 'Información complementaria',
            complete: true,
        },
        {
            id: 'abrir-legajo',
            label: 'Legajo',
            complete: data.abrir_legajo
                ? Boolean(
                      data.legajo_nivel_riesgo &&
                      data.legajo_problematicas.length,
                  )
                : true,
        },
    ];
    const seccionesCompletas = secciones.filter(
        (seccion) => seccion.complete,
    ).length;

    return (
        <>
            <Head title={modoLegajo ? 'Nuevo legajo' : 'Nueva persona'} />

            <main
                className="bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mx-auto w-full max-w-6xl">
                    <div className="mb-8 max-w-3xl">
                        <div className="flex items-center gap-2 text-xs font-semibold tracking-[0.14em] text-[#697586] uppercase">
                            <span>{modoLegajo ? 'Legajos' : 'Personas'}</span>
                            <span className="text-[#B4C1B8]">/</span>
                            <span className="text-[#2B2F36]/45">
                                {modoLegajo ? 'Nuevo legajo' : 'Nueva alta'}
                            </span>
                        </div>
                        <h2
                            className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            {modoLegajo
                                ? 'Crear un legajo'
                                : 'Registrar una persona'}
                        </h2>
                        <p className="mt-2 max-w-2xl text-sm leading-6 text-[#697586]">
                            {modoLegajo
                                ? 'Completá los datos de la persona y del legajo. Se guardará todo en un único registro.'
                                : 'Completá la información básica para crear el registro.'}
                            Los campos marcados con asterisco son obligatorios.
                        </p>
                    </div>

                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_290px]">
                        <form onSubmit={submit} className="space-y-6">
                            <ResumenErrores
                                key={JSON.stringify(errors)}
                                errors={errors as Record<string, string>}
                                etiquetas={FIELD_LABELS}
                                floating
                            />

                            <Card
                                id="datos-personales"
                                className="scroll-mt-6 overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]"
                            >
                                <SectionHeading
                                    icon={<UserRound size={19} />}
                                    eyebrow="Paso 01"
                                    title="Datos personales"
                                    description="Identificación y medios de contacto de la persona."
                                />
                                <CardContent className="grid gap-5 px-5 py-6 sm:grid-cols-2 sm:px-6">
                                    <div>
                                        <Label
                                            htmlFor="nombre"
                                            className={labelClass}
                                        >
                                            Nombre{' '}
                                            <span className="text-[#A84635]">
                                                *
                                            </span>
                                        </Label>
                                        <Input
                                            id="nombre"
                                            value={data.nombre}
                                            onChange={(event) =>
                                                setData(
                                                    'nombre',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            autoComplete="given-name"
                                            required
                                        />
                                        <FieldError message={errors.nombre} />
                                    </div>
                                    <div>
                                        <Label
                                            htmlFor="apellido"
                                            className={labelClass}
                                        >
                                            Apellido{' '}
                                            <span className="text-[#A84635]">
                                                *
                                            </span>
                                        </Label>
                                        <Input
                                            id="apellido"
                                            value={data.apellido}
                                            onChange={(event) =>
                                                setData(
                                                    'apellido',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            autoComplete="family-name"
                                            required
                                        />
                                        <FieldError message={errors.apellido} />
                                    </div>
                                    <div>
                                        <Label
                                            htmlFor="dni"
                                            className={labelClass}
                                        >
                                            DNI{' '}
                                            <span className="text-[#A84635]">
                                                *
                                            </span>
                                        </Label>
                                        <Input
                                            id="dni"
                                            value={data.dni}
                                            onChange={(event) =>
                                                setData(
                                                    'dni',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            inputMode="numeric"
                                            autoComplete="off"
                                            required
                                        />
                                        <FieldError message={errors.dni} />
                                    </div>
                                    <div>
                                        <Label
                                            htmlFor="fecha_nacimiento"
                                            className={labelClass}
                                        >
                                            Fecha de nacimiento{' '}
                                            <span className="text-[#A84635]">
                                                *
                                            </span>
                                        </Label>
                                        <Input
                                            id="fecha_nacimiento"
                                            type="date"
                                            value={data.fecha_nacimiento}
                                            onChange={(event) =>
                                                setData(
                                                    'fecha_nacimiento',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            required
                                        />
                                        <FieldError
                                            message={errors.fecha_nacimiento}
                                        />
                                    </div>
                                    <div>
                                        <Label
                                            htmlFor="genero"
                                            className={labelClass}
                                        >
                                            Género
                                        </Label>
                                        <select
                                            id="genero"
                                            className={`${inputClass} block w-full rounded-md border px-3 text-sm`}
                                            value={data.genero}
                                            onChange={(event) =>
                                                setData(
                                                    'genero',
                                                    event.target.value,
                                                )
                                            }
                                        >
                                            <option value="NO_ESPECIFICA">
                                                Prefiere no especificar
                                            </option>
                                            <option value="F">Femenino</option>
                                            <option value="M">Masculino</option>
                                            <option value="X">
                                                No binario / X
                                            </option>
                                        </select>
                                        <FieldError message={errors.genero} />
                                    </div>
                                    <div>
                                        <Label
                                            htmlFor="telefono"
                                            className={labelClass}
                                        >
                                            Teléfono
                                        </Label>
                                        <Input
                                            id="telefono"
                                            value={data.telefono}
                                            onChange={(event) =>
                                                setData(
                                                    'telefono',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            autoComplete="tel"
                                        />
                                        <FieldError message={errors.telefono} />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label
                                            htmlFor="email"
                                            className={labelClass}
                                        >
                                            Correo electrónico
                                        </Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(event) =>
                                                setData(
                                                    'email',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            autoComplete="email"
                                        />
                                        <FieldError message={errors.email} />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card
                                id="domicilio"
                                className="scroll-mt-6 overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]"
                            >
                                <SectionHeading
                                    icon={<Home size={19} />}
                                    eyebrow="Paso 02"
                                    title="Domicilio"
                                    description="Ubicación actual y referencias territoriales."
                                />
                                <CardContent className="grid gap-5 px-5 py-6 sm:grid-cols-6 sm:px-6">
                                    <div className="sm:col-span-4">
                                        <Label
                                            htmlFor="direccion_calle"
                                            className={labelClass}
                                        >
                                            Calle
                                        </Label>
                                        <Input
                                            id="direccion_calle"
                                            value={data.direccion_calle}
                                            onChange={(event) =>
                                                setData(
                                                    'direccion_calle',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            autoComplete="street-address"
                                        />
                                        <FieldError
                                            message={errors.direccion_calle}
                                        />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label
                                            htmlFor="direccion_numero"
                                            className={labelClass}
                                        >
                                            Número
                                        </Label>
                                        <Input
                                            id="direccion_numero"
                                            value={data.direccion_numero}
                                            onChange={(event) =>
                                                setData(
                                                    'direccion_numero',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                        <FieldError
                                            message={errors.direccion_numero}
                                        />
                                    </div>
                                    <div className="sm:col-span-6">
                                        <Label
                                            htmlFor="direccion_piso_depto"
                                            className={labelClass}
                                        >
                                            Piso / departamento{' '}
                                            <span className="font-normal text-[#2B2F36]/45">
                                                (opcional)
                                            </span>
                                        </Label>
                                        <Input
                                            id="direccion_piso_depto"
                                            value={data.direccion_piso_depto}
                                            onChange={(event) =>
                                                setData(
                                                    'direccion_piso_depto',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                        <FieldError
                                            message={
                                                errors.direccion_piso_depto
                                            }
                                        />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label
                                            htmlFor="provincia_id"
                                            className={labelClass}
                                        >
                                            Provincia{' '}
                                            <span className="text-[#A84635]">
                                                *
                                            </span>
                                        </Label>
                                        <select
                                            id="provincia_id"
                                            className={`${inputClass} block w-full rounded-md border px-3 text-sm`}
                                            value={data.provincia_id}
                                            required
                                            onChange={(event) => {
                                                const provinciaId =
                                                    event.target.value;

                                                setData(
                                                    'provincia_id',
                                                    provinciaId,
                                                );
                                                setData('localidad_id', '');
                                                setData('barrio_id', '');
                                                setLocalidades([]);
                                                setBarrios([]);
                                                setCargandoLocalidades(
                                                    Boolean(provinciaId),
                                                );
                                                setCargandoBarrios(false);
                                            }}
                                        >
                                            <option value="">
                                                Seleccionar provincia
                                            </option>
                                            {provincias.map((provincia) => (
                                                <option
                                                    key={provincia.id}
                                                    value={provincia.id}
                                                >
                                                    {provincia.nombre}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errors.provincia_id}
                                        />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label
                                            htmlFor="localidad_id"
                                            className={labelClass}
                                        >
                                            Localidad{' '}
                                            <span className="text-[#A84635]">
                                                *
                                            </span>
                                        </Label>
                                        <select
                                            id="localidad_id"
                                            className={`${inputClass} block w-full rounded-md border px-3 text-sm disabled:cursor-not-allowed disabled:bg-[#F1F3F1]`}
                                            value={data.localidad_id}
                                            disabled={
                                                !data.provincia_id ||
                                                cargandoLocalidades
                                            }
                                            required
                                            aria-busy={cargandoLocalidades}
                                            onChange={(event) => {
                                                const localidadId =
                                                    event.target.value;

                                                setData(
                                                    'localidad_id',
                                                    localidadId,
                                                );
                                                setData('barrio_id', '');
                                                setBarrios([]);
                                                setCargandoBarrios(
                                                    Boolean(localidadId),
                                                );
                                            }}
                                        >
                                            <option value="">
                                                {cargandoLocalidades
                                                    ? 'Cargando localidades…'
                                                    : data.provincia_id
                                                      ? 'Seleccionar localidad'
                                                      : 'Primero elegí una provincia'}
                                            </option>
                                            {localidades.map((localidad) => (
                                                <option
                                                    key={localidad.id}
                                                    value={localidad.id}
                                                >
                                                    {localidad.nombre}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errors.localidad_id}
                                        />
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Label
                                            htmlFor="barrio_id"
                                            className={labelClass}
                                        >
                                            Barrio{' '}
                                            <span className="font-normal text-[#2B2F36]/45">
                                                (opcional)
                                            </span>
                                        </Label>
                                        <select
                                            id="barrio_id"
                                            className={`${inputClass} block w-full rounded-md border px-3 text-sm disabled:cursor-not-allowed disabled:bg-[#F1F3F1]`}
                                            value={data.barrio_id}
                                            disabled={
                                                !data.localidad_id ||
                                                cargandoBarrios
                                            }
                                            aria-busy={cargandoBarrios}
                                            onChange={(event) =>
                                                setData(
                                                    'barrio_id',
                                                    event.target.value,
                                                )
                                            }
                                        >
                                            <option value="">
                                                {cargandoBarrios
                                                    ? 'Cargando barrios…'
                                                    : data.localidad_id
                                                      ? 'Seleccionar barrio (opcional)'
                                                      : 'Primero elegí una localidad'}
                                            </option>
                                            {barrios.map((barrio) => (
                                                <option
                                                    key={barrio.id}
                                                    value={barrio.id}
                                                >
                                                    {barrio.nombre}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errors.barrio_id}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card
                                id="informacion-complementaria"
                                className="scroll-mt-6 overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]"
                            >
                                <SectionHeading
                                    icon={<MapPin size={19} />}
                                    eyebrow="Paso 03"
                                    title="Información complementaria"
                                    description="Notas útiles para el seguimiento institucional."
                                />
                                <CardContent className="px-5 py-6 sm:px-6">
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
                                        className="mt-2 block w-full resize-y rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3 text-sm text-[#2B2F36] shadow-none transition outline-none placeholder:text-[#2B2F36]/35 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                        placeholder="Agregá información relevante para la gestión…"
                                    />
                                    <FieldError
                                        message={errors.observaciones}
                                    />
                                </CardContent>
                            </Card>

                            <Card
                                id="abrir-legajo"
                                className={`scroll-mt-6 overflow-hidden !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] ${data.abrir_legajo ? 'border-[#2C4A5E]/35' : 'border-[#DCE3DF]'}`}
                            >
                                <SectionHeading
                                    icon={<ClipboardPlus size={19} />}
                                    eyebrow={
                                        modoLegajo
                                            ? 'Paso 04'
                                            : 'Paso 04 · Opcional'
                                    }
                                    title={
                                        modoLegajo
                                            ? 'Datos del legajo'
                                            : 'Abrir legajo'
                                    }
                                    description={
                                        modoLegajo
                                            ? 'Registrá el motivo, riesgo, derivación y problemáticas asociadas.'
                                            : 'Podés iniciar el legajo ahora o hacerlo más adelante.'
                                    }
                                />
                                <CardContent className="px-5 py-6 sm:px-6">
                                    {!modoLegajo && (
                                        <label className="flex cursor-pointer items-start gap-3 rounded-lg border border-[#DCE3DF] bg-[#FAFBFA] p-4 transition hover:border-[#2C4A5E]/40">
                                            <input
                                                type="checkbox"
                                                checked={data.abrir_legajo}
                                                onChange={(event) =>
                                                    setData(
                                                        'abrir_legajo',
                                                        event.target.checked,
                                                    )
                                                }
                                                className="mt-0.5 size-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                            />
                                            <span>
                                                <span className="block text-sm font-semibold text-[#2B2F36]">
                                                    Abrir un legajo para esta
                                                    persona
                                                </span>
                                                <span className="mt-1 block text-xs leading-5 text-[#2B2F36]/60">
                                                    Se solicitarán los datos del
                                                    caso y se generará
                                                    automáticamente el número de
                                                    legajo.
                                                </span>
                                            </span>
                                        </label>
                                    )}

                                    {data.abrir_legajo && (
                                        <div className="mt-6 space-y-5 border-t border-[#E4E8E5] pt-5">
                                            {previaLegajo && (
                                                <div className="flex items-center justify-between gap-3 rounded-lg bg-[#EDF3EF] px-4 py-3">
                                                    <div>
                                                        <p className="text-[0.65rem] font-bold tracking-[0.12em] text-[#41634A] uppercase">
                                                            Número estimado
                                                        </p>
                                                        <p className="mt-1 font-mono text-sm font-semibold tracking-wide text-[#2C4A5E]">
                                                            {previaLegajo}
                                                        </p>
                                                    </div>
                                                    <ShieldCheck
                                                        className="text-[#6B8F71]"
                                                        size={20}
                                                    />
                                                </div>
                                            )}
                                            <div>
                                                <Label className={labelClass}>
                                                    Problematica{' '}
                                                    <span className="text-[#A84635]">
                                                        *
                                                    </span>
                                                </Label>
                                                <div className="mt-2 grid gap-2 rounded-lg border border-[#D5D1C6] bg-white p-3 sm:grid-cols-2">
                                                    {problematicas.map(
                                                        (problematica) => (
                                                            <label
                                                                key={
                                                                    problematica.id
                                                                }
                                                                className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-[#2B2F36] transition hover:bg-[#F4F6F5]"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    checked={data.legajo_problematicas.includes(
                                                                        problematica.id,
                                                                    )}
                                                                    onChange={() =>
                                                                        toggleProblematica(
                                                                            problematica.id,
                                                                        )
                                                                    }
                                                                    className="size-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                                                />
                                                                {
                                                                    problematica.nombre
                                                                }
                                                            </label>
                                                        ),
                                                    )}
                                                </div>
                                                <FieldError
                                                    message={
                                                        errors.legajo_problematicas
                                                    }
                                                />
                                            </div>
                                            <div className="grid gap-5 sm:grid-cols-2">
                                                <div>
                                                    <Label
                                                        htmlFor="legajo_nivel_riesgo"
                                                        className={labelClass}
                                                    >
                                                        Nivel de riesgo{' '}
                                                        <span className="text-[#A84635]">
                                                            *
                                                        </span>
                                                    </Label>
                                                    <select
                                                        id="legajo_nivel_riesgo"
                                                        className={`${inputClass} block w-full rounded-md border px-3 text-sm`}
                                                        value={
                                                            data.legajo_nivel_riesgo
                                                        }
                                                        onChange={(event) =>
                                                            setData(
                                                                'legajo_nivel_riesgo',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                    >
                                                        <option value="">
                                                            Seleccionar
                                                        </option>
                                                        <option value="Bajo">
                                                            Bajo
                                                        </option>
                                                        <option value="Medio">
                                                            Medio
                                                        </option>
                                                        <option value="Alto">
                                                            Alto
                                                        </option>
                                                        <option value="Critico">
                                                            Crítico
                                                        </option>
                                                    </select>
                                                    <FieldError
                                                        message={
                                                            errors.legajo_nivel_riesgo
                                                        }
                                                    />
                                                </div>
                                                <div>
                                                    <Label
                                                        htmlFor="legajo_profesional_id"
                                                        className={labelClass}
                                                    >
                                                        Profesional
                                                        interviniente
                                                    </Label>
                                                    <select
                                                        id="legajo_profesional_id"
                                                        className={`${inputClass} block w-full rounded-md border px-3 text-sm`}
                                                        value={
                                                            data.legajo_profesional_id
                                                        }
                                                        onChange={(event) =>
                                                            setData(
                                                                'legajo_profesional_id',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                    >
                                                        <option value="">
                                                            Sin asignar
                                                        </option>
                                                        {profesionales.map(
                                                            (profesional) => (
                                                                <option
                                                                    key={
                                                                        profesional.id
                                                                    }
                                                                    value={
                                                                        profesional.id
                                                                    }
                                                                >
                                                                    {
                                                                        profesional.nombre_usuario
                                                                    }
                                                                    {profesional.profesion
                                                                        ? ` · ${profesional.profesion}`
                                                                        : ''}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    <FieldError
                                                        message={
                                                            errors.legajo_profesional_id
                                                        }
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <Label
                                                    htmlFor="legajo_origen_derivacion"
                                                    className={labelClass}
                                                >
                                                    Institución o referente que
                                                    deriva{' '}
                                                    <span className="text-[#A84635]">
                                                        *
                                                    </span>
                                                </Label>
                                                <Input
                                                    id="legajo_origen_derivacion"
                                                    value={
                                                        data.legajo_origen_derivacion
                                                    }
                                                    onChange={(event) =>
                                                        setData(
                                                            'legajo_origen_derivacion',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className={inputClass}
                                                    placeholder="Ej.: escuela, hospital o referente institucional"
                                                />
                                                <FieldError
                                                    message={
                                                        errors.legajo_origen_derivacion
                                                    }
                                                />
                                            </div>
                                            <div>
                                                <Label
                                                    htmlFor="legajo_observaciones"
                                                    className={labelClass}
                                                >
                                                    Observaciones del legajo{' '}
                                                    <span className="font-normal text-[#2B2F36]/45">
                                                        (opcional)
                                                    </span>
                                                </Label>
                                                <textarea
                                                    id="legajo_observaciones"
                                                    rows={3}
                                                    value={
                                                        data.legajo_observaciones
                                                    }
                                                    onChange={(event) =>
                                                        setData(
                                                            'legajo_observaciones',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="mt-2 block w-full resize-y rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3 text-sm text-[#2B2F36] transition outline-none placeholder:text-[#2B2F36]/35 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                                />
                                                <FieldError
                                                    message={
                                                        errors.legajo_observaciones
                                                    }
                                                />
                                            </div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <div className="sticky bottom-3 z-20 -mx-1 flex flex-col-reverse gap-3 rounded-lg border border-[#DCE3DF] bg-[#F4F6F5] p-3 sm:flex-row sm:items-center sm:justify-end lg:static lg:mx-0 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
                                <Link
                                    href={modoLegajo ? '/legajos' : '/'}
                                    className="inline-flex h-11 items-center justify-center rounded-md px-5 text-sm font-semibold text-[#2B2F36]/65 transition hover:bg-white hover:text-[#2B2F36]"
                                >
                                    Cancelar
                                </Link>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="h-11 bg-[#2C4A5E] px-6 text-sm font-semibold text-white shadow-sm hover:bg-[#1F394B]"
                                >
                                    {processing
                                        ? 'Guardando…'
                                        : modoLegajo
                                          ? 'Crear legajo'
                                          : 'Registrar persona'}
                                </Button>
                            </div>
                        </form>

                        <aside className="order-first space-y-4 lg:sticky lg:top-6 lg:order-none">
                            <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                                <CardHeader className="px-5 pt-5 pb-3">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <ShieldCheck
                                                size={17}
                                                className="text-[#6B8F71]"
                                            />
                                            <div>
                                                <CardTitle className="text-sm font-semibold text-[#173247]">
                                                    Tu progreso
                                                </CardTitle>
                                                <p className="mt-1 text-xs text-[#2B2F36]/55">
                                                    {seccionesCompletas} de{' '}
                                                    {secciones.length} secciones
                                                    listas
                                                </p>
                                            </div>
                                        </div>
                                        <span className="text-xs font-semibold text-[#6B8F71]">
                                            {Math.round(
                                                (seccionesCompletas /
                                                    secciones.length) *
                                                    100,
                                            )}
                                            %
                                        </span>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-2 px-5 pb-5 text-sm text-[#2B2F36]/65">
                                    {secciones.map((seccion) => (
                                        <a
                                            key={seccion.id}
                                            href={`#${seccion.id}`}
                                            className="group flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-[#F4F6F5]"
                                        >
                                            <span
                                                className={`flex size-6 shrink-0 items-center justify-center rounded-full ${seccion.complete ? 'bg-[#E3F0E6] text-[#41634A]' : 'bg-[#EEF0ED] text-[#A1AAA3]'}`}
                                            >
                                                {seccion.complete ? (
                                                    <Check
                                                        size={13}
                                                        strokeWidth={3}
                                                    />
                                                ) : (
                                                    <span className="size-1.5 rounded-full bg-current" />
                                                )}
                                            </span>
                                            <span className="flex-1">
                                                {seccion.label}
                                            </span>
                                            <ArrowDown
                                                size={14}
                                                className="text-[#2B2F36]/30 transition group-hover:translate-y-0.5 group-hover:text-[#2C4A5E]"
                                            />
                                        </a>
                                    ))}
                                    <Separator className="bg-[#E4E8E5]" />
                                    <p className="text-xs leading-5 text-[#2B2F36]/50">
                                        Los campos con * son obligatorios. La
                                        información queda disponible para el
                                        equipo autorizado.
                                    </p>
                                </CardContent>
                            </Card>

                            <div className="rounded-lg bg-[#4F498E] p-5 text-white">
                                <Badge className="border-0 bg-[#6B8F71] text-white">
                                    Gestión protegida
                                </Badge>
                                <p className="mt-3 text-sm leading-6 text-white/75">
                                    Completá los datos con precisión. Podrás
                                    agregar documentos e intervenciones desde el
                                    legajo.
                                </p>
                            </div>
                        </aside>
                    </div>
                </div>
            </main>
        </>
    );
}
