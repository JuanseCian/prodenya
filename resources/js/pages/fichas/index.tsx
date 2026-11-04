import { Head, Link, router } from '@inertiajs/react';
import {
    CalendarDays,
    FileText,
    MapPin,
    Search,
    UserRound,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import fichasRoutes from '@/routes/fichas';
import legajosRoutes from '@/routes/legajos';

interface Ficha {
    id: number;
    numero_legajo: string;
    nivel_riesgo: string | null;
    estado: string | null;
    fecha_apertura: string | null;
    profesional: string | null;
    origen_derivacion: string | null;
    problematicas: string[];
    observaciones: string | null;
    persona: {
        nombre_completo: string;
        dni: string | null;
        fecha_nacimiento: string | null;
        edad: number | null;
        domicilio: string;
        localidad: string | null;
        barrio: string | null;
    } | null;
}

interface Props {
    fichas: Paginador<Ficha>;
    buscar?: string | null;
}

const riesgoClass: Record<string, string> = {
    Bajo: 'bg-[#E3F0E6] text-[#41634A]',
    Medio: 'bg-[#FBF1DC] text-[#8A6420]',
    Alto: 'bg-[#FCE9DC] text-[#9A4E1C]',
    Critico: 'bg-[#F8E1DD] text-[#A84635]',
};

const inputClass =
    'h-11 border-[#D5D1C6] bg-white pl-10 text-[#2B2F36] shadow-none focus-visible:border-[#2C4A5E] focus-visible:ring-[#2C4A5E]/20';

export default function FichasIndex({ fichas, buscar }: Props) {
    const [busqueda, setBusqueda] = useState(buscar ?? '');

    const buscarFichas = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get(
            fichasRoutes.index.url(),
            busqueda ? { buscar: busqueda } : {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Fichas" />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="grid size-14 shrink-0 place-items-center rounded-2xl border border-[#D5E6D9] bg-[#EAF4EE] text-[#39795B] shadow-[0_8px_20px_rgba(57,121,91,0.08)]">
                                <FileText size={25} strokeWidth={1.7} />
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.16em] text-[#6B8F71] uppercase">
                                    Archivo de protección
                                </p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">
                                    Fichas
                                </h1>
                                <p className="mt-2 max-w-2xl text-sm leading-6 text-[#2B2F36]/65">
                                    Una ficha digital por cada legajo. Abrí
                                    cualquiera para consultar su información
                                    completa.
                                </p>
                            </div>
                        </div>
                        <Link
                            href={legajosRoutes.create.url()}
                            className="inline-flex h-11 items-center justify-center rounded-md bg-[#2C4A5E] px-5 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                        >
                            Crear nuevo legajo
                        </Link>
                    </div>

                    <form
                        onSubmit={buscarFichas}
                        className="mb-6 flex flex-col gap-2 sm:flex-row"
                    >
                        <div className="relative w-full sm:max-w-xl">
                            <Search className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-[#2B2F36]/40" />
                            <Input
                                value={busqueda}
                                onChange={(event) =>
                                    setBusqueda(event.target.value)
                                }
                                placeholder="Buscar por legajo, nombre, apellido, DNI o problemática"
                                className={inputClass}
                            />
                        </div>
                        <button
                            type="submit"
                            className="h-11 rounded-md border border-[#DCE3DF] bg-white px-5 text-sm font-semibold text-[#2C4A5E] transition hover:border-[#2C4A5E]/40 hover:bg-[#F7FAF8]"
                        >
                            Buscar fichas
                        </button>
                    </form>

                    {fichas.data.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-[#BFE3D2] bg-[#F3FCF7] px-6 py-16 text-center">
                            <FileText
                                className="mx-auto text-[#6B8F71]"
                                size={28}
                                strokeWidth={1.7}
                            />
                            <p className="mt-3 text-sm font-semibold text-[#2B2F36]">
                                {buscar
                                    ? 'No se encontraron fichas con esa búsqueda.'
                                    : 'Todavía no hay fichas registradas.'}
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                            {fichas.data.map((ficha) => (
                                <Link
                                    key={ficha.id}
                                    href={legajosRoutes.show.url(ficha.id)}
                                    className="group relative overflow-hidden rounded-2xl border border-[#D8D7CF] bg-[#FFFDF7] p-5 shadow-[0_14px_35px_rgba(93,84,61,0.08)] transition hover:-translate-y-0.5 hover:border-[#AFC7B6] hover:shadow-[0_18px_40px_rgba(23,50,71,0.12)]"
                                >
                                    <div className="pointer-events-none absolute top-0 right-0 left-0 h-1 bg-[#D78B5B]" />
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-[0.65rem] font-bold tracking-[0.16em] text-[#A36A4A] uppercase">
                                                Ficha de legajo
                                            </p>
                                            <p className="mt-1 font-mono text-lg font-semibold tracking-wide text-[#173247]">
                                                {ficha.numero_legajo}
                                            </p>
                                        </div>
                                        {ficha.estado && (
                                            <Badge className="border-0 bg-[#EAF4EE] text-[#39795B]">
                                                {ficha.estado}
                                            </Badge>
                                        )}
                                    </div>

                                    <div className="mt-5 border-t border-[#E8E1D4] pt-4">
                                        {ficha.persona ? (
                                            <>
                                                <div className="flex items-start gap-2.5">
                                                    <UserRound className="mt-0.5 size-4 shrink-0 text-[#6B8F71]" />
                                                    <div className="min-w-0">
                                                        <p className="truncate text-base font-semibold text-[#2C4A5E]">
                                                            {
                                                                ficha.persona
                                                                    .nombre_completo
                                                            }
                                                        </p>
                                                        <p className="mt-1 text-xs text-[#697586]">
                                                            DNI{' '}
                                                            {ficha.persona
                                                                .dni ??
                                                                'Sin informar'}
                                                            {ficha.persona
                                                                .edad !==
                                                                null &&
                                                                ` · ${ficha.persona.edad} años`}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="mt-4 grid gap-2 text-xs text-[#2B2F36]/70">
                                                    <p className="flex items-center gap-2">
                                                        <CalendarDays className="size-3.5 text-[#6B8F71]" />
                                                        Nacimiento:{' '}
                                                        {ficha.persona
                                                            .fecha_nacimiento ??
                                                            'Sin informar'}
                                                    </p>
                                                    <p className="flex items-center gap-2">
                                                        <MapPin className="size-3.5 text-[#6B8F71]" />
                                                        {ficha.persona
                                                            .localidad ??
                                                            'Localidad sin informar'}
                                                        {ficha.persona.barrio &&
                                                            ` · ${ficha.persona.barrio}`}
                                                    </p>
                                                </div>
                                            </>
                                        ) : (
                                            <p className="text-sm text-[#697586]">
                                                Datos personales reservados.
                                            </p>
                                        )}
                                    </div>

                                    <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-[#E8E1D4] pt-4 text-xs">
                                        {ficha.nivel_riesgo && (
                                            <span
                                                className={`rounded-full px-2.5 py-1 font-semibold ${riesgoClass[ficha.nivel_riesgo] ?? 'bg-[#EEF0ED] text-[#2B2F36]'}`}
                                            >
                                                Riesgo{' '}
                                                {ficha.nivel_riesgo ===
                                                'Critico'
                                                    ? 'crítico'
                                                    : ficha.nivel_riesgo.toLowerCase()}
                                            </span>
                                        )}
                                        {ficha.fecha_apertura && (
                                            <span className="text-[#697586]">
                                                Apertura {ficha.fecha_apertura}
                                            </span>
                                        )}
                                    </div>

                                    <p className="mt-4 text-xs font-semibold text-[#2C4A5E] group-hover:underline">
                                        Abrir legajo →
                                    </p>
                                </Link>
                            ))}
                        </div>
                    )}

                    <div className="mt-6 overflow-hidden rounded-xl border border-[#DCE3DF] bg-white">
                        <Paginacion paginador={fichas} unidad="fichas" />
                    </div>
                </div>
            </main>
        </>
    );
}
