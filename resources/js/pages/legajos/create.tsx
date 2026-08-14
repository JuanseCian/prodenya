import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useMemo, useState } from 'react';

interface PersonaOpcion {
    id: number;
    nombre: string;
    apellido: string;
    dni: string;
}

interface CreateLegajoProps {
    personas: PersonaOpcion[];
}

export default function CreateLegajo({ personas }: CreateLegajoProps) {
    const { data, setData, post, processing, errors } = useForm({
        persona_id: '',
        numero_legajo: '',
        observaciones: '',
    });

    const [busqueda, setBusqueda] = useState('');

    const personasFiltradas = useMemo(() => {
        const q = busqueda.trim().toLowerCase();
        if (!q) return personas;
        return personas.filter(
            (p) =>
                `${p.nombre} ${p.apellido}`.toLowerCase().includes(q) ||
                p.dni.includes(q),
        );
    }, [busqueda, personas]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/legajos');
    };

    const inputClass =
        'block w-full rounded-xl border border-[#D8C9F0]/60 bg-white/80 px-3.5 py-2 text-sm text-[#4A3F55] placeholder-[#4A3F55]/30 shadow-sm outline-none transition focus:border-[#B49BDE] focus:ring-4 focus:ring-[#D8C9F0]/40';
    const labelClass = 'mb-1.5 block text-sm font-medium text-[#4A3F55]';

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
                className="bg-[#FBF8F4]"
            >
                <header className="sticky top-0 z-10 border-b border-[#EAE3F5] bg-white/80 backdrop-blur-xl">
                    <div className="mx-auto flex max-w-2xl items-center justify-between px-4 py-4 sm:px-6">
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

                <main className="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-10" style={{ fontFamily: "'Inter', sans-serif" }}>
                    <h2 className="text-2xl font-semibold text-[#4A3F55]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                        Nuevo legajo
                    </h2>
                    <p className="mt-1 text-sm text-[#4A3F55]/60">
                        Elegí la persona para la que vas a abrir el legajo.
                    </p>

                    <form onSubmit={submit} className="mt-8 space-y-6">
                        <section className="rounded-2xl border border-white bg-white p-6 shadow-sm shadow-[#D8C9F0]/10">
                            <label className={labelClass}>Buscar persona por nombre o DNI</label>
                            <input
                                className={inputClass}
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder="Ej: García o 30111222"
                            />

                            <div className="mt-3 max-h-56 overflow-y-auto rounded-xl border border-[#EAE3F5]">
                                {personasFiltradas.length === 0 ? (
                                    <p className="p-3 text-sm text-[#4A3F55]/50">No se encontraron personas.</p>
                                ) : (
                                    personasFiltradas.map((p) => (
                                        <label
                                            key={p.id}
                                            className={`flex cursor-pointer items-center justify-between border-b border-[#F5F1FA] px-3 py-2 text-sm last:border-0 hover:bg-[#F5E9FB]/50 ${
                                                String(data.persona_id) === String(p.id) ? 'bg-[#F5E9FB]' : ''
                                            }`}
                                        >
                                            <span className="text-[#4A3F55]">
                                                {p.apellido}, {p.nombre}{' '}
                                                <span className="text-[#4A3F55]/50">— DNI {p.dni}</span>
                                            </span>
                                            <input
                                                type="radio"
                                                name="persona_id"
                                                checked={String(data.persona_id) === String(p.id)}
                                                onChange={() => setData('persona_id', String(p.id))}
                                                className="h-4 w-4 text-[#B49BDE] focus:ring-[#D8C9F0]"
                                            />
                                        </label>
                                    ))
                                )}
                            </div>
                            {errors.persona_id && <p className="mt-1.5 text-sm text-[#D97A6C]">{errors.persona_id}</p>}
                        </section>

                        <section className="rounded-2xl border border-white bg-white p-6 shadow-sm shadow-[#D8C9F0]/10">
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
                            <div className="mt-4">
                                <label className={labelClass}>Observaciones (opcional)</label>
                                <textarea
                                    className={inputClass}
                                    rows={3}
                                    value={data.observaciones}
                                    onChange={(e) => setData('observaciones', e.target.value)}
                                />
                            </div>
                        </section>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-full bg-gradient-to-r from-[#C9B6E4] to-[#F6C6A6] px-4 py-2.5 text-sm font-semibold text-[#4A3F55] shadow-md shadow-[#D8C9F0]/50 transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Guardando…' : 'Crear legajo'}
                        </button>
                    </form>
                </main>
            </div>
        </>
    );
}
