import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Check,
    FileText,
    MessageCircle,
    Paperclip,
    Plus,
    RefreshCw,
    Search,
    Send,
    ShieldCheck,
    UserRound,
    X,
} from 'lucide-react';
import type { FormEvent, KeyboardEvent } from 'react';
import { useEffect, useRef, useState } from 'react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Bubble, BubbleContent } from '@/components/ui/bubble';
import {
    Conversation,
    ConversationContent,
    ConversationScrollButton,
} from '@/components/ui/conversation';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Message,
    MessageAvatar,
    MessageContent,
    MessageFooter,
    MessageHeader,
} from '@/components/ui/message';
import { Skeleton } from '@/components/ui/skeleton';

interface Conversacion {
    id: number;
    nombre: string;
    avatar: string | null;
    ultimo_mensaje: string;
    ultimo_mensaje_en: string | null;
    no_leidos: number;
}

interface Recurso {
    tipo: string;
    id: number;
    label: string;
    detail?: string | null;
    accesible?: boolean;
    url?: string | null;
}

interface Mensaje {
    id: number;
    cuerpo: string | null;
    usuario_id: number;
    usuario: string | null;
    usuario_avatar: string | null;
    creado_en: string | null;
    recursos: Recurso[];
    archivos: ArchivoMensaje[];
}

interface ArchivoMensaje {
    id: number;
    nombre: string;
    mime: string;
    tamanio: number;
    url: string;
}

interface Usuario {
    id: number;
    name: string;
    email: string;
    profesion: string | null;
}

interface Props {
    conversaciones: Conversacion[];
    conversacionSeleccionada: number | null;
    nombreConversacion: string | null;
    avatarConversacion: string | null;
    mensajes: Mensaje[];
    usuarios: Usuario[];
}

const formatDate = (value: string | null) => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('es-AR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
};

