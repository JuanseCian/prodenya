import { Head, Link, router } from '@inertiajs/react';
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
    SlidersHorizontal,
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
import legajosRoutes from '@/routes/legajos';
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
    clasificacion_categoria: string | null;
    clasificacion_categoria_label: string | null;
    clasificacion_estado: string | null;
    clasificacion_confianza: number | null;
    clasificacion_metodo: string | null;
    clasificacion_revisado_en: string | null;
    clasificacion_revisado_por: number | null;
    clasificacion_motivos: string[];
    prioridad: string | null;
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
        clasificacion: string | null;
        clasificacion_estado: string | null;
        prioridad: string | null;
        remitente: string | null;
        asunto: string | null;
        con_adjuntos: boolean | null;
        legajo_id: number | null;
        folder: Folder;
    };
    carpetas: Record<Folder, number>;
    noLeidos: number;
    estados: string[];
    clasificaciones: { value: string; label: string }[];
    clasificacionEstados: string[];
    prioridades: string[];
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
    Nuevo: 'border-primary/20 bg-secondary text-secondary-foreground',
    Clasificado: 'border-success/25 bg-success/10 text-success',
    Derivado: 'border-warning/30 bg-warning/10 text-warning',
    Respondido: 'border-success/25 bg-success/10 text-success',
    Desestimado: 'border-destructive/25 bg-destructive/10 text-destructive',
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
    clasificaciones,
    clasificacionEstados,
    prioridades,
    responsables,
    syncError,
    googleConfigured,
    googleConnected,
    googleEmail,
    googleSendAccess,
}: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [noLeidosFiltro, setNoLeidosFiltro] = useState(filtros.no_leidos);
    const [soloAdjuntosFiltro, setSoloAdjuntosFiltro] = useState(
        filtros.con_adjuntos ?? false,
    );
    const [estadoFiltro, setEstadoFiltro] = useState(filtros.estado ?? '');
    const [clasificacionFiltro, setClasificacionFiltro] = useState(
        filtros.clasificacion ?? '',
    );
    const [clasificacionEstadoFiltro, setClasificacionEstadoFiltro] = useState(
        filtros.clasificacion_estado ?? '',
    );
    const [prioridadFiltro, setPrioridadFiltro] = useState(
        filtros.prioridad ?? '',
    );
    const [folder, setFolder] = useState<Folder>(filtros.folder);
    const [mensajeId, setMensajeId] = useState<number | null>(null);
    const [mensajeDetalle, setMensajeDetalle] = useState<Mensaje | null>(null);
    const [mensajeSeleccionado, setMensajeSeleccionado] =
        useState<Mensaje | null>(null);
    const [estado, setEstado] = useState('Clasificado');
    const [clasificacionCategoria, setClasificacionCategoria] = useState('');
    const [clasificacionPrioridad, setClasificacionPrioridad] =
        useState('normal');
    const [asignadoA, setAsignadoA] = useState('');
    const [fechaLimite, setFechaLimite] = useState('');
    const [legajoQuery, setLegajoQuery] = useState('');
    const [legajosEncontrados, setLegajosEncontrados] = useState<
        LegajoResultado[]
    >([]);
    const [legajosSeleccionados, setLegajosSeleccionados] = useState<
        LegajoResultado[]
    >([]);
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
    const [filtrosAbiertos, setFiltrosAbiertos] = useState(
        Boolean(
            filtros.estado ||
            filtros.clasificacion ||
            filtros.clasificacion_estado ||
            filtros.prioridad,
        ),
    );

    useEffect(() => {
        if (
            buscar === (filtros.buscar ?? '') &&
            noLeidosFiltro === filtros.no_leidos &&
            soloAdjuntosFiltro === (filtros.con_adjuntos ?? false) &&
            estadoFiltro === (filtros.estado ?? '') &&
            clasificacionFiltro === (filtros.clasificacion ?? '') &&
            clasificacionEstadoFiltro ===
                (filtros.clasificacion_estado ?? '') &&
            prioridadFiltro === (filtros.prioridad ?? '') &&
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
                        ...(soloAdjuntosFiltro ? { con_adjuntos: 1 } : {}),
                        ...(estadoFiltro ? { estado: estadoFiltro } : {}),
                        ...(clasificacionFiltro
                            ? { clasificacion: clasificacionFiltro }
                            : {}),
                        ...(clasificacionEstadoFiltro
                            ? {
                                  clasificacion_estado:
                                      clasificacionEstadoFiltro,
                              }
                            : {}),
                        ...(prioridadFiltro
                            ? { prioridad: prioridadFiltro }
                            : {}),
                        ...(folder !== 'inbox' ? { folder } : {}),
                    },
                }),
                {},
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [
        buscar,
        noLeidosFiltro,
        soloAdjuntosFiltro,
        estadoFiltro,
        clasificacionFiltro,
        clasificacionEstadoFiltro,
        prioridadFiltro,
        folder,
        filtros,
    ]);

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
            .then((result: { message: Mensaje }) =>
                setMensajeDetalle(result.message),
            )
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
            fetch(
                mailbox.legajos.buscar.url({ query: { buscar: legajoQuery } }),
            )
                .then((response) => response.json())
                .then((result: LegajoResultado[]) =>
                    setLegajosEncontrados(result),
                )
                .catch(() => setLegajosEncontrados([]));
        }, 250);

        return () => clearTimeout(timeout);
    }, [legajoQuery, mensajeSeleccionado]);

    const mensajeLista = useMemo(
        () => mensajes.data.find((mensaje) => mensaje.id === mensajeId) ?? null,
        [mensajes.data, mensajeId],
    );

    const abrirMensaje = (mensaje: Mensaje) => {
        if (mensaje.id !== mensajeId) {
            setMensajeDetalle(null);
        }

        setMensajeId(mensaje.id);

        if (!mensaje.is_read) {
            router.patch(
                mailbox.read.url(mensaje.id),
                {},
                { preserveState: true, preserveScroll: true },
            );
        }
    };

    const abrirClasificacion = (mensaje: Mensaje) => {
        setMensajeSeleccionado(mensaje);
        setEstado(mensaje.estado === 'Nuevo' ? 'Clasificado' : mensaje.estado);
        setClasificacionCategoria(mensaje.clasificacion_categoria ?? '');
        setClasificacionPrioridad(mensaje.prioridad ?? 'normal');
        setAsignadoA(mensaje.derivacion?.asignado_a?.toString() ?? '');
        setFechaLimite(mensaje.derivacion?.fecha_limite ?? '');
        setLegajoQuery('');
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
                ...(clasificacionCategoria
                    ? { clasificacion_categoria: clasificacionCategoria }
                    : {}),
                ...(clasificacionPrioridad
                    ? { prioridad: clasificacionPrioridad }
                    : {}),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setMensajeSeleccionado(null);
                    setMensajeDetalle(null);
                },
            },
        );
    };

    const confirmarClasificacion = (mensaje: Mensaje) => {
        router.patch(
            mailbox.classification.confirm.url(mensaje.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => setMensajeDetalle(null),
            },
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
        setComposeSubject(
            mensaje.subject.startsWith('Re:')
                ? mensaje.subject
                : `Re: ${mensaje.subject}`,
        );
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
        router.post(
            mailbox.sync.url(),
            {},
            { preserveScroll: true, onFinish: () => setSyncing(false) },
        );
    };

    const desvincularGmail = () => {
        router.post(
            mailbox.google.disconnect.url(),
            {},
            { preserveScroll: true },
        );
    };

    const detalle = mensajeId ? (mensajeDetalle ?? mensajeLista) : null;
    const cargandoDetalle = mensajeId !== null && mensajeDetalle === null;
    const previousPage = mensajes.links[0];
    const nextPage = mensajes.links[mensajes.links.length - 1];

    const filtrosActivos = [
        estadoFiltro,
        clasificacionFiltro,
        clasificacionEstadoFiltro,
        prioridadFiltro,
    ].filter(Boolean).length;

    const limpiarFiltros = () => {
        setEstadoFiltro('');
        setClasificacionFiltro('');
        setClasificacionEstadoFiltro('');
        setPrioridadFiltro('');
        setNoLeidosFiltro(false);
        setSoloAdjuntosFiltro(false);
    };

    const carpetaActual = folderItems.find((item) => item.key === folder);
    const selectClass =
        'h-9 min-w-0 rounded-xl border-input bg-background px-3 text-xs text-foreground outline-none focus:border-ring focus:ring-2 focus:ring-ring/30';
    const chipClass = (activo: boolean) =>
        `inline-flex h-8 shrink-0 items-center gap-1.5 rounded-full border px-3 text-xs font-semibold transition-colors ${
            activo
                ? 'border-primary bg-primary text-primary-foreground'
                : 'border-border bg-background text-muted-foreground hover:bg-accent hover:text-foreground'
        }`;

    return (
        <>
            <Head title="Mailbox institucional" />
            <main className="admin-page-shell min-h-screen bg-background px-4 py-5 text-foreground sm:px-6 sm:py-6 lg:px-8">
                <div className="mx-auto max-w-[1440px]">
                    <header className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div className="min-w-0">
                            <p className="text-[length:calc(11px_+_2px)] font-bold tracking-[0.18em] text-primary uppercase">
                                Comunicaciones
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-[-0.03em] text-foreground sm:text-3xl">
                                Mailbox institucional
                            </h1>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {googleConnected ? (
                                <div className="flex h-9 items-center gap-2 rounded-xl border border-border bg-card pr-1 pl-3 text-xs">
                                    <span className="size-2 rounded-full bg-success" />
                                    <span className="max-w-[220px] truncate font-medium text-foreground">
                                        {googleEmail}
                                    </span>
                                    {googleConfigured && !googleSendAccess && (
                                        <a
                                            href={mailbox.google.redirect.url()}
                                            className="rounded-lg bg-warning/10 px-2 py-1 font-semibold text-warning hover:bg-warning/15"
                                        >
                                            Autorizar envíos
                                        </a>
                                    )}
                                    <button
                                        type="button"
                                        onClick={desvincularGmail}
                                        className="rounded-lg px-2 py-1 text-muted-foreground hover:bg-accent hover:text-foreground"
                                    >
                                        Desvincular
                                    </button>
                                </div>
                            ) : googleConfigured ? (
                                <a
                                    href={mailbox.google.redirect.url()}
                                    className="inline-flex h-9 items-center gap-2 rounded-xl border border-input bg-card px-3 text-xs font-semibold text-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                >
                                    <span className="size-2 rounded-full bg-muted-foreground/50" />
                                    Vincular Gmail
                                </a>
                            ) : (
                                <Badge className="h-9 border-0 bg-warning/10 px-3 text-warning">
                                    Gmail OAuth sin configurar
                                </Badge>
                            )}
                            <Button
                                type="button"
                                variant="outline"
                                onClick={sincronizar}
                                disabled={syncing}
                                className="h-9 rounded-xl bg-card text-xs"
                                title="Traer correos nuevos"
                            >
                                <RefreshCw
                                    className={`size-3.5 ${syncing ? 'animate-spin' : ''}`}
                                />
                                <span className="hidden sm:inline">
                                    {syncing ? 'Actualizando…' : 'Actualizar'}
                                </span>
                            </Button>
                            <Button
                                type="button"
                                onClick={abrirNuevoCorreo}
                                className="h-9 rounded-xl px-4 text-xs"
                            >
                                <PenLine className="size-3.5" />
                                Redactar
                            </Button>
                        </div>
                    </header>

                    {syncError && (
                        <div className="mb-4 rounded-xl border border-destructive/25 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                            No se pudo sincronizar el buzón: {syncError}
                        </div>
                    )}

                    <section className="grid overflow-hidden rounded-[1.5rem] border border-border bg-card shadow-[0_22px_60px_-38px_rgba(23,50,71,0.45)] lg:h-[calc(100svh-11rem)] lg:min-h-[560px] lg:grid-cols-[200px_minmax(320px,380px)_minmax(0,1fr)]">
                        <aside className="min-w-0 border-b border-border bg-surface-soft p-3 lg:overflow-y-auto lg:border-r lg:border-b-0">
                            <nav
                                aria-label="Carpetas"
                                className="flex gap-1 overflow-x-auto lg:grid"
                            >
                                {folderItems.map(
                                    ({ key, label, icon: Icon }) => (
                                        <button
                                            key={key}
                                            type="button"
                                            onClick={() => {
                                                setFolder(key);
                                                setMensajeId(null);
                                            }}
                                            aria-current={
                                                folder === key
                                                    ? 'page'
                                                    : undefined
                                            }
                                            className={`flex h-10 shrink-0 items-center gap-3 rounded-xl px-3 text-left text-sm transition-colors ${folder === key ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:bg-card hover:text-foreground'}`}
                                        >
                                            <Icon className="size-4" />
                                            <span className="flex-1 truncate">
                                                {label}
                                            </span>
                                            {carpetas[key] > 0 && (
                                                <span
                                                    className={`text-xs tabular-nums ${folder === key ? 'text-primary-foreground/75' : 'text-muted-foreground'}`}
                                                >
                                                    {carpetas[key]}
                                                </span>
                                            )}
                                        </button>
                                    ),
                                )}
                            </nav>

                            {clasificaciones.length > 0 && (
                                <div className="mt-5 hidden border-t border-border pt-4 lg:block">
                                    <p className="flex items-center justify-between px-3 text-[length:calc(11px_+_2px)] font-bold tracking-[0.12em] text-muted-foreground uppercase">
                                        Categorías
                                        <Tag className="size-3.5" />
                                    </p>
                                    <div className="mt-2 grid gap-0.5">
                                        {clasificaciones.map((item) => {
                                            const activa =
                                                clasificacionFiltro ===
                                                item.value;

                                            return (
                                                <button
                                                    key={item.value}
                                                    type="button"
                                                    onClick={() =>
                                                        setClasificacionFiltro(
                                                            activa
                                                                ? ''
                                                                : item.value,
                                                        )
                                                    }
                                                    aria-pressed={activa}
                                                    className={`truncate rounded-lg px-3 py-1.5 text-left text-xs transition-colors ${activa ? 'bg-primary/10 font-semibold text-primary' : 'text-muted-foreground hover:bg-card hover:text-foreground'}`}
                                                >
                                                    {item.label}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </aside>

                        <section
                            className={`min-w-0 flex-col border-b border-border lg:flex lg:min-h-0 lg:border-r lg:border-b-0 ${mensajeId ? 'hidden' : 'flex'}`}
                        >
                            <div className="border-b border-border p-4">
                                <div className="flex items-baseline justify-between gap-2">
                                    <h2 className="font-semibold text-foreground">
                                        {carpetaActual?.label}
                                    </h2>
                                    <p className="text-xs text-muted-foreground">
                                        {mensajes.total} ·{' '}
                                        <span className="font-semibold text-primary">
                                            {noLeidos} sin leer
                                        </span>
                                    </p>
                                </div>
                                <div className="mt-3 flex gap-2">
                                    <div className="relative min-w-0 flex-1">
                                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            value={buscar}
                                            onChange={(event) =>
                                                setBuscar(event.target.value)
                                            }
                                            placeholder="Buscar remitente, asunto o contenido"
                                            aria-label="Buscar correos"
                                            className="h-10 rounded-xl bg-background pl-9 text-sm"
                                        />
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() =>
                                            setFiltrosAbiertos(!filtrosAbiertos)
                                        }
                                        aria-expanded={filtrosAbiertos}
                                        aria-controls="mailbox-filtros"
                                        className={`h-10 rounded-xl text-xs ${filtrosAbiertos || filtrosActivos > 0 ? 'border-primary/40 bg-primary/5 text-primary' : 'bg-background'}`}
                                    >
                                        <SlidersHorizontal className="size-3.5" />
                                        Filtros
                                        {filtrosActivos > 0 && (
                                            <span className="rounded-full bg-primary px-1.5 text-[length:calc(10px_+_2px)] text-primary-foreground">
                                                {filtrosActivos}
                                            </span>
                                        )}
                                    </Button>
                                </div>
                                <div className="mt-3 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setNoLeidosFiltro(!noLeidosFiltro)
                                        }
                                        aria-pressed={noLeidosFiltro}
                                        className={chipClass(noLeidosFiltro)}
                                    >
                                        <Mail className="size-3" />
                                        No leídos
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setSoloAdjuntosFiltro(
                                                !soloAdjuntosFiltro,
                                            )
                                        }
                                        aria-pressed={soloAdjuntosFiltro}
                                        className={chipClass(
                                            soloAdjuntosFiltro,
                                        )}
                                    >
                                        <Paperclip className="size-3" />
                                        Con adjuntos
                                    </button>
                                    {(filtrosActivos > 0 ||
                                        noLeidosFiltro ||
                                        soloAdjuntosFiltro) && (
                                        <button
                                            type="button"
                                            onClick={limpiarFiltros}
                                            className="ml-auto h-8 rounded-full px-2 text-xs font-semibold text-muted-foreground hover:text-foreground"
                                        >
                                            Limpiar
                                        </button>
                                    )}
                                </div>
                                {filtrosAbiertos && (
                                    <div
                                        id="mailbox-filtros"
                                        className="mt-3 grid grid-cols-2 gap-2 rounded-xl border border-border bg-surface-soft p-2"
                                    >
                                        <select
                                            value={estadoFiltro}
                                            onChange={(event) =>
                                                setEstadoFiltro(
                                                    event.target.value,
                                                )
                                            }
                                            aria-label="Estado"
                                            className={selectClass}
                                        >
                                            <option value="">
                                                Todos los estados
                                            </option>
                                            {estados.map((item) => (
                                                <option key={item} value={item}>
                                                    {item}
                                                </option>
                                            ))}
                                        </select>
                                        <select
                                            value={prioridadFiltro}
                                            onChange={(event) =>
                                                setPrioridadFiltro(
                                                    event.target.value,
                                                )
                                            }
                                            aria-label="Prioridad"
                                            className={selectClass}
                                        >
                                            <option value="">
                                                Toda prioridad
                                            </option>
                                            {prioridades.map((item) => (
                                                <option key={item} value={item}>
                                                    Prioridad {item}
                                                </option>
                                            ))}
                                        </select>
                                        <select
                                            value={clasificacionFiltro}
                                            onChange={(event) =>
                                                setClasificacionFiltro(
                                                    event.target.value,
                                                )
                                            }
                                            aria-label="Categoría"
                                            className={selectClass}
                                        >
                                            <option value="">
                                                Todas las categorías
                                            </option>
                                            {clasificaciones.map((item) => (
                                                <option
                                                    key={item.value}
                                                    value={item.value}
                                                >
                                                    {item.label}
                                                </option>
                                            ))}
                                        </select>
                                        <select
                                            value={clasificacionEstadoFiltro}
                                            onChange={(event) =>
                                                setClasificacionEstadoFiltro(
                                                    event.target.value,
                                                )
                                            }
                                            aria-label="Revisión de la clasificación"
                                            className={selectClass}
                                        >
                                            <option value="">
                                                Toda revisión
                                            </option>
                                            {clasificacionEstados.map(
                                                (item) => (
                                                    <option
                                                        key={item}
                                                        value={item}
                                                    >
                                                        {item.replaceAll(
                                                            '_',
                                                            ' ',
                                                        )}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                    </div>
                                )}
                            </div>

                            <div className="min-h-[320px] flex-1 space-y-1 p-2 lg:min-h-0 lg:overflow-y-auto">
                                {mensajes.data.length === 0 ? (
                                    <div className="px-5 py-16 text-center">
                                        <Mail className="mx-auto size-7 text-primary" />
                                        <p className="mt-3 text-sm font-semibold text-foreground">
                                            No hay comunicaciones para mostrar.
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            Probá cambiar la carpeta o los
                                            filtros.
                                        </p>
                                    </div>
                                ) : (
                                    mensajes.data.map((mensaje) => (
                                        <button
                                            type="button"
                                            key={mensaje.id}
                                            onClick={() =>
                                                abrirMensaje(mensaje)
                                            }
                                            aria-current={
                                                mensaje.id === mensajeId
                                                    ? 'true'
                                                    : undefined
                                            }
                                            className={`block w-full rounded-xl px-3.5 py-3 text-left transition-colors hover:bg-accent ${mensaje.id === mensajeId ? 'bg-secondary ring-1 ring-primary/20' : mensaje.is_read ? 'bg-card' : 'bg-surface-soft'}`}
                                        >
                                            <div className="flex items-start gap-3">
                                                <span
                                                    className={`mt-1.5 size-2 shrink-0 rounded-full ${mensaje.is_read ? 'bg-transparent' : 'bg-primary'}`}
                                                />
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex items-start justify-between gap-2">
                                                        <p
                                                            className={`truncate text-sm text-foreground ${mensaje.is_read ? 'font-medium' : 'font-bold'}`}
                                                        >
                                                            {mensaje.direction ===
                                                            'outgoing'
                                                                ? `Para: ${mensaje.to_email}`
                                                                : mensaje.from_name ||
                                                                  mensaje.from_email}
                                                        </p>
                                                        <span className="flex shrink-0 items-center gap-1.5">
                                                            {mensaje.has_attachments && (
                                                                <Paperclip className="size-3 text-muted-foreground" />
                                                            )}
                                                            {mensaje.is_starred && (
                                                                <Star className="size-3 fill-warning text-warning" />
                                                            )}
                                                            <time className="text-[length:calc(10px_+_2px)] text-muted-foreground">
                                                                {formatDate(
                                                                    mensaje.received_at ??
                                                                        mensaje.sent_at,
                                                                )}
                                                            </time>
                                                        </span>
                                                    </div>
                                                    <p className="mt-0.5 truncate text-xs font-medium text-foreground/80">
                                                        {mensaje.subject}
                                                    </p>
                                                    <p className="mt-0.5 line-clamp-1 text-xs leading-5 text-muted-foreground">
                                                        {mensaje.preview ||
                                                            'Sin contenido de texto.'}
                                                    </p>
                                                    <div className="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                        {mensaje.estado && (
                                                            <span
                                                                className={`rounded-full border px-2 py-0.5 text-[length:calc(10px_+_2px)] font-semibold ${estadoClasses[mensaje.estado] ?? estadoClasses.Nuevo}`}
                                                            >
                                                                {mensaje.estado}
                                                            </span>
                                                        )}
                                                        {mensaje.clasificacion_categoria_label && (
                                                            <span className="max-w-[150px] truncate rounded-full border border-primary/20 bg-primary/5 px-2 py-0.5 text-[length:calc(10px_+_2px)] font-semibold text-primary">
                                                                {
                                                                    mensaje.clasificacion_categoria_label
                                                                }
                                                            </span>
                                                        )}
                                                        {mensaje.prioridad &&
                                                            mensaje.prioridad !==
                                                                'normal' && (
                                                                <span className="rounded-full border border-warning/25 bg-warning/10 px-2 py-0.5 text-[length:calc(10px_+_2px)] font-semibold text-warning">
                                                                    {
                                                                        mensaje.prioridad
                                                                    }
                                                                </span>
                                                            )}
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    ))
                                )}
                            </div>
                            <div className="flex items-center justify-between border-t border-border px-4 py-3 text-xs text-muted-foreground">
                                <span>
                                    {mensajes.from ?? 0}-{mensajes.to ?? 0} de{' '}
                                    {mensajes.total}
                                </span>
                                <div className="flex items-center gap-1">
                                    <button
                                        type="button"
                                        disabled={!previousPage?.url}
                                        onClick={() =>
                                            previousPage?.url &&
                                            router.visit(previousPage.url, {
                                                preserveState: true,
                                                preserveScroll: true,
                                            })
                                        }
                                        className="flex size-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent hover:text-foreground disabled:opacity-40"
                                        aria-label="Página anterior"
                                    >
                                        <ChevronLeft className="size-4" />
                                    </button>
                                    <span className="px-2 font-medium text-foreground/75">
                                        {mensajes.current_page} /{' '}
                                        {mensajes.last_page}
                                    </span>
                                    <button
                                        type="button"
                                        disabled={!nextPage?.url}
                                        onClick={() =>
                                            nextPage?.url &&
                                            router.visit(nextPage.url, {
                                                preserveState: true,
                                                preserveScroll: true,
                                            })
                                        }
                                        className="flex size-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent hover:text-foreground disabled:opacity-40"
                                        aria-label="Página siguiente"
                                    >
                                        <ChevronRight className="size-4" />
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section
                            className={`min-w-0 flex-col bg-card lg:flex lg:min-h-0 ${mensajeId ? 'flex' : 'hidden'}`}
                        >
                            {!detalle ? (
                                <div className="flex h-full min-h-[520px] items-center justify-center px-6 text-center">
                                    <div>
                                        <FileText className="mx-auto size-10 text-border" />
                                        <h2 className="mt-4 text-lg font-semibold text-foreground/75">
                                            Seleccioná un correo
                                        </h2>
                                        <p className="mt-1 max-w-xs text-sm leading-6 text-muted-foreground">
                                            El contenido completo y las acciones
                                            aparecerán en este panel.
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <>
                                    <div className="flex flex-wrap items-center gap-2 border-b border-border px-4 py-3 sm:px-6">
                                        <button
                                            type="button"
                                            onClick={() => setMensajeId(null)}
                                            className="inline-flex h-9 items-center gap-1 rounded-xl pr-3 pl-2 text-xs font-semibold text-muted-foreground hover:bg-accent hover:text-foreground lg:hidden"
                                        >
                                            <ChevronLeft className="size-4" />
                                            Bandeja
                                        </button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                abrirRespuesta(detalle)
                                            }
                                            disabled={
                                                detalle.direction === 'outgoing'
                                            }
                                            className="h-9 rounded-xl bg-background text-xs"
                                        >
                                            <Reply className="size-3.5" />
                                            Responder
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                abrirClasificacion(detalle)
                                            }
                                            className="h-9 rounded-xl bg-background text-xs"
                                        >
                                            <Tag className="size-3.5" />
                                            Clasificar
                                        </Button>
                                        {detalle.clasificacion_estado ===
                                            'sugerido_automaticamente' && (
                                            <Button
                                                type="button"
                                                onClick={() =>
                                                    confirmarClasificacion(
                                                        detalle,
                                                    )
                                                }
                                                className="h-9 rounded-xl text-xs"
                                            >
                                                <Check className="size-3.5" />
                                                Confirmar sugerencia
                                            </Button>
                                        )}
                                        {cargandoDetalle && (
                                            <LoaderCircle className="ml-auto size-4 animate-spin text-primary" />
                                        )}
                                    </div>

                                    <article className="flex-1 px-4 py-5 sm:px-7 sm:py-6 lg:min-h-0 lg:overflow-y-auto">
                                        <p className="text-xs font-semibold tracking-[0.12em] text-primary uppercase">
                                            {detalle.direction === 'outgoing'
                                                ? 'Enviado'
                                                : 'Comunicación recibida'}
                                        </p>
                                        <h2 className="mt-1 text-xl leading-snug font-semibold tracking-[-0.02em] break-words text-foreground">
                                            {detalle.subject}
                                        </h2>
                                        <div className="mt-3 flex flex-wrap gap-1.5">
                                            <span
                                                className={`rounded-full border px-2.5 py-1 text-[length:calc(11px_+_2px)] font-semibold ${estadoClasses[detalle.estado] ?? estadoClasses.Nuevo}`}
                                            >
                                                {detalle.estado}
                                            </span>
                                            {detalle.clasificacion_categoria_label && (
                                                <span className="rounded-full border border-primary/20 bg-primary/5 px-2.5 py-1 text-[length:calc(11px_+_2px)] font-semibold text-primary">
                                                    {
                                                        detalle.clasificacion_categoria_label
                                                    }
                                                </span>
                                            )}
                                            {detalle.prioridad && (
                                                <span className="rounded-full border border-warning/25 bg-warning/10 px-2.5 py-1 text-[length:calc(11px_+_2px)] font-semibold text-warning">
                                                    Prioridad{' '}
                                                    {detalle.prioridad}
                                                </span>
                                            )}
                                            {detalle.legajos.map((legajo) => (
                                                <Link
                                                    key={legajo.id}
                                                    href={legajosRoutes.show.url(
                                                        legajo.id,
                                                    )}
                                                    className="rounded-full bg-muted px-2.5 py-1 text-[length:calc(11px_+_2px)] font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                                >
                                                    Legajo{' '}
                                                    {legajo.numero_legajo}
                                                </Link>
                                            ))}
                                        </div>

                                        <div className="mt-5 flex items-start justify-between gap-3 rounded-2xl border border-border bg-surface-soft px-4 py-3">
                                            <div className="flex min-w-0 items-center gap-3">
                                                <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                                                    {(
                                                        (detalle.direction ===
                                                        'outgoing'
                                                            ? detalle.to_email
                                                            : detalle.from_name ||
                                                              detalle.from_email) ??
                                                        '?'
                                                    )
                                                        .charAt(0)
                                                        .toUpperCase()}
                                                </span>
                                                <div className="min-w-0 text-sm">
                                                    <p className="truncate font-semibold text-foreground">
                                                        {detalle.direction ===
                                                        'outgoing'
                                                            ? `Para: ${detalle.to_email ?? ''}`
                                                            : detalle.from_name ||
                                                              'Remitente desconocido'}
                                                    </p>
                                                    <p className="truncate text-xs text-muted-foreground">
                                                        {detalle.direction ===
                                                        'outgoing'
                                                            ? (googleEmail ??
                                                              '')
                                                            : detalle.from_email}
                                                        {detalle.cc_email &&
                                                            ` · CC: ${detalle.cc_email}`}
                                                    </p>
                                                </div>
                                            </div>
                                            <time className="shrink-0 text-xs text-muted-foreground">
                                                {formatDate(
                                                    detalle.received_at ??
                                                        detalle.sent_at,
                                                )}
                                            </time>
                                        </div>

                                        <div className="mt-6 max-w-3xl text-sm leading-7 break-words whitespace-pre-wrap text-foreground/80">
                                            {detalle.body_text ||
                                                detalle.preview ||
                                                'Sin contenido de texto.'}
                                        </div>

                                        {detalle.attachments.length > 0 && (
                                            <div className="mt-8 border-t border-border pt-5">
                                                <p className="text-xs font-bold tracking-[0.1em] text-muted-foreground uppercase">
                                                    Adjuntos (
                                                    {detalle.attachments.length}
                                                    )
                                                </p>
                                                <div className="mt-3 flex flex-wrap gap-2">
                                                    {detalle.attachments.map(
                                                        (attachment) => (
                                                            <a
                                                                key={
                                                                    attachment.index
                                                                }
                                                                href={mailbox.attachment.url(
                                                                    [
                                                                        detalle.id,
                                                                        attachment.index,
                                                                    ],
                                                                )}
                                                                download
                                                                className="inline-flex max-w-full items-center gap-2 rounded-xl border border-border bg-surface-soft px-3 py-2 text-xs font-semibold text-foreground transition-colors hover:border-primary/50 hover:bg-accent"
                                                            >
                                                                <Paperclip className="size-3.5 shrink-0" />
                                                                <span className="truncate">
                                                                    {
                                                                        attachment.name
                                                                    }
                                                                </span>
                                                                <span className="shrink-0 font-normal text-muted-foreground">
                                                                    {formatBytes(
                                                                        attachment.size,
                                                                    )}
                                                                </span>
                                                            </a>
                                                        ),
                                                    )}
                                                </div>
                                            </div>
                                        )}

                                        {detalle.clasificacion_motivos.length >
                                            0 && (
                                            <div className="mt-6 rounded-2xl border border-border bg-surface-soft p-4">
                                                <div className="flex items-center justify-between gap-3">
                                                    <p className="text-xs font-bold tracking-[0.1em] text-muted-foreground uppercase">
                                                        Trazabilidad de
                                                        clasificación
                                                    </p>
                                                    {detalle.clasificacion_confianza !==
                                                        null && (
                                                        <span className="text-xs font-semibold text-primary">
                                                            {Math.round(
                                                                detalle.clasificacion_confianza *
                                                                    100,
                                                            )}
                                                            % de confianza
                                                        </span>
                                                    )}
                                                </div>
                                                <ul className="mt-2 space-y-1 text-xs leading-5 text-muted-foreground">
                                                    {detalle.clasificacion_motivos.map(
                                                        (motivo) => (
                                                            <li key={motivo}>
                                                                • {motivo}
                                                            </li>
                                                        ),
                                                    )}
                                                </ul>
                                            </div>
                                        )}
                                    </article>
                                </>
                            )}
                        </section>
                    </section>
                </div>
            </main>

            <Dialog
                open={mensajeSeleccionado !== null}
                onOpenChange={(open) => !open && setMensajeSeleccionado(null)}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Clasificar comunicación</DialogTitle>
                        <DialogDescription>
                            Organizá el oficio, vinculalo a los legajos
                            correspondientes y derivalo a una persona
                            responsable.
                        </DialogDescription>
                    </DialogHeader>
                    {mensajeSeleccionado && (
                        <div className="space-y-5">
                            <div className="rounded-2xl border border-border bg-surface-soft p-4">
                                <p className="text-sm font-semibold text-foreground">
                                    {mensajeSeleccionado.subject}
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {mensajeSeleccionado.from_name ||
                                        mensajeSeleccionado.from_email}{' '}
                                    · {mensajeSeleccionado.from_email}
                                </p>
                            </div>
                            <label className="grid gap-2 text-sm font-semibold text-foreground">
                                Categoría institucional
                                <select
                                    value={clasificacionCategoria}
                                    onChange={(event) =>
                                        setClasificacionCategoria(
                                            event.target.value,
                                        )
                                    }
                                    className="h-10 rounded-xl border-input bg-background px-3 text-sm font-normal"
                                >
                                    <option value="">
                                        Sin cambiar categoría
                                    </option>
                                    {clasificaciones.map((item) => (
                                        <option
                                            key={item.value}
                                            value={item.value}
                                        >
                                            {item.label}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="grid gap-2 text-sm font-semibold text-foreground">
                                Prioridad
                                <select
                                    value={clasificacionPrioridad}
                                    onChange={(event) =>
                                        setClasificacionPrioridad(
                                            event.target.value,
                                        )
                                    }
                                    className="h-10 rounded-xl border-input bg-background px-3 text-sm font-normal"
                                >
                                    {prioridades.map((item) => (
                                        <option key={item} value={item}>
                                            {item}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="grid gap-2 text-sm font-semibold text-foreground">
                                Estado
                                <select
                                    value={estado}
                                    onChange={(event) =>
                                        setEstado(event.target.value)
                                    }
                                    className="h-10 rounded-xl border-input bg-background px-3 text-sm font-normal"
                                >
                                    {estados.map((item) => (
                                        <option key={item} value={item}>
                                            {item}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <div className="grid gap-2">
                                <label className="text-sm font-semibold text-foreground">
                                    Legajos vinculados
                                </label>
                                <div className="flex flex-wrap gap-2">
                                    {legajosSeleccionados.map((legajo) => (
                                        <span
                                            key={legajo.id}
                                            className="inline-flex items-center gap-1 rounded-full bg-secondary px-3 py-1.5 text-xs font-semibold text-secondary-foreground"
                                        >
                                            {legajo.numero_legajo}
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    alternarLegajo(legajo)
                                                }
                                                aria-label={`Quitar ${legajo.numero_legajo}`}
                                            >
                                                <X className="size-3" />
                                            </button>
                                        </span>
                                    ))}
                                </div>
                                <div className="relative">
                                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        value={legajoQuery}
                                        onChange={(event) =>
                                            setLegajoQuery(event.target.value)
                                        }
                                        placeholder="Buscar por DNI, nombre o número de legajo"
                                        className="h-10 rounded-xl bg-background pl-9"
                                    />
                                </div>
                                {legajosEncontrados.length > 0 && (
                                    <div className="divide-y divide-border overflow-hidden rounded-xl border border-border bg-card">
                                        {legajosEncontrados.map((legajo) => (
                                            <button
                                                key={legajo.id}
                                                type="button"
                                                onClick={() =>
                                                    alternarLegajo(legajo)
                                                }
                                                className="flex w-full items-center justify-between px-3 py-2.5 text-left text-sm hover:bg-accent"
                                            >
                                                <span>
                                                    <strong className="text-foreground">
                                                        {legajo.numero_legajo}
                                                    </strong>
                                                    <small className="ml-2 text-muted-foreground">
                                                        {legajo.nombre}
                                                    </small>
                                                </span>
                                                <span className="text-xs font-semibold text-primary">
                                                    Vincular
                                                </span>
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                            {estado === 'Derivado' && (
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="grid gap-2 text-sm font-semibold text-foreground">
                                        Responsable
                                        <select
                                            value={asignadoA}
                                            onChange={(event) =>
                                                setAsignadoA(event.target.value)
                                            }
                                            className="h-10 rounded-xl border-input bg-background px-3 text-sm font-normal"
                                        >
                                            <option value="">
                                                Seleccionar persona
                                            </option>
                                            {responsables.map((responsable) => (
                                                <option
                                                    key={responsable.id}
                                                    value={responsable.id}
                                                >
                                                    {responsable.name}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="grid gap-2 text-sm font-semibold text-foreground">
                                        Vencimiento
                                        <Input
                                            type="date"
                                            value={fechaLimite}
                                            onChange={(event) =>
                                                setFechaLimite(
                                                    event.target.value,
                                                )
                                            }
                                            className="h-10 rounded-xl bg-background"
                                        />
                                    </label>
                                </div>
                            )}
                        </div>
                    )}
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setMensajeSeleccionado(null)}
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="button"
                            onClick={guardarClasificacion}
                            className="rounded-xl"
                        >
                            Guardar clasificación
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={composeOpen} onOpenChange={setComposeOpen}>
                <DialogContent className="sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>
                            {composeReplyId
                                ? 'Responder correo'
                                : 'Nuevo correo'}
                        </DialogTitle>
                        <DialogDescription>
                            El mensaje se enviará desde la cuenta institucional
                            autorizada.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={enviarCorreo} className="grid gap-4">
                        {!composeReplyId && (
                            <label className="grid gap-2 text-sm font-semibold text-foreground">
                                Destinatario
                                <Input
                                    type="email"
                                    required
                                    value={composeTo}
                                    onChange={(event) =>
                                        setComposeTo(event.target.value)
                                    }
                                    placeholder="correo@ejemplo.com"
                                    className="bg-background"
                                />
                            </label>
                        )}
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="grid gap-2 text-sm font-semibold text-foreground">
                                CC
                                <Input
                                    type="email"
                                    value={composeCc}
                                    onChange={(event) =>
                                        setComposeCc(event.target.value)
                                    }
                                    placeholder="copia@ejemplo.com"
                                    className="bg-background"
                                />
                            </label>
                            <label className="grid gap-2 text-sm font-semibold text-foreground">
                                CCO
                                <Input
                                    type="email"
                                    value={composeBcc}
                                    onChange={(event) =>
                                        setComposeBcc(event.target.value)
                                    }
                                    placeholder="oculta@ejemplo.com"
                                    className="bg-background"
                                />
                            </label>
                        </div>
                        <label className="grid gap-2 text-sm font-semibold text-foreground">
                            Asunto
                            <Input
                                required
                                value={composeSubject}
                                onChange={(event) =>
                                    setComposeSubject(event.target.value)
                                }
                                className="bg-background"
                            />
                        </label>
                        <label className="grid gap-2 text-sm font-semibold text-foreground">
                            Mensaje
                            <textarea
                                required
                                value={composeBody}
                                onChange={(event) =>
                                    setComposeBody(event.target.value)
                                }
                                rows={8}
                                className="min-h-40 rounded-xl border border-input bg-background px-3 py-2 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/30"
                            />
                        </label>
                        <label className="grid gap-2 text-sm font-semibold text-foreground">
                            Adjuntos
                            <Input
                                type="file"
                                multiple
                                onChange={(event) =>
                                    setComposeFiles(
                                        Array.from(event.target.files ?? []),
                                    )
                                }
                                className="bg-background text-sm font-normal"
                            />
                        </label>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setComposeOpen(false)}
                            >
                                Cancelar
                            </Button>
                            <Button
                                type="submit"
                                disabled={sending}
                                className="rounded-xl"
                            >
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
