import { createRoot } from 'react-dom/client';
import type { Root } from 'react-dom/client';
import PasskeyOnboardingModal from '@/components/passkey-onboarding-modal';
import { Toaster } from '@/components/ui/sonner';

type ReactMount = HTMLElement & {
    __prodenyaRoot?: Root;
};

const headerExtrasMount = document.querySelector<ReactMount>(
    '[data-site-header-extras]',
);

if (headerExtrasMount) {
    try {
        const standalone = headerExtrasMount.dataset.mode !== 'inertia';
        const passkeyEnabled = headerExtrasMount.dataset.passkeyEnabled === '1';
        const passkeyPasswordConfirmed =
            headerExtrasMount.dataset.passkeyPasswordConfirmed === '1';

        const root =
            headerExtrasMount.__prodenyaRoot ?? createRoot(headerExtrasMount);
        headerExtrasMount.__prodenyaRoot = root;

        root.render(
            <>
                <PasskeyOnboardingModal
                    enabled={passkeyEnabled}
                    passwordConfirmed={passkeyPasswordConfirmed}
                />
                {standalone && <Toaster />}
            </>,
        );
    } catch {
        headerExtrasMount.remove();
    }
}
