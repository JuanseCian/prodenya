import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    CalendarDays,
    ClipboardList,
    FileText,
    LogOut,
    Plus,
    ShieldAlert,
    UserRound,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

interface PersonaReciente {
    id: number;
    nombreCompleto: string;
    dni: string;
    localidad: string | null;
    fechaAlta: string;
}

interface DashboardProps {
    estadisticas: Estadisticas;
    personasRecientes: PersonaReciente[];
    legajosRecientes: LegajoReciente[];
}

interface Estadisticas {
    personasActivas: number;
    legajosAbiertos: number;
    riesgoPrioritario: number;
    aperturasMes: number;
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

type KpiTone = 'blue' | 'green' | 'orange' | 'violet';

const kpiTones: Record<
    KpiTone,
    { icon: string; surface: string; value: string }
> = {
    blue: {
        icon: 'bg-[#E8F4FF] text-[#4B9FDA]',
        surface: 'border-[#DCE3DF] bg-white',
        value: 'text-[#2B2F36]',
    },
    green: {
        icon: 'bg-[#EAF4EE] text-[#39795B]',
        surface: 'border-[#DCE3DF] bg-white',
        value: 'text-[#2B2F36]',
    },
    orange: {
        icon: 'bg-[#FFF3DF] text-[#A56D24]',
        surface: 'border-[#DCE3DF] bg-white',
        value: 'text-[#2B2F36]',
    },
    violet: {
        icon: 'bg-[#F2EFFF] text-[#7169D0]',
        surface: 'border-[#DCE3DF] bg-white',
        value: 'text-[#2B2F36]',
    },
};

function KpiCard({
    label,
    value,
    detail,
    icon: Icon,
    tone,
}: {
    label: string;
    value: number;
    detail: string;
    icon: LucideIcon;
    tone: KpiTone;
}) {
    const styles = kpiTones[tone];

    return (
        <article className={`rounded-lg border p-5 ${styles.surface}`}>
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p
                        className="text-[0.68rem] font-bold tracking-[0.08em] text-[#2B2F36]/60 uppercase"
                        style={{ fontFamily: FONT_BODY }}
                    >
                        {label}
                    </p>
                    <p
                        className={`mt-2 text-[2rem] font-bold tracking-[-0.04em] ${styles.value}`}
                        style={{ fontFamily: FONT_BODY }}
                    >
                        {value.toLocaleString('es-AR')}
                    </p>
                </div>
                <span
                    className={`grid size-14 shrink-0 place-items-center rounded-2xl transition group-hover:scale-105 group-hover:rotate-3 ${styles.icon}`}
                >
                    <Icon size={19} strokeWidth={1.8} />
                </span>
            </div>
            <p
                className="mt-4 text-xs font-medium text-[#2B2F36]/55"
                style={{ fontFamily: FONT_BODY }}
            >
                {detail}
            </p>
        </article>
    );
}

export default function Dashboard({
    estadisticas,
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
                }}
                className="bg-[#F4F6F5]"
            >
                {/* Header */}
                <header className="sticky top-0 z-10 border-b border-[#DFE4EA] bg-white">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                        <div>
                            <span
                                className="text-[0.65rem] font-semibold tracking-[0.2em] text-[#6B8F71] uppercase"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Promoción y Protección de Derechos
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
                                className="hidden text-sm font-medium text-[#2C4A5E]/70 sm:inline"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                Hola,{' '}
                                {auth.user?.nombre_usuario ?? auth.user?.email}
                            </span>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="flex items-center gap-1.5 rounded-xl border border-[#D5E6F0] bg-[#F6FBFE] px-3.5 py-1.5 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#A8CFE5] hover:bg-[#EDF8FF]"
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
                        className="text-2xl font-semibold tracking-tight text-[#173247] sm:text-3xl"
                        style={{ fontFamily: FONT_HEADING }}
                    >
                        Panel general
                    </h2>

