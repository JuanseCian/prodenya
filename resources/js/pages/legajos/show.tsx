import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Clock3, Lock, LockOpen, Pencil, Plus } from 'lucide-react';

import {
    AvisoEstado,
    Dato,
    FONT_BODY,
    FONT_HEADING,
    NivelBadge,
} from '@/components/detalle';
import ResumenErrores from '@/components/resumen-errores';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface LegajoDetalle {
    id: number;
    intervenciones_legajo_id: number;
    numero_legajo: string;
    motivo_ingreso: string | null;
    nivel_riesgo: string;
    estado: string;
    origen_derivacion: string | null;
    observaciones: string | null;
    fecha_apertura: string | null;
    profesional: string | null;
    problematicas: string[];
    persona: {
        id: number;
        nombre_completo: string;
        dni: string;
        edad: number | null;
    } | null;
}

interface Intervencion {
    id: number;
    fecha: string | null;
    hora: string | null;
    tipo: string | null;
    detalle: string;
    profesional: string | null;
    creado_por: string | null;
    firma_nombre: string | null;
    firma_aclaracion: string | null;
    tiene_firma_responsable: boolean;
    firma_entrevistado_nombre: string | null;
    firma_entrevistado_aclaracion: string | null;
    tiene_firma_entrevistado: boolean;
}

interface Props {
    legajo: LegajoDetalle;
    status?: string | null;
    intervenciones?: Intervencion[];
    puedeGestionarIntervenciones?: boolean;
}

const tituloCard = 'text-base font-semibold text-[#2C4A5E]';

