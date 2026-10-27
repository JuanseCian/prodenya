import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Check,
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
import type { FormEvent } from 'react';
import { useEffect, useState } from 'react';

import {
    Conversation,
    ConversationContent,
    ConversationScrollButton,
} from '@/components/ui/conversation';

interface Conversacion {
    id: number;
    nombre: string;
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
    creado_en: string | null;
    recursos: Recurso[];
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

export default function Comunicacion({
    conversaciones,
    conversacionSeleccionada,
    nombreConversacion,
    mensajes,
    usuarios,
}: Props) {
    const [mostrarNuevo, setMostrarNuevo] = useState(false);
    const [buscarRecurso, setBuscarRecurso] = useState('');
    const [sugerencias, setSugerencias] = useState<Recurso[]>([]);
    const [recursoSeleccionado, setRecursoSeleccionado] =
        useState<Recurso | null>(null);
    const [actualizando, setActualizando] = useState(false);
    const currentUserId = usePage().props.auth.user.id;

    const nuevoChat = useForm({ usuario_id: '' });
    const mensajeForm = useForm({
        cuerpo: '',
        tipo_recurso: '',
        recurso_id: '',
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
    };

    const quitarRecurso = () => {
        setRecursoSeleccionado(null);
        mensajeForm.setData('tipo_recurso', '');
        mensajeForm.setData('recurso_id', '');
    };

    const enviar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!conversacionSeleccionada) {
            return;
        }

        mensajeForm.post(
            `/comunicacion/${conversacionSeleccionada}/mensajes`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    mensajeForm.reset();
                    quitarRecurso();
                },
            },
        );
    };

    const actualizar = () => {
        setActualizando(true);
        router.reload({
            only: ['conversaciones', 'mensajes', 'nombreConversacion'],
            onFinish: () => setActualizando(false),
        });
    };

    return (
        <>
            <Head title="Comunicación" />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
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

                    <div className="grid min-h-[650px] overflow-hidden rounded-2xl border border-[#D5E0DA] bg-white shadow-[0_22px_60px_rgba(23,50,71,0.1)] lg:grid-cols-[310px_minmax(0,1fr)]">
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
                                    onClick={() => setMostrarNuevo((value) => !value)}
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
                                        nuevoChat.post('/comunicacion/conversaciones', {
                                            onSuccess: () => setMostrarNuevo(false),
                                        });
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
                                        <option value="">Seleccionar usuario…</option>
                                        {usuarios.map((usuario) => (
                                            <option key={usuario.id} value={usuario.id}>
                                                {usuario.name}{usuario.profesion ? ` · ${usuario.profesion}` : ''}
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
                                        disabled={nuevoChat.processing || !nuevoChat.data.usuario_id}
                                        className="mt-3 h-9 w-full rounded-lg bg-[#6B8F71] text-xs font-bold text-white transition hover:bg-[#53745A] disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Abrir conversación
                                    </button>
                                </form>
                            )}

                            <div className="max-h-[570px] overflow-y-auto p-2.5">
                                {conversaciones.length === 0 ? (
                                    <div className="px-4 py-12 text-center">
                                        <MessageCircle className="mx-auto size-8 text-[#6B8F71]" />
                                        <p className="mt-3 text-sm font-semibold text-[#2C4A5E]">
                                            Todavía no hay chats.
                                        </p>
                                        <p className="mt-1 text-xs leading-5 text-[#697586]">
                                            Iniciá una conversación con otro usuario.
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
                                            <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#DDEBE1] text-xs font-bold text-[#41634A]">
                                                {initials(conversacion.nombre)}
                                            </span>
                                            <span className="min-w-0 flex-1">
                                                <span className="flex items-center justify-between gap-2">
                                                    <span className="truncate text-sm font-semibold text-[#2C4A5E]">
                                                        {conversacion.nombre || 'Conversación'}
                                                    </span>
                                                    {conversacion.no_leidos > 0 && (
                                                        <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#2C4A5E] text-[0.65rem] font-bold text-white">
                                                            {conversacion.no_leidos}
                                                        </span>
                                                    )}
                                                </span>
                                                <span className="mt-1 block truncate text-xs text-[#697586]">
                                                    {conversacion.ultimo_mensaje}
                                                </span>
                                            </span>
                                        </button>
                                    ))
                                )}
                            </div>
                        </aside>

                        <section className="flex min-h-[650px] min-w-0 flex-col bg-white">
                            {conversacionSeleccionada ? (
                                <>
                                    <header className="flex items-center justify-between gap-3 border-b border-[#E4E8E5] bg-white px-5 py-4 sm:px-7">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <span className="relative flex size-11 shrink-0 items-center justify-center rounded-2xl bg-[#2C4A5E] text-xs font-bold text-white shadow-sm">
                                                {initials(nombreConversacion ?? '')}
                                                <span className="absolute -right-0.5 -bottom-0.5 size-3 rounded-full border-2 border-white bg-[#6B8F71]" />
                                            </span>
                                            <div className="min-w-0">
                                                <h2 className="truncate text-base font-bold text-[#173247]">
                                                    {nombreConversacion || 'Conversación'}
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
                                                className={actualizando ? 'animate-spin' : ''}
                                            />
                                        </button>
                                    </header>

                                    <Conversation className="bg-[radial-gradient(circle_at_top_left,_rgba(234,243,237,0.75),_transparent_38%),#FAFBFA]">
                                        <ConversationContent className="space-y-5 px-4 py-5 sm:px-7">
                                        {mensajes.length === 0 ? (
                                            <div className="flex h-full min-h-80 flex-col items-center justify-center text-center">
                                                <span className="flex size-12 items-center justify-center rounded-full bg-[#EAF3ED] text-[#267148]">
                                                    <MessageCircle size={22} />
                                                </span>
                                                <p className="mt-3 text-sm font-semibold text-[#2C4A5E]">
                                                    Iniciá la conversación
                                                </p>
                                                <p className="mt-1 max-w-xs text-xs leading-5 text-[#697586]">
                                                    Podés escribir un mensaje o
                                                    compartir un recurso del sistema.
                                                </p>
                                            </div>
                                        ) : (
                                            mensajes.map((mensaje) => {
                                                const propio = mensaje.usuario_id === currentUserId;

                                                return (
                                                <article
                                                    key={mensaje.id}
                                                    className={`flex items-start gap-3 ${propio ? 'justify-end' : 'justify-start'}`}
                                                >
                                                    {!propio && (
                                                        <span className="mt-1 flex size-8 shrink-0 items-center justify-center rounded-xl bg-[#DDEBE1] text-[0.65rem] font-bold text-[#41634A]">
                                                            {initials(mensaje.usuario ?? 'Usuario')}
                                                        </span>
                                                    )}
                                                    <div className={`min-w-0 max-w-[min(42rem,88%)] rounded-2xl px-4 py-3 shadow-sm ${propio ? 'rounded-tr-md bg-[#2C4A5E] text-white shadow-[0_8px_20px_rgba(44,74,94,0.18)]' : 'rounded-tl-md border border-[#E4E8E5] bg-white text-[#2B2F36]'}`}>
                                                        <div className="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                                            <p className={`text-xs font-bold ${propio ? 'text-white' : 'text-[#2C4A5E]'}`}>
                                                                {mensaje.usuario ?? 'Usuario'}
                                                            </p>
                                                            <time className={`text-[0.68rem] ${propio ? 'text-white/65' : 'text-[#697586]'}`}>
                                                                {formatDate(mensaje.creado_en)}
                                                            </time>
                                                        </div>
                                                        {mensaje.cuerpo && (
                                                            <p className={`mt-2 whitespace-pre-wrap text-sm leading-6 ${propio ? 'text-white/95' : 'text-[#2B2F36]'}`}>
                                                                {mensaje.cuerpo}
                                                            </p>
                                                        )}
                                                        {mensaje.recursos.length > 0 && (
                                                            <div className="mt-3 space-y-2">
                                                                {mensaje.recursos.map((recurso) => (
                                                                    <a
                                                                        key={`${recurso.tipo}-${recurso.id}`}
                                                                        href={recurso.accesible ? recurso.url ?? '#' : undefined}
                                                                        className={`flex items-center gap-2 rounded-lg border px-3 py-2 text-xs ${recurso.accesible ? (propio ? 'border-white/20 bg-white/10 text-white hover:bg-white/15' : 'border-[#CFE1D4] bg-[#EAF3ED] text-[#267148] hover:bg-[#DDEBE1]') : 'border-[#E4E8E5] bg-[#F8FAF9] text-[#697586]'}`}
                                                                    >
                                                                        <Paperclip size={14} />
                                                                        <span className="truncate font-semibold">
                                                                            {recurso.label}
                                                                        </span>
                                                                    </a>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                </article>
                                                );
                                            })
                                        )}
                                        </ConversationContent>
                                        <ConversationScrollButton aria-label="Ir al mensaje más reciente" />
                                    </Conversation>

                                    <form onSubmit={enviar} className="border-t border-[#E4E8E5] bg-white p-4 sm:p-5">
                                        {recursoSeleccionado && (
                                            <div className="mb-3 flex items-center justify-between gap-3 rounded-lg border border-[#CFE1D4] bg-[#EAF3ED] px-3 py-2 text-xs text-[#267148]">
                                                <span className="flex min-w-0 items-center gap-2">
                                                    <Paperclip size={14} className="shrink-0" />
                                                    <span className="truncate font-semibold">
                                                        {recursoSeleccionado.label}
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
                                                rows={2}
                                                placeholder="Escribí un mensaje…"
                                                className="block w-full resize-none rounded-xl border border-[#D5D1C6] bg-[#FAFBFA] px-4 py-3 pr-14 text-sm leading-6 text-[#2B2F36] outline-none placeholder:text-[#697586]/60 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                            />
                                            <button
                                                type="submit"
                                                disabled={mensajeForm.processing || (!mensajeForm.data.cuerpo.trim() && !recursoSeleccionado)}
                                                className="absolute right-2 bottom-2 inline-flex size-9 items-center justify-center rounded-lg bg-[#2C4A5E] text-white transition hover:bg-[#1F394B] disabled:cursor-not-allowed disabled:opacity-40"
                                                aria-label="Enviar mensaje"
                                            >
                                                <Send size={16} />
                                            </button>
                                        </div>
                                        <div className="relative mt-2">
                                            <Search className="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-[#697586]" />
                                            <input
                                                value={buscarRecurso}
                                                onChange={(event) =>
                                                    setBuscarRecurso(event.target.value)
                                                }
                                                placeholder="Buscar y adjuntar un legajo, intervención, documento o mail…"
                                                className="h-9 w-full rounded-lg border border-transparent bg-[#F8FAF9] pl-8 pr-3 text-xs text-[#2B2F36] outline-none transition placeholder:text-[#697586] focus:border-[#DCE3DF] focus:bg-white"
                                            />
                                            {recursosVisibles.length > 0 && (
                                                <div className="absolute right-0 bottom-full left-0 z-20 mb-2 max-h-64 overflow-y-auto rounded-xl border border-[#DCE3DF] bg-white p-1 shadow-[0_15px_35px_rgba(23,50,71,0.15)]">
                                                    {recursosVisibles.map((recurso) => (
                                                        <button
                                                            key={`${recurso.tipo}-${recurso.id}`}
                                                            type="button"
                                                            onClick={() => seleccionarRecurso(recurso)}
                                                            className="flex w-full items-start gap-2 rounded-lg px-3 py-2 text-left transition hover:bg-[#F4F6F5]"
                                                        >
                                                            <Paperclip size={14} className="mt-0.5 shrink-0 text-[#6B8F71]" />
                                                            <span className="min-w-0">
                                                                <span className="block truncate text-xs font-semibold text-[#2C4A5E]">
                                                                    {recurso.label}
                                                                </span>
                                                                {recurso.detail && (
                                                                    <span className="mt-0.5 block truncate text-[0.68rem] text-[#697586]">
                                                                        {recurso.detail}
                                                                    </span>
                                                                )}
                                                            </span>
                                                        </button>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                        <p className="mt-2 flex items-center gap-1 text-[0.68rem] text-[#697586]">
                                            <Check size={12} className="text-[#267148]" />
                                            Los recursos se comparten sin duplicar información.
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
                                        Seleccioná un chat existente o creá uno nuevo
                                        para comunicarte con otro usuario.
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