                    <div className="mt-1 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p
                            className="text-sm text-[#2C4A5E]/65"
                            style={{ fontFamily: FONT_BODY }}
                        >
                            Resumen del trabajo actual.
                        </p>
                        <div className="flex gap-2">
                            <Link
                                href="/personas/nueva"
                                className="inline-flex items-center gap-2 rounded-md bg-[#2C4A5E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                <Plus size={16} />
                                Nueva persona
                            </Link>
                            <Link
                                href="/legajos/nuevo"
                                className="inline-flex items-center gap-2 rounded-md border border-[#DCE3DF] bg-white px-4 py-2 text-sm font-semibold text-[#2B2F36] transition hover:border-[#B9C5BD] hover:bg-[#F4F6F5]"
                                style={{ fontFamily: FONT_BODY }}
                            >
                                <ClipboardList size={16} />
                                Nuevo legajo
                            </Link>
                        </div>
                    </div>

                    <section
                        aria-labelledby="estadisticas-title"
                        className="relative mt-8 overflow-hidden rounded-xl border border-[#DCE3DF] bg-white p-5 shadow-[0_14px_35px_rgba(23,50,71,0.06)] sm:p-7"
                    >
                        <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div className="flex items-start gap-3">
                                <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-[#EEECF7] text-[#5B559E]">
                                    <Activity size={18} strokeWidth={1.8} />
                                </span>
                                <div>
                                    <p
                                        className="text-[0.68rem] font-bold tracking-[0.12em] text-[#23845B] uppercase"
                                        style={{ fontFamily: FONT_BODY }}
                                    >
                                        Datos registrados
                                    </p>
                                    <h3
                                        id="estadisticas-title"
                                        className="mt-1 text-xl font-semibold tracking-tight text-[#173247] sm:text-2xl"
                                        style={{ fontFamily: FONT_HEADING }}
                                    >
                                        Panorama de gestión
                                    </h3>
                                    <p
                                        className="mt-1 text-sm text-[#2C4A5E]/65"
                                        style={{ fontFamily: FONT_BODY }}
                                    >
                                        Una lectura rápida del estado actual de
                                        los legajos.
                                    </p>
                                </div>
                            </div>
                            <div className="inline-flex w-fit items-center gap-2 rounded-md border border-[#DFE4EA] bg-[#F7F8FA] px-3 py-2 text-xs font-semibold text-[#697586]">
                                <CalendarDays
                                    size={15}
                                    className="text-[#23845B]"
                                />
                                Actualizado hoy
                            </div>
                        </div>

                        <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <KpiCard
                                label="Personas activas"
                                value={estadisticas.personasActivas}
                                detail="Registradas actualmente"
                                icon={UsersRound}
                                tone="blue"
                            />
                            <KpiCard
                                label="Legajos abiertos"
                                value={estadisticas.legajosAbiertos}
                                detail="Casos con seguimiento"
                                icon={ClipboardList}
                                tone="green"
                            />
                            <KpiCard
                                label="Riesgo prioritario"
                                value={estadisticas.riesgoPrioritario}
                                detail="Alto o crítico"
                                icon={ShieldAlert}
                                tone="orange"
                            />
                            <KpiCard
                                label="Aperturas del mes"
                                value={estadisticas.aperturasMes}
                                detail="Desde el inicio del mes"
                                icon={Activity}
                                tone="violet"
                            />
                        </div>
                    </section>

                    <div className="mt-8 grid gap-6 xl:grid-cols-[1fr_1.15fr]">
                        {/* Personas recientes */}
                        <div className="rounded-lg border border-[#DFE4EA] bg-white p-5 sm:p-6">
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
                                            <tr className="border-b border-[#DDEBF3] text-[#1670AD]/65">
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
                                                        className="border-b border-[#EDF5F8] last:border-0"
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
                            className="rounded-lg border border-[#DFE4EA] bg-white p-5 sm:p-6"
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
                                        Últimas aperturas registradas.
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
                                <div className="mt-5 rounded-2xl border border-dashed border-[#BFE3D2] bg-[#F3FCF7] p-5 text-center">
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
                                            className="rounded-2xl border border-[#E0EEF3] bg-[#F8FCFE] p-4 transition hover:border-[#B9DCEB] hover:bg-[#F3FAFD]"
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
                                            <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-[#E3F0F4] pt-3 text-xs text-[#2C4A5E]/65">
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
