import { router } from '@inertiajs/react';
import { useEffect } from 'react';

export function useUnsavedChanges(isDirty: boolean) {
    useEffect(() => {
        if (!isDirty) {
            return;
        }

        const handleBeforeUnload = (event: BeforeUnloadEvent) => {
            event.preventDefault();
            event.returnValue = '';
        };

        const removeBeforeListener = router.on('before', (event) => {
            if (
                window.confirm(
                    'Hay cambios sin guardar. ¿Querés salir de esta sección?',
                )
            ) {
                return;
            }

            event.preventDefault();
        });

        window.addEventListener('beforeunload', handleBeforeUnload);

        return () => {
            removeBeforeListener();
            window.removeEventListener('beforeunload', handleBeforeUnload);
        };
    }, [isDirty]);
}
