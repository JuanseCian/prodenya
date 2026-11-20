import { Head } from '@inertiajs/react';
import { Moon, Monitor, Palette, Sun } from 'lucide-react';
import AppearanceTabs from '@/components/appearance-tabs';
import SettingsSection from '@/components/settings-section';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Preferencias" />

            <div className="space-y-6 sm:space-y-8">
                <SettingsSection
                    icon={Palette}
                    eyebrow="Personalización"
                    title="Apariencia de la plataforma"
                    description="Seleccioná el modo que mejor se adapte a tu jornada de trabajo."
                >
                    <AppearanceTabs />

                    <div className="mt-6 grid gap-3 sm:grid-cols-3">
                        <div className="rounded-2xl border border-border bg-surface-soft p-4">
                            <Sun className="size-4 text-primary" />
                            <p className="mt-3 text-sm font-semibold text-foreground">
                                Claro
                            </p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Más luminoso y directo para espacios abiertos.
                            </p>
                        </div>
                        <div className="rounded-2xl border border-border bg-surface-soft p-4">
                            <Moon className="size-4 text-primary" />
                            <p className="mt-3 text-sm font-semibold text-foreground">
                                Oscuro
                            </p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Menos brillo para jornadas prolongadas.
                            </p>
                        </div>
                        <div className="rounded-2xl border border-border bg-surface-soft p-4">
                            <Monitor className="size-4 text-primary" />
                            <p className="mt-3 text-sm font-semibold text-foreground">
                                Sistema
                            </p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Sigue la configuración de tu dispositivo.
                            </p>
                        </div>
                    </div>
                </SettingsSection>
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
