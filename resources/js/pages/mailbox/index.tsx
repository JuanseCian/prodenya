import { Head, router } from '@inertiajs/react';
import {
    AlertTriangle,
    Archive,
    Check,
    ChevronLeft,
    ChevronRight,
    FileText,
    Inbox,
    LoaderCircle,
    Mail,
    Paperclip,
    PenLine,
    RefreshCw,
    Reply,
    Search,
    Send,
    Settings2,
    Star,
    Tag,
    Trash2,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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

type Folder = 'inbox' | 'sent' | 'archive' | 'spam' | 'trash';

interface Mensaje {
    id: number;
    message_id: string | null;
    provider: string | null;
    provider_message_id: string | null;
    folder: Folder | null;
    direction: 'incoming' | 'outgoing';
    subject: string;
    from_name: string | null;
    from_email: string;
    to_email: string | null;
    cc_email: string | null;
    bcc_email: string | null;
    received_at: string | null;
    sent_at: string | null;
    is_read: boolean;
    is_starred: boolean;
    has_attachments: boolean;
    estado: string;
    body_text: string | null;
    attachments: { index: number; name: string; mime: string; size: number }[];
    legajos: { id: number; numero_legajo: string }[];
    derivacion: {
        asignado_a: number | null;
        fecha_limite: string | null;
    } | null;
    preview: string;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    mensajes: Paginator<Mensaje>;
    filtros: {
        buscar: string | null;
        no_leidos: boolean;
        estado: string | null;
        folder: Folder;
    };
    carpetas: Record<Folder, number>;
    noLeidos: number;
    estados: string[];
    responsables: Responsable[];
    syncError: string | null;
    googleConfigured: boolean;
    googleConnected: boolean;
    googleEmail: string | null;
    googleSendAccess: boolean;
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

const folderItems: { key: Folder; label: string; icon: typeof Inbox }[] = [
    { key: 'inbox', label: 'Bandeja de entrada', icon: Inbox },
    { key: 'sent', label: 'Enviados', icon: Send },
    { key: 'archive', label: 'Archivados', icon: Archive },
    { key: 'spam', label: 'Spam', icon: AlertTriangle },
    { key: 'trash', label: 'Papelera', icon: Trash2 },
];

const estadoClasses: Record<string, string> = {
    Nuevo: 'border-[#D8E4EE] bg-[#F2F7FB] text-[#35627A]',
    Clasificado: 'border-[#DCE4D6] bg-[#F2F7EF] text-[#55714C]',
    Derivado: 'border-[#E7DCC8] bg-[#FBF5EA] text-[#9A6C2D]',
    Respondido: 'border-[#D8E8E2] bg-[#EFF8F4] text-[#397461]',
    Desestimado: 'border-[#E8D7DA] bg-[#FCF1F3] text-[#9B5964]',
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return 'Sin fecha';
    }

    return new Intl.DateTimeFormat('es-AR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatBytes = (bytes: number): string => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    return `${Math.round(bytes / 1024)} KB`;
};

export default function Mailbox({
    mensajes,
    filtros,
    carpetas,
    noLeidos,
    estados,
    responsables,
    syncError,
    googleConfigured,
    googleConnected,
    googleEmail,
    googleSendAccess,
}: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [noLeidosFiltro, setNoLeidosFiltro] = useState(filtros.no_leidos);
    const [estadoFiltro, setEstadoFiltro] = useState(filtros.estado ?? '');
    const [folder, setFolder] = useState<Folder>(filtros.folder);
    const [mensajeId, setMensajeId] = useState<number | null>(null);
    const [mensajeDetalle, setMensajeDetalle] = useState<Mensaje | null>(null);
    const [mensajeSeleccionado, setMensajeSeleccionado] = useState<Mensaje | null>(null);
    const [estado, setEstado] = useState('Clasificado');
    const [asignadoA, setAsignadoA] = useState('');
    const [fechaLimite, setFechaLimite] = useState('');
    const [legajoQuery, setLegajoQuery] = useState('');
    const [legajosEncontrados, setLegajosEncontrados] = useState<LegajoResultado[]>([]);
    const [legajosSeleccionados, setLegajosSeleccionados] = useState<LegajoResultado[]>([]);
    const [composeOpen, setComposeOpen] = useState(false);
    const [composeTo, setComposeTo] = useState('');
    const [composeCc, setComposeCc] = useState('');
    const [composeBcc, setComposeBcc] = useState('');
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
            estadoFiltro === (filtros.estado ?? '') &&
            folder === filtros.folder
        ) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                mailbox.index.url({
                    query: {
                        ...(buscar ? { buscar } : {}),
                        ...(noLeidosFiltro ? { no_leidos: 1 } : {}),
                        ...(estadoFiltro ? { estado: estadoFiltro } : {}),
                        ...(folder !== 'inbox' ? { folder } : {}),
                    },
                }),
                {},
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [buscar, noLeidosFiltro, estadoFiltro, folder, filtros]);

    useEffect(() => {
        if (!mensajeId) {
            return;
        }

        const controller = new AbortController();
        fetch(mailbox.show.url(mensajeId), { signal: controller.signal })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('No se pudo cargar el correo.');
                }

                return response.json();
            })
            .then((result: { message: Mensaje }) => setMensajeDetalle(result.message))
            .catch((error: Error) => {
                if (error.name !== 'AbortError') {
                    setMensajeDetalle(null);
                }
            });

        return () => controller.abort();
    }, [mensajeId]);

    useEffect(() => {
        if (!mensajeSeleccionado || legajoQuery.trim().length < 2) {
            return;
        }

        const timeout = setTimeout(() => {
            fetch(mailbox.legajos.buscar.url({ query: { buscar: legajoQuery } }))
                .then((response) => response.json())
                .then((result: LegajoResultado[]) => setLegajosEncontrados(result))
                .catch(() => setLegajosEncontrados([]));
        }, 250);

        return () => clearTimeout(timeout);
    }, [legajoQuery, mensajeSeleccionado]);

    const mensajeLista = useMemo(
        () => mensajes.data.find((mensaje) => mensaje.id === mensajeId) ?? null,
        [mensajes.data, mensajeId],
    );

    const abrirMensaje = (mensaje: Mensaje) => {
        setMensajeId(mensaje.id);

        if (!mensaje.is_read) {
            router.patch(mailbox.read.url(mensaje.id), {}, { preserveState: true, preserveScroll: true });
        }
    };

    const abrirClasificacion = (mensaje: Mensaje) => {
        setMensajeSeleccionado(mensaje);
        setEstado(mensaje.estado === 'Nuevo' ? 'Clasificado' : mensaje.estado);
        setAsignadoA(mensaje.derivacion?.asignado_a?.toString() ?? '');
        setFechaLimite(mensaje.derivacion?.fecha_limite ?? '');
        setLegajoQuery('');
        setLegajosSeleccionados(
            mensaje.legajos.map((legajo) => ({ ...legajo, nombre: '', dni: null })),
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
            { preserveScroll: true, onSuccess: () => setMensajeSeleccionado(null) },
        );
    };

    const abrirNuevoCorreo = () => {
        setComposeTo('');
        setComposeCc('');
        setComposeBcc('');
        setComposeSubject('');
        setComposeBody('');
        setComposeReplyId(null);
        setComposeFiles([]);
        setComposeOpen(true);
    };

    const abrirRespuesta = (mensaje: Mensaje) => {
        setComposeTo(mensaje.from_email);
        setComposeCc('');
        setComposeBcc('');
        setComposeSubject(mensaje.subject.startsWith('Re:') ? mensaje.subject : `Re: ${mensaje.subject}`);
        setComposeBody('');
        setComposeReplyId(mensaje.id);
        setComposeFiles([]);
        setComposeOpen(true);
    };

    const enviarCorreo = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = new FormData();
        data.append('subject', composeSubject);
        data.append('body', composeBody);

        if (!composeReplyId) {
            data.append('to_email', composeTo);
        }

        if (composeCc) {
            data.append('cc_email', composeCc);
        }

        if (composeBcc) {
            data.append('bcc_email', composeBcc);
        }

        composeFiles.forEach((file) => data.append('attachments[]', file));

        setSending(true);
        router.post(composeReplyId ? mailbox.reply.url(composeReplyId) : mailbox.send.url(), data, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => setComposeOpen(false),
            onFinish: () => setSending(false),
        });
    };

    const sincronizar = () => {
        setSyncing(true);
        router.post(mailbox.sync.url(), {}, { preserveScroll: true, onFinish: () => setSyncing(false) });
    };

    const desvincularGmail = () => {
        router.post(mailbox.google.disconnect.url(), {}, { preserveScroll: true });
    };

    const detalle = mensajeId ? mensajeDetalle ?? mensajeLista : null;
    const cargandoDetalle = mensajeId !== null && mensajeDetalle === null;
    const previousPage = mensajes.links[0];
    const nextPage = mensajes.links[mensajes.links.length - 1];

    return (
        <>
            <Head title="Mailbox institucional" />
            <main className="min-h-screen bg-[#F4F6F5] px-3 py-4 text-[#24313A] sm:px-6 sm:py-6 lg:px-8">
                <div className="mx-auto max-w-[1600px]">
                    <header className="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p className="text-[11px] font-bold tracking-[0.18em] text-[#6B8F71] uppercase">Administración / Comunicaciones</p>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-[#173247]">Mailbox institucional</h1>
                            <p className="mt-1 text-sm text-[#697586]">Gestión operativa, clasificación y seguimiento de comunicaciones oficiales.</p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {googleConnected ? (
                                <>
                                    <Badge className="border-0 bg-[#E8F7EF] text-[#267148]">Gmail: {googleEmail}</Badge>
                                    {googleConfigured && !googleSendAccess && (
                                        <a href={mailbox.google.redirect.url()} className="inline-flex h-9 items-center rounded-md border border-[#E7DCC8] bg-[#FFF6E6] px-3 text-xs font-semibold text-[#9A6C2D]">Autorizar envíos</a>
                                    )}
                                    <Button type="button" variant="outline" onClick={desvincularGmail} className="h-9 border-[#D5D1C6] bg-white text-xs text-[#2C4A5E]">Desvincular</Button>
                                </>
                            ) : googleConfigured ? (
                                <a href={mailbox.google.redirect.url()} className="inline-flex h-9 items-center rounded-md border border-[#D5D1C6] bg-white px-3 text-xs font-semibold text-[#2C4A5E]">Vincular Gmail</a>
                            ) : <Badge className="border-0 bg-[#FFF6E6] text-[#9A6C2D]">Gmail OAuth sin configurar</Badge>}
                            <Button type="button" onClick={abrirNuevoCorreo} className="h-9 bg-[#2C4A5E] text-xs text-white hover:bg-[#173247]"><PenLine className="size-3.5" />Nuevo correo</Button>
                            <Button type="button" variant="outline" onClick={sincronizar} disabled={syncing} className="h-9 border-[#D5D1C6] bg-white text-xs text-[#2C4A5E]"><RefreshCw className={`size-3.5 ${syncing ? 'animate-spin' : ''}`} />Actualizar</Button>
                        </div>
                    </header>

                    {syncError && <div className="mb-4 rounded-md border border-[#E8D7DA] bg-[#FCF1F3] px-4 py-3 text-sm text-[#9B5964]">No se pudo sincronizar el buzón: {syncError}</div>}

                    <section className="grid min-h-[680px] overflow-hidden rounded-xl border border-[#DCE3DF] bg-white shadow-[0_14px_35px_rgba(23,50,71,0.07)] lg:grid-cols-[220px_minmax(300px,390px)_minmax(0,1fr)]">
                        <aside className="border-b border-[#E4E8E5] bg-[#F8FAF9] p-3 lg:border-r lg:border-b-0">
                            <Button type="button" onClick={abrirNuevoCorreo} className="mb-5 h-11 w-full justify-center rounded-lg bg-[#6B8F71] text-white shadow-sm hover:bg-[#55765C]"><PenLine className="size-4" />Redactar</Button>
                            <nav className="grid gap-1">
                                {folderItems.map(({ key, label, icon: Icon }) => (
                                    <button key={key} type="button" onClick={() => setFolder(key)} className={`flex h-10 items-center gap-3 rounded-lg px-3 text-left text-sm transition ${folder === key ? 'bg-[#E5F0EA] font-semibold text-[#2F6044]' : 'text-[#52616B] hover:bg-white'}`}>
                                        <Icon className="size-4" />
                                        <span className="flex-1 truncate">{label}</span>
                                        {carpetas[key] > 0 && <span className="text-xs tabular-nums text-[#7A878D]">{carpetas[key]}</span>}
                                    </button>
                                ))}
                            </nav>
                            <div className="mt-7 border-t border-[#E4E8E5] pt-4">
                                <div className="flex items-center justify-between px-3 text-[11px] font-bold tracking-[0.12em] text-[#82908A] uppercase"><span>Etiquetas</span><Tag className="size-3.5" /></div>
                                <p className="mt-3 px-3 text-xs leading-5 text-[#8A9693]">Las etiquetas institucionales se mostrarán aquí cuando estén configuradas.</p>
                            </div>
                            <button type="button" className="mt-5 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-[#52616B] hover:bg-white"><Settings2 className="size-4" />Preferencias</button>
                        </aside>

                        <section className="min-w-0 border-b border-[#E4E8E5] lg:border-r lg:border-b-0">
                            <div className="border-b border-[#E4E8E5] p-4">
                                <div className="flex items-center justify-between gap-2"><div><h2 className="font-semibold text-[#173247]">{folderItems.find((item) => item.key === folder)?.label}</h2><p className="mt-1 text-xs text-[#82908A]">{mensajes.total} comunicaciones</p></div><span className="rounded-full bg-[#EFF5F1] px-2.5 py-1 text-xs font-semibold text-[#397461]">{noLeidos} sin leer</span></div>
                                <div className="relative mt-4"><Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#82908A]" /><Input value={buscar} onChange={(event) => setBuscar(event.target.value)} placeholder="Buscar remitente, asunto o contenido" className="h-10 border-[#D5D1C6] bg-white pl-9 text-sm" /></div>
                                <div className="mt-3 flex gap-2"><label className="flex flex-1 items-center gap-2 text-xs text-[#52616B]"><input type="checkbox" checked={noLeidosFiltro} onChange={(event) => setNoLeidosFiltro(event.target.checked)} className="size-3.5 rounded border-[#B9C5BD] text-[#2C4A5E]" />Sólo no leídos</label><select value={estadoFiltro} onChange={(event) => setEstadoFiltro(event.target.value)} className="h-8 max-w-[150px] rounded-md border border-[#D5D1C6] bg-white px-2 text-xs text-[#52616B] outline-none"><option value="">Todos los estados</option>{estados.map((item) => <option key={item} value={item}>{item}</option>)}</select></div>
                            </div>
                            <div className="divide-y divide-[#EEF0ED] lg:max-h-[530px] lg:overflow-y-auto">
                                {mensajes.data.length === 0 ? <div className="px-5 py-16 text-center"><Mail className="mx-auto size-7 text-[#6B8F71]" /><p className="mt-3 text-sm font-semibold text-[#52616B]">No hay comunicaciones para mostrar.</p><p className="mt-1 text-xs text-[#82908A]">Probá cambiar la carpeta o los filtros.</p></div> : mensajes.data.map((mensaje) => <button type="button" key={mensaje.id} onClick={() => abrirMensaje(mensaje)} className={`block w-full px-4 py-3.5 text-left transition hover:bg-[#F8FAF9] ${mensaje.id === mensajeId ? 'bg-[#EAF3EF]' : mensaje.is_read ? 'bg-white' : 'bg-[#FBFDFB]'}`}><div className="flex items-start gap-3"><span className={`mt-1.5 size-2 shrink-0 rounded-full ${mensaje.is_read ? 'bg-transparent' : 'bg-[#6B8F71]'}`} /><div className="min-w-0 flex-1"><div className="flex items-start justify-between gap-2"><p className={`truncate text-sm text-[#173247] ${mensaje.is_read ? 'font-medium' : 'font-bold'}`}>{mensaje.direction === 'outgoing' ? `Para: ${mensaje.to_email}` : mensaje.from_name || mensaje.from_email}</p><time className="shrink-0 text-[10px] text-[#82908A]">{formatDate(mensaje.received_at ?? mensaje.sent_at)}</time></div><p className="mt-1 truncate text-xs font-medium text-[#52616B]">{mensaje.subject}</p><p className="mt-1 line-clamp-2 text-xs leading-5 text-[#82908A]">{mensaje.preview || 'Sin contenido de texto.'}</p><div className="mt-2 flex items-center gap-2">{mensaje.estado && <span className={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${estadoClasses[mensaje.estado] ?? estadoClasses.Nuevo}`}>{mensaje.estado}</span>}{mensaje.has_attachments && <Paperclip className="size-3 text-[#82908A]" />}{mensaje.is_starred && <Star className="size-3 fill-[#C5964B] text-[#C5964B]" />}</div></div></div></button>)}
                            </div>
                            <div className="flex items-center justify-between border-t border-[#E4E8E5] px-4 py-3 text-xs text-[#82908A]"><span>{mensajes.from ?? 0}-{mensajes.to ?? 0} de {mensajes.total}</span><div className="flex items-center gap-1"><button type="button" disabled={!previousPage?.url} onClick={() => previousPage?.url && router.visit(previousPage.url, { preserveState: true, preserveScroll: true })} className="flex size-7 items-center justify-center rounded-md text-[#52616B] hover:bg-[#F4F6F5] disabled:opacity-40" aria-label="Página anterior"><ChevronLeft className="size-4" /></button><span className="px-2 font-medium text-[#52616B]">{mensajes.current_page} / {mensajes.last_page}</span><button type="button" disabled={!nextPage?.url} onClick={() => nextPage?.url && router.visit(nextPage.url, { preserveState: true, preserveScroll: true })} className="flex size-7 items-center justify-center rounded-md text-[#52616B] hover:bg-[#F4F6F5] disabled:opacity-40" aria-label="Página siguiente"><ChevronRight className="size-4" /></button></div></div>
                        </section>

                        <section className="min-w-0 bg-white">
                            {!detalle ? <div className="flex h-full min-h-[520px] items-center justify-center px-6 text-center"><div><FileText className="mx-auto size-10 text-[#B9C5BD]" /><h2 className="mt-4 text-lg font-semibold text-[#52616B]">Seleccioná un correo</h2><p className="mt-1 max-w-xs text-sm leading-6 text-[#82908A]">El contenido completo y las acciones aparecerán en este panel.</p></div></div> : <div className="h-full"><div className="flex items-center justify-between gap-3 border-b border-[#E4E8E5] px-5 py-4"><div className="min-w-0"><p className="text-xs font-semibold tracking-[0.12em] text-[#6B8F71] uppercase">{detalle.direction === 'outgoing' ? 'Enviado' : 'Comunicación recibida'}</p><h2 className="mt-1 truncate text-xl font-semibold text-[#173247]">{detalle.subject}</h2></div><button type="button" onClick={() => setMensajeId(null)} className="rounded-md p-2 text-[#82908A] hover:bg-[#F4F6F5] lg:hidden" aria-label="Cerrar correo"><X className="size-4" /></button></div><div className="flex flex-wrap items-center gap-2 border-b border-[#EEF0ED] px-5 py-3"><Button type="button" variant="outline" onClick={() => abrirRespuesta(detalle)} disabled={detalle.direction === 'outgoing'} className="h-8 border-[#D5D1C6] text-xs text-[#2C4A5E]"><Reply className="size-3.5" />Responder</Button><Button type="button" variant="outline" onClick={() => abrirClasificacion(detalle)} className="h-8 border-[#D5D1C6] text-xs text-[#2C4A5E]"><Tag className="size-3.5" />Clasificar</Button>{!detalle.is_read && <Button type="button" variant="outline" onClick={() => abrirMensaje(detalle)} className="h-8 border-[#D5D1C6] text-xs text-[#2C4A5E]"><Check className="size-3.5" />Marcar leído</Button>}{cargandoDetalle && <LoaderCircle className="ml-auto size-4 animate-spin text-[#6B8F71]" />}</div><article className="px-5 py-5"><div className="flex flex-wrap items-start justify-between gap-3"><div className="text-sm"><p className="font-semibold text-[#173247]">{detalle.direction === 'outgoing' ? 'Para' : detalle.from_name || 'Remitente desconocido'}</p><p className="mt-1 text-xs text-[#82908A]">{detalle.direction === 'outgoing' ? detalle.to_email : detalle.from_email}</p></div><time className="text-xs text-[#82908A]">{formatDate(detalle.received_at ?? detalle.sent_at)}</time></div><div className="mt-6 max-w-3xl whitespace-pre-wrap text-sm leading-7 text-[#3C4A52]">{detalle.body_text || detalle.preview || 'Sin contenido de texto.'}</div>{detalle.attachments.length > 0 && <div className="mt-7 border-t border-[#EEF0ED] pt-4"><p className="text-xs font-bold tracking-[0.1em] text-[#82908A] uppercase">Adjuntos</p><div className="mt-3 flex flex-wrap gap-2">{detalle.attachments.map((attachment) => <a key={attachment.index} href={mailbox.attachment.url([detalle.id, attachment.index])} download className="inline-flex items-center gap-2 rounded-lg border border-[#DCE3DF] bg-[#F8FAF9] px-3 py-2 text-xs font-semibold text-[#2C4A5E] hover:border-[#6B8F71]"><Paperclip className="size-3.5" /><span>{attachment.name}</span><span className="font-normal text-[#82908A]">{formatBytes(attachment.size)}</span></a>)}</div></div>}<div className="mt-8 flex flex-wrap gap-2 border-t border-[#EEF0ED] pt-4"><span className={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${estadoClasses[detalle.estado] ?? estadoClasses.Nuevo}`}>{detalle.estado}</span>{detalle.legajos.map((legajo) => <span key={legajo.id} className="rounded-full bg-[#F4F6F5] px-2.5 py-1 text-[11px] font-medium text-[#697586]">Legajo {legajo.numero_legajo}</span>)}</div></article></div>}
                        </section>
                    </section>
                </div>
            </main>

            <Dialog open={mensajeSeleccionado !== null} onOpenChange={(open) => !open && setMensajeSeleccionado(null)}><DialogContent className="max-h-[90vh] overflow-y-auto border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-xl"><DialogHeader><DialogTitle className="text-[#173247]">Clasificar comunicación</DialogTitle><DialogDescription className="text-[#697586]">Organizá el oficio, vinculalo a los legajos correspondientes y derivalo a una persona responsable.</DialogDescription></DialogHeader>{mensajeSeleccionado && <div className="space-y-5"><div className="rounded-lg border border-[#E4E8E5] bg-white p-4"><p className="text-sm font-semibold text-[#173247]">{mensajeSeleccionado.subject}</p><p className="mt-1 text-xs text-[#697586]">{mensajeSeleccionado.from_name || mensajeSeleccionado.from_email} · {mensajeSeleccionado.from_email}</p></div><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Estado<select value={estado} onChange={(event) => setEstado(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal">{estados.map((item) => <option key={item} value={item}>{item}</option>)}</select></label><div className="grid gap-2"><label className="text-sm font-semibold text-[#2B2F36]">Legajos vinculados</label><div className="flex flex-wrap gap-2">{legajosSeleccionados.map((legajo) => <span key={legajo.id} className="inline-flex items-center gap-1 rounded-full bg-[#EAF3EF] px-3 py-1.5 text-xs font-semibold text-[#397461]">{legajo.numero_legajo}<button type="button" onClick={() => alternarLegajo(legajo)} aria-label={`Quitar ${legajo.numero_legajo}`}><X className="size-3" /></button></span>)}</div><div className="relative"><Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#82908A]" /><Input value={legajoQuery} onChange={(event) => setLegajoQuery(event.target.value)} placeholder="Buscar por DNI, nombre o número de legajo" className="h-10 border-[#D5D1C6] bg-white pl-9" /></div>{legajosEncontrados.length > 0 && <div className="divide-y divide-[#EEF0ED] overflow-hidden rounded-md border border-[#E4E8E5] bg-white">{legajosEncontrados.map((legajo) => <button key={legajo.id} type="button" onClick={() => alternarLegajo(legajo)} className="flex w-full items-center justify-between px-3 py-2.5 text-left text-sm hover:bg-[#F4F7F5]"><span><strong className="text-[#173247]">{legajo.numero_legajo}</strong><small className="ml-2 text-[#697586]">{legajo.nombre}</small></span><span className="text-xs font-semibold text-[#6B8F71]">Vincular</span></button>)}</div>}</div>{estado === 'Derivado' && <div className="grid gap-4 sm:grid-cols-2"><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Responsable<select value={asignadoA} onChange={(event) => setAsignadoA(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal"><option value="">Seleccionar persona</option>{responsables.map((responsable) => <option key={responsable.id} value={responsable.id}>{responsable.name}</option>)}</select></label><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Vencimiento<Input type="date" value={fechaLimite} onChange={(event) => setFechaLimite(event.target.value)} className="h-10 border-[#D5D1C6] bg-white" /></label></div>}</div>}<DialogFooter><Button type="button" variant="outline" onClick={() => setMensajeSeleccionado(null)}>Cancelar</Button><Button type="button" onClick={guardarClasificacion} className="bg-[#2C4A5E] text-white hover:bg-[#173247]">Guardar clasificación</Button></DialogFooter></DialogContent></Dialog>

            <Dialog open={composeOpen} onOpenChange={setComposeOpen}><DialogContent className="border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-xl"><DialogHeader><DialogTitle className="text-[#173247]">{composeReplyId ? 'Responder correo' : 'Nuevo correo'}</DialogTitle><DialogDescription className="text-[#697586]">El mensaje se enviará desde la cuenta institucional autorizada.</DialogDescription></DialogHeader><form onSubmit={enviarCorreo} className="grid gap-4">{!composeReplyId && <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Destinatario<Input type="email" required value={composeTo} onChange={(event) => setComposeTo(event.target.value)} placeholder="correo@ejemplo.com" className="border-[#D5D1C6] bg-white" /></label>}<div className="grid gap-4 sm:grid-cols-2"><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">CC<Input type="email" value={composeCc} onChange={(event) => setComposeCc(event.target.value)} placeholder="copia@ejemplo.com" className="border-[#D5D1C6] bg-white" /></label><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">CCO<Input type="email" value={composeBcc} onChange={(event) => setComposeBcc(event.target.value)} placeholder="oculta@ejemplo.com" className="border-[#D5D1C6] bg-white" /></label></div><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Asunto<Input required value={composeSubject} onChange={(event) => setComposeSubject(event.target.value)} className="border-[#D5D1C6] bg-white" /></label><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Mensaje<textarea required value={composeBody} onChange={(event) => setComposeBody(event.target.value)} rows={8} className="rounded-md border border-[#D5D1C6] bg-white px-3 py-2 text-sm outline-none focus:border-[#2C4A5E]" /></label><label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Adjuntos<Input type="file" multiple onChange={(event) => setComposeFiles(Array.from(event.target.files ?? []))} className="border-[#D5D1C6] bg-white text-sm font-normal" /></label><DialogFooter><Button type="button" variant="outline" onClick={() => setComposeOpen(false)}>Cancelar</Button><Button type="submit" disabled={sending} className="bg-[#2C4A5E] text-white hover:bg-[#173247]"><Mail className="size-4" />{sending ? 'Enviando…' : 'Enviar correo'}</Button></DialogFooter></form></DialogContent></Dialog>
        </>
    );
}
