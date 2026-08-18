import { Head, Link, usePage } from '@inertiajs/react';
import { LogOut } from 'lucide-react';

interface PersonaReciente {
    id: number;
    nombreCompleto: string;
    dni: string;
    localidad: string | null;
    fechaAlta: string;
}

interface DashboardProps {
    personasRecientes: PersonaReciente[];
}

interface AuthUser {
    nombre_usuario?: string;
    email?: string;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

export default function Dashboard({ personasRecientes }: DashboardProps) {
    const { auth } = usePage<{ auth: { user: AuthUser | null } }>().props;

    return (
        <>
            <Head title="Panel general">
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
                {/* Header */}
                <header className="sticky top-0 z-10 border-b border-[#E3E0D8] bg-white">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                        <div>
                            <span
                                className="text-[0.65rem] font-semibold tracking-[0.2em] text-[#6B8F71] uppercase"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Sistema de gestión
                            </span>
                            <h1
                                className="text-xl font-medium text-[#2C4A5E] sm:text-2xl"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Prodenya
                            </h1>
                        </div>

                        <div className="flex items-center gap-4">
                            <span
                                className="hidden text-sm text-[#2B2F36]/70 sm:inline"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Hola, {auth.user?.nombre_usuario ?? auth.user?.email}
                            </span>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="flex items-center gap-1.5 rounded-md border border-[#D5D1C6] bg-white px-3.5 py-1.5 text-sm font-medium text-[#2B2F36] transition hover:bg-[#F6F5F1]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                <LogOut size={15} />
                                Salir
                            </Link>
                        </div>
                    </div>
                </header>

                {/* Contenido */}
                <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
                    <h2
                        className="text-xl font-medium text-[#2C4A5E] sm:text-2xl"
                        style={{ fontFamily: FONT_HEADING }}
                    >
                        Panel general
                    </h2>

                    <div className="mt-1 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p
                            className="text-sm text-[#2B2F36]/60"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            Resumen del estado actual del sistema.
                        </p>
                        <div className="flex gap-2">
                            <Link
                                href="/personas/nueva"
                                className="rounded-md bg-[#2C4A5E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#233B4B]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                + Nueva persona
                            </Link>
                            <Link
                                href="/legajos/nuevo"
                                className="rounded-md border border-[#D5D1C6] bg-white px-4 py-2 text-sm font-medium text-[#2B2F36] transition hover:bg-[#F6F5F1]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                + Nuevo legajo
                            </Link>
                        </div>
                    </div>

                    {/* Personas recientes */}
                    <div className="mt-6 rounded-lg border border-[#E3E0D8] bg-white p-5 sm:p-6">
                        <h3
                            className="text-base font-medium text-[#2C4A5E]"
                            style={{ fontFamily: FONT_HEADING }}
                        >
                            Altas recientes
                        </h3>

                        {personasRecientes.length === 0 ? (
                            <p
                                className="mt-4 text-sm text-[#2B2F36]/50"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Todavía no hay personas registradas.
                            </p>
                        ) : (
                            <div className="mt-4 overflow-x-auto">
                                <table className="w-full text-left text-sm" style={{ fontFamily: FONT_BODY }}>
                                    <thead>
                                        <tr className="border-b border-[#E3E0D8] text-[#2B2F36]/50">
                                            <th className="py-2 pr-4 font-medium">Nombre</th>
                                            <th className="py-2 pr-4 font-medium">DNI</th>
                                            <th className="py-2 pr-4 font-medium">Localidad</th>
                                            <th className="py-2 pr-4 font-medium">Fecha de alta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {personasRecientes.map((persona) => (
                                            <tr key={persona.id} className="border-b border-[#F0EEE7] last:border-0">
                                                <td className="py-2.5 pr-4 text-[#2B2F36]">{persona.nombreCompleto}</td>
                                                <td className="py-2.5 pr-4 text-[#2B2F36]/70">{persona.dni}</td>
                                                <td className="py-2.5 pr-4 text-[#2B2F36]/70">{persona.localidad ?? '—'}</td>
                                                <td className="py-2.5 pr-4 text-[#2B2F36]/70">{persona.fechaAlta}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
