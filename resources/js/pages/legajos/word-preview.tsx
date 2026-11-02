import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Download, FileText } from 'lucide-react';

import legajosRoutes from '@/routes/legajos';

type PersonaPreview = {
    nombre: string;
    dni: string | null;
    fecha_nacimiento: string | null;
    edad: number | null;
    genero: string | null;
    direccion: string | null;
    localidad: string | null;
    barrio: string | null;
    telefono: string | null;
    email: string | null;
};

type IntervencionPreview = {
    fecha: string | null;
    hora: string | null;
    tipo: string | null;
    profesional: string | null;
    detalle: string | null;
};

type DerivacionPreview = {
    estado: string;
    modo: string;
    motivo: string | null;
    destinatario: string | null;
    fecha_limite: string | null;
};

type WordPreview = {
    numero_legajo: string;
    estado: string;
    nivel_riesgo: string;
    fecha_generado: string;
    persona: PersonaPreview | null;
    datos_legajo: {
        origen_derivacion: string | null;
        profesional: string | null;
        problematicas: string[];
        observaciones: string | null;
    };
    intervenciones: IntervencionPreview[];
    derivaciones: DerivacionPreview[];
    vinculos: { tipo: string; persona: string }[];
    responsables: { nombre: string | null; rol: string }[];
};

type Props = {
    legajo: WordPreview;
    downloadUrl: string;
};

const mostrar = (value: string | number | null | undefined): string =>
    value === null || value === undefined || value === '' ? '—' : String(value);

