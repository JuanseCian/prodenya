import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { ProdenyaToast } from '@/components/prodenya-toast';
import type { FlashToast } from '@/types/ui';

export function useFlashToast(): void {
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
}
