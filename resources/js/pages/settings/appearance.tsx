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
                    <p className="mb-2 text-xs font-bold tracking-[0.16em] text-[#5d9b7a] uppercase">Personalización</p>
                    <h1 className="text-2xl font-semibold tracking-[-0.03em] text-[#173247] sm:text-3xl">Preferencias</h1>
                    <p className="mt-2 text-sm leading-6 text-[#63757e] sm:text-base">Elegí cómo querés ver la plataforma.</p>
                </div>
                <section className="rounded-2xl border border-[#d7e5dd] bg-white p-5 shadow-[0_12px_32px_rgba(23,50,71,0.06)] sm:p-7" aria-labelledby="appearance-title">
                    <div className="mb-6 flex items-start gap-3 border-b border-[#e5eee8] pb-5">
                        <span className="flex size-10 items-center justify-center rounded-xl bg-[#e7f4eb] text-[#4f916d]" aria-hidden="true">
                            <Palette className="size-5" />
                        </span>
                        <div>
                            <h2 id="appearance-title" className="font-semibold text-[#173247]">Apariencia de la plataforma</h2>
                            <p className="mt-1 text-sm leading-5 text-[#71828a]">Seleccioná el modo que mejor se adapte a tu jornada de trabajo.</p>
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
