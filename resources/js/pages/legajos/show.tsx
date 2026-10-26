import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    BarChart3,
    CheckCircle2,
    Clock3,
    Link2,
    Lock,
    LockOpen,
    Pencil,
    Plus,
    Send,
    XCircle,
    Unlink,
    UsersRound,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';

import {
    AvisoEstado,
    Dato,
    FONT_BODY,
    FONT_HEADING,
    NivelBadge,
} from '@/components/detalle';
import ResumenErrores from '@/components/resumen-errores';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import derivacionesRoutes from '@/routes/derivaciones';
import legajosRoutes from '@/routes/legajos';

interface LegajoDetalle {
    id: number;
    intervenciones_legajo_id: number;
    derivaciones_legajo_id: number;
    numero_legajo: string;
    motivo_ingreso: string | null;
    nivel_riesgo: string;
    estado: string;
    origen_derivacion: string | null;
    observaciones: string | null;
    fecha_apertura: string | null;
    profesional: string | null;
    problematicas: string[];
    persona: {
        id: number;
        nombre_completo: string;
        dni: string;
        edad: number | null;
    } | null;
}

interface Intervencion {
    id: number;
    fecha: string | null;
    hora: string | null;
    tipo: string | null;
    detalle: string;
    profesional: string | null;
    creado_por: string | null;
    firma_nombre: string | null;
    firma_aclaracion: string | null;
    tiene_firma_responsable: boolean;
    firma_entrevistado_nombre: string | null;
    firma_entrevistado_aclaracion: string | null;
    tiene_firma_entrevistado: boolean;
}

interface Vinculo {
    id: number;
    tipo: string;
    grupo: string | null;
    persona: {
        id: number;
        nombre: string;
        numero_legajo: string | null;
    };
}

interface PersonaDisponible {
    id: number;
    nombre: string;
    numero_legajo: string | null;
}

interface ResponsableDisponible {
    id: number;
    name: string;
    profesion: string | null;
    rol: string | null;
}

interface Responsable {
    id: number;
    usuario_id: number;
    nombre: string | null;
    es_principal: boolean;
}

interface Derivacion {
    id: number;
    estado: string;
    modo: string;
    motivo: string;
    detalle: string | null;
    fecha_limite: string | null;
    destinatario_id: number;
    destinatario: string | null;
    solicitante: string | null;
    motivo_rechazo: string | null;
}

interface Props {
    legajo: LegajoDetalle;
    status?: string | null;
    intervenciones?: Intervencion[];
    puedeGestionarIntervenciones?: boolean;
    puedeGestionarDerivaciones?: boolean;
    usuarioActualId: number;
    responsablesDisponibles?: ResponsableDisponible[];
    responsables?: Responsable[];
    derivaciones?: Derivacion[];
    puedeVerVinculos?: boolean;
    vinculos?: Vinculo[];
    personasDisponibles?: PersonaDisponible[];
}

const tituloCard = 'text-base font-semibold text-[#2C4A5E]';

