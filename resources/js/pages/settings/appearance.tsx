import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Preferencias" />

            <h1 className="sr-only">Preferencias</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Preferencias"
                    description="Elegí cómo querés ver la plataforma."
                />
                <AppearanceTabs />
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Preferencias',
            href: editAppearance(),
        },
    ],
};
