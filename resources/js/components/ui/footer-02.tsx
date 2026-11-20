import { Link } from '@inertiajs/react';
import { home } from '@/routes';

export function Footer02() {

    return (
        <footer className="border-t border-white/10 bg-brand-strong pb-[calc(78px+env(safe-area-inset-bottom))] text-white lg:pb-0">
            <div className="mx-auto flex w-full max-w-7xl flex-col items-center px-5 py-12 sm:px-8 lg:py-14">
                <Link
                    href={home()}
                    className="inline-flex items-center gap-3 rounded-xl text-center focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#9ce0bf]"
                >
                    <img
                        src="/assets/img/SN.png"
                        alt="San Nicolás"
                        className="size-14 rounded-xl bg-white/10 p-2 object-contain"
                    />
                    <span className="text-left">
                        <span className="block text-base font-bold tracking-[0.16em] text-white">
                            PRODENYA
                        </span>
                        <span className="mt-1 block text-xs text-white/60">
                            Promoción y Protección de Derechos
                        </span>
                    </span>
                </Link>

                <p className="mt-6 max-w-xl text-center text-sm leading-6 text-white/70">
                    Plataforma institucional para acompañar la promoción y
                    protección integral de los derechos de niñas, niños y
                    adolescentes.
                </p>
                <p className="mt-4 text-center text-xs font-semibold tracking-[0.14em] text-[#9ce0bf] uppercase">
                    Municipalidad de San Nicolás de los Arroyos
                </p>

            </div>

            <div className="border-t border-white/10 bg-black/10">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-2 px-5 py-5 text-center text-xs text-white/50 sm:px-8 md:flex-row md:items-center md:justify-between md:text-left">
                    <span>© {new Date().getFullYear()} PRODENYA</span>
                    <span>Uso institucional · San Nicolás de los Arroyos</span>
                </div>
            </div>
        </footer>
    );
}
