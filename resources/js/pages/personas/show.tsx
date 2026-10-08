import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Pencil, UserX } from 'lucide-react';

import { AvisoEstado, Dato, FONT_BODY, FONT_HEADING, NivelBadge } from '@/components/detalle';
import ResumenErrores from '@/components/resumen-errores';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface PersonaDetalle {
    id: number;
    nombre: string;
    apellido: string;
    dni: string;
    fecha_nacimiento_texto: string | null;
    edad: number | null;
    genero: string;
    direccion_calle: string | null;
    direccion_numero: string | null;
    direccion_piso_depto: string | null;
    provincia: string | null;
    localidad: string | null;
    barrio: string | null;
    telefono: string | null;
    email: string | null;
    observaciones: string | null;
    activo: boolean;
}

interface LegajoResumen {
    id: number;
    numero_legajo: string;
    estado: string;
    nivel_riesgo: string;
    fecha_apertura: string | null;
    problematicas: string[];
}

interface Props {
    persona: PersonaDetalle;
    legajos: LegajoResumen[];
    status?: string | null;
}

const GENERO_LABEL: Record<string, string> = {
    M: 'Masculino',
    F: 'Femenino',
    X: 'No binario / X',
    NO_ESPECIFICA: 'No especifica',
};

const tituloCard = 'text-base font-semibold text-[#2C4A5E]';

export default function PersonaShow({ persona, legajos, status }: Props) {
    const errors = (usePage().props.errors ?? {}) as Record<string, string>;

    const domicilio = [
        [persona.direccion_calle, persona.direccion_numero].filter(Boolean).join(' '),
        persona.direccion_piso_depto,
    ]
        .filter(Boolean)
        .join(', ');

    const darDeBaja = () => {
        if (
            window.confirm(
                `¿Dar de baja a ${persona.apellido}, ${persona.nombre}?\n\nDejará de aparecer en los listados. Los datos no se borran.`,
            )
        ) {
            router.delete(`/personas/${persona.id}`);
        }
    };

    return (
        <>
            <Head title={`${persona.apellido}, ${persona.nombre}`} />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <Link
                    href="/personas"
                    className="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                >
                    <ArrowLeft size={15} />
                    Volver al listado
                </Link>

                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-2 text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                            <span>Persona</span>
                            {!persona.activo && (
                                <Badge className="border-0 bg-[#F8E1DD] text-[#A84635]">
                                    Dada de baja
                                </Badge>
                            )}
                        </div>
                        <h2
                            className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            {persona.apellido}, {persona.nombre}
                        </h2>
                        <p className="mt-2 font-mono text-sm text-[#2B2F36]/65">
                            DNI {persona.dni}
                        </p>
                    </div>

                    {persona.activo && (
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href={`/personas/${persona.id}/edit`}
                                className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1F394B]"
                            >
                                <Pencil size={15} />
                                Editar
                            </Link>
                            <button
                                type="button"
                                onClick={darDeBaja}
                                className="inline-flex h-11 items-center gap-2 rounded-md border border-[#E3B7AE] bg-white px-4 text-sm font-semibold text-[#A84635] transition hover:bg-[#FBEFEC]"
                            >
                                <UserX size={15} />
                                Dar de baja
                            </button>
                        </div>
                    )}
                </div>

                <AvisoEstado mensaje={status} />
                <div className="mb-6">
                    <ResumenErrores errors={errors} />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                Datos personales
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            <dl className="grid gap-4 sm:grid-cols-2">
                                <Dato etiqueta="Fecha de nacimiento">
                                    {persona.fecha_nacimiento_texto}
                                    {persona.edad !== null && ` (${persona.edad} años)`}
                                </Dato>
                                <Dato etiqueta="Género">
                                    {GENERO_LABEL[persona.genero] ?? persona.genero}
                                </Dato>
                                <Dato etiqueta="Teléfono">{persona.telefono}</Dato>
                                <Dato etiqueta="Correo electrónico">{persona.email}</Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                Domicilio
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            <dl className="grid gap-4 sm:grid-cols-2">
                                <Dato etiqueta="Dirección">{domicilio}</Dato>
                                <Dato etiqueta="Provincia">{persona.provincia}</Dato>
                                <Dato etiqueta="Localidad">{persona.localidad}</Dato>
                                <Dato etiqueta="Barrio">{persona.barrio}</Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                Observaciones
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            <dl>
                                <Dato etiqueta="Notas">{persona.observaciones}</Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                Legajos
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            {legajos.length === 0 ? (
                                <p className="px-5 py-6 text-sm text-[#2B2F36]/60">
                                    Esta persona todavía no tiene legajos.
                                </p>
                            ) : (
                                <ul className="divide-y divide-[#EEF0ED]">
                                    {legajos.map((legajo) => (
                                        <li key={legajo.id}>
                                            <Link
                                                href={`/legajos/${legajo.id}`}
                                                className="flex flex-col gap-2 px-5 py-4 transition hover:bg-[#FAFBFA] sm:flex-row sm:items-center sm:justify-between"
                                            >
                                                <div>
                                                    <p className="font-mono text-sm font-semibold text-[#2C4A5E]">
                                                        {legajo.numero_legajo}
                                                    </p>
                                                    <p className="mt-0.5 text-xs text-[#2B2F36]/55">
                                                        {legajo.problematicas.join(' · ') || 'Sin problemáticas'}
                                                        {legajo.fecha_apertura && ` — abierto el ${legajo.fecha_apertura}`}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <NivelBadge nivel={legajo.nivel_riesgo} />
                                                    <Badge className="border-0 bg-[#EEF0ED] text-[#2B2F36]">
                                                        {legajo.estado}
                                                    </Badge>
                                                </div>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </main>
        </>
    );
}