export default function LegajoShow({
    legajo,
    status,
    intervenciones = [],
    puedeGestionarIntervenciones = false,
    puedeGestionarDerivaciones = false,
    usuarioActualId,
    responsablesDisponibles = [],
    responsables = [],
    derivaciones = [],
    puedeVerVinculos = false,
    vinculos = [],
    personasDisponibles = [],
}: Props) {
    const errors = (usePage().props.errors ?? {}) as Record<string, string>;
    const cerrado = legajo.estado === 'Cerrado';
    const [derivacionesOpen, setDerivacionesOpen] = useState(false);
    const vinculoForm = useForm({
        persona_relacionada_id: '',
        tipo_vinculo: 'hermano/a',
    });
    const derivacionForm = useForm({
        destinatario_id: '',
        modo: 'adicional',
        motivo: '',
        detalle: '',
        fecha_limite: '',
    });

    const cambiarEstado = () => {
        const accion = cerrado ? 'reabrir' : 'cerrar';

        if (
            window.confirm(
                `¿Querés ${accion} el legajo ${legajo.numero_legajo}?`,
            )
        ) {
            router.patch(
                `/legajos/${legajo.id}/${accion}`,
                {},
                { preserveScroll: true },
            );
        }
    };

    const agregarVinculo = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        vinculoForm.post(`/legajos/${legajo.id}/vinculos`, {
            preserveScroll: true,
            onSuccess: () => vinculoForm.reset(),
        });
    };

    const quitarVinculo = (id: number) => {
        if (!window.confirm('¿Querés desvincular estas personas?')) {
            return;
        }

        router.delete(`/legajos/${legajo.id}/vinculos/${id}`, {
            preserveScroll: true,
        });
    };

    const crearDerivacion = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        derivacionForm.post(
            legajosRoutes.derivaciones.store.url(legajo.derivaciones_legajo_id),
            {
                preserveScroll: true,
                onSuccess: () => derivacionForm.reset(),
            },
        );
    };

    const actualizarDerivacion = (
        id: number,
        accion: 'aceptar' | 'rechazar' | 'cancelar',
    ) => {
        const motivoRechazo = accion === 'rechazar'
            ? window.prompt('Indicá el motivo del rechazo:')
            : null;

        if (accion === 'rechazar' && !motivoRechazo?.trim()) {
            return;
        }

        router.patch(
            derivacionesRoutes[accion].url(id),
            accion === 'rechazar' ? { motivo_rechazo: motivoRechazo } : {},
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Head title={`Legajo ${legajo.numero_legajo}`} />

            <main
                className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <Link
                    href="/legajos"
                    className="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                >
                    <ArrowLeft size={15} />
                    Volver al listado
                </Link>

                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                            Legajo
                        </p>
                        <h2 className="mt-3 font-mono text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                            {legajo.numero_legajo}
                        </h2>
                        <div className="mt-3 flex flex-wrap items-center gap-2">
                            <NivelBadge nivel={legajo.nivel_riesgo} />
                            <Badge className="border-0 bg-[#EEF0ED] text-[#2B2F36]">
                                {legajo.estado}
                            </Badge>
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={`/legajos/${legajo.id}/edit`}
                            className="inline-flex h-11 items-center gap-2 rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                        >
                            <Pencil size={15} />
                            Editar
                        </Link>
                        <a
                            href={legajosRoutes.estadisticas.url(legajo.id)}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#DCE3DF] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            <BarChart3 size={15} />
                            Estadísticas
                        </a>
                        <button
                            type="button"
                            onClick={() => setDerivacionesOpen(true)}
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#DCE3DF] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            <Send size={15} />
                            Derivaciones
                        </button>
                        <button
                            type="button"
                            onClick={cambiarEstado}
                            className="inline-flex h-11 items-center gap-2 rounded-md border border-[#DCE3DF] bg-white px-4 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                        >
                            {cerrado ? (
                                <LockOpen size={15} />
                            ) : (
                                <Lock size={15} />
                            )}
                            {cerrado ? 'Reabrir legajo' : 'Cerrar legajo'}
                        </button>
                    </div>
                </div>

                <AvisoEstado mensaje={status} />
                <div className="mb-6">
                    <ResumenErrores errors={errors} />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Persona
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            {legajo.persona ? (
                                <dl className="grid gap-4 sm:grid-cols-2">
                                    <Dato etiqueta="Nombre">
                                        <Link
                                            href={`/personas/${legajo.persona.id}`}
                                            className="font-semibold text-[#2C4A5E] underline"
                                        >
                                            {legajo.persona.nombre_completo}
                                        </Link>
                                    </Dato>
                                    <Dato etiqueta="DNI">
                                        {legajo.persona.dni}
                                    </Dato>
                                    <Dato etiqueta="Edad">
                                        {legajo.persona.edad !== null
                                            ? `${legajo.persona.edad} años`
                                            : null}
                                    </Dato>
                                </dl>
                            ) : (
                                <p className="text-sm text-[#2B2F36]/60">
                                    Sin persona asociada.
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Seguimiento
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-5 py-5">
                            <dl className="grid gap-4 sm:grid-cols-2">
                                <Dato etiqueta="Fecha de apertura">
                                    {legajo.fecha_apertura}
                                </Dato>
                                <Dato etiqueta="Profesional asignado">
                                    {legajo.profesional}
                                </Dato>
                                <Dato etiqueta="Origen de la derivación">
                                    {legajo.origen_derivacion}
                                </Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Caso
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5 px-5 py-5">
                            <dl className="space-y-5">
                                <Dato etiqueta="Problemáticas">
                                    {legajo.problematicas.length > 0 && (
                                        <span className="flex flex-wrap gap-1.5">
                                            {legajo.problematicas.map(
                                                (nombre) => (
                                                    <Badge
                                                        key={nombre}
                                                        className="border-0 bg-[#EDF3EF] text-[#2C4A5E]"
                                                    >
                                                        {nombre}
                                                    </Badge>
                                                ),
                                            )}
                                        </span>
                                    )}
                                </Dato>
                                <Dato etiqueta="Motivo de ingreso">
                                    {legajo.motivo_ingreso}
                                </Dato>
                                <Dato etiqueta="Observaciones">
                                    {legajo.observaciones}
                                </Dato>
                            </dl>
                        </CardContent>
                    </Card>

                    {puedeVerVinculos && (
                        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)] lg:col-span-2">
                            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                <div className="flex items-center gap-2">
                                    <UsersRound size={18} className="text-[#6B8F71]" />
                                    <CardTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                        Vínculos familiares
                                    </CardTitle>
                                </div>
                                <p className="mt-1 text-sm text-[#2B2F36]/60">
                                    Relacioná este legajo con otros sin mezclar sus historiales.
                                </p>
                            </CardHeader>
                            <CardContent className="space-y-5 px-5 py-5">
                                {vinculos.length > 0 ? (
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        {vinculos.map((vinculo) => (
                                            <div key={vinculo.id} className="flex items-center justify-between gap-3 rounded-lg border border-[#DCE3DF] bg-[#FBFCFB] p-3">
                                                <div className="min-w-0">
                                                    <div className="flex items-center gap-2 text-xs font-semibold text-[#6B8F71]">
                                                        <Link2 size={13} />
                                                        {vinculo.tipo}
                                                    </div>
                                                    <Link href={`/legajos/${vinculo.persona.id}`} className="mt-1 block truncate text-sm font-semibold text-[#2C4A5E] hover:underline">
                                                        {vinculo.persona.nombre}
                                                    </Link>
                                                    <p className="mt-1 font-mono text-xs text-[#697586]">
                                                        {vinculo.persona.numero_legajo ?? 'Sin número de legajo'}
                                                    </p>
                                                </div>
                                                <button type="button" onClick={() => quitarVinculo(vinculo.id)} className="shrink-0 rounded-md p-2 text-[#8A6A63] transition hover:bg-[#FFF1EF] hover:text-[#A8493C]" aria-label={`Desvincular a ${vinculo.persona.nombre}`}>
                                                    <Unlink size={16} />
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-5 text-sm text-[#697586]">
                                        Todavía no hay vínculos registrados.
                                    </p>
                                )}

                                {personasDisponibles.length > 0 && (
                                    <form onSubmit={agregarVinculo} className="grid gap-3 border-t border-[#E4E8E5] pt-5 sm:grid-cols-[minmax(0,1fr)_190px_auto] sm:items-end">
                                        <div>
                                            <label htmlFor="persona_relacionada_id" className="text-xs font-semibold text-[#2C4A5E]">Legajo relacionado</label>
                                            <select id="persona_relacionada_id" value={vinculoForm.data.persona_relacionada_id} onChange={(event) => vinculoForm.setData('persona_relacionada_id', event.target.value)} className="mt-2 h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20" required>
                                                <option value="">Seleccionar persona...</option>
                                                {personasDisponibles.map((persona) => (
                                                    <option key={persona.id} value={persona.id}>
                                                        {persona.nombre} · {persona.numero_legajo}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        <div>
                                            <label htmlFor="tipo_vinculo" className="text-xs font-semibold text-[#2C4A5E]">Tipo de vínculo</label>
                                            <select id="tipo_vinculo" value={vinculoForm.data.tipo_vinculo} onChange={(event) => vinculoForm.setData('tipo_vinculo', event.target.value)} className="mt-2 h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20">
                                                <option>hermano/a</option>
                                                <option>primo/a</option>
                                                <option>tío/a</option>
                                                <option>sobrino/a</option>
                                                <option>madre</option>
                                                <option>padre</option>
                                                <option>hijo/a</option>
                                                <option>abuelo/a</option>
                                                <option>nieto/a</option>
                                                <option>tutor/a</option>
                                                <option>referente afectivo</option>
                                                <option>pareja</option>
                                                <option>otro familiar</option>
                                            </select>
                                        </div>
                                        <button type="submit" disabled={vinculoForm.processing} className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B] disabled:cursor-not-allowed disabled:opacity-60">
                                            <Plus size={15} />
                                            {vinculoForm.processing ? 'Guardando...' : 'Agregar'}
                                        </button>
                                    </form>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    <Dialog open={derivacionesOpen} onOpenChange={setDerivacionesOpen}>
                        <DialogContent className="max-h-[90vh] overflow-y-auto border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-2xl">
                            <DialogHeader>
                            <div className="flex items-center gap-2">
                                <Send size={18} className="text-[#6B8F71]" />
                                <DialogTitle className={tituloCard} style={{ fontFamily: FONT_HEADING }}>
                                    Derivaciones y responsables
                                </DialogTitle>
                            </div>
                            <DialogDescription className="mt-1 text-sm text-[#2B2F36]/60">
                                Enviá el caso a otro profesional y conservá el historial de decisiones.
                            </DialogDescription>
                            </DialogHeader>
                        <div className="space-y-5 px-1 py-2">
                            {responsables.length > 0 && (
                                <div className="flex flex-wrap gap-2">
                                    {responsables.map((responsable) => (
                                        <Badge key={responsable.id} className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                            {responsable.nombre ?? 'Responsable'}{responsable.es_principal ? ' · principal' : ''}
                                        </Badge>
                                    ))}
                                </div>
                            )}

                            {derivaciones.length > 0 ? (
                                <div className="space-y-3">
                                    {derivaciones.map((derivacion) => (
                                        <article key={derivacion.id} className="rounded-lg border border-[#DCE3DF] bg-[#FBFCFB] p-4">
                                            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <Badge className="border-0 bg-[#F2F7FB] text-[#35627A]">{derivacion.estado}</Badge>
                                                        <span className="text-xs text-[#697586]">{derivacion.modo}</span>
                                                    </div>
                                                    <p className="mt-2 text-sm font-semibold text-[#173247]">{derivacion.motivo}</p>
                                                    <p className="mt-1 text-xs text-[#697586]">Para {derivacion.destinatario ?? 'sin destinatario'} · solicitó {derivacion.solicitante ?? 'sin datos'}</p>
                                                    {derivacion.detalle && <p className="mt-3 whitespace-pre-line text-sm leading-6 text-[#2B2F36]/75">{derivacion.detalle}</p>}
                                                </div>
                                                <div className="flex shrink-0 gap-2">
                                                    {derivacion.estado === 'pendiente' && derivacion.destinatario_id === usuarioActualId && (
                                                        <>
                                                            <button type="button" onClick={() => actualizarDerivacion(derivacion.id, 'aceptar')} className="inline-flex items-center gap-1.5 rounded-md border border-[#C7DED0] bg-[#F2F9F4] px-3 py-2 text-xs font-semibold text-[#397461]">
                                                                <CheckCircle2 size={14} /> Aceptar
                                                            </button>
                                                            <button type="button" onClick={() => actualizarDerivacion(derivacion.id, 'rechazar')} className="inline-flex items-center gap-1.5 rounded-md border border-[#E8D7DA] bg-[#FCF1F3] px-3 py-2 text-xs font-semibold text-[#9B5964]">
                                                                <XCircle size={14} /> Rechazar
                                                            </button>
                                                        </>
                                                    )}
                                                    {derivacion.estado === 'pendiente' && puedeGestionarDerivaciones && (
                                                        <button type="button" onClick={() => actualizarDerivacion(derivacion.id, 'cancelar')} className="inline-flex items-center gap-1.5 rounded-md border border-[#E8D7DA] bg-[#FCF1F3] px-3 py-2 text-xs font-semibold text-[#9B5964]">
                                                            <XCircle size={14} /> Cancelar
                                                        </button>
                                                    )}
                                                </div>
                                            </div>
                                            {derivacion.motivo_rechazo && <p className="mt-3 text-xs text-[#9B5964]">Motivo del rechazo: {derivacion.motivo_rechazo}</p>}
                                        </article>
                                    ))}
                                </div>
                            ) : (
                                <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-5 text-sm text-[#697586]">Todavía no hay derivaciones registradas.</p>
                            )}

                            {puedeGestionarDerivaciones && (
                                <form onSubmit={crearDerivacion} className="grid gap-3 border-t border-[#E4E8E5] pt-5">
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">Destinatario
                                            <select value={derivacionForm.data.destinatario_id} onChange={(event) => derivacionForm.setData('destinatario_id', event.target.value)} className="h-10 rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm font-normal text-[#2B2F36]" required>
                                                <option value="">Seleccionar profesional...</option>
                                                {responsablesDisponibles.filter((responsable) => responsable.id !== usuarioActualId).map((responsable) => <option key={responsable.id} value={responsable.id}>{responsable.name}{responsable.profesion ? ` · ${responsable.profesion}` : ''}</option>)}
                                            </select>
                                        </label>
                                        <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">Modo
                                            <select value={derivacionForm.data.modo} onChange={(event) => derivacionForm.setData('modo', event.target.value)} className="h-10 rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm font-normal text-[#2B2F36]">
                                                <option value="adicional">Sumar responsable</option>
                                                <option value="reemplazo">Reemplazar responsables</option>
                                            </select>
                                        </label>
                                    </div>
                                    <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px]">
                                        <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">Motivo
                                            <Input value={derivacionForm.data.motivo} onChange={(event) => derivacionForm.setData('motivo', event.target.value)} placeholder="Ej. Seguimiento especializado" className="h-10 border-[#D5D1C6] bg-white text-sm font-normal" required />
                                        </label>
                                        <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">Fecha límite
                                            <Input type="date" value={derivacionForm.data.fecha_limite} onChange={(event) => derivacionForm.setData('fecha_limite', event.target.value)} className="h-10 border-[#D5D1C6] bg-white text-sm font-normal" />
                                        </label>
                                    </div>
                                    <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">Detalle
                                        <textarea value={derivacionForm.data.detalle} onChange={(event) => derivacionForm.setData('detalle', event.target.value)} rows={3} className="rounded-lg border border-[#D5D1C6] bg-white px-3 py-2 text-sm font-normal text-[#2B2F36] outline-none focus:border-[#2C4A5E]" placeholder="Información que la otra persona necesita para tomar el caso." />
                                    </label>
                                    <button type="submit" disabled={derivacionForm.processing} className="inline-flex h-10 w-fit items-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B] disabled:opacity-60">
                                        <Send size={15} /> {derivacionForm.processing ? 'Enviando...' : 'Enviar derivación'}
                                    </button>
                                </form>
                            )}
                        </div>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card className="mt-6 border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                    <CardHeader className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <CardTitle
                                className={tituloCard}
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Intervenciones y registros
                            </CardTitle>
                            <p className="mt-1 text-sm text-[#2B2F36]/60">
                                Actas, oficios, denuncias, mails y actuaciones
                                asociadas a este legajo.
                            </p>
                        </div>
                        {puedeGestionarIntervenciones && (
                            <Link
                                href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/create`}
                                className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                            >
                                <Plus size={16} />
                                Nueva intervención
                            </Link>
                        )}
                    </CardHeader>
                    <CardContent className="px-5 py-5 sm:px-6">
                        {intervenciones.length === 0 ? (
                            <div className="rounded-md border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-5 py-8 text-center">
                                <p className="text-sm font-semibold text-[#2C4A5E]">
                                    Todavía no hay intervenciones registradas.
                                </p>
                                {puedeGestionarIntervenciones && (
                                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                                        Registrá la primera actuación para
                                        comenzar el historial del legajo.
                                    </p>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {intervenciones.map((intervencion) => (
                                    <article
                                        key={intervencion.id}
                                        className="rounded-lg border border-[#DCE3DF] bg-white p-4 sm:p-5"
                                    >
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <Badge className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                                        {intervencion.tipo ??
                                                            'Registro'}
                                                    </Badge>
                                                    <span className="inline-flex items-center gap-1 text-xs font-semibold text-[#697586]">
                                                        <Clock3 size={13} />
                                                        {intervencion.fecha}
                                                        {intervencion.hora
                                                            ? ` · ${intervencion.hora.slice(0, 5)}`
                                                            : ''}
                                                    </span>
                                                </div>
                                                <p className="mt-3 whitespace-pre-line text-sm leading-6 text-[#2B2F36]">
                                                    {intervencion.detalle}
                                                </p>
                                            </div>
                                            {puedeGestionarIntervenciones && (
                                                <Link
                                                    href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/${intervencion.id}/edit`}
                                                    className="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                >
                                                    <Pencil size={14} />
                                                    Editar
                                                </Link>
                                            )}
                                        </div>
                                        <div className="mt-4 grid gap-3 border-t border-[#E4E8E5] pt-3 text-xs text-[#697586] sm:grid-cols-2 lg:grid-cols-4">
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Responsable:{' '}
                                                </strong>
                                                {intervencion.profesional ??
                                                    'Sin asignar'}
                                            </p>
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Registró:{' '}
                                                </strong>
                                                {intervencion.creado_por ??
                                                    'Sin datos'}
                                            </p>
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Firma:{' '}
                                                </strong>
                                                {intervencion.firma_nombre
                                                    ? `${intervencion.firma_nombre} · ${intervencion.firma_aclaracion ?? ''}`
                                                    : 'Sin firma'}
                                                {intervencion.tiene_firma_responsable && (
                                                    <a
                                                        href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/${intervencion.id}/firma/responsable/descargar`}
                                                        className="ml-1 underline"
                                                    >
                                                        (descargar)
                                                    </a>
                                                )}
                                            </p>
                                            <p>
                                                <strong className="text-[#2B2F36]">
                                                    Entrevistado:{' '}
                                                </strong>
                                                {intervencion.firma_entrevistado_nombre
                                                    ? `${intervencion.firma_entrevistado_nombre} · ${intervencion.firma_entrevistado_aclaracion ?? ''}`
                                                    : 'Sin firma'}
                                                {intervencion.tiene_firma_entrevistado && (
                                                    <a
                                                        href={`/legajos/${legajo.intervenciones_legajo_id}/intervenciones/${intervencion.id}/firma/entrevistado/descargar`}
                                                        className="ml-1 underline"
                                                    >
                                                        (descargar)
                                                    </a>
                                                )}
                                            </p>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