export default function LegajoShow({
    legajo,
    status,
    intervenciones = [],
    puedeGestionarIntervenciones = false,
}: Props) {
    const errors = (usePage().props.errors ?? {}) as Record<string, string>;
    const cerrado = legajo.estado === 'Cerrado';

    const cambiarEstado = () => {
        const accion = cerrado ? 'reabrir' : 'cerrar';

        if (
            window.confirm(
                `¿Querés ${accion} el legajo ${legajo.numero_legajo}?`,
            )
        ) {
            router.patch(
                `/legajos/${legajo.id}/${accion}`,
                {},
                { preserveScroll: true },
            );
        }
    };

    return (
        <>
            <Head title={`Legajo ${legajo.numero_legajo}`} />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <Link
                    href="/legajos"
                    className="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                >
                    <ArrowLeft size={15} />
                    Volver al listado
                </Link>

                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                            Legajo
                        </p>
                        <h2 className="mt-3 font-mono text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                            {legajo.numero_legajo}
                        </h2>
                        <div className="mt-3 flex flex-wrap items-center gap-2">
                            <NivelBadge nivel={legajo.nivel_riesgo} />
                            <Badge className="border-0 bg-[#EEF0ED] text-[#2B2F36]">
                                {legajo.estado}
                            </Badge>
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={`/legajos/${legajo.id}/edit`}
                            className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                        >
                            <Pencil size={15} />
                            Editar
                        </Link>
                        <button
                            type="button"
                            onClick={cambiarEstado}
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#DCE3DF] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            {cerrado ? (
                                <LockOpen size={15} />
                            ) : (
                                <Lock size={15} />
                            )}
                            {cerrado ? 'Reabrir legajo' : 'Cerrar legajo'}
                        </button>
                    </div>
                </div>

                <AvisoEstado mensaje={status} />
                <div className="mb-6">
                    <ResumenErrores errors={errors} />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Persona
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            {legajo.persona ? (
                                <dl className="grid gap-4 sm:grid-cols-2">
                                    <Dato etiqueta="Nombre">
                                        <Link
                                            href={`/personas/${legajo.persona.id}`}
                                            className="font-semibold text-[#2C4A5E] underline"
                                        >
                                            {legajo.persona.nombre_completo}
                                        </Link>
                                    </Dato>
                                    <Dato etiqueta="DNI">
                                        {legajo.persona.dni}
                                    </Dato>
                                    <Dato etiqueta="Edad">
                                        {legajo.persona.edad !== null
                                            ? `${legajo.persona.edad} años`
                                            : null}
                                    </Dato>
                                </dl>
                            ) : (
                                <p className="text-sm text-[#2B2F36]/60">
                                    Sin persona asociada.
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Seguimiento
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            <dl className="grid gap-4 sm:grid-cols-2">
                                <Dato etiqueta="Fecha de apertura">
                                    {legajo.fecha_apertura}
                                </Dato>
                                <Dato etiqueta="Profesional asignado">
                                    {legajo.profesional}
                                </Dato>
                                <Dato etiqueta="Origen de la derivación">
                                    {legajo.origen_derivacion}
                                </Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Caso
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5 px-5 py-5">
                            <dl className="space-y-5">
                                <Dato etiqueta="Problemáticas">
                                    {legajo.problematicas.length > 0 && (
                                        <span className="flex flex-wrap gap-1.5">
                                            {legajo.problematicas.map(
                                                (nombre) => (
                                                    <Badge
                                                        key={nombre}
                                                        className="border-0 bg-[#EDF3EF] text-[#2C4A5E]"
                                                    >
                                                        {nombre}
                                                    </Badge>
                                                ),
                                            )}
                                        </span>
                                    )}
                                </Dato>
                                <Dato etiqueta="Motivo de ingreso">
                                    {legajo.motivo_ingreso}
                                </Dato>
                                <Dato etiqueta="Observaciones">
                                    {legajo.observaciones}
                                </Dato>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <Card className="mt-6 border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                    <CardHeader className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Intervenciones y registros
                            </CardTitle>
                            <p className="mt-1 text-sm text-[#2B2F36]/60">
                                Actas, oficios, denuncias, mails y actuaciones
                                asociadas a este legajo.
                            </p>
                        </div>
                        {puedeGestionarIntervenciones && (
                            <Link
                                href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/create`}
                                className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                            >
                                <Plus size={16} />
                                Nueva intervención
                            </Link>
                        )}
                    </CardHeader>
                    <CardContent className="px-5 py-5 sm:px-6">
                        {intervenciones.length === 0 ? (
                            <div className="rounded-md border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-5 py-8 text-center">
                                <p className="text-sm font-semibold text-[#2C4A5E]">
                                    Todavía no hay intervenciones registradas.
                                </p>
                                {puedeGestionarIntervenciones && (
                                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                                        Registrá la primera actuación para
                                        comenzar el historial del legajo.
                                    </p>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {intervenciones.map((intervencion) => (
                                    <article
                                        key={intervencion.id}
                                        className="rounded-lg border border-[#DCE3DF] bg-white p-4 sm:p-5"
                                    >
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <Badge className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                                        {intervencion.tipo ??
                                                            'Registro'}
                                                    </Badge>
                                                    <span className="inline-flex items-center gap-1 text-xs font-semibold text-[#697586]">
                                                        <Clock3 size={13} />
                                                        {intervencion.fecha}
                                                        {intervencion.hora
                                                            ? ` · ${intervencion.hora.slice(0, 5)}`
                                                            : ''}
                                                    </span>
                                                </div>
                                                <p className="mt-3 whitespace-pre-line text-sm leading-6 text-[#2B2F36]">
                                                    {intervencion.detalle}
                                                </p>
                                            </div>
                                            {puedeGestionarIntervenciones && (
                                                <Link
                                                    href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/${intervencion.id}/edit`}
                                                    className="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                >
                                                    <Pencil size={14} />
                                                    Editar
                                                </Link>
                                            )}
                                        </div>
                                        <div className="mt-4 grid gap-3 border-t border-[#E4E8E5] pt-3 text-xs text-[#697586] sm:grid-cols-2 lg:grid-cols-4">
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Responsable:{' '}
                                                </strong>
                                                {intervencion.profesional ??
                                                    'Sin asignar'}
                                            </p>
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Registró:{' '}
                                                </strong>
                                                {intervencion.creado_por ??
                                                    'Sin datos'}
                                            </p>
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Firma:{' '}
                                                </strong>
                                                {intervencion.firma_nombre
                                                    ? `${intervencion.firma_nombre} · ${intervencion.firma_aclaracion ?? ''}`
                                                    : 'Sin firma'}
                                                {intervencion.tiene_firma_responsable && (
                                                    <a
                                                        href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/${intervencion.id}/firma/responsable/descargar`}
                                                        className="ml-1 underline"
                                                    >
                                                        (descargar)
                                                    </a>
                                                )}
                                            </p>
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Entrevistado:{' '}
                                                </strong>
                                                {intervencion.firma_entrevistado_nombre
                                                    ? `${intervencion.firma_entrevistado_nombre} · ${intervencion.firma_entrevistado_aclaracion ?? ''}`
                                                    : 'Sin firma'}
                                                {intervencion.tiene_firma_entrevistado && (
                                                    <a
                                                        href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/${intervencion.id}/firma/entrevistado/descargar`}
                                                        className="ml-1 underline"
                                                    >
                                                        (descargar)
                                                    </a>
                                                )}
                                            </p>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
