import { Head } from '@inertiajs/react';
import { Crown, Medal, Trophy } from 'lucide-react';

import EstadisticasShell from '@/components/estadisticas-shell';
import type { EstadisticasModulo } from '@/components/estadisticas-shell';

type RankingItem = {
    posicion: number;
    nombre: string;
    profesion: string | null;
    rol: string | null;
    puntos: number;
    movimientos: number;
};

type Props = {
    ranking: RankingItem[];
    participantes: number;
    modulosDisponibles: EstadisticasModulo[];
};

const podiumStyles = [
    'border-[#D7B657] bg-[#FFF9E8] dark:border-[#8F7731] dark:bg-[#3C3520]',
    'border-[#B9C4CC] bg-[#F6F9FA] dark:border-[#65737D] dark:bg-[#273238]',
    'border-[#C78D64] bg-[#FFF5EE] dark:border-[#885F43] dark:bg-[#382A23]',
];

const podiumIcons = [Crown, Medal, Medal];

function iniciales(nombre: string): string {
    return nombre
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((parte) => parte[0])
        .join('')
        .toUpperCase();
}

function etiquetaRol(rol: string | null): string {
    return rol ? rol.charAt(0).toUpperCase() + rol.slice(1) : 'Equipo';
}

export default function Ranking({ ranking, participantes, modulosDisponibles }: Props) {
    const podium = ranking.slice(0, 3);
    const restantes = ranking.slice(3);

    return (
        <>
            <Head title="Ranking de gestión" />
            <EstadisticasShell
                active="ranking"
                title="Ranking de gestión"
                modulosDisponibles={modulosDisponibles}
            >
                <div className="stats-page-surface min-h-[calc(100vh-4rem)] px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <div className="mx-auto max-w-6xl">
                        <section className="border-b border-[#DCE3E6] pb-7 dark:border-[#34424B]">
                            <p className="text-sm font-semibold text-[#39795B] dark:text-[#8BC9A9]">Reconocimiento del equipo</p>
                            <h2 className="mt-2 max-w-3xl text-3xl font-semibold tracking-[-0.035em] text-[#173247] sm:text-4xl dark:text-[#EDF6FB]">
                                Actividad y cumplimiento acumulado.
                            </h2>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-[#5A7180] dark:text-[#AFC2CC]">
                                El puntaje se construye con movimientos auditables de tareas, intervenciones y actas. Solo lo pueden consultar administración y coordinación.
                            </p>
                        </section>

                        <div className="mt-7 flex items-center gap-3 rounded-md border border-[#DCE3E6] bg-white px-4 py-3 text-sm text-[#5A7180] shadow-[0_8px_24px_rgba(23,50,71,0.04)] dark:border-[#34424B] dark:bg-[#1D2A31] dark:text-[#AFC2CC]">
                            <Trophy className="size-5 shrink-0 text-[#C5982E]" />
                            <span><strong className="text-[#173247] dark:text-[#EDF6FB]">{participantes}</strong> participantes activos · ranking acumulado desde la implementación.</span>
                        </div>

                        {ranking.length === 0 ? (
                            <div className="mt-7 rounded-lg border border-dashed border-[#DCE3E6] bg-white px-6 py-20 text-center text-sm text-[#697586] dark:border-[#34424B] dark:bg-[#1D2A31] dark:text-[#AFC2CC]">
                                Todavía no hay usuarios operativos para mostrar.
                            </div>
                        ) : (
                            <>
                                <section className="mt-7 grid gap-4 md:grid-cols-3" aria-label="Podio del ranking">
                                    {podium.map((item, index) => {
                                        const Icon = podiumIcons[index];

                                        return (
                                            <article key={item.nombre} className={`rounded-lg border p-5 shadow-[0_10px_28px_rgba(23,50,71,0.045)] ${podiumStyles[index]}`}>
                                                <div className="flex items-center justify-between">
                                                    <span className="text-xs font-bold tracking-[0.13em] text-[#697586] uppercase dark:text-[#AFC2CC]">Puesto {item.posicion}</span>
                                                    <Icon className="size-5 text-[#B88928]" />
                                                </div>
                                                <div className="mt-5 flex items-center gap-3">
                                                    <span className="grid size-12 place-items-center rounded-full bg-[#173247] text-sm font-bold text-white dark:bg-[#CDE8D6] dark:text-[#173247]">{iniciales(item.nombre)}</span>
                                                    <div className="min-w-0">
                                                        <h3 className="truncate font-semibold text-[#173247] dark:text-[#EDF6FB]">{item.nombre}</h3>
                                                        <p className="mt-1 text-xs text-[#697586] dark:text-[#AFC2CC]">{etiquetaRol(item.rol)}</p>
                                                    </div>
                                                </div>
                                                <p className="mt-6 text-3xl font-bold tracking-tight text-[#173247] dark:text-[#EDF6FB]">{item.puntos} <span className="text-sm font-semibold text-[#697586] dark:text-[#AFC2CC]">puntos</span></p>
                                                <p className="mt-1 text-xs text-[#697586] dark:text-[#AFC2CC]">{item.movimientos} movimientos registrados</p>
                                            </article>
                                        );
                                    })}
                                </section>

                                {restantes.length > 0 && (
                                    <section className="mt-7 overflow-hidden rounded-lg border border-[#DCE3E6] bg-white shadow-[0_10px_28px_rgba(23,50,71,0.045)] dark:border-[#34424B] dark:bg-[#1D2A31]" aria-label="Resto del ranking">
                                        <div className="border-b border-[#E8EEEA] px-5 py-4 dark:border-[#34424B]">
                                            <h2 className="font-semibold text-[#173247] dark:text-[#EDF6FB]">Resto del equipo</h2>
                                        </div>
                                        <div className="divide-y divide-[#E8EEEA] dark:divide-[#34424B]">
                                            {restantes.map((item) => (
                                                <div key={item.nombre} className="flex items-center gap-3 px-5 py-4">
                                                    <span className="w-8 text-center text-sm font-bold text-[#697586] dark:text-[#AFC2CC]">{item.posicion}</span>
                                                    <span className="grid size-9 place-items-center rounded-full bg-[#EAF2ED] text-xs font-bold text-[#39795B] dark:bg-[#2A4440] dark:text-[#A9D6B9]">{iniciales(item.nombre)}</span>
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-sm font-semibold text-[#2C4A5E] dark:text-[#D8E2E8]">{item.nombre}</p>
                                                        <p className="text-xs text-[#697586] dark:text-[#AFC2CC]">{etiquetaRol(item.rol)} · {item.movimientos} movimientos</p>
                                                    </div>
                                                    <strong className="text-sm text-[#173247] dark:text-[#EDF6FB]">{item.puntos} pts.</strong>
                                                </div>
                                            ))}
                                        </div>
                                    </section>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </EstadisticasShell>
        </>
    );
}
