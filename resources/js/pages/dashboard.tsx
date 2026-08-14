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
                className="bg-[#FBF8F4]"
            >
                {/* Header */}
                <header className="sticky top-0 z-10 border-b border-[#EAE3F5] bg-white/80 backdrop-blur-xl">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                        <div>
                            <span
                                className="text-[0.65rem] font-semibold tracking-[0.2em] text-[#9B87C4] uppercase"
                                style={{ fontFamily: "'Inter', sans-serif" }}
                            >
                                Sistema de gestión
                            </span>
                            <h1
                                className="text-xl font-semibold text-[#4A3F55] sm:text-2xl"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Prodenya
                            </h1>
                        </div>

                        <div className="flex items-center gap-4">
                            <span
                                className="hidden text-sm text-[#4A3F55]/70 sm:inline"
                                style={{ fontFamily: "'Inter', sans-serif" }}
                            >
                                Hola, {auth.user?.nombre_usuario ?? auth.user?.email}
                            </span>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="flex items-center gap-1.5 rounded-full border border-[#D8C9F0]/60 bg-white px-3.5 py-1.5 text-sm font-medium text-[#4A3F55] transition hover:bg-[#F5E9FB]"
                                style={{ fontFamily: "'Inter', sans-serif" }}
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
                        className="text-xl font-semibold text-[#4A3F55] sm:text-2xl"
                        style={{ fontFamily: "'Poppins', sans-serif" }}
                    >
                        Panel general
                    </h2>
                    <div className="mt-1 flex items-center justify-between">
                        <p
                            className="text-sm text-[#4A3F55]/60"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                        >
                            Resumen del estado actual del sistema.
                        </p>
                        <div className="flex gap-2">
                            <Link
                                href="/personas/nueva"
                                className="rounded-full bg-gradient-to-r from-[#C9B6E4] to-[#F6C6A6] px-4 py-2 text-sm font-semibold text-[#4A3F55] shadow-sm transition hover:brightness-105"
                                style={{ fontFamily: "'Inter', sans-serif" }}
                            >
                                + Nueva persona
                            </Link>
                            <Link
                                href="/legajos/nuevo"
                                className="rounded-full border border-[#D8C9F0]/60 bg-white px-4 py-2 text-sm font-medium text-[#4A3F55] transition hover:bg-[#F5E9FB]"
                                style={{ fontFamily: "'Inter', sans-serif" }}
                            >
                                + Nuevo legajo
                            </Link>
                        </div>
                    </div>

                    {/* Personas recientes */}
                    <div className="mt-6 rounded-2xl border border-white bg-white p-5 shadow-sm shadow-[#D8C9F0]/10 sm:p-6">
                        <h3
                            className="text-base font-semibold text-[#4A3F55]"
                            style={{ fontFamily: "'Poppins', sans-serif" }}
                        >
                            Altas recientes
                        </h3>

                        {personasRecientes.length === 0 ? (
                            <p
                                className="mt-4 text-sm text-[#4A3F55]/50"
                                style={{ fontFamily: "'Inter', sans-serif" }}
                            >
                                Todavía no hay personas registradas.
                            </p>
                        ) : (
                            <div className="mt-4 overflow-x-auto">
                                <table className="w-full text-left text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>
                                    <thead>
                                        <tr className="border-b border-[#EAE3F5] text-[#4A3F55]/50">
                                            <th className="py-2 pr-4 font-medium">Nombre</th>
                                            <th className="py-2 pr-4 font-medium">DNI</th>
                                            <th className="py-2 pr-4 font-medium">Localidad</th>
                                            <th className="py-2 pr-4 font-medium">Fecha de alta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {personasRecientes.map((persona) => (
                                            <tr key={persona.id} className="border-b border-[#F5F1FA] last:border-0">
                                                <td className="py-2.5 pr-4 text-[#4A3F55]">{persona.nombreCompleto}</td>
                                                <td className="py-2.5 pr-4 text-[#4A3F55]/70">{persona.dni}</td>
                                                <td className="py-2.5 pr-4 text-[#4A3F55]/70">{persona.localidad ?? '—'}</td>
                                                <td className="py-2.5 pr-4 text-[#4A3F55]/70">{persona.fechaAlta}</td>
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
