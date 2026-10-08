import { Head, Link, useForm } from '@inertiajs/react';
import {
    Check,
    ClipboardPlus,
    Home,
    MapPin,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import { FormEventHandler, type ReactNode, useEffect, useState } from 'react';

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
}

interface CreatePersonaProps {
    provincias: Opcion[];
    problematicas: Opcion[];
    profesionales: Profesional[];
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
                <div className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#2C4A5E] text-white shadow-sm">
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
                    <CardDescription className="mt-1 text-sm text-[#2B2F36]/60">
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
        abrir_legajo: false,
        legajo_motivo_ingreso: '',
        legajo_nivel_riesgo: '',
        legajo_origen_derivacion: '',
        legajo_profesional_id: '',
        legajo_problematicas: [] as number[],
        legajo_observaciones: '',
    });

    const [localidades, setLocalidades] = useState<Opcion[]>([]);
    const [barrios, setBarrios] = useState<Opcion[]>([]);

    useEffect(() => {
        if (!data.provincia_id) {
            setLocalidades([]);
            return;
        }

        fetch(`/api/localidades?provincia_id=${data.provincia_id}`)
            .then((response) => response.json())
            .then(setLocalidades);
    }, [data.provincia_id]);

    useEffect(() => {
        if (!data.localidad_id) {
            setBarrios([]);
            return;
        }

        fetch(`/api/barrios?localidad_id=${data.localidad_id}`)
            .then((response) => response.json())
            .then(setBarrios);
    }, [data.localidad_id]);

    const toggleProblematica = (id: number) => {
        setData(
            'legajo_problematicas',
            data.legajo_problematicas.includes(id)
                ? data.legajo_problematicas.filter((problematica) => problematica !== id)
                : [...data.legajo_problematicas, id],
        );
    };

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post('/personas');
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

        if (!data.apellido || !data.nombre || !data.dni) return null;

        return `${apellido}-${nombre}-${ultimosDigitos}`;
    })();

    return (
        <>
            <Head title="Nueva persona" />

            <main
                className="bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mb-8 max-w-3xl">
                    <div className="flex items-center gap-2 text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                        <span>Personas</span>
                        <span className="text-[#B4C1B8]">/</span>
                        <span className="text-[#2B2F36]/45">Nueva alta</span>
                    </div>
                    <h2
                        className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                        style={{ fontFamily: FONT_HEADING }}
                    >
                        Registrar una persona
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-[#2B2F36]/65 sm:text-base">
                        Completá la información básica para crear el registro.
                        Los campos marcados con asterisco son obligatorios.
                    </p>
                </div>

                <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_290px]">
                    <form onSubmit={submit} className="space-y-6">
                        <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
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
                                    />
                                    <FieldError message={errors.apellido} />
                                </div>
                                <div>
                                    <Label htmlFor="dni" className={labelClass}>
                                        DNI{' '}
                                        <span className="text-[#A84635]">
                                            *
                                        </span>
                                    </Label>
                                    <Input
                                        id="dni"
                                        value={data.dni}
                                        onChange={(event) =>
                                            setData('dni', event.target.value)
                                        }
                                        className={inputClass}
                                        inputMode="numeric"
                                        autoComplete="off"
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
                                            setData('email', event.target.value)
                                        }
                                        className={inputClass}
                                        autoComplete="email"
                                    />
                                    <FieldError message={errors.email} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
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
                                        onChange={(event) => {
                                            setData(
                                                'provincia_id',
                                                event.target.value,
                                            );
                                            setData('localidad_id', '');
                                            setData('barrio_id', '');
                                        }}
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
                                    <FieldError message={errors.provincia_id} />
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
                                        disabled={!data.provincia_id}
                                        onChange={(event) => {
                                            setData(
                                                'localidad_id',
                                                event.target.value,
                                            );
                                            setData('barrio_id', '');
                                        }}
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
                                    <FieldError message={errors.localidad_id} />
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
                                        disabled={!data.localidad_id}
                                        onChange={(event) =>
                                            setData(
                                                'barrio_id',
                                                event.target.value,
                                            )
                                        }
                                    >
                                        <option value="">Seleccionar</option>
                                        {barrios.map((barrio) => (
                                            <option
                                                key={barrio.id}
                                                value={barrio.id}
                                            >
                                                {barrio.nombre}
                                            </option>
                                        ))}
                                    </select>
                                    <FieldError message={errors.barrio_id} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
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
                                <FieldError message={errors.observaciones} />
                            </CardContent>
                        </Card>

                        <Card
                            className={`overflow-hidden !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] ${data.abrir_legajo ? 'border-[#2C4A5E]/35' : 'border-[#DCE3DF]'}`}
                        >
                            <SectionHeading
                                icon={<ClipboardPlus size={19} />}
                                eyebrow="Paso 04 · Opcional"
                                title="Abrir legajo"
                                description="Podés iniciar el legajo ahora o hacerlo más adelante."
                            />
                            <CardContent className="px-5 py-6 sm:px-6">
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
                                            Abrir un legajo para esta persona
                                        </span>
                                        <span className="mt-1 block text-xs leading-5 text-[#2B2F36]/60">
                                            Se solicitarán los datos del caso y
                                            se generará automáticamente el
                                            número de legajo.
                                        </span>
                                    </span>
                                </label>

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
                                            <Label
                                                htmlFor="legajo_motivo_ingreso"
                                                className={labelClass}
                                            >
                                                Motivo de ingreso{' '}
                                                <span className="text-[#A84635]">
                                                    *
                                                </span>
                                            </Label>
                                            <textarea
                                                id="legajo_motivo_ingreso"
                                                rows={3}
                                                value={
                                                    data.legajo_motivo_ingreso
                                                }
                                                onChange={(event) =>
                                                    setData(
                                                        'legajo_motivo_ingreso',
                                                        event.target.value,
                                                    )
                                                }
                                                className="mt-2 block w-full resize-y rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3 text-sm text-[#2B2F36] transition outline-none placeholder:text-[#2B2F36]/35 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                                placeholder="Describí brevemente por qué se abre el legajo…"
                                            />
                                            <FieldError
                                                message={
                                                    errors.legajo_motivo_ingreso
                                                }
                                            />
                                        </div>
                                        <div>
                                            <Label className={labelClass}>
                                                 Problematica{' '}
                                                <span className="text-[#A84635]">
                                                    *
                                                </span>
                                            </Label>
                                            <div className="mt-2 grid gap-2 rounded-lg border border-[#D5D1C6] bg-white p-3 sm:grid-cols-2">
                                                {problematicas.map((problematica) => (
                                                    <label
                                                        key={problematica.id}
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
                                                        {problematica.nombre}
                                                    </label>
                                                ))}
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
                                                            event.target.value,
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
                                                    Profesional interviniente
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
                                                            event.target.value,
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
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
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
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <div className="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <Link
                                href="/"
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
                                    : 'Registrar persona'}
                            </Button>
                        </div>
                    </form>

                    <aside className="space-y-4 lg:sticky lg:top-6">
                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="px-5 pt-5 pb-3">
                                <div className="flex items-center gap-2">
                                    <ShieldCheck
                                        size={17}
                                        className="text-[#6B8F71]"
                                    />
                                    <CardTitle className="text-sm font-semibold text-[#173247]">
                                        Antes de guardar
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-3 px-5 pb-5 text-sm text-[#2B2F36]/65">
                                {[
                                    [
                                        'Identificación',
                                        Boolean(
                                            data.nombre &&
                                            data.apellido &&
                                            data.dni &&
                                            data.fecha_nacimiento,
                                        ),
                                    ],
                                    [
                                        'Domicilio',
                                        Boolean(
                                            data.provincia_id &&
                                            data.localidad_id,
                                        ),
                                    ],
                                    [
                                        'Legajo',
                                        data.abrir_legajo
                                            ? Boolean(
                                                  data.legajo_motivo_ingreso &&
                                                  data.legajo_nivel_riesgo &&
                                                  data.legajo_problematicas
                                                      .length,
                                              )
                                            : true,
                                    ],
                                ].map(([label, complete]) => (
                                    <div
                                        key={String(label)}
                                        className="flex items-center justify-between gap-3"
                                    >
                                        <span>{label}</span>
                                        <span
                                            className={`flex size-5 items-center justify-center rounded-full ${complete ? 'bg-[#E3F0E6] text-[#41634A]' : 'bg-[#EEF0ED] text-[#A1AAA3]'}`}
                                        >
                                            <Check size={13} strokeWidth={3} />
                                        </span>
                                    </div>
                                ))}
                                <Separator className="bg-[#E4E8E5]" />
                                <p className="text-xs leading-5 text-[#2B2F36]/50">
                                    La información se guarda en el sistema
                                    municipal y queda disponible para el equipo
                                    autorizado.
                                </p>
                            </CardContent>
                        </Card>

                        <div className="rounded-xl bg-[#173247] p-5 text-white shadow-[0_14px_35px_rgba(23,50,71,0.12)]">
                            <Badge className="border-0 bg-[#6B8F71] text-white">
                                Gestión protegida
                            </Badge>
                            <p className="mt-3 text-sm leading-6 text-white/75">
                                Completá los datos con precisión. Podrás agregar
                                documentos e intervenciones desde el legajo.
                            </p>
                        </div>
                    </aside>
                </div>
            </main>
        </>
    );
}
