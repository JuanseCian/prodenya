import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, BarChart3 } from 'lucide-react';

import { FONT_BODY, FONT_HEADING } from '@/components/detalle';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Estadisticas {
    intervenciones: {
        total: number;
        firmadas: number;
        por_tipo: { nombre: string; total: number }[];
    };
    tareas: {
        total: number;
        pendientes: number;
        completadas: number;
        canceladas: number;
    };
    derivaciones: {
        total: number;
        pendientes: number;
        aceptadas: number;
        rechazadas: number;
        canceladas: number;
    };
    documentos: number;
    mensajes: number;
    accesos: number;
}

interface Props {
    legajo: {
        id: number;
        numero_legajo: string;
        nombre: string;
        estado: string;
    };
    estadisticas: Estadisticas;
}

const tituloCard = 'text-base font-semibold text-[#2C4A5E]';

export default function LegajoEstadisticas({ legajo, estadisticas }: Props) {
    const indicadores = [
        ['Intervenciones', estadisticas.intervenciones.total],
        ['Intervenciones firmadas', estadisticas.intervenciones.firmadas],
        ['Tareas pendientes', estadisticas.tareas.pendientes],
        ['Tareas completadas', estadisticas.tareas.completadas],
        ['Derivaciones', estadisticas.derivaciones.total],
        ['Documentos', estadisticas.documentos],
        ['Mensajes vinculados', estadisticas.mensajes],
        ['Accesos registrados', estadisticas.accesos],
    ];

    return (
        <>
            <Head title={`Estadísticas · ${legajo.numero_legajo}`} />
            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <div className="mx-auto max-w-6xl">
                    <Link
                        href={`/legajos/${legajo.id}`}
                        className="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                    >
                        <ArrowLeft size={15} />
                        Volver al legajo
                    </Link>

                    <header className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                                Estadísticas individuales
                            </p>
                            <h1
                                className="mt-3 font-mono text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                {legajo.numero_legajo}
                            </h1>
                            <p className="mt-2 text-sm text-[#697586]">{legajo.nombre}</p>
                        </div>
                        <Badge className="w-fit border-0 bg-[#EEF0ED] text-[#2B2F36]">
                            {legajo.estado}
                        </Badge>
                    </header>

                    <div className="grid gap-6 lg:grid-cols-2">
                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                <div className="flex items-center gap-2">
                                    <BarChart3 size={18} className="text-[#6B8F71]" />
                                    <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                        Resumen del caso
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent className="grid gap-3 px-5 py-5 sm:grid-cols-2 lg:grid-cols-4">
                                {indicadores.map(([label, value]) => (
                                    <div key={label} className="rounded-lg border border-[#E4E8E5] bg-[#FBFCFB] px-4 py-3">
                                        <p className="text-xs font-semibold text-[#697586]">{label}</p>
                                        <p className="mt-1 text-2xl font-semibold text-[#173247]">{value}</p>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                    Intervenciones por tipo
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="px-5 py-5">
                                {estadisticas.intervenciones.por_tipo.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {estadisticas.intervenciones.por_tipo.map((tipo) => (
                                            <Badge key={tipo.nombre} className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                                {tipo.nombre}: {tipo.total}
                                            </Badge>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-[#697586]">Todavía no hay intervenciones registradas.</p>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                    Estado de tareas y derivaciones
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 px-5 py-5 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs font-semibold text-[#697586]">Tareas</p>
                                    <p className="mt-2 text-sm text-[#2B2F36]">{estadisticas.tareas.pendientes} pendientes · {estadisticas.tareas.canceladas} canceladas</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold text-[#697586]">Derivaciones</p>
                                    <p className="mt-2 text-sm text-[#2B2F36]">{estadisticas.derivaciones.pendientes} pendientes · {estadisticas.derivaciones.aceptadas} aceptadas</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </main>
        </>
    );
}
