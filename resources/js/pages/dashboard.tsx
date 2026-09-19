import { Head, Link, usePage } from '@inertiajs/react';
import { ClipboardList, FileText, LogOut, Plus, UserRound } from 'lucide-react';

interface PersonaReciente {
    id: number;
    nombreCompleto: string;
    dni: string;
    localidad: string | null;
    fechaAlta: string;
}

interface DashboardProps {
    personasRecientes: PersonaReciente[];
    legajosRecientes: LegajoReciente[];
}

interface LegajoReciente {
    id: number;
    numero: string;
    persona: string;
    dni: string;
    riesgo: 'Bajo' | 'Medio' | 'Alto' | 'Critico';
    estado: 'Abierto' | 'Cerrado' | 'Archivado';
    fechaApertura: string;
    motivo: string;
}

interface AuthUser {
    nombre_usuario?: string;
    email?: string;
}

const FONT_HEADING = "'Lora', serif";
const FONT_BODY = "'Public Sans', sans-serif";

function RiskBadge({ risk }: { risk: LegajoReciente['riesgo'] }) {
    const styles = {
        Bajo: 'bg-[#E8F0E9] text-[#41634A]',
        Medio: 'bg-[#F5EEDB] text-[#8A6A27]',
        Alto: 'bg-[#F8E6D9] text-[#9A4F2E]',
        Critico: 'bg-[#F5D9D6] text-[#8C3029]',
    };

    return (
        <span
            className={`rounded-full px-2 py-0.5 text-[0.68rem] font-semibold ${styles[risk]}`}
        >
            {risk}
        </span>
    );
}

export default function Dashboard({
    personasRecientes,
    legajosRecientes,
}: DashboardProps) {
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
                style={{
                    position: 'fixed',
                    inset: 0,
                    width: '100vw',
                    height: '100dvh',
                    overflowY: 'auto',
                    colorScheme: 'light',
                }}
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
                                Hola,{' '}
                                {auth.user?.nombre_usuario ?? auth.user?.email}
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
                                className="inline-flex items-center gap-2 rounded-md bg-[#2C4A5E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#233B4B]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                <Plus size={16} />
                                Nueva persona
                            </Link>
                            <Link
                                href="/legajos/nuevo"
                                className="inline-flex items-center gap-2 rounded-md border border-[#D5D1C6] bg-white px-4 py-2 text-sm font-medium text-[#2B2F36] transition hover:bg-[#F6F5F1]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                <ClipboardList size={16} />
                                Nuevo legajo
                            </Link>
                        </div>
                    </div>

                    <div className="mt-8 grid gap-6 xl:grid-cols-[1fr_1.15fr]">
                        {/* Personas recientes */}
                        <div className="rounded-xl border border-[#E3E0D8] bg-white p-5 shadow-[0_10px_30px_rgba(44,74,94,0.04)] sm:p-6">
                            <h3
                                className="flex items-center gap-2 text-base font-medium text-[#2C4A5E]"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                <UserRound size={18} strokeWidth={1.8} />
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
                                    <table
                                        className="w-full text-left text-sm"
                                        style={{ fontFamily: FONT_BODY }}
                                    >
                                        <thead>
                                            <tr className="border-b border-[#E3E0D8] text-[#2B2F36]/50">
                                                <th className="py-2 pr-4 font-medium">
                                                    Nombre
                                                </th>
                                                <th className="py-2 pr-4 font-medium">
                                                    DNI
                                                </th>
                                                <th className="py-2 pr-4 font-medium">
                                                    Localidad
                                                </th>
                                                <th className="py-2 pr-4 font-medium">
                                                    Fecha de alta
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {personasRecientes.map(
                                                (persona) => (
                                                    <tr
                                                        key={persona.id}
                                                        className="border-b border-[#F0EEE7] last:border-0"
                                                    >
                                                        <td className="py-2.5 pr-4 text-[#2B2F36]">
                                                            {
                                                                persona.nombreCompleto
                                                            }
                                                        </td>
                                                        <td className="py-2.5 pr-4 text-[#2B2F36]/70">
                                                            {persona.dni}
                                                        </td>
                                                        <td className="py-2.5 pr-4 text-[#2B2F36]/70">
                                                            {persona.localidad ??
                                                                '—'}
                                                        </td>
                                                        <td className="py-2.5 pr-4 text-[#2B2F36]/70">
                                                            {persona.fechaAlta}
                                                        </td>
                                                    </tr>
                                                ),
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>

                        {/* Legajos recientes */}
                        <div
                            id="legajos"
                            className="rounded-xl border border-[#E3E0D8] bg-white p-5 shadow-[0_10px_30px_rgba(44,74,94,0.04)] sm:p-6"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <h3
                                        className="flex items-center gap-2 text-base font-medium text-[#2C4A5E]"
                                        style={{ fontFamily: FONT_HEADING }}
                                    >
                                        <FileText size={18} strokeWidth={1.8} />
                                        Legajos recientes
                                    </h3>
                                    <p
                                        className="mt-1 text-xs text-[#2B2F36]/55"
                                        style={{ fontFamily: FONT_BODY }}
                                    >
                                        Últimas aperturas registradas en el
                                        sistema.
                                    </p>
                                </div>
                                <Link
                                    href="/legajos/nuevo"
                                    className="shrink-0 text-xs font-semibold text-[#2C4A5E] underline-offset-4 hover:underline"
                                    style={{ fontFamily: FONT_BODY }}
                                >
                                    Abrir legajo
                                </Link>
                            </div>

                            {legajosRecientes.length === 0 ? (
                                <div className="mt-5 rounded-lg border border-dashed border-[#D5D1C6] bg-[#FAF9F6] p-5 text-center">
                                    <ClipboardList
                                        className="mx-auto text-[#6B8F71]"
                                        size={22}
                                        strokeWidth={1.7}
                                    />
                                    <p
                                        className="mt-2 text-sm text-[#2B2F36]/60"
                                        style={{ fontFamily: FONT_BODY }}
                                    >
                                        Todavía no hay legajos registrados.
                                    </p>
                                </div>
                            ) : (
                                <div className="mt-4 space-y-3">
                                    {legajosRecientes.map((legajo) => (
                                        <article
                                            key={legajo.id}
                                            className="rounded-lg border border-[#E8E5DD] bg-[#FCFBF8] p-4"
                                        >
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <span className="font-mono text-sm font-semibold tracking-wide text-[#2C4A5E]">
                                                            {legajo.numero}
                                                        </span>
                                                        <RiskBadge
                                                            risk={legajo.riesgo}
                                                        />
                                                        <span className="rounded-full bg-[#E8F0E9] px-2 py-0.5 text-[0.68rem] font-semibold text-[#41634A]">
                                                            {legajo.estado}
                                                        </span>
                                                    </div>
                                                    <p
                                                        className="mt-2 truncate text-sm font-semibold text-[#2B2F36]"
                                                        title={legajo.persona}
                                                    >
                                                        {legajo.persona}
                                                    </p>
                                                </div>
                                                <span className="text-xs text-[#2B2F36]/50">
                                                    Apertura{' '}
                                                    {legajo.fechaApertura}
                                                </span>
                                            </div>
                                            <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-[#EEECE6] pt-3 text-xs text-[#2B2F36]/60">
                                                <span className="flex items-center gap-1.5">
                                                    <UserRound size={13} /> DNI{' '}
                                                    {legajo.dni}
                                                </span>
                                                <span className="flex items-center gap-1.5">
                                                    <FileText size={13} />{' '}
                                                    {legajo.motivo}
                                                </span>
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
