import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';
import { ProdenyaToast } from '@/components/prodenya-toast';
import type { FlashToast } from '@/types/ui';

export function useFlashToast(): void {
    const lastStatus = useRef<string | null>(null);
    const lastError = useRef<string | null>(null);

    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.toast as FlashToast | undefined;

            if (!data) {
                return;
            }

            toast.custom(
                (id) => (
                    <ProdenyaToast
                        type={data.type}
                        title={data.title}
                        message={data.message}
                        onDismiss={() => toast.dismiss(id)}
                    />
                ),
                { duration: 5000 },
            );
        });
    }, []);

    useEffect(() => {
        return router.on('navigate', (event) => {
            const status = event.detail.page.props.status;

            if (typeof status !== 'string' || status.length === 0) {
                lastStatus.current = null;
            } else if (lastStatus.current !== status) {
                lastStatus.current = status;
                toast.custom(
                    (id) => (
                        <ProdenyaToast
                            type="success"
                            message={status}
                            onDismiss={() => toast.dismiss(id)}
                        />
                    ),
                    { duration: 5000 },
                );
            }

            const errors = event.detail.page.props.errors as
                | Record<string, unknown>
                | undefined;
            const error = errors
                ? Object.values(errors).find((value) => value)
                : null;
            const message = Array.isArray(error)
                ? String(error[0] ?? '')
                : typeof error === 'string'
                  ? error
                  : null;

            if (!message) {
                lastError.current = null;

                return;
            }

            if (lastError.current === message) {
                return;
            }

            lastError.current = message;
            toast.custom(
                (id) => (
                    <ProdenyaToast
                        type="error"
                        message={message}
                        onDismiss={() => toast.dismiss(id)}
                    />
                ),
                { duration: 6000 },
            );
        });
    }, []);
}
