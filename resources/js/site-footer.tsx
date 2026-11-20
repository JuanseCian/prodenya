import { createRoot } from 'react-dom/client';
import type { Root } from 'react-dom/client';
import { Footer02 } from '@/components/ui/footer-02';

type ReactMount = HTMLElement & {
    __prodenyaRoot?: Root;
};

const footerMount = document.querySelector<ReactMount>('[data-site-footer]');

if (footerMount) {
    try {
        const root = footerMount.__prodenyaRoot ?? createRoot(footerMount);
        footerMount.__prodenyaRoot = root;

        root.render(<Footer02 />);
    } catch {
        footerMount.remove();
    }
}