const initials = (value: string) =>
    value
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${Math.ceil(bytes / 1024)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

export default function Comunicacion({
    conversaciones,
    conversacionSeleccionada,
    nombreConversacion,
    mensajes,
    usuarios,
    avatarConversacion,
}: Props) {
    const [mostrarNuevo, setMostrarNuevo] = useState(false);
    const [buscarRecurso, setBuscarRecurso] = useState('');
    const [sugerencias, setSugerencias] = useState<Recurso[]>([]);
    const [recursoSeleccionado, setRecursoSeleccionado] =
        useState<Recurso | null>(null);
    const [mostrarAdjuntos, setMostrarAdjuntos] = useState(false);
    const [recursoModalAbierto, setRecursoModalAbierto] = useState(false);
    const [actualizando, setActualizando] = useState(false);
    const archivoInputRef = useRef<HTMLInputElement>(null);
    const currentUser = usePage().props.auth.user;
    const currentUserId = currentUser.id;
    const currentUserAvatar = currentUser.avatar;

    const nuevoChat = useForm({ usuario_id: '' });
    const mensajeForm = useForm({
        cuerpo: '',
        tipo_recurso: '',
        recurso_id: '',
        archivos: [] as File[],
    });

    useEffect(() => {
        if (buscarRecurso.trim().length < 2) {
            return;
        }

        const controller = new AbortController();
        const timeout = setTimeout(() => {
            fetch(
                `/comunicacion/recursos/buscar?buscar=${encodeURIComponent(buscarRecurso)}`,
                {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                },
            )
                .then((response) => response.json())
                .then((recursos: Recurso[]) => setSugerencias(recursos))
                .catch(() => setSugerencias([]));
        }, 250);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [buscarRecurso]);

    const recursosVisibles =
        buscarRecurso.trim().length >= 2 ? sugerencias : [];

    const seleccionarRecurso = (recurso: Recurso) => {
        setRecursoSeleccionado(recurso);
        mensajeForm.setData('tipo_recurso', recurso.tipo);
        mensajeForm.setData('recurso_id', String(recurso.id));
        setBuscarRecurso('');
        setSugerencias([]);
        setRecursoModalAbierto(false);
    };

    const abrirSelectorRecursos = () => {
        setMostrarAdjuntos(false);
        setBuscarRecurso('');
        setSugerencias([]);
        setRecursoModalAbierto(true);
    };

    const quitarRecurso = () => {
        setRecursoSeleccionado(null);
        mensajeForm.setData('tipo_recurso', '');
        mensajeForm.setData('recurso_id', '');
    };

    const seleccionarArchivos = (files: FileList | null) => {
        if (!files) {
            return;
        }

        mensajeForm.setData(
            'archivos',
            [...mensajeForm.data.archivos, ...Array.from(files)].slice(0, 10),
        );
    };

    const quitarArchivo = (index: number) => {
        mensajeForm.setData(
            'archivos',
            mensajeForm.data.archivos.filter(
                (_, archivoIndex) => archivoIndex !== index,
            ),
        );
    };

    const enviarMensaje = () => {
        if (
            !conversacionSeleccionada ||
            mensajeForm.processing ||
            (!mensajeForm.data.cuerpo.trim() &&
                !recursoSeleccionado &&
                mensajeForm.data.archivos.length === 0)
        ) {
            return;
        }

        mensajeForm.post(`/comunicacion/${conversacionSeleccionada}/mensajes`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                mensajeForm.reset();
                quitarRecurso();

                if (archivoInputRef.current) {
                    archivoInputRef.current.value = '';
                }
            },
        });
    };

    const enviar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        enviarMensaje();
    };

    const manejarTeclaMensaje = (event: KeyboardEvent<HTMLTextAreaElement>) => {
        if (
            event.key !== 'Enter' ||
            event.shiftKey ||
            event.nativeEvent.isComposing
        ) {
            return;
        }

        event.preventDefault();
        enviarMensaje();
    };

    const actualizar = () => {
        setActualizando(true);
        router.reload({
            only: [
                'conversaciones',
                'mensajes',
                'nombreConversacion',
                'avatarConversacion',
            ],
            onFinish: () => setActualizando(false),
        });
    };

    return (
        <>
            <Head title="Comunicación" />
            <Dialog
                open={recursoModalAbierto}
                onOpenChange={(abierto) => {
                    setRecursoModalAbierto(abierto);

                    if (!abierto) {
                        setBuscarRecurso('');
                        setSugerencias([]);
                    }
                }}
            >
                <DialogContent className="border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle className="text-[#173247]">
                            Compartir legajo o acta
                        </DialogTitle>
                        <DialogDescription className="text-[#697586]">
                            Buscá un recurso para adjuntarlo a la conversación.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="relative">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#697586]" />
                        <input
                            autoFocus
                            value={buscarRecurso}
                            onChange={(event) =>
                                setBuscarRecurso(event.target.value)
                            }
                            placeholder="Buscar por número, nombre o título…"
                            className="h-11 w-full rounded-xl border border-[#D5D1C6] bg-white pr-3 pl-10 text-sm text-[#2B2F36] transition outline-none placeholder:text-[#697586] focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                        />
                    </div>
                    <div className="max-h-72 overflow-y-auto rounded-xl border border-[#E4E8E5] bg-white p-1">
                        {recursosVisibles.length > 0 ? (
                            recursosVisibles.map((recurso) => (
                                <button
                                    key={`${recurso.tipo}-${recurso.id}`}
                                    type="button"
                                    onClick={() => seleccionarRecurso(recurso)}
                                    className="flex w-full items-start gap-3 rounded-lg px-3 py-3 text-left transition hover:bg-[#F4F6F5]"
                                >
                                    <Paperclip
                                        size={16}
                                        className="mt-0.5 shrink-0 text-[#6B8F71]"
                                    />
                                    <span className="min-w-0">
                                        <span className="block truncate text-sm font-semibold text-[#2C4A5E]">
                                            {recurso.label}
                                        </span>
                                        {recurso.detail && (
                                            <span className="mt-1 block truncate text-xs text-[#697586]">
                                                {recurso.detail}
                                            </span>
                                        )}
                                    </span>
                                </button>
                            ))
                        ) : buscarRecurso.trim().length >= 2 ? (
                            <p className="px-3 py-8 text-center text-sm text-[#697586]">
                                No encontramos recursos con esa búsqueda.
                            </p>
                        ) : (
                            <p className="px-3 py-8 text-center text-sm text-[#697586]">
                                Escribí al menos dos caracteres para buscar.
                            </p>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
            <main className="admin-page-shell min-h-screen bg-background px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">
                                Espacio interno
                            </p>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                                Comunicación
                            </h1>
                            <p className="mt-2 max-w-2xl text-sm text-[#697586]">
                                Conversaciones de trabajo y recursos compartidos
                                entre usuarios del sistema.
                            </p>
                        </div>
                        <div className="flex items-center gap-3 rounded-2xl border border-[#CFE1D4] bg-[#EAF3ED] px-4 py-3 text-xs text-[#41634A] shadow-sm">
                            <span className="flex size-8 items-center justify-center rounded-xl bg-white/80 text-[#267148]">
                                <ShieldCheck size={17} />
                            </span>
                            <span>
                                <strong className="block text-[#41634A]">
                                    Comunicación protegida
                                </strong>
                                <span className="text-[#41634A]/75">
                                    Referencias seguras, sin duplicar datos
                                </span>
                            </span>
                        </div>
                    </div>

                    <div className="grid h-[calc(100dvh-8rem)] min-h-[540px] overflow-hidden rounded-2xl border border-[#D5E0DA] bg-white shadow-[0_22px_60px_rgba(23,50,71,0.1)] lg:h-[650px] lg:min-h-0 lg:grid-cols-[310px_minmax(0,1fr)]">
                        <aside className="border-b border-[#E4E8E5] bg-[#F8FAF9] lg:border-r lg:border-b-0">
                            <div className="flex items-center justify-between border-b border-[#E4E8E5] bg-white/70 px-4 py-4">
                                <div>
                                    <p className="text-sm font-bold text-[#2C4A5E]">
                                        Conversaciones
                                    </p>
                                    <p className="mt-0.5 text-xs text-[#697586]">
                                        {conversaciones.length} activas
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setMostrarNuevo((value) => !value)
                                    }
                                    className="inline-flex size-9 items-center justify-center rounded-lg bg-[#2C4A5E] text-white transition hover:bg-[#1F394B]"
                                    aria-label="Nueva conversación"
                                >
                                    <Plus size={17} />
                                </button>
                            </div>

                            {mostrarNuevo && (
                                <form
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        nuevoChat.post(
                                            '/comunicacion/conversaciones',
                                            {
                                                onSuccess: () =>
                                                    setMostrarNuevo(false),
                                            },
                                        );
                                    }}
                                    className="border-b border-[#E4E8E5] bg-white p-4"
                                >
                                    <label className="text-xs font-bold tracking-[0.08em] text-[#6B8F71] uppercase">
                                        Iniciar chat
                                    </label>
                                    <select
                                        value={nuevoChat.data.usuario_id}
                                        onChange={(event) =>
                                            nuevoChat.setData(
                                                'usuario_id',
                                                event.target.value,
                                            )
                                        }
                                        className="mt-2 h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                    >
                                        <option value="">
                                            Seleccionar usuario…
                                        </option>
                                        {usuarios.map((usuario) => (
                                            <option
                                                key={usuario.id}
                                                value={usuario.id}
                                            >
                                                {usuario.name}
                                                {usuario.profesion
                                                    ? ` · ${usuario.profesion}`
                                                    : ''}
                                            </option>
                                        ))}
                                    </select>
                                    {nuevoChat.errors.usuario_id && (
                                        <p className="mt-1 text-xs text-[#A84635]">
                                            {nuevoChat.errors.usuario_id}
                                        </p>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={
                                            nuevoChat.processing ||
                                            !nuevoChat.data.usuario_id
                                        }
                                        className="mt-3 h-9 w-full rounded-lg bg-[#6B8F71] text-xs font-bold text-white transition hover:bg-[#53745A] disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Abrir conversación
                                    </button>
                                </form>
                            )}

                            <div className="max-h-[38dvh] overflow-y-auto p-2.5 lg:max-h-[570px]">
                                {conversaciones.length === 0 ? (
                                    <div className="px-4 py-12 text-center">
                                        <MessageCircle className="mx-auto size-8 text-[#6B8F71]" />
                                        <p className="mt-3 text-sm font-semibold text-[#2C4A5E]">
                                            Todavía no hay chats.
                                        </p>
                                        <p className="mt-1 text-xs leading-5 text-[#697586]">
                                            Iniciá una conversación con otro
                                            usuario.
                                        </p>
                                    </div>
                                ) : (
                                    conversaciones.map((conversacion) => (
                                        <button
                                            key={conversacion.id}
                                            type="button"
                                            onClick={() =>
                                                router.visit(
                                                    `/comunicacion/${conversacion.id}`,
                                                    { preserveScroll: true },
                                                )
                                            }
                                            className={`mb-1 flex w-full items-start gap-3 rounded-xl border p-3 text-left transition ${conversacion.id === conversacionSeleccionada ? 'border-[#CFE1D4] bg-[#EAF3ED] shadow-sm' : 'border-transparent hover:border-[#E4E8E5] hover:bg-white'}`}
                                        >
                                            <Avatar className="size-9 rounded-full bg-[#DDEBE1]">
                                                {conversacion.avatar && (
                                                    <AvatarImage
                                                        src={
                                                            conversacion.avatar
                                                        }
                                                        alt={
                                                            conversacion.nombre
                                                        }
                                                    />
                                                )}
                                                <AvatarFallback className="rounded-full bg-[#DDEBE1] text-xs font-bold text-[#41634A]">
                                                    {initials(
                                                        conversacion.nombre,
                                                    )}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span className="min-w-0 flex-1">
                                                <span className="flex items-center justify-between gap-2">
                                                    <span className="truncate text-sm font-semibold text-[#2C4A5E]">
                                                        {conversacion.nombre ||
                                                            'Conversación'}
                                                    </span>
                                                    {conversacion.no_leidos >
                                                        0 && (
                                                        <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#2C4A5E] text-[length:calc(0.65rem_+_2px)] font-bold text-white">
                                                            {
                                                                conversacion.no_leidos
                                                            }
                                                        </span>
                                                    )}
                                                </span>
                                                <span className="mt-1 block truncate text-xs text-[#697586]">
                                                    {
                                                        conversacion.ultimo_mensaje
                                                    }
                                                </span>
                                            </span>
                                        </button>
                                    ))
                                )}
                            </div>
                        </aside>

                        <section className="flex min-h-0 min-w-0 flex-col bg-white">
                            {conversacionSeleccionada ? (
                                <>
                                    <header className="flex items-center justify-between gap-3 border-b border-[#E4E8E5] bg-white px-5 py-4 sm:px-7">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <span className="relative flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#2C4A5E] text-xs font-bold text-white shadow-sm">
                                                {avatarConversacion && (
                                                    <img
                                                        src={avatarConversacion}
                                                        alt={
                                                            nombreConversacion ??
                                                            'Conversación'
                                                        }
                                                        className="size-full object-cover"
                                                    />
                                                )}
                                                {!avatarConversacion &&
                                                    initials(
                                                        nombreConversacion ??
                                                            '',
                                                    )}
                                                <span className="absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-white bg-[#6B8F71]" />
                                            </span>
                                            <div className="min-w-0">
                                                <h2 className="truncate text-base font-bold text-[#173247]">
                                                    {nombreConversacion ||
                                                        'Conversación'}
                                                </h2>
                                                <p className="mt-0.5 flex items-center gap-1.5 text-xs text-[#697586]">
                                                    <span className="size-1.5 rounded-full bg-[#6B8F71]" />
                                                    Conversación interna
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={actualizar}
                                            disabled={actualizando}
                                            className="inline-flex size-9 items-center justify-center rounded-lg border border-[#DCE3DF] text-[#2C4A5E] transition hover:bg-[#F4F6F5] disabled:opacity-50"
                                            aria-label="Actualizar conversación"
                                        >
                                            <RefreshCw
                                                size={16}
                                                className={
                                                    actualizando
                                                        ? 'animate-spin'
                                                        : ''
                                                }
                                            />
                                        </button>
                                    </header>

                                    <Conversation className="min-h-0 bg-[radial-gradient(circle_at_top_left,_rgba(234,243,237,0.75),_transparent_38%),#FAFBFA]">
                                        <ConversationContent className="space-y-5 px-4 py-5 sm:px-7">
                                            {actualizando ? (
                                                <div className="space-y-5">
                                                    {[0, 1, 2].map((index) => (
                                                        <div
                                                            key={index}
                                                            className={`flex items-start gap-3 ${index % 2 === 0 ? 'justify-start' : 'justify-end'}`}
                                                        >
                                                            {index % 2 ===
                                                                0 && (
                                                                <Skeleton className="mt-1 size-8 shrink-0 rounded-xl" />
                                                            )}
                                                            <Skeleton className="h-16 max-w-[min(42rem,70%)] min-w-0 flex-1 rounded-2xl" />
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : mensajes.length === 0 ? (
                                                <div className="flex h-full min-h-80 flex-col items-center justify-center text-center">
                                                    <span className="flex size-12 items-center justify-center rounded-full bg-[#EAF3ED] text-[#267148]">
                                                        <MessageCircle
                                                            size={22}
                                                        />
                                                    </span>
                                                    <p className="mt-3 text-sm font-semibold text-[#2C4A5E]">
                                                        Iniciá la conversación
                                                    </p>
                                                    <p className="mt-1 max-w-xs text-xs leading-5 text-[#697586]">
                                                        Podés escribir un
                                                        mensaje o compartir un
                                                        recurso del sistema.
                                                    </p>
                                                </div>
                                            ) : (
                                                mensajes.map((mensaje) => {
                                                    const propio =
                                                        mensaje.usuario_id ===
                                                        currentUserId;

                                                    return (
                                                        <Message
                                                            key={mensaje.id}
                                                            align={
                                                                propio
                                                                    ? 'end'
                                                                    : 'start'
                                                            }
                                                        >
                                                            {!propio && (
                                                                <MessageAvatar>
                                                                    <Avatar className="size-8 rounded-xl bg-[#DDEBE1]">
                                                                        {mensaje.usuario_avatar && (
                                                                            <AvatarImage
                                                                                src={
                                                                                    mensaje.usuario_avatar
                                                                                }
                                                                                alt={
                                                                                    mensaje.usuario ??
                                                                                    'Usuario'
                                                                                }
                                                                            />
                                                                        )}
                                                                        <AvatarFallback className="rounded-xl bg-[#DDEBE1] text-[length:calc(0.65rem_+_2px)] font-bold text-[#41634A]">
                                                                            {initials(
                                                                                mensaje.usuario ??
                                                                                    'Usuario',
                                                                            )}
                                                                        </AvatarFallback>
                                                                    </Avatar>
                                                                </MessageAvatar>
                                                            )}
                                                            <MessageContent>
                                                                <MessageHeader
                                                                    className={
                                                                        propio
                                                                            ? 'text-right text-[#6B8F71]'
                                                                            : ''
                                                                    }
                                                                >
                                                                    {mensaje.usuario ??
                                                                        'Usuario'}
                                                                </MessageHeader>
                                                                <Bubble
                                                                    className={
                                                                        propio
                                                                            ? 'rounded-tr-md border-[#2C4A5E] bg-[#2C4A5E] text-white shadow-[0_8px_20px_rgba(44,74,94,0.18)]'
                                                                            : 'rounded-tl-md border-[#E4E8E5] bg-white text-[#2B2F36]'
                                                                    }
                                                                >
                                                                    <BubbleContent>
                                                                        {mensaje.cuerpo && (
                                                                            <p className="whitespace-pre-wrap">
                                                                                {
                                                                                    mensaje.cuerpo
                                                                                }
                                                                            </p>
                                                                        )}
                                                                        {mensaje
                                                                            .recursos
                                                                            .length >
                                                                            0 && (
                                                                            <div
                                                                                className={`${mensaje.cuerpo ? 'mt-3' : ''} space-y-2`}
                                                                            >
                                                                                {mensaje.recursos.map(
                                                                                    (
                                                                                        recurso,
                                                                                    ) => (
                                                                                        <a
                                                                                            key={`${recurso.tipo}-${recurso.id}`}
                                                                                            href={
                                                                                                recurso.accesible
                                                                                                    ? (recurso.url ??
                                                                                                      '#')
                                                                                                    : undefined
                                                                                            }
                                                                                            className={`flex items-center gap-2 rounded-lg border px-3 py-2 text-xs ${recurso.accesible ? (propio ? 'border-white/20 bg-white/10 text-white hover:bg-white/15' : 'border-[#CFE1D4] bg-[#EAF3ED] text-[#267148] hover:bg-[#DDEBE1]') : 'border-[#E4E8E5] bg-[#F8FAF9] text-[#697586]'}`}
                                                                                        >
                                                                                            <Paperclip
                                                                                                size={
                                                                                                    14
                                                                                                }
                                                                                            />
                                                                                            <span className="truncate font-semibold">
                                                                                                {
                                                                                                    recurso.label
                                                                                                }
                                                                                            </span>
                                                                                        </a>
                                                                                    ),
                                                                                )}
                                                                            </div>
                                                                        )}
                                                                        {mensaje
                                                                            .archivos
                                                                            .length >
                                                                            0 && (
                                                                            <div
                                                                                className={`${mensaje.cuerpo || mensaje.recursos.length > 0 ? 'mt-3' : ''} space-y-2`}
                                                                            >
                                                                                {mensaje.archivos.map(
                                                                                    (
                                                                                        archivo,
                                                                                    ) =>
                                                                                        archivo.mime.startsWith(
                                                                                            'image/',
                                                                                        ) ? (
                                                                                            <a
                                                                                                key={
                                                                                                    archivo.id
                                                                                                }
                                                                                                href={
                                                                                                    archivo.url
                                                                                                }
                                                                                                target="_blank"
                                                                                                rel="noreferrer"
                                                                                                className="block overflow-hidden rounded-lg border border-white/20"
                                                                                            >
                                                                                                <img
                                                                                                    src={
                                                                                                        archivo.url
                                                                                                    }
                                                                                                    alt={
                                                                                                        archivo.nombre
                                                                                                    }
                                                                                                    className="max-h-64 w-full object-cover"
                                                                                                />
                                                                                            </a>
                                                                                        ) : (
                                                                                            <a
                                                                                                key={
                                                                                                    archivo.id
                                                                                                }
                                                                                                href={
                                                                                                    archivo.url
                                                                                                }
                                                                                                target="_blank"
                                                                                                rel="noreferrer"
                                                                                                className={`flex items-center gap-2 rounded-lg border px-3 py-2 text-xs ${propio ? 'border-white/20 bg-white/10 text-white hover:bg-white/15' : 'border-[#CFE1D4] bg-[#EAF3ED] text-[#267148] hover:bg-[#DDEBE1]'}`}
                                                                                            >
                                                                                                <FileText
                                                                                                    size={
                                                                                                        14
                                                                                                    }
                                                                                                    className="shrink-0"
                                                                                                />
                                                                                                <span className="min-w-0 truncate font-semibold">
                                                                                                    {
                                                                                                        archivo.nombre
                                                                                                    }
                                                                                                </span>
                                                                                                <span className="shrink-0 opacity-70">
                                                                                                    {formatFileSize(
                                                                                                        archivo.tamanio,
                                                                                                    )}
                                                                                                </span>
                                                                                            </a>
                                                                                        ),
                                                                                )}
                                                                            </div>
                                                                        )}
                                                                    </BubbleContent>
                                                                </Bubble>
                                                                <MessageFooter
                                                                    className={
                                                                        propio
                                                                            ? 'justify-end text-[#6B8F71]'
                                                                            : ''
                                                                    }
                                                                >
                                                                    <time>
                                                                        {formatDate(
                                                                            mensaje.creado_en,
                                                                        )}
                                                                    </time>
                                                                </MessageFooter>
                                                            </MessageContent>
                                                            {propio && (
                                                                <MessageAvatar>
                                                                    <Avatar className="size-8 rounded-xl bg-[#2C4A5E]">
                                                                        {currentUserAvatar && (
                                                                            <AvatarImage
                                                                                src={
                                                                                    currentUserAvatar
                                                                                }
                                                                                alt={
                                                                                    mensaje.usuario ??
                                                                                    'Usuario'
                                                                                }
                                                                            />
                                                                        )}
                                                                        <AvatarFallback className="rounded-xl bg-[#2C4A5E] text-[length:calc(0.65rem_+_2px)] font-bold text-white">
                                                                            {initials(
                                                                                mensaje.usuario ??
                                                                                    'Usuario',
                                                                            )}
                                                                        </AvatarFallback>
                                                                    </Avatar>
                                                                </MessageAvatar>
                                                            )}
                                                        </Message>
                                                    );
                                                })
                                            )}
                                        </ConversationContent>
                                        <ConversationScrollButton aria-label="Ir al mensaje más reciente" />
                                    </Conversation>

                                    <form
                                        onSubmit={enviar}
                                        className="border-t border-[#E4E8E5] bg-white p-4 sm:p-5"
                                    >
                                        {recursoSeleccionado && (
                                            <div className="mb-3 flex items-center justify-between gap-3 rounded-lg border border-[#CFE1D4] bg-[#EAF3ED] px-3 py-2 text-xs text-[#267148]">
                                                <span className="flex min-w-0 items-center gap-2">
                                                    <Paperclip
                                                        size={14}
                                                        className="shrink-0"
                                                    />
                                                    <span className="truncate font-semibold">
                                                        {
                                                            recursoSeleccionado.label
                                                        }
                                                    </span>
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={quitarRecurso}
                                                    className="shrink-0 rounded p-1 hover:bg-[#DDEBE1]"
                                                    aria-label="Quitar recurso"
                                                >
                                                    <X size={14} />
                                                </button>
                                            </div>
                                        )}
                                        <div className="relative">
                                            <textarea
                                                value={mensajeForm.data.cuerpo}
                                                onChange={(event) =>
                                                    mensajeForm.setData(
                                                        'cuerpo',
                                                        event.target.value,
                                                    )
                                                }
                                                onKeyDown={manejarTeclaMensaje}
                                                rows={2}
                                                placeholder="Escribí un mensaje…"
                                                className="block w-full resize-none rounded-xl border border-[#D5D1C6] bg-[#FAFBFA] px-4 py-3 pr-14 text-sm leading-6 text-[#2B2F36] outline-none placeholder:text-[#697586]/60 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                            />
                                            <button
                                                type="submit"
                                                disabled={
                                                    mensajeForm.processing ||
                                                    (!mensajeForm.data.cuerpo.trim() &&
                                                        !recursoSeleccionado &&
                                                        mensajeForm.data
                                                            .archivos.length ===
                                                            0)
                                                }
                                                className="absolute right-2 bottom-2 inline-flex size-9 items-center justify-center rounded-lg bg-[#2C4A5E] text-white transition hover:bg-[#1F394B] disabled:cursor-not-allowed disabled:opacity-40"
                                                aria-label="Enviar mensaje"
                                            >
                                                <Send size={16} />
                                            </button>
                                        </div>
                                        <div className="mt-3 flex items-center justify-between gap-3">
                                            <div className="relative flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setMostrarAdjuntos(
                                                            (value) => !value,
                                                        )
                                                    }
                                                    className="inline-flex size-9 items-center justify-center rounded-lg border border-[#DCE3DF] text-[#2C4A5E] transition hover:bg-[#F4F6F5]"
                                                    aria-label="Adjuntar contenido"
                                                    aria-expanded={
                                                        mostrarAdjuntos
                                                    }
                                                >
                                                    <Plus size={17} />
                                                </button>
                                                {mostrarAdjuntos && (
                                                    <div className="absolute bottom-full left-0 z-30 mb-2 w-56 rounded-xl border border-[#DCE3DF] bg-white p-1.5 shadow-[0_15px_35px_rgba(23,50,71,0.15)]">
                                                        <button
                                                            type="button"
                                                            onClick={
                                                                abrirSelectorRecursos
                                                            }
                                                            className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-semibold text-[#2C4A5E] hover:bg-[#F4F6F5]"
                                                        >
                                                            <Paperclip
                                                                size={15}
                                                            />
                                                            Legajo, acta o
                                                            recurso
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setMostrarAdjuntos(
                                                                    false,
                                                                );
                                                                archivoInputRef.current?.click();
                                                            }}
                                                            className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-semibold text-[#2C4A5E] hover:bg-[#F4F6F5]"
                                                        >
                                                            <FileText
                                                                size={15}
                                                            />
                                                            Foto o archivo
                                                        </button>
                                                    </div>
                                                )}
                                                <input
                                                    ref={archivoInputRef}
                                                    type="file"
                                                    multiple
                                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                                                    className="hidden"
                                                    onChange={(event) => {
                                                        seleccionarArchivos(
                                                            event.target.files,
                                                        );
                                                        event.target.value = '';
                                                    }}
                                                />
                                                <span className="text-xs text-[#697586]">
                                                    Adjuntar legajos, actas,
                                                    fotos o archivos
                                                </span>
                                            </div>
                                        </div>
                                        {mensajeForm.data.archivos.length >
                                            0 && (
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                {mensajeForm.data.archivos.map(
                                                    (archivo, index) => (
                                                        <span
                                                            key={`${archivo.name}-${index}`}
                                                            className="inline-flex max-w-full items-center gap-2 rounded-lg border border-[#DCE3DF] bg-[#F8FAF9] px-2.5 py-1.5 text-xs text-[#2C4A5E]"
                                                        >
                                                            <FileText
                                                                size={13}
                                                                className="shrink-0 text-[#6B8F71]"
                                                            />
                                                            <span className="max-w-48 truncate font-semibold">
                                                                {archivo.name}
                                                            </span>
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    quitarArchivo(
                                                                        index,
                                                                    )
                                                                }
                                                                className="rounded p-0.5 text-[#697586] hover:bg-[#EAF3ED] hover:text-[#2C4A5E]"
                                                                aria-label={`Quitar ${archivo.name}`}
                                                            >
                                                                <X size={13} />
                                                            </button>
                                                        </span>
                                                    ),
                                                )}
                                            </div>
                                        )}
                                        <p className="mt-2 flex items-center gap-1 text-[length:calc(0.68rem_+_2px)] text-[#697586]">
                                            <Check
                                                size={12}
                                                className="text-[#267148]"
                                            />
                                            Los recursos se comparten sin
                                            duplicar información.
                                        </p>
                                        {mensajeForm.errors.cuerpo && (
                                            <p className="mt-1 text-xs text-[#A84635]">
                                                {mensajeForm.errors.cuerpo}
                                            </p>
                                        )}
                                    </form>
                                </>
                            ) : (
                                <div className="flex flex-1 flex-col items-center justify-center px-6 py-16 text-center">
                                    <span className="flex size-16 items-center justify-center rounded-2xl bg-[#EAF3ED] text-[#267148]">
                                        <MessageCircle size={30} />
                                    </span>
                                    <h2 className="mt-5 text-lg font-semibold text-[#2C4A5E]">
                                        Elegí una conversación
                                    </h2>
                                    <p className="mt-2 max-w-sm text-sm leading-6 text-[#697586]">
                                        Seleccioná un chat existente o creá uno
                                        nuevo para comunicarte con otro usuario.
                                    </p>
                                    <button
                                        type="button"
                                        onClick={() => setMostrarNuevo(true)}
                                        className="mt-5 inline-flex h-10 items-center gap-2 rounded-lg bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                                    >
                                        <UserRound size={15} />
                                        Nuevo chat
                                    </button>
                                </div>
                            )}
                        </section>
                    </div>
                </div>
            </main>
        </>
    );
}
