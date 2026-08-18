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

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

export default function CreateLegajo({ personas }: CreateLegajoProps) {
    const { data, setData, post, processing, errors } = useForm({
        persona_id: '',
        motivo_ingreso: '',
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
                        <h1
                            className="text-xl font-medium text-[#2C4A5E]"
                            style={{ fontFamily: FONT_HEADING }}
                        >
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
                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
                            <label className={labelClass}>Buscar persona por nombre o DNI</label>
                            <input
                                className={inputClass}
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder="Ej: García o 30111222"
                            />

                            <div className="mt-3 max-h-56 overflow-y-auto rounded-md border border-[#E3E0D8]">
                                {personasFiltradas.length === 0 ? (
                                    <p className="p-3 text-sm text-[#2B2F36]/50">No se encontraron personas.</p>
                                ) : (
                                    personasFiltradas.map((p) => (
                                        <label
                                            key={p.id}
                                            className={`flex cursor-pointer items-center justify-between border-b border-[#F0EEE7] px-3 py-2 text-sm last:border-0 hover:bg-[#F6F5F1] ${
                                                String(data.persona_id) === String(p.id) ? 'bg-[#F6F5F1]' : ''
                                            }`}
                                        >
                                            <span className="text-[#2B2F36]">
                                                {p.apellido}, {p.nombre}{' '}
                                                <span className="text-[#2B2F36]/50">— DNI {p.dni}</span>
                                            </span>
                                            <input
                                                type="radio"
                                                name="persona_id"
                                                checked={String(data.persona_id) === String(p.id)}
                                                onChange={() => setData('persona_id', String(p.id))}
                                                className="h-4 w-4 text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                            />
                                        </label>
                                    ))
                                )}
                            </div>
                            {errors.persona_id && <p className={errorClass}>{errors.persona_id}</p>}

                            {previaLegajo && (
                                <p className="mt-3 text-xs text-[#2B2F36]/50">
                                    Número de legajo (se genera automáticamente): <span className="font-mono font-medium text-[#2C4A5E]">{previaLegajo}</span>
                                </p>
                            )}
                        </section>

                        <section className="rounded-lg border border-[#E3E0D8] bg-white p-6">
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
