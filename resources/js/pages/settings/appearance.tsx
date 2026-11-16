import { Head } from '@inertiajs/react';
import { Palette } from 'lucide-react';
import AppearanceTabs from '@/components/appearance-tabs';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Preferencias" />

            <h1 className="sr-only">Preferencias</h1>

            <div className="space-y-6 sm:space-y-8">
                <div>
                    <p className="mb-2 text-xs font-bold tracking-[0.16em] text-primary uppercase">Personalización</p>
                    <h1 className="text-2xl font-semibold tracking-[-0.03em] text-foreground sm:text-3xl">Preferencias</h1>
                    <p className="mt-2 text-sm leading-6 text-muted-foreground sm:text-base">Elegí cómo querés ver la plataforma.</p>
                </div>
                <section className="rounded-2xl border border-border bg-card p-5 shadow-sm sm:p-7" aria-labelledby="appearance-title">
                    <div className="mb-6 flex items-start gap-3 border-b border-border pb-5">
                        <span className="flex size-10 items-center justify-center rounded-xl bg-secondary text-primary" aria-hidden="true">
                            <Palette className="size-5" />
                        </span>
                        <div>
                            <h2 id="appearance-title" className="font-semibold text-card-foreground">Apariencia de la plataforma</h2>
                            <p className="mt-1 text-sm leading-5 text-muted-foreground">Seleccioná el modo que mejor se adapte a tu jornada de trabajo.</p>
                        </div>
                    </div>
                    <AppearanceTabs />
                </section>
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