function DatosTabla({ filas }: { filas: [string, string | number | null][] }) {
    return (
        <div className="overflow-hidden rounded-xl border border-[#C9D6D1]">
            <table className="w-full border-collapse text-sm">
                <tbody>
                    {filas.map(([etiqueta, valor]) => (
                        <tr key={etiqueta} className="border-b border-[#DCE6E1] last:border-b-0">
                            <th className="w-1/3 bg-[#EDF3EF] px-4 py-3 text-left font-semibold text-[#173247]">
                                {etiqueta}
                            </th>
                            <td className="px-4 py-3 text-[#2B2F36]">{mostrar(valor)}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function Seccion({ titulo, children }: { titulo: string; children: React.ReactNode }) {
    return (
        <section className="mt-8 first:mt-0">
            <h2 className="mb-3 border-b-2 border-[#C9D6D1] pb-2 text-lg font-bold text-[#2C4A5E]">
                {titulo}
            </h2>
            {children}
        </section>
    );
}

function Vacia({ children }: { children: string }) {
    return <p className="rounded-xl border border-dashed border-[#C9D6D1] px-4 py-5 text-sm italic text-[#5B6B66]">{children}</p>;
}

export default function WordPreview({ legajo, downloadUrl }: Props) {
    const persona = legajo.persona;

    return (
        <>
            <Head title={`Previsualización · Legajo ${legajo.numero_legajo}`} />
            <main className="min-h-screen bg-[#F0F3F1] px-4 py-6 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-5xl">
                    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href={legajosRoutes.index().url}
                            className="inline-flex items-center gap-2 self-start text-sm font-semibold text-[#2C4A5E] hover:text-[#173247]"
                        >
                            <ArrowLeft size={17} />
                            Volver a legajos
                        </Link>
                        <a
                            href={downloadUrl}
                            download
                            className="inline-flex items-center justify-center gap-2 rounded-lg bg-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#173247]"
                        >
                            <Download size={17} />
                            Descargar Word
                        </a>
                    </div>

                    <div className="mb-4 flex items-center gap-3 rounded-xl border border-[#C9D6D1] bg-white px-4 py-3 text-sm text-[#5B6B66] shadow-sm">
                        <FileText className="shrink-0 text-[#6B8F71]" size={20} />
                        <span>Esta es la previsualización del documento que se descargará.</span>
                    </div>

                    <article className="rounded-2xl border border-[#C9D6D1] bg-white px-5 py-8 shadow-[0_18px_50px_rgba(23,50,71,0.12)] sm:px-10 sm:py-10">
                        <header className="border-b border-[#C9D6D1] pb-6 text-center">
                            <p className="text-xs font-bold tracking-[0.16em] text-[#6B8F71] uppercase">
                                PRODENYA · Promoción y Protección de Derechos
                            </p>
                            <h1 className="mt-4 text-3xl font-bold text-[#173247]">
                                Legajo N.º {legajo.numero_legajo}
                            </h1>
                            <p className="mt-2 text-lg text-[#2C4A5E]">
                                {persona?.nombre ?? 'Sin persona asociada'}
                            </p>
                            <div className="mt-4 flex flex-wrap justify-center gap-2 text-xs font-bold uppercase">
                                <span className="rounded-full bg-[#E3F0E6] px-3 py-1 text-[#41634A]">
                                    Estado: {legajo.estado}
                                </span>
                                <span className="rounded-full bg-[#EDF3EF] px-3 py-1 text-[#2C4A5E]">
                                    Riesgo: {legajo.nivel_riesgo}
                                </span>
                            </div>
                            <p className="mt-4 text-xs italic text-[#5B6B66]">
                                Documento generado el {legajo.fecha_generado} por el sistema PRODENYA.
                            </p>
                        </header>

                        <div className="mt-8">
                            <Seccion titulo="Datos del legajo">
                                <DatosTabla
                                    filas={[
                                        ['Nivel de riesgo', legajo.nivel_riesgo],
                                        ['Origen de la derivación', legajo.datos_legajo.origen_derivacion],
                                        ['Profesional asignado', legajo.datos_legajo.profesional],
                                        ['Problemáticas', legajo.datos_legajo.problematicas.join(', ') || null],
                                        ['Observaciones', legajo.datos_legajo.observaciones],
                                    ]}
                                />
                            </Seccion>

                            <Seccion titulo="Datos de la persona">
                                {persona ? (
                                    <DatosTabla
                                        filas={[
                                            ['DNI', persona.dni],
                                            ['Fecha de nacimiento', persona.fecha_nacimiento],
                                            ['Edad', persona.edad === null ? null : `${persona.edad} años`],
                                            ['Género', persona.genero],
                                            ['Dirección', persona.direccion],
                                            ['Localidad', persona.localidad],
                                            ['Barrio', persona.barrio],
                                            ['Teléfono', persona.telefono],
                                            ['Email', persona.email],
                                        ]}
                                    />
                                ) : (
                                    <Vacia>No hay datos de persona asociados.</Vacia>
                                )}
                            </Seccion>

                            <Seccion titulo="Intervenciones">
                                {legajo.intervenciones.length === 0 ? (
                                    <Vacia>No se registraron intervenciones.</Vacia>
                                ) : (
                                    <div className="overflow-x-auto rounded-xl border border-[#C9D6D1]">
                                        <table className="w-full min-w-[680px] border-collapse text-sm">
                                            <thead className="bg-[#2C4A5E] text-left text-xs text-white">
                                                <tr>
                                                    {['Fecha', 'Hora', 'Tipo', 'Profesional', 'Detalle'].map((titulo) => (
                                                        <th key={titulo} className="px-3 py-3 font-bold">{titulo}</th>
                                                    ))}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {legajo.intervenciones.map((intervencion, index) => (
                                                    <tr key={`${intervencion.fecha}-${index}`} className="border-b border-[#DCE6E1] last:border-b-0 even:bg-[#F3F7F5]">
                                                        <td className="px-3 py-3">{mostrar(intervencion.fecha)}</td>
                                                        <td className="px-3 py-3">{mostrar(intervencion.hora)}</td>
                                                        <td className="px-3 py-3">{mostrar(intervencion.tipo)}</td>
                                                        <td className="px-3 py-3">{mostrar(intervencion.profesional)}</td>
                                                        <td className="px-3 py-3">{mostrar(intervencion.detalle)}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </Seccion>

                            <Seccion titulo="Derivaciones">
                                {legajo.derivaciones.length === 0 ? (
                                    <Vacia>No se registraron derivaciones.</Vacia>
                                ) : (
                                    <DatosTabla
                                        filas={legajo.derivaciones.map((derivacion, index) => [
                                            `Derivación ${index + 1}`,
                                            `${derivacion.estado} · ${derivacion.modo} · ${mostrar(derivacion.motivo)} · Destinatario: ${mostrar(derivacion.destinatario)} · Fecha límite: ${mostrar(derivacion.fecha_limite)}`,
                                        ])}
                                    />
                                )}
                            </Seccion>

                            <Seccion titulo="Vínculos familiares">
                                {legajo.vinculos.length === 0 ? (
                                    <Vacia>No se registraron vínculos familiares.</Vacia>
                                ) : (
                                    <DatosTabla filas={legajo.vinculos.map((vinculo) => [vinculo.tipo, vinculo.persona])} />
                                )}
                            </Seccion>

                            <Seccion titulo="Responsables del caso">
                                {legajo.responsables.length === 0 ? (
                                    <Vacia>No hay responsables activos asignados.</Vacia>
                                ) : (
                                    <DatosTabla filas={legajo.responsables.map((responsable) => [responsable.rol, responsable.nombre])} />
                                )}
                            </Seccion>
                        </div>
                    </article>

                    <div className="mt-5 flex justify-end">
                        <a
                            href={downloadUrl}
                            download
                            className="inline-flex items-center gap-2 rounded-lg border border-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-[#2C4A5E] transition hover:bg-[#EAF3ED]"
                        >
                            <Download size={17} />
                            Descargar este documento
                        </a>
                    </div>
                </div>
            </main>
        </>
    );
}
