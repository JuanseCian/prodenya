import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';

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
    tiposMaltrato: Opcion[];
    profesionales: Profesional[];
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

export default function CreatePersona({ provincias, tiposMaltrato, profesionales }: CreatePersonaProps) {
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
        legajo_tipos_maltrato: [] as number[],
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
            .then((r) => r.json())
            .then(setLocalidades);
    }, [data.provincia_id]);

    useEffect(() => {
        if (!data.localidad_id) {
            setBarrios([]);
            return;
        }
        fetch(`/api/barrios?localidad_id=${data.localidad_id}`)
            .then((r) => r.json())
            .then(setBarrios);
    }, [data.localidad_id]);

    const toggleTipoMaltrato = (id: number) => {
        setData(
            'legajo_tipos_maltrato',
            data.legajo_tipos_maltrato.includes(id)
                ? data.legajo_tipos_maltrato.filter((t) => t !== id)
                : [...data.legajo_tipos_maltrato, id],
        );
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/personas');
    };

    const inputClass =
        'block w-full rounded-md border border-[#D5D1C6] bg-white px-3.5 py-2 text-sm text-[#2B2F36] placeholder-[#2B2F36]/30 outline-none transition focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';
    const labelClass = 'mb-1.5 block text-sm font-medium text-[#2B2F36]';
    const errorClass = 'mt-1 text-sm text-[#B1553F]';

    // Vista previa informativa del número de legajo (el definitivo se genera en el servidor)
    const previaLegajo = (() => {
        const soloLetras = (s: string) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase().replace(/[^A-Z]/g, '');
        const ape = soloLetras(data.apellido).padEnd(3, 'X').slice(0, 3);
        const nom = soloLetras(data.nombre).padEnd(3, 'X').slice(0, 3);
        const dniDigitos = data.dni.replace(/\D/g, '');
        const ult3 = dniDigitos.length >= 3 ? dniDigitos.slice(-3) : dniDigitos.padStart(3, '0');
        if (!data.apellido || !data.nombre || !data.dni) return null;
        return `${ape}-${nom}-${ult3}`;
    })();

    return (
        <>
            <Head title="Nueva persona">
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
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
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

                <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-10" style={{ fontFamily: FONT_BODY }}>
                    <h2 className="text-2xl font-medium text-[#2C4A5E]" style={{ fontFamily: FONT_HEADING }}>
                        Nueva persona
                    </h2>
                    <p className="mt-1 text-sm text-[#2B2F36]/60">Cargá los datos de la persona a registrar.</p>

                    <form onSubmit={submit} className="mt-8 space-y-6">
                        {/* Datos personales */}
                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
                            <h3 className="text-base font-medium text-[#2C4A5E]" style={{ fontFamily: FONT_HEADING }}>
                                Datos personales
                            </h3>

                            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className={labelClass}>Nombre</label>
                                    <input className={inputClass} value={data.nombre} onChange={(e) => setData('nombre', e.target.value)} />
                                    {errors.nombre && <p className={errorClass}>{errors.nombre}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Apellido</label>
                                    <input className={inputClass} value={data.apellido} onChange={(e) => setData('apellido', e.target.value)} />
                                    {errors.apellido && <p className={errorClass}>{errors.apellido}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>DNI</label>
                                    <input className={inputClass} value={data.dni} onChange={(e) => setData('dni', e.target.value)} />
                                    {errors.dni && <p className={errorClass}>{errors.dni}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Fecha de nacimiento</label>
                                    <input
                                        type="date"
                                        className={inputClass}
                                        value={data.fecha_nacimiento}
                                        onChange={(e) => setData('fecha_nacimiento', e.target.value)}
                                    />
                                    {errors.fecha_nacimiento && <p className={errorClass}>{errors.fecha_nacimiento}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Género</label>
                                    <select className={inputClass} value={data.genero} onChange={(e) => setData('genero', e.target.value)}>
                                        <option value="NO_ESPECIFICA">Prefiere no especificar</option>
                                        <option value="F">Femenino</option>
                                        <option value="M">Masculino</option>
                                        <option value="X">No binario / X</option>
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClass}>Teléfono</label>
                                    <input className={inputClass} value={data.telefono} onChange={(e) => setData('telefono', e.target.value)} />
                                </div>
                                <div className="sm:col-span-2">
                                    <label className={labelClass}>Email</label>
                                    <input type="email" className={inputClass} value={data.email} onChange={(e) => setData('email', e.target.value)} />
                                </div>
                            </div>
                        </section>

                        {/* Dirección */}
                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
                            <h3 className="text-base font-medium text-[#2C4A5E]" style={{ fontFamily: FONT_HEADING }}>
                                Dirección
                            </h3>

                            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div className="sm:col-span-2">
                                    <label className={labelClass}>Calle</label>
                                    <input className={inputClass} value={data.direccion_calle} onChange={(e) => setData('direccion_calle', e.target.value)} />
                                </div>
                                <div>
                                    <label className={labelClass}>Número</label>
                                    <input className={inputClass} value={data.direccion_numero} onChange={(e) => setData('direccion_numero', e.target.value)} />
                                </div>
                                <div className="sm:col-span-3">
                                    <label className={labelClass}>Piso / depto (opcional)</label>
                                    <input className={inputClass} value={data.direccion_piso_depto} onChange={(e) => setData('direccion_piso_depto', e.target.value)} />
                                </div>

                                <div>
                                    <label className={labelClass}>Provincia</label>
                                    <select
                                        className={inputClass}
                                        value={data.provincia_id}
                                        onChange={(e) => {
                                            setData('provincia_id', e.target.value);
                                            setData('localidad_id', '');
                                            setData('barrio_id', '');
                                        }}
                                    >
                                        <option value="">Elegir…</option>
                                        {provincias.map((p) => (
                                            <option key={p.id} value={p.id}>{p.nombre}</option>
                                        ))}
                                    </select>
                                    {errors.provincia_id && <p className={errorClass}>{errors.provincia_id}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Localidad</label>
                                    <select
                                        className={inputClass}
                                        value={data.localidad_id}
                                        disabled={!data.provincia_id}
                                        onChange={(e) => {
                                            setData('localidad_id', e.target.value);
                                            setData('barrio_id', '');
                                        }}
                                    >
                                        <option value="">Elegir…</option>
                                        {localidades.map((l) => (
                                            <option key={l.id} value={l.id}>{l.nombre}</option>
                                        ))}
                                    </select>
                                    {errors.localidad_id && <p className={errorClass}>{errors.localidad_id}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Barrio (opcional)</label>
                                    <select
                                        className={inputClass}
                                        value={data.barrio_id}
                                        disabled={!data.localidad_id}
                                        onChange={(e) => setData('barrio_id', e.target.value)}
                                    >
                                        <option value="">Elegir…</option>
                                        {barrios.map((b) => (
                                            <option key={b.id} value={b.id}>{b.nombre}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </section>

                        {/* Observaciones */}
                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
                            <label className={labelClass}>Observaciones (opcional)</label>
                            <textarea className={inputClass} rows={3} value={data.observaciones} onChange={(e) => setData('observaciones', e.target.value)} />
                        </section>

                        {/* Legajo opcional */}
                        <section className="rounded-lg border border-[#2C4A5E]/25 bg-[#2C4A5E]/[0.03] p-6">
                            <label className="flex items-center gap-2 text-sm font-medium text-[#2B2F36]">
                                <input
                                    type="checkbox"
                                    checked={data.abrir_legajo}
                                    onChange={(e) => setData('abrir_legajo', e.target.checked)}
                                    className="h-4 w-4 rounded border-[#D5D1C6] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                />
                                Abrir legajo para esta persona ahora
                            </label>

                            {data.abrir_legajo && (
                                <div className="mt-4 space-y-4">
                                    {previaLegajo && (
                                        <p className="text-xs text-[#2B2F36]/50">
                                            Número de legajo (se genera automáticamente): <span className="font-mono font-medium text-[#2C4A5E]">{previaLegajo}</span>
                                        </p>
                                    )}

                                    <div>
                                        <label className={labelClass}>Motivo de ingreso</label>
                                        <textarea
                                            className={inputClass}
                                            rows={3}
                                            value={data.legajo_motivo_ingreso}
                                            onChange={(e) => setData('legajo_motivo_ingreso', e.target.value)}
                                            placeholder="Describí el motivo por el cual se abre el legajo…"
                                        />
                                        {errors.legajo_motivo_ingreso && <p className={errorClass}>{errors.legajo_motivo_ingreso}</p>}
                                    </div>

                                    <div>
                                        <label className={labelClass}>Tipo de maltrato (podés elegir más de uno)</label>
                                        <div className="flex flex-wrap gap-x-5 gap-y-2 rounded-md border border-[#D5D1C6] bg-white px-3.5 py-3">
                                            {tiposMaltrato.map((tipo) => (
                                                <label key={tipo.id} className="flex items-center gap-1.5 text-sm text-[#2B2F36]">
                                                    <input
                                                        type="checkbox"
                                                        checked={data.legajo_tipos_maltrato.includes(tipo.id)}
                                                        onChange={() => toggleTipoMaltrato(tipo.id)}
                                                        className="h-4 w-4 rounded border-[#D5D1C6] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                                    />
                                                    {tipo.nombre}
                                                </label>
                                            ))}
                                        </div>
                                        {errors.legajo_tipos_maltrato && <p className={errorClass}>{errors.legajo_tipos_maltrato}</p>}
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label className={labelClass}>Nivel de riesgo</label>
                                            <select
                                                className={inputClass}
                                                value={data.legajo_nivel_riesgo}
                                                onChange={(e) => setData('legajo_nivel_riesgo', e.target.value)}
                                            >
                                                <option value="">Elegir…</option>
                                                <option value="Bajo">Bajo</option>
                                                <option value="Medio">Medio</option>
                                                <option value="Alto">Alto</option>
                                                <option value="Critico">Crítico</option>
                                            </select>
                                            {errors.legajo_nivel_riesgo && <p className={errorClass}>{errors.legajo_nivel_riesgo}</p>}
                                        </div>

                                        <div>
                                            <label className={labelClass}>Profesional interviniente (opcional)</label>
                                            <select
                                                className={inputClass}
                                                value={data.legajo_profesional_id}
                                                onChange={(e) => setData('legajo_profesional_id', e.target.value)}
                                            >
                                                <option value="">Sin asignar</option>
                                                {profesionales.map((p) => (
                                                    <option key={p.id} value={p.id}>{p.nombre_usuario}</option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label className={labelClass}>Institución o persona que deriva</label>
                                        <input
                                            className={inputClass}
                                            value={data.legajo_origen_derivacion}
                                            onChange={(e) => setData('legajo_origen_derivacion', e.target.value)}
                                            placeholder="Ej: Escuela N°12, Hospital Municipal, denuncia anónima…"
                                        />
                                        {errors.legajo_origen_derivacion && <p className={errorClass}>{errors.legajo_origen_derivacion}</p>}
                                    </div>

                                    <div>
                                        <label className={labelClass}>Observaciones del legajo (opcional)</label>
                                        <textarea
                                            className={inputClass}
                                            rows={2}
                                            value={data.legajo_observaciones}
                                            onChange={(e) => setData('legajo_observaciones', e.target.value)}
                                        />
                                    </div>
                                </div>
                            )}
                        </section>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-md bg-[#2C4A5E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#233B4B] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Guardando…' : 'Registrar persona'}
                        </button>
                    </form>
                </main>
            </div>
        </>
    );
}
