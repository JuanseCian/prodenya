import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';

interface Opcion {
    id: number;
    nombre: string;
}

interface CreatePersonaProps {
    provincias: Opcion[];
}

export default function CreatePersona({ provincias }: CreatePersonaProps) {
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
        numero_legajo: '',
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

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/personas');
    };

    const inputClass =
        'block w-full rounded-xl border border-[#D8C9F0]/60 bg-white/80 px-3.5 py-2 text-sm text-[#4A3F55] placeholder-[#4A3F55]/30 shadow-sm outline-none transition focus:border-[#B49BDE] focus:ring-4 focus:ring-[#D8C9F0]/40';
    const labelClass = 'mb-1.5 block text-sm font-medium text-[#4A3F55]';

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
                className="bg-[#FBF8F4]"
            >
                <header className="sticky top-0 z-10 border-b border-[#EAE3F5] bg-white/80 backdrop-blur-xl">
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
                        <h1
                            className="text-xl font-semibold text-[#4A3F55]"
                            style={{ fontFamily: "'Poppins', sans-serif" }}
                        >
                            Prodenya
                        </h1>
                        <Link
                            href="/dashboard"
                            className="text-sm font-medium text-[#4A3F55]/70 hover:text-[#4A3F55]"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                        >
                            ← Volver al panel
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-10" style={{ fontFamily: "'Inter', sans-serif" }}>
                    <h2 className="text-2xl font-semibold text-[#4A3F55]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                        Nueva persona
                    </h2>
                    <p className="mt-1 text-sm text-[#4A3F55]/60">Cargá los datos de la persona a registrar.</p>

                    <form onSubmit={submit} className="mt-8 space-y-8">
                        {/* Datos personales */}
                        <section className="rounded-2xl border border-white bg-white p-6 shadow-sm shadow-[#D8C9F0]/10">
                            <h3 className="text-base font-semibold text-[#4A3F55]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                                Datos personales
                            </h3>

                            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className={labelClass}>Nombre</label>
                                    <input
                                        className={inputClass}
                                        value={data.nombre}
                                        onChange={(e) => setData('nombre', e.target.value)}
                                    />
                                    {errors.nombre && <p className="mt-1 text-sm text-[#D97A6C]">{errors.nombre}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Apellido</label>
                                    <input
                                        className={inputClass}
                                        value={data.apellido}
                                        onChange={(e) => setData('apellido', e.target.value)}
                                    />
                                    {errors.apellido && <p className="mt-1 text-sm text-[#D97A6C]">{errors.apellido}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>DNI</label>
                                    <input
                                        className={inputClass}
                                        value={data.dni}
                                        onChange={(e) => setData('dni', e.target.value)}
                                    />
                                    {errors.dni && <p className="mt-1 text-sm text-[#D97A6C]">{errors.dni}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Fecha de nacimiento</label>
                                    <input
                                        type="date"
                                        className={inputClass}
                                        value={data.fecha_nacimiento}
                                        onChange={(e) => setData('fecha_nacimiento', e.target.value)}
                                    />
                                    {errors.fecha_nacimiento && (
                                        <p className="mt-1 text-sm text-[#D97A6C]">{errors.fecha_nacimiento}</p>
                                    )}
                                </div>
                                <div>
                                    <label className={labelClass}>Género</label>
                                    <select
                                        className={inputClass}
                                        value={data.genero}
                                        onChange={(e) => setData('genero', e.target.value)}
                                    >
                                        <option value="NO_ESPECIFICA">Prefiere no especificar</option>
                                        <option value="F">Femenino</option>
                                        <option value="M">Masculino</option>
                                        <option value="X">No binario / X</option>
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClass}>Teléfono</label>
                                    <input
                                        className={inputClass}
                                        value={data.telefono}
                                        onChange={(e) => setData('telefono', e.target.value)}
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <label className={labelClass}>Email</label>
                                    <input
                                        type="email"
                                        className={inputClass}
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                    />
                                </div>
                            </div>
                        </section>

                        {/* Dirección */}
                        <section className="rounded-2xl border border-white bg-white p-6 shadow-sm shadow-[#D8C9F0]/10">
                            <h3 className="text-base font-semibold text-[#4A3F55]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                                Dirección
                            </h3>

                            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div className="sm:col-span-2">
                                    <label className={labelClass}>Calle</label>
                                    <input
                                        className={inputClass}
                                        value={data.direccion_calle}
                                        onChange={(e) => setData('direccion_calle', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className={labelClass}>Número</label>
                                    <input
                                        className={inputClass}
                                        value={data.direccion_numero}
                                        onChange={(e) => setData('direccion_numero', e.target.value)}
                                    />
                                </div>
                                <div className="sm:col-span-3">
                                    <label className={labelClass}>Piso / depto (opcional)</label>
                                    <input
                                        className={inputClass}
                                        value={data.direccion_piso_depto}
                                        onChange={(e) => setData('direccion_piso_depto', e.target.value)}
                                    />
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
                                            <option key={p.id} value={p.id}>
                                                {p.nombre}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.provincia_id && <p className="mt-1 text-sm text-[#D97A6C]">{errors.provincia_id}</p>}
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
                                            <option key={l.id} value={l.id}>
                                                {l.nombre}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.localidad_id && <p className="mt-1 text-sm text-[#D97A6C]">{errors.localidad_id}</p>}
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
                                            <option key={b.id} value={b.id}>
                                                {b.nombre}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </section>

                        {/* Observaciones */}
                        <section className="rounded-2xl border border-white bg-white p-6 shadow-sm shadow-[#D8C9F0]/10">
                            <label className={labelClass}>Observaciones (opcional)</label>
                            <textarea
                                className={inputClass}
                                rows={3}
                                value={data.observaciones}
                                onChange={(e) => setData('observaciones', e.target.value)}
                            />
                        </section>

                        {/* Legajo opcional */}
                        <section className="rounded-2xl border border-[#D8C9F0]/40 bg-[#F5E9FB]/40 p-6">
                            <label className="flex items-center gap-2 text-sm font-medium text-[#4A3F55]">
                                <input
                                    type="checkbox"
                                    checked={data.abrir_legajo}
                                    onChange={(e) => setData('abrir_legajo', e.target.checked)}
                                    className="h-4 w-4 rounded border-[#D8C9F0] text-[#B49BDE] focus:ring-[#D8C9F0]"
                                />
                                Abrir legajo para esta persona ahora
                            </label>

                            {data.abrir_legajo && (
                                <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className={labelClass}>Número de legajo</label>
                                        <input
                                            className={inputClass}
                                            value={data.numero_legajo}
                                            onChange={(e) => setData('numero_legajo', e.target.value)}
                                            placeholder="Ej: LEG-2026-001"
                                        />
                                        {errors.numero_legajo && (
                                            <p className="mt-1 text-sm text-[#D97A6C]">{errors.numero_legajo}</p>
                                        )}
                                    </div>
                                    <div className="sm:col-span-2">
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
                            className="w-full rounded-full bg-gradient-to-r from-[#C9B6E4] to-[#F6C6A6] px-4 py-2.5 text-sm font-semibold text-[#4A3F55] shadow-md shadow-[#D8C9F0]/50 transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Guardando…' : 'Registrar persona'}
                        </button>
                    </form>
                </main>
            </div>
        </>
    );
}
