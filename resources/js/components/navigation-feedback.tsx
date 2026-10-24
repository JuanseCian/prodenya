import { router } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

const SLOW_NAVIGATION_DELAY = 700;

export function NavigationFeedback() {
    const [showSlowLoader, setShowSlowLoader] = useState(false);

    useEffect(() => {
        const root = document.documentElement;
        root.classList.add('is-page-entering');

        const initialLoader = document.getElementById('initial-page-loader');
        const removePageEnter = window.setTimeout(() => {
            root.classList.remove('is-page-entering');
        }, 300);

        if (initialLoader) {
            initialLoader.classList.add('is-closing');

            const removeInitialLoader = window.setTimeout(() => {
                initialLoader.remove();
            }, 220);

            return () => {
                window.clearTimeout(removeInitialLoader);
                window.clearTimeout(removePageEnter);
                root.classList.remove('is-page-entering');
            };
        }

        return () => {
            window.clearTimeout(removePageEnter);
            root.classList.remove('is-page-entering');
        };
    }, []);

    useEffect(() => {
        let slowLoaderTimer: number | undefined;

        const clearSlowLoader = () => {
            if (slowLoaderTimer !== undefined) {
                window.clearTimeout(slowLoaderTimer);
                slowLoaderTimer = undefined;
            }

            setShowSlowLoader(false);
        };

        const removeStartListener = router.on('start', () => {
            clearSlowLoader();
            slowLoaderTimer = window.setTimeout(() => {
                setShowSlowLoader(true);
            }, SLOW_NAVIGATION_DELAY);
        });
        const removeFinishListener = router.on('finish', clearSlowLoader);

        return () => {
            removeStartListener();
            removeFinishListener();
            clearSlowLoader();
        };
    }, []);

    if (!showSlowLoader) {
        return null;
    }

    return (
        <div
            className="prodenya-slow-navigation"
            role="status"
            aria-live="polite"
            aria-label="Cargando página"
        >
            <div className="prodenya-slow-navigation-card">
                <span className="prodenya-loading-mark" aria-hidden="true">
                    <LoaderCircle size={25} strokeWidth={1.8} />
                </span>
                <div>
                    <p className="text-sm font-semibold text-[#173247] dark:text-[#EDF6FB]">
                        Cargando la página
                    </p>
                    <p className="mt-1 text-xs text-[#697586] dark:text-[#A8BBC5]">
                        Esto está tardando un poco más de lo habitual.
                    </p>
                </div>
            </div>
        </div>
    );
}
