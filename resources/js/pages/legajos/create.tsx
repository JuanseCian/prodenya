import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface PersonaOpcion {
    id: number;
    nombre: string;
    apellido: string;
    dni: string;
    fechaNacimiento: string;
    edad: number;
    genero: string;
    direccion: string;
    localidad: string | null;
    barrio: string | null;
    telefono: string | null;
    email: string | null;
}

interface Opcion {
    id: number;
    nombre: string;
}

interface Profesional {
    id: number;
    nombre_usuario: string;
    email: string;
}

interface CreateLegajoProps {
    personas: PersonaOpcion[];
    tiposMaltrato: Opcion[];
    profesionales: Profesional[];
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

const GENERO_LABEL: Record<string, string> = {
    M: 'Masculino',
    F: 'Femenino',
    X: 'No binario / X',
    NO_ESPECIFICA: 'No especifica',
};

export default function CreateLegajo({ personas, tiposMaltrato, profesionales }: CreateLegajoProps) {
    const { data, setData, post, processing, errors } = useForm({
        persona_id: '',
        motivo_ingreso: '',
        nivel_riesgo: '',
        origen_derivacion: '',
        profesional_id: '',
        tipos_maltrato: [] as number[],
        observaciones: '',
    });

    const personaSeleccionada = personas.find((p) => String(p.id) === String(data.persona_id));

    const previaLegajo = (() => {
        if (!personaSeleccionada) return null;
        const soloLetras = (s: string) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase().replace(/[^A-Z]/g, '');
        const ape = soloLetras(personaSeleccionada.apellido).padEnd(3, 'X').slice(0, 3);
        const nom = soloLetras(personaSeleccionada.nombre).padEnd(3, 'X').slice(0, 3);
        const dniDigitos = personaSeleccionada.dni.replace(/\D/g, '');
        const ult3 = dniDigitos.length >= 3 ? dniDigitos.slice(-3) : dniDigitos.padStart(3, '0');
        return `${ape}-${nom}-${ult3}`;
    })();

    const toggleTipoMaltrato = (id: number) => {
        setData(
            'tipos_maltrato',
            data.tipos_maltrato.includes(id)
                ? data.tipos_maltrato.filter((t) => t !== id)
                : [...data.tipos_maltrato, id],
        );
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/legajos');
    };

    const inputClass =
        'block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 outline-none transition focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';
    const labelClass = 'mb-1.5 block text-sm font-medium text-[#2B2F36]';
    const errorClass = 'mt-1.5 text-sm text-[#B1553F]';

    return (
        <>
            <Head title="Nuevo legajo">
                <style>{`
                    html, body, #app {
                        width: 100% !important;
                        max-width: none !important;
                        min-height: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: none !important;
                    }
                `}</style>
            </Head>

            <div
                style={{ position: 'fixed', inset: 0, width: '100vw', height: '100dvh', overflowY: 'auto' }}
                className="bg-[#F6F5F1]"
            >
                <header className="sticky top-0 z-10 border-b border-[#E3E0D8] bg-white">
                    <div className="mx-auto flex max-w-2xl items-center justify-between px-4 py-4 sm:px-6">
                        <h1 className="text-xl font-medium text-[#2C4A5E]" style={{ fontFamily: FONT_HEADING }}>
                            Prodenya
                        </h1>
                        <Link
                            href="/dashboard"
                            className="text-sm font-medium text-[#2B2F36]/70 hover:text-[#2B2F36]"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            ← Volver al panel
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-10" style={{ fontFamily: FONT_BODY }}>
                    <h2 className="text-2xl font-medium text-[#2C4A5E]" style={{ fontFamily: FONT_HEADING }}>
                        Nuevo legajo
                    </h2>
                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                        Elegí la persona para la que vas a abrir el legajo.
                    </p>

                    <form onSubmit={submit} className="mt-8 space-y-6">
                        {/* Selección de persona */}
                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
                            <label className={labelClass}>Persona</label>
                            <select
                                className={inputClass}
                                value={data.persona_id}
                                onChange={(e) => setData('persona_id', e.target.value)}
                            >
                                <option value="">Elegir persona…</option>
                                {personas.map((p) => (
                                    <option key={p.id} value={p.id}>
                                        {p.apellido}, {p.nombre} — DNI {p.dni}
                                    </option>
                                ))}
                            </select>
                            {errors.persona_id && <p className={errorClass}>{errors.persona_id}</p>}

                            {personaSeleccionada && (
                                <div className="mt-4 rounded-md border border-[#E3E0D8] bg-[#F6F5F1] p-4 text-sm text-[#2B2F36]">
                                    <div className="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">
                                        <p><span className="text-[#2B2F36]/50">Nombre completo:</span> {personaSeleccionada.apellido}, {personaSeleccionada.nombre}</p>
                                        <p><span className="text-[#2B2F36]/50">DNI:</span> {personaSeleccionada.dni}</p>
                                        <p><span className="text-[#2B2F36]/50">Fecha de nacimiento:</span> {personaSeleccionada.fechaNacimiento} ({personaSeleccionada.edad} años)</p>
                                        <p><span className="text-[#2B2F36]/50">Género:</span> {GENERO_LABEL[personaSeleccionada.genero] ?? personaSeleccionada.genero}</p>
                                        <p className="sm:col-span-2"><span className="text-[#2B2F36]/50">Dirección:</span> {personaSeleccionada.direccion || '—'}{personaSeleccionada.barrio ? `, ${personaSeleccionada.barrio}` : ''}{personaSeleccionada.localidad ? `, ${personaSeleccionada.localidad}` : ''}</p>
                                        <p><span className="text-[#2B2F36]/50">Teléfono:</span> {personaSeleccionada.telefono || '—'}</p>
                                        <p><span className="text-[#2B2F36]/50">Email:</span> {personaSeleccionada.email || '—'}</p>
                                    </div>
                                </div>
                            )}

                            {previaLegajo && (
                                <p className="mt-3 text-xs text-[#2B2F36]/50">
                                    Número de legajo (se genera automáticamente): <span className="font-mono font-medium text-[#2C4A5E]">{previaLegajo}</span>
                                </p>
                            )}
                        </section>

                        {/* Datos del caso */}
                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
                            <h3 className="text-base font-medium text-[#2C4A5E]" style={{ fontFamily: FONT_HEADING }}>
                                Datos del caso
                            </h3>

                            <div className="mt-4 space-y-4">
                                <div>
                                    <label className={labelClass}>Motivo de ingreso</label>
                                    <textarea
                                        className={inputClass}
                                        rows={3}
                                        value={data.motivo_ingreso}
                                        onChange={(e) => setData('motivo_ingreso', e.target.value)}
                                        placeholder="Describí el motivo por el cual se abre el legajo…"
                                    />
                                    {errors.motivo_ingreso && <p className={errorClass}>{errors.motivo_ingreso}</p>}
                                </div>

                                <div>
                                    <label className={labelClass}>Tipo de maltrato (podés elegir más de uno)</label>
                                    <div className="flex flex-wrap gap-x-5 gap-y-2 rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3">
                                        {tiposMaltrato.map((tipo) => (
                                            <label key={tipo.id} className="flex items-center gap-1.5 text-sm text-[#2B2F36]">
                                                <input
                                                    type="checkbox"
                                                    checked={data.tipos_maltrato.includes(tipo.id)}
                                                    onChange={() => toggleTipoMaltrato(tipo.id)}
                                                    className="h-4 w-4 rounded border-[#D5D1C6] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                                />
                                                {tipo.nombre}
                                            </label>
                                        ))}
                                    </div>
                                    {errors.tipos_maltrato && <p className={errorClass}>{errors.tipos_maltrato}</p>}
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className={labelClass}>Nivel de riesgo</label>
                                        <select
                                            className={inputClass}
                                            value={data.nivel_riesgo}
                                            onChange={(e) => setData('nivel_riesgo', e.target.value)}
                                        >
                                            <option value="">Elegir…</option>
                                            <option value="Bajo">Bajo</option>
                                            <option value="Medio">Medio</option>
                                            <option value="Alto">Alto</option>
                                            <option value="Critico">Crítico</option>
                                        </select>
                                        {errors.nivel_riesgo && <p className={errorClass}>{errors.nivel_riesgo}</p>}
                                    </div>

                                    <div>
                                        <label className={labelClass}>Profesional interviniente (opcional)</label>
                                        <select
                                            className={inputClass}
                                            value={data.profesional_id}
                                            onChange={(e) => setData('profesional_id', e.target.value)}
                                        >
                                            <option value="">Sin asignar</option>
                                            {profesionales.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.nombre_usuario}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label className={labelClass}>Institución o persona que deriva</label>
                                    <input
                                        className={inputClass}
                                        value={data.origen_derivacion}
                                        onChange={(e) => setData('origen_derivacion', e.target.value)}
                                        placeholder="Ej: Escuela N°12, Hospital Municipal, denuncia anónima…"
                                    />
                                    {errors.origen_derivacion && <p className={errorClass}>{errors.origen_derivacion}</p>}
                                </div>

                                <div>
                                    <label className={labelClass}>Observaciones (opcional)</label>
                                    <textarea
                                        className={inputClass}
                                        rows={3}
                                        value={data.observaciones}
                                        onChange={(e) => setData('observaciones', e.target.value)}
                                    />
                                </div>
                            </div>
                        </section>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-md bg-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#233B4B] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Guardando…' : 'Crear legajo'}
                        </button>
                    </form>
                </main>
            </div>
        </>
    );
}
