import {
    BookOpenCheck,
    ClipboardPenLine,
    FileCheck2,
    FileText,
    FileType,
    GraduationCap,
    Home,
    Phone,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { createRoot } from 'react-dom/client';

import { RadialOrbitalTimeline } from '@/components/ui/radial-orbital-timeline';
import type { RadialOrbitalTimelineItem } from '@/components/ui/radial-orbital-timeline';

type SerializedTimelineItem = Omit<RadialOrbitalTimelineItem, 'icon'> & {
    icon: keyof typeof icons;
};

const icons = {
    book: BookOpenCheck,
    clipboard: ClipboardPenLine,
    file: FileText,
    check: FileCheck2,
    form: FileType,
    school: GraduationCap,
    home: Home,
    phone: Phone,
} satisfies Record<string, LucideIcon>;

function mountQuickActaOrbital(): void {
    const rootElement = document.getElementById('home-quick-acta-orbital');
    const dataElement = document.getElementById('home-quick-acta-data');

    if (!rootElement || !dataElement) {
        return;
    }

    let serializedItems: SerializedTimelineItem[];

    try {
        serializedItems = JSON.parse(
            dataElement.textContent ?? '[]',
        ) as SerializedTimelineItem[];
    } catch {
        return;
    }

    const timelineData = serializedItems.map((item) => ({
        ...item,
        icon: icons[item.icon] ?? FileText,
    }));

    createRoot(rootElement).render(
        <RadialOrbitalTimeline timelineData={timelineData} />,
    );
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountQuickActaOrbital, {
        once: true,
    });
} else {
    mountQuickActaOrbital();
}
