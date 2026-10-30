import { usePoll, router } from '@inertiajs/react';
import { Bell, CheckCheck, ExternalLink, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

import { ProdenyaToast } from '@/components/prodenya-toast';
import notificaciones from '@/routes/notificaciones';
import type { NotificationItem } from '@/types';

const POLLING_INTERVAL = 15000;

function formatDate(value: string | null): string {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('es-AR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
}

type Props = {
    initialNotifications: NotificationItem[];
    initialUnreadCount: number;
    standalone?: boolean;
    endpoint?: string;
    placement?: 'floating' | 'header' | 'dashboard';
};

export function NotificationCenter({
    initialNotifications,
    initialUnreadCount,
    standalone = false,
    endpoint,
    placement = 'floating',
}: Props) {
    const [open, setOpen] = useState(false);
    const [notifications, setNotifications] = useState(initialNotifications);
    const [unreadCount, setUnreadCount] = useState(initialUnreadCount);
    const knownIds = useRef(new Set<string>());
    const initialized = useRef(false);
    const entryToastShown = useRef(false);

    usePoll(
        POLLING_INTERVAL,
        {
            only: ['notificaciones', 'notificacionesNoLeidas'],
        },
        { mode: 'rest', autoStart: !standalone },
    );

    useEffect(() => {
        if (standalone || !endpoint) {
            return;
        }

        return router.on('navigate', (event) => {
            const props = event.detail.page.props;

            setNotifications((props.notificaciones ?? []) as NotificationItem[]);
            setUnreadCount(Number(props.notificacionesNoLeidas ?? 0));
        });
    }, [endpoint, standalone]);

    useEffect(() => {
        if (!standalone || !endpoint) {
            return;
        }

        let cancelado = false;

        const cargar = async (): Promise<void> => {
            const response = await fetch(endpoint, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok || cancelado) {
                return;
            }

            const data = (await response.json()) as {
                notificaciones: NotificationItem[];
                notificacionesNoLeidas: number;
            };

            if (cancelado) {
                return;
            }

            setNotifications(data.notificaciones);
            setUnreadCount(data.notificacionesNoLeidas);
        };

        void cargar();
        const interval = window.setInterval(() => void cargar(), POLLING_INTERVAL);

        return () => {
            cancelado = true;
            window.clearInterval(interval);
        };
    }, [endpoint, standalone]);

    useEffect(() => {
        if (entryToastShown.current || initialUnreadCount < 1) {
            return;
        }

        entryToastShown.current = true;
        const message =
            initialUnreadCount === 1
                ? 'Tenés 1 notificación sin leer. Abrí la campana para verla.'
                : `Tenés ${initialUnreadCount} notificaciones sin leer. Abrí la campana para verlas.`;

        toast.custom(
            (id) => (
                <ProdenyaToast
                    type="info"
                    title="Tenés nuevas notificaciones"
                    message={message}
                    onDismiss={() => toast.dismiss(id)}
                />
            ),
            { duration: 7000 },
        );
    }, [initialUnreadCount]);

    useEffect(() => {
        const newNotifications = notifications.filter(
            (notification) => !knownIds.current.has(notification.id),
        );

        if (initialized.current) {
            newNotifications.forEach((notification) => {
                toast.custom(
                    (id) => (
                        <ProdenyaToast
                            type="info"
                            title={notification.titulo}
                            message={notification.mensaje}
                            onDismiss={() => toast.dismiss(id)}
                        />
                    ),
                    { duration: 6000 },
                );
            });
        }

        notifications.forEach((notification) => {
            knownIds.current.add(notification.id);
        });
        initialized.current = true;
    }, [notifications]);

    const marcarLeida = (notification: NotificationItem): void => {
        if (standalone) {
            void fetch(notificaciones.leer(notification.id).url, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                },
            }).then(() => {
                setNotifications((actuales) =>
                    actuales.map((actual) =>
                        actual.id === notification.id
                            ? { ...actual, leida: true }
                            : actual,
                    ),
                );
                setUnreadCount((actual) => Math.max(0, actual - 1));

                if (notification.url) {
                    window.location.href = notification.url;
                }
            });

            return;
        }

        router.patch(
            notificaciones.leer(notification.id).url,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    if (notification.url) {
                        router.visit(notification.url);
                    }
                },
            },
        );
    };

    const marcarTodasLeidas = (): void => {
        if (standalone) {
            void fetch(notificaciones.leerTodas().url, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                },
            }).then(() => {
                setNotifications((actuales) =>
                    actuales.map((actual) => ({ ...actual, leida: true })),
                );
                setUnreadCount(0);
            });

            return;
        }

        router.patch(notificaciones.leerTodas().url, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div
            className={
                placement === 'header'
                    ? 'relative z-[70]'
                    : placement === 'dashboard'
                      ? 'relative z-[70]'
                    : 'fixed top-20 right-4 z-[70]'
            }
        >
            <button
                type="button"
                aria-label="Abrir notificaciones"
                aria-expanded={open}
                onClick={() => setOpen((actual) => !actual)}
                className={`relative grid size-11 place-items-center rounded-full border transition focus-visible:ring-2 focus-visible:ring-[#6B8F71]/50 focus-visible:outline-none ${
                    placement === 'header'
                        ? 'border-white/20 bg-white/10 text-white shadow-none hover:border-white/30 hover:bg-white/16'
                        : placement === 'dashboard'
                          ? 'border-[#DCE3DF] bg-white text-[#2C4A5E] shadow-sm hover:bg-[#F3F7F4]'
                          : 'border-[#DCE3DF] bg-white text-[#2C4A5E] shadow-[0_8px_24px_rgba(23,50,71,0.16)] hover:bg-[#F3F7F4] dark:border-[#3B4552] dark:bg-[#232A34] dark:text-white dark:hover:bg-[#2E3742]'
                }`}
            >
                <Bell size={19} />
                {unreadCount > 0 && (
                    <span className="absolute -top-1 -right-1 grid min-w-5 place-items-center rounded-full bg-[#A84635] px-1 text-[0.65rem] font-bold text-white">
                        {unreadCount > 99 ? '99+' : unreadCount}
                    </span>
                )}
            </button>

            {open && (
                <section className="absolute top-[calc(100%+0.75rem)] right-0 w-[min(calc(100vw-2rem),390px)] origin-top-right animate-in overflow-hidden rounded-2xl border border-[#DCE3DF] bg-white shadow-[0_18px_50px_rgba(23,50,71,0.2)] duration-200 fade-in-0 zoom-in-95 dark:border-[#3B4552] dark:bg-[#232A34]">
                    <header className="flex items-center justify-between border-b border-[#E4E8E5] px-4 py-3 dark:border-[#3B4552]">
                        <div>
                            <h2 className="text-sm font-bold text-[#173247] dark:text-white">
                                Notificaciones
                            </h2>
                            <p className="mt-0.5 text-xs text-[#697586] dark:text-white/60">
                                {unreadCount > 0
                                    ? `${unreadCount} sin leer`
                                    : 'Todo al día'}
                            </p>
                        </div>
                        <div className="flex items-center gap-1">
                            {unreadCount > 0 && (
                                <button
                                    type="button"
                                    onClick={marcarTodasLeidas}
                                    title="Marcar todas como leídas"
                                    className="grid size-8 place-items-center rounded-lg text-[#6B8F71] transition hover:bg-[#F3F7F4] focus-visible:ring-2 focus-visible:ring-[#6B8F71]/50 focus-visible:outline-none"
                                >
                                    <CheckCheck size={17} />
                                </button>
                            )}
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                aria-label="Cerrar notificaciones"
                                className="grid size-8 place-items-center rounded-lg text-[#697586] transition hover:bg-[#F3F7F4] focus-visible:ring-2 focus-visible:ring-[#6B8F71]/50 focus-visible:outline-none"
                            >
                                <X size={17} />
                            </button>
                        </div>
                    </header>

                    <div className="max-h-[min(65vh,480px)] overflow-y-auto">
                        {notifications.length === 0 ? (
                            <p className="px-4 py-8 text-center text-sm text-[#697586]">
                                No tenés notificaciones nuevas.
                            </p>
                        ) : (
                            notifications.map((notification) => (
                                <button
                                    key={notification.id}
                                    type="button"
                                    onClick={() => marcarLeida(notification)}
                                    className={`flex w-full items-start gap-3 border-b border-[#EEF0ED] px-4 py-3 text-left transition last:border-b-0 hover:bg-[#F7FAF8] focus-visible:bg-[#F7FAF8] focus-visible:outline-none dark:border-[#3B4552] dark:hover:bg-[#2E3742] ${notification.leida ? 'opacity-65' : ''}`}
                                >
                                    <span
                                        className={`mt-1 size-2 shrink-0 rounded-full ${notification.leida ? 'bg-[#C9D2CC]' : 'bg-[#A84635]'}`}
                                    />
                                    <span className="min-w-0 flex-1">
                                        <span className="flex items-start justify-between gap-2">
                                            <strong className="text-sm text-[#2C4A5E] dark:text-white">
                                                {notification.titulo}
                                            </strong>
                                            {notification.url && (
                                                <ExternalLink
                                                    size={14}
                                                    className="mt-0.5 shrink-0 text-[#6B8F71]"
                                                />
                                            )}
                                        </span>
                                        <span className="mt-1 block text-xs leading-5 text-[#697586] dark:text-white/60">
                                            {notification.mensaje}
                                        </span>
                                        <span className="mt-1 block text-[0.68rem] text-[#9AA5A0]">
                                            {formatDate(notification.creada_en)}
                                        </span>
                                    </span>
                                </button>
                            ))
                        )}
                    </div>
                </section>
            )}
        </div>
    );
}
