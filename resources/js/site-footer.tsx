import { createRoot } from 'react-dom/client';
import type { Root } from 'react-dom/client';
import { Footer02 } from '@/components/ui/footer-02';
import type { FooterAccess } from '@/components/ui/footer-02';

type ReactMount = HTMLElement & {
    __prodenyaRoot?: Root;
};

const footerMount = document.querySelector<ReactMount>('[data-site-footer]');

if (footerMount) {
    try {
        const access = JSON.parse(
            footerMount.dataset.access ?? '{}',
        ) as Partial<FooterAccess>;

        const root = footerMount.__prodenyaRoot ?? createRoot(footerMount);
        footerMount.__prodenyaRoot = root;

        root.render(
            <Footer02
                access={access}
                authenticated={footerMount.dataset.authenticated === '1'}
            />,
        );
    } catch {
        footerMount.remove();
    }
}
