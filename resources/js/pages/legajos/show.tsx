import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Lock, LockOpen, Pencil } from 'lucide-react';

import { AvisoEstado, Dato, FONT_BODY, FONT_HEADING, NivelBadge } from '@/components/detalle';
import ResumenErrores from '@/components/resumen-errores';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface LegajoDetalle {
    id: number;
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

interface Props {
    legajo: LegajoDetalle;
    status?: string | null;
}

const tituloCard = 'text-base font-semibold text-[#2C4A5E]';

export default function LegajoShow({ legajo, status }: Props) {
    const errors = (usePage().props.errors ?? {}) as Record<string, string>;
    const cerrado = legajo.estado === 'Cerrado';

    const cambiarEstado = () => {
        const accion = cerrado ? 'reabrir' : 'cerrar';

        if (window.confirm(`¿Querés ${accion} el legajo ${legajo.numero_legajo}?`)) {
            router.patch(`/legajos/${legajo.id}/${accion}`, {}, { preserveScroll: true });
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
                        <h2
                            className="mt-3 font-mono text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                        >
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
                            className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1F394B]"
                        >
                            <Pencil size={15} />
                            Editar
                        </Link>
                        <button
                            type="button"
                            onClick={cambiarEstado}
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#D5D1C6] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            {cerrado ? <LockOpen size={15} /> : <Lock size={15} />}
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
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
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
                                    <Dato etiqueta="DNI">{legajo.persona.dni}</Dato>
                                    <Dato etiqueta="Edad">
                                        {legajo.persona.edad !== null ? `${legajo.persona.edad} años` : null}
                                    </Dato>
                                </dl>
                            ) : (
                                <p className="text-sm text-[#2B2F36]/60">Sin persona asociada.</p>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                Seguimiento
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            <dl className="grid gap-4 sm:grid-cols-2">
                                <Dato etiqueta="Fecha de apertura">{legajo.fecha_apertura}</Dato>
                                <Dato etiqueta="Profesional asignado">{legajo.profesional}</Dato>
                                <Dato etiqueta="Origen de la derivación">{legajo.origen_derivacion}</Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                Caso
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5 px-5 py-5">
                            <dl className="space-y-5">
                                <Dato etiqueta="Problemáticas">
                                    {legajo.problematicas.length > 0 && (
                                        <span className="flex flex-wrap gap-1.5">
                                            {legajo.problematicas.map((nombre) => (
                                                <Badge key={nombre} className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                                    {nombre}
                                                </Badge>
                                            ))}
                                        </span>
                                    )}
                                </Dato>
                                <Dato etiqueta="Motivo de ingreso">{legajo.motivo_ingreso}</Dato>
                                <Dato etiqueta="Observaciones">{legajo.observaciones}</Dato>
                            </dl>
                        </CardContent>
                    </Card>
                </div>
            </main>
        </>
    );
}
