import { Head } from '@inertiajs/react';
import { HeartPulse } from 'lucide-react';

import EstadisticasShell from '@/components/estadisticas-shell';

const FONT_HEADING = 'inherit';

export default function EstadisticasIntervenciones() {
    return (
        <>
            <Head title="Estadísticas de intervenciones" />
            <EstadisticasShell
                active="intervenciones"
                title="Intervenciones"
                subtitle="Sin datos aún"
            >
                <div className="stats-page-surface min-h-[calc(100vh-77px)] px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <div className="mx-auto max-w-3xl">
                        <div className="rounded-lg border border-[#DFE4EA] bg-white p-8 text-center dark:border-[#3B4552] dark:bg-[#232A34]">
                            <div className="mx-auto grid size-10 place-items-center rounded-lg bg-[#EEECF7] text-[#5B559E] dark:bg-[#38385A] dark:text-[#AAA4E5]">
                                <HeartPulse size={23} strokeWidth={1.8} />
                            </div>
                            <h1
                                className="mt-4 text-xl font-semibold text-[#173247] dark:text-[#EDF6FB]"
                                style={{ fontFamily: FONT_HEADING }}
                            >
                                Intervenciones
                            </h1>
                            <p className="mt-2 text-sm text-[#2C4A5E]/60 dark:text-[#B7C9D4]">
                                Este módulo todavía no tiene datos.
                            </p>
                        </div>
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
