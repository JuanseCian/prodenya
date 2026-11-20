import { createRoot } from 'react-dom/client';
import type { Root } from 'react-dom/client';
import { NotificationCenter } from '@/components/notification-center';
import PasskeyOnboardingModal from '@/components/passkey-onboarding-modal';
import { Toaster } from '@/components/ui/sonner';
import type { NotificationItem } from '@/types';

type ReactMount = HTMLElement & {
    __prodenyaRoot?: Root;
};

const notificationMount = document.querySelector<ReactMount>(
    '[data-site-notification-center]',
);

if (notificationMount) {
    try {
        const notifications = JSON.parse(
            notificationMount.dataset.notifications ?? '[]',
        ) as NotificationItem[];
        const unreadCount = Number(
            notificationMount.dataset.notificationsUnread ?? 0,
        );
        const standalone = notificationMount.dataset.mode !== 'inertia';
        const passkeyEnabled =
            notificationMount.dataset.passkeyEnabled === '1';
        const passkeyPasswordConfirmed =
            notificationMount.dataset.passkeyPasswordConfirmed === '1';

        const root =
            notificationMount.__prodenyaRoot ?? createRoot(notificationMount);
        notificationMount.__prodenyaRoot = root;

        root.render(
            <>
                <NotificationCenter
                    initialNotifications={notifications}
                    initialUnreadCount={unreadCount}
                    standalone={standalone}
                    endpoint={notificationMount.dataset.endpoint}
                    placement="header"
                />
                <PasskeyOnboardingModal
                    enabled={passkeyEnabled}
                    passwordConfirmed={passkeyPasswordConfirmed}
                />
                {standalone && <Toaster />}
            </>,
        );
    } catch {
        notificationMount.remove();
    }
}
