import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';
import { ProdenyaToast } from '@/components/prodenya-toast';
import type { FlashToast } from '@/types/ui';

export function useFlashToast(): void {
    const lastStatus = useRef<string | null>(null);

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

                return;
            }

            if (lastStatus.current === status) {
                return;
            }

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
        });
    }, []);
}
