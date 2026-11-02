import { Head, router } from '@inertiajs/react';
import {
    Check,
    ChevronDown,
    Eye,
    Mail,
    Paperclip,
    PenLine,
    RefreshCw,
    Reply,
    Search,
    Tag,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import mailbox from '@/routes/mailbox';

interface Mensaje {
    id: number;
    direction: 'incoming' | 'outgoing';
    subject: string;
    from_name: string | null;
    from_email: string;
    to_email: string | null;
    received_at: string | null;
    sent_at: string | null;
    is_read: boolean;
    estado: string;
    body_text: string | null;
    attachments: { path: string; name: string; mime: string; size: number }[];
    legajos: { id: number; numero_legajo: string }[];
    derivacion: {
        asignado_a: number | null;
        fecha_limite: string | null;
    } | null;
    preview: string;
}

interface Props {
    mensajes: Paginador<Mensaje>;
    filtros: { buscar: string | null; no_leidos: boolean; estado: string | null };
    noLeidos: number;
    estados: string[];
    responsables: Responsable[];
    syncError: string | null;
}

interface Responsable {
    id: number;
    name: string;
    profesion: string | null;
    rol: string | null;
}

interface LegajoResultado {
    id: number;
    numero_legajo: string;
    nombre: string;
    dni: string | null;
}

const formatDate = (value: string | null) => {
    if (!value) {
        return 'Sin fecha';
    }

    return new Intl.DateTimeFormat('es-AR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const estadoClasses: Record<string, string> = {
    Nuevo: 'border-[#D8E4EE] bg-[#F2F7FB] text-[#35627A]',
    Clasificado: 'border-[#DCE4D6] bg-[#F2F7EF] text-[#55714C]',
    Derivado: 'border-[#E7DCC8] bg-[#FBF5EA] text-[#9A6C2D]',
    Respondido: 'border-[#D8E8E2] bg-[#EFF8F4] text-[#397461]',
    Desestimado: 'border-[#E8D7DA] bg-[#FCF1F3] text-[#9B5964]',
};

export default function Mailbox({
    mensajes,
    filtros,
    noLeidos,
    estados,
    responsables,
    syncError,
}: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [noLeidosFiltro, setNoLeidosFiltro] = useState(filtros.no_leidos);
    const [estadoFiltro, setEstadoFiltro] = useState(filtros.estado ?? '');
    const [mensajeSeleccionado, setMensajeSeleccionado] =
        useState<Mensaje | null>(null);
    const [estado, setEstado] = useState('Clasificado');
    const [asignadoA, setAsignadoA] = useState('');
    const [fechaLimite, setFechaLimite] = useState('');
    const [legajoQuery, setLegajoQuery] = useState('');
    const [legajosEncontrados, setLegajosEncontrados] = useState<LegajoResultado[]>([]);
    const [legajosSeleccionados, setLegajosSeleccionados] = useState<LegajoResultado[]>([]);
    const [composeOpen, setComposeOpen] = useState(false);
    const [composeTo, setComposeTo] = useState('');
    const [composeSubject, setComposeSubject] = useState('');
    const [composeBody, setComposeBody] = useState('');
    const [composeReplyId, setComposeReplyId] = useState<number | null>(null);
    const [composeFiles, setComposeFiles] = useState<File[]>([]);
    const [sending, setSending] = useState(false);
    const [syncing, setSyncing] = useState(false);

    useEffect(() => {
        if (
            buscar === (filtros.buscar ?? '') &&
            noLeidosFiltro === filtros.no_leidos &&
            estadoFiltro === (filtros.estado ?? '')
        ) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                mailbox.index.url(),
                {
                    ...(buscar ? { buscar } : {}),
                    ...(noLeidosFiltro ? { no_leidos: 1 } : {}),
                    ...(estadoFiltro ? { estado: estadoFiltro } : {}),
                },
                { preserveState: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [
        buscar,
        noLeidosFiltro,
        estadoFiltro,
        filtros.buscar,
        filtros.no_leidos,
        filtros.estado,
    ]);

    useEffect(() => {
        if (!mensajeSeleccionado || legajoQuery.trim().length < 2) {
            return;
        }

        const timeout = setTimeout(() => {
            fetch(
                mailbox.legajos.buscar.url({
                    query: { buscar: legajoQuery },
                }),
            )
                .then((response) => response.json())
                .then((result: LegajoResultado[]) => setLegajosEncontrados(result))
                .catch(() => setLegajosEncontrados([]));
        }, 250);

        return () => clearTimeout(timeout);
    }, [legajoQuery, mensajeSeleccionado]);

    const marcarLeido = (id: number) => {
        router.patch(mailbox.read.url(id), {}, { preserveScroll: true });
    };

    const abrirClasificacion = (mensaje: Mensaje) => {
        setMensajeSeleccionado(mensaje);
        setEstado(mensaje.estado === 'Nuevo' ? 'Clasificado' : mensaje.estado);
        setAsignadoA(mensaje.derivacion?.asignado_a?.toString() ?? '');
        setFechaLimite(mensaje.derivacion?.fecha_limite ?? '');
        setLegajoQuery('');
        setLegajosEncontrados([]);
        setLegajosSeleccionados(
            mensaje.legajos.map((legajo) => ({
                ...legajo,
                nombre: '',
                dni: null,
            })),
        );
    };

    const alternarLegajo = (legajo: LegajoResultado) => {
        setLegajosSeleccionados((actuales) =>
            actuales.some((actual) => actual.id === legajo.id)
                ? actuales.filter((actual) => actual.id !== legajo.id)
                : [...actuales, legajo],
        );
        setLegajoQuery('');
        setLegajosEncontrados([]);
    };

    const guardarClasificacion = () => {
        if (!mensajeSeleccionado) {
            return;
        }

        router.patch(
            mailbox.classify.url(mensajeSeleccionado.id),
            {
                estado,
                legajo_ids: legajosSeleccionados.map((legajo) => legajo.id),
                ...(asignadoA ? { asignado_a: Number(asignadoA) } : {}),
                ...(fechaLimite ? { fecha_limite: fechaLimite } : {}),
            },
            {
                preserveScroll: true,
                onSuccess: () => setMensajeSeleccionado(null),
            },
        );
    };

    const abrirNuevoCorreo = () => {
        setComposeTo('');
        setComposeSubject('');
        setComposeBody('');
        setComposeReplyId(null);
        setComposeFiles([]);
        setComposeOpen(true);
    };

    const abrirRespuesta = (mensaje: Mensaje) => {
        setComposeTo(mensaje.from_email);
        setComposeSubject(mensaje.subject.startsWith('Re:') ? mensaje.subject : `Re: ${mensaje.subject}`);
        setComposeBody('');
        setComposeReplyId(mensaje.id);
        setComposeFiles([]);
        setMensajeSeleccionado(null);
        setComposeOpen(true);
    };

    const enviarCorreo = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const data = new FormData();
        data.append('subject', composeSubject);
        data.append('body', composeBody);

        if (composeReplyId) {
            composeFiles.forEach((file) => data.append('attachments[]', file));
        } else {
            data.append('to_email', composeTo);
            composeFiles.forEach((file) => data.append('attachments[]', file));
        }

        setSending(true);
        router.post(
            composeReplyId
                ? mailbox.reply.url(composeReplyId)
                : mailbox.send.url(),
            data,
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => setComposeOpen(false),
                onFinish: () => setSending(false),
            },
        );
    };

    const sincronizar = () => {
        setSyncing(true);
        router.post(mailbox.sync.url(), {}, {
            preserveScroll: true,
            onFinish: () => setSyncing(false),
        });
    };

    return (
        <>
            <Head title="Mailbox" />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                                Administración
                            </p>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                                Mailbox
                            </h1>
                            <p className="mt-2 text-sm text-[#697586]">
                                Bandeja institucional para la gestión de correos.
                            </p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {noLeidos > 0 && (
                                <Badge className="w-fit border-0 bg-[#E8F7EF] text-[#267148]">
                                    {noLeidos}{' '}
                                    {noLeidos === 1
                                        ? 'correo nuevo'
                                        : 'correos nuevos'}
                                </Badge>
                            )}
                            <Button type="button" onClick={abrirNuevoCorreo} className="bg-[#2C4A5E] hover:bg-[#173247]">
                                <PenLine className="size-4" />
                                Nuevo correo
                            </Button>
                            <Button type="button" variant="outline" onClick={sincronizar} disabled={syncing} className="border-[#D5D1C6] bg-white text-[#2C4A5E]">
                                <RefreshCw className={`size-4 ${syncing ? 'animate-spin' : ''}`} />
                                Actualizar
                            </Button>
                        </div>
                    </div>
                    {syncError && (
                        <div className="mb-4 rounded-md border border-[#E8D7DA] bg-[#FCF1F3] px-4 py-3 text-sm text-[#9B5964]">
                            No se pudo sincronizar Hostinger: {syncError}
                        </div>
                    )}

                    <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <div className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                            <div className="relative w-full sm:max-w-lg">
                                <Search className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#2B2F36]/40" />
                                <Input
                                    value={buscar}
                                    onChange={(event) =>
                                        setBuscar(event.target.value)
                                    }
                                    placeholder="Buscar por remitente, asunto o contenido…"
                                    className="h-11 border-[#D5D1C6] bg-white pl-10 shadow-none"
                                />
                            </div>
                            <label className="flex h-11 items-center gap-2 px-1 text-sm font-medium text-[#2B2F36]">
                                <input
                                    type="checkbox"
                                    checked={noLeidosFiltro}
                                    onChange={(event) =>
                                        setNoLeidosFiltro(event.target.checked)
                                    }
                                    className="size-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                />
                                Solo no leídos
                            </label>
                            <label className="relative flex h-11 w-full items-center sm:ml-auto sm:w-52">
                                <Tag className="pointer-events-none absolute left-3.5 size-4 text-[#2B2F36]/40" />
                                <select
                                    value={estadoFiltro}
                                    onChange={(event) => setEstadoFiltro(event.target.value)}
                                    className="h-11 w-full appearance-none rounded-md border border-[#D5D1C6] bg-white pl-10 pr-9 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/15"
                                >
                                    <option value="">Todos los estados</option>
                                    {estados.map((item) => (
                                        <option key={item} value={item}>{item}</option>
                                    ))}
                                </select>
                                <ChevronDown className="pointer-events-none absolute right-3 size-4 text-[#2B2F36]/40" />
                            </label>
                        </div>

                        <CardContent className="p-0">
                            {mensajes.data.length === 0 ? (
                                <div className="px-6 py-16 text-center">
                                    <Mail className="mx-auto size-8 text-[#6B8F71]" />
                                    <p className="mt-3 text-sm font-semibold text-[#2B2F36]">
                                        No hay correos para mostrar.
                                    </p>
                                </div>
                            ) : (
                                <div className="divide-y divide-[#EEF0ED]">
                                    {mensajes.data.map((mensaje) => (
                                        <article
                                            key={mensaje.id}
                                            className={`flex gap-4 px-5 py-5 transition hover:bg-[#FAFBFA] sm:px-6 ${mensaje.is_read ? '' : 'bg-[#F8FAF9]'}`}
                                        >
                                            <div className="mt-1 shrink-0 text-[#2C4A5E]">
                                                <Mail className="size-5" />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                                    <div className="min-w-0">
                                                        <p
                                                            className={`truncate text-sm ${mensaje.is_read ? 'font-medium' : 'font-bold'} text-[#173247]`}
                                                        >
                                                            {mensaje.subject}
                                                        </p>
                                                         <p className="mt-1 truncate text-xs text-[#2B2F36]/60">
                                                             {mensaje.direction === 'outgoing' ? 'Para: ' : ''}
                                                             {mensaje.direction === 'outgoing'
                                                                 ? mensaje.to_email
                                                                 : `${mensaje.from_name ? `${mensaje.from_name} · ` : ''}${mensaje.from_email}`}
                                                         </p>
                                                    </div>
                                                    <time className="shrink-0 text-xs text-[#2B2F36]/50">
                                                         {formatDate(mensaje.received_at ?? mensaje.sent_at)}
                                                    </time>
                                                </div>
                                                <p className="mt-3 line-clamp-2 text-sm leading-6 text-[#2B2F36]/70">
                                                     {mensaje.preview ||
                                                         'Sin contenido de texto.'}
                                                 </p>
                                                <div className="mt-3 flex flex-wrap items-center gap-2">
                                                    <span className={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${estadoClasses[mensaje.estado] ?? estadoClasses.Nuevo}`}>
                                                        {mensaje.estado}
                                                    </span>
                                                    {mensaje.legajos.map((legajo) => (
                                                        <span key={legajo.id} className="rounded-full bg-[#F4F6F5] px-2.5 py-1 text-[11px] font-medium text-[#697586]">
                                                            {legajo.numero_legajo}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                             <button
                                                 type="button"
                                                 onClick={() => abrirClasificacion(mensaje)}
                                                className="inline-flex h-9 shrink-0 items-center gap-1.5 self-start rounded-md border border-[#D5D1C6] bg-white px-3 text-xs font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                                            >
                                                 <Eye className="size-3.5" />
                                                 <span className="hidden sm:inline">Ver</span>
                                             </button>
                                            {!mensaje.is_read && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        marcarLeido(mensaje.id)
                                                    }
                                                    className="inline-flex h-9 shrink-0 items-center gap-1.5 self-start rounded-md border border-[#D5D1C6] bg-white px-3 text-xs font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40"
                                                >
                                                    <Check className="size-3.5" />
                                                    Leído
                                                </button>
                                            )}
                                        </article>
                                    ))}
                                </div>
                            )}
                            <Paginacion paginador={mensajes} unidad="correos" />
                        </CardContent>
                    </Card>
                </div>
            </main>
            <Dialog
                open={mensajeSeleccionado !== null}
                onOpenChange={(open) => !open && setMensajeSeleccionado(null)}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle className="text-[#173247]">Clasificar comunicación</DialogTitle>
                        <DialogDescription className="text-[#697586]">
                            Organizá el oficio, vinculalo a los legajos correspondientes y derivalo a una persona responsable.
                        </DialogDescription>
                    </DialogHeader>

                    {mensajeSeleccionado && (
                        <div className="space-y-5">
                             <div className="rounded-lg border border-[#E4E8E5] bg-white p-4">
                                 <p className="text-sm font-semibold text-[#173247]">{mensajeSeleccionado.subject}</p>
                                 <p className="mt-1 text-xs text-[#697586]">
                                     {mensajeSeleccionado.direction === 'outgoing' ? `Para: ${mensajeSeleccionado.to_email}` : `${mensajeSeleccionado.from_name || mensajeSeleccionado.from_email} · ${mensajeSeleccionado.from_email}`}
                                 </p>
                                 <p className="mt-3 max-h-52 overflow-y-auto whitespace-pre-wrap text-sm leading-6 text-[#2B2F36]/75">{mensajeSeleccionado.body_text || 'Sin contenido de texto.'}</p>
                                 {mensajeSeleccionado.attachments.length > 0 && (
                                     <div className="mt-4 flex flex-wrap gap-2">
                                         {mensajeSeleccionado.attachments.map((attachment, index) => (
                                             <a key={`${attachment.path}-${index}`} href={mailbox.attachment.url([mensajeSeleccionado.id, index])} download className="inline-flex items-center gap-1.5 rounded-md border border-[#D5D1C6] px-2.5 py-1.5 text-xs font-semibold text-[#2C4A5E] hover:bg-[#F4F6F5]">
                                                 <Paperclip className="size-3.5" />
                                                 {attachment.name}
                                             </a>
                                         ))}
                                     </div>
                                 )}
                                 {mensajeSeleccionado.direction === 'incoming' && (
                                     <Button type="button" variant="outline" onClick={() => abrirRespuesta(mensajeSeleccionado)} className="mt-4 border-[#D5D1C6] text-[#2C4A5E]">
                                         <Reply className="size-4" />
                                         Responder
                                     </Button>
                                 )}
                             </div>

                            <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                                Estado
                                <select value={estado} onChange={(event) => setEstado(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/15">
                                    {estados.map((item) => <option key={item} value={item}>{item}</option>)}
                                </select>
                            </label>

                            <div className="grid gap-2">
                                <label className="text-sm font-semibold text-[#2B2F36]">Legajos vinculados</label>
                                <div className="flex flex-wrap gap-2">
                                    {legajosSeleccionados.map((legajo) => (
                                        <span key={legajo.id} className="inline-flex items-center gap-1 rounded-full bg-[#EAF3EF] px-3 py-1.5 text-xs font-semibold text-[#397461]">
                                            {legajo.numero_legajo}
                                            <button type="button" onClick={() => alternarLegajo(legajo)} aria-label={`Quitar ${legajo.numero_legajo}`}><X className="size-3" /></button>
                                        </span>
                                    ))}
                                </div>
                                <div className="relative">
                                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#2B2F36]/40" />
                                    <Input
                                        value={legajoQuery}
                                        onChange={(event) => {
                                            const value = event.target.value;
                                            setLegajoQuery(value);

                                            if (value.trim().length < 2) {
                                                setLegajosEncontrados([]);
                                            }
                                        }}
                                        placeholder="Buscar por DNI, nombre o número de legajo"
                                        className="h-10 border-[#D5D1C6] bg-white pl-9"
                                    />
                                </div>
                                {legajosEncontrados.length > 0 && (
                                    <div className="divide-y divide-[#EEF0ED] overflow-hidden rounded-md border border-[#E4E8E5] bg-white">
                                        {legajosEncontrados.map((legajo) => (
                                            <button key={legajo.id} type="button" onClick={() => alternarLegajo(legajo)} className="flex w-full items-center justify-between px-3 py-2.5 text-left text-sm hover:bg-[#F4F7F5]">
                                                <span><strong className="text-[#173247]">{legajo.numero_legajo}</strong><small className="ml-2 text-[#697586]">{legajo.nombre}{legajo.dni ? ` · DNI ${legajo.dni}` : ''}</small></span>
                                                <span className="text-xs font-semibold text-[#6B8F71]">Vincular</span>
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {estado === 'Derivado' && (
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                                        Responsable
                                        <select value={asignadoA} onChange={(event) => setAsignadoA(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/15">
                                            <option value="">Seleccionar persona</option>
                                            {responsables.map((responsable) => <option key={responsable.id} value={responsable.id}>{responsable.name}{responsable.profesion ? ` · ${responsable.profesion}` : ''}</option>)}
                                        </select>
                                    </label>
                                    <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                                        Vencimiento
                                        <Input type="date" value={fechaLimite} onChange={(event) => setFechaLimite(event.target.value)} className="h-10 border-[#D5D1C6] bg-white" />
                                    </label>
                                </div>
                            )}
                        </div>
                    )}
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setMensajeSeleccionado(null)}>Cancelar</Button>
                        <Button type="button" onClick={guardarClasificacion} className="bg-[#2C4A5E] hover:bg-[#173247]">Guardar clasificación</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            <Dialog open={composeOpen} onOpenChange={setComposeOpen}>
                <DialogContent className="border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle className="text-[#173247]">{composeReplyId ? 'Responder correo' : 'Nuevo correo'}</DialogTitle>
                        <DialogDescription className="text-[#697586]">El mensaje se enviará desde prodenya@desarrollohumanosn.com.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={enviarCorreo} className="grid gap-4">
                        {!composeReplyId && (
                            <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                                Destinatario
                                <Input type="email" required value={composeTo} onChange={(event) => setComposeTo(event.target.value)} placeholder="correo@ejemplo.com" className="border-[#D5D1C6] bg-white" />
                            </label>
                        )}
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                            Asunto
                            <Input required value={composeSubject} onChange={(event) => setComposeSubject(event.target.value)} className="border-[#D5D1C6] bg-white" />
                        </label>
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                            Mensaje
                            <textarea required value={composeBody} onChange={(event) => setComposeBody(event.target.value)} rows={8} className="rounded-md border border-[#D5D1C6] bg-white px-3 py-2 text-sm font-normal outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/15" />
                        </label>
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">
                            Adjuntos
                            <Input type="file" multiple onChange={(event) => setComposeFiles(Array.from(event.target.files ?? []))} className="border-[#D5D1C6] bg-white text-sm font-normal" />
                        </label>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setComposeOpen(false)}>Cancelar</Button>
                            <Button type="submit" disabled={sending} className="bg-[#2C4A5E] hover:bg-[#173247]">
                                <Mail className="size-4" />
                                {sending ? 'Enviando…' : 'Enviar correo'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
