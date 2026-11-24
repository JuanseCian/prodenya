import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, FileDown, Link2, Save, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';

import ActaHoja from '@/components/acta-hoja';
import type { DatosActa, TipoActa } from '@/components/acta-hoja';
import type {
    DatosPlanilla,
    MiembroGrupo,
    Responsable,
    TipoPlanilla,
} from '@/components/auh-planilla-dialog';
import AuhPlanillaHoja from '@/components/auh-planilla-hoja';
import type { FirmaEnCurso } from '@/components/auh-planilla-hoja';
import BuscadorPersonas from '@/components/buscador-personas';
import type { PersonaEncontrada } from '@/components/buscador-personas';
import { Button } from '@/components/ui/button';
import actasRoutes from '@/routes/actas';

interface LegajoResumen {
    id: number;
    numero_legajo: string;
    url: string;
}

interface Props {
    tipo: string;
    tipoLabel: string;
    grupo: string;
    esAuh: boolean;
    tipoAuh: TipoPlanilla | null;
    firmantes: Record<string, string>;
    legajo: LegajoResumen | null;
    datos: DatosActa | DatosPlanilla;
    barrios: string[];
    escuelas: string[];
}

/** Firmantes cuya aclaración es el adulto responsable de la planilla de AUH. */
const FIRMANTES_RESPONSABLE = ['responsable', 'solicitante'];

const miembroVacio: MiembroGrupo = {
    nombre: '',
    vinculo: '',
    fecha_relacion: '',
    dni: '',
    fecha_nacimiento: '',
};

const conFilasMinimas = (miembros: MiembroGrupo[]): MiembroGrupo[] => [
    ...miembros,
    ...Array.from({ length: Math.max(0, 5 - miembros.length) }, () => ({
        ...miembroVacio,
    })),
];

const xsrfToken = (): string => {
    const cookie = document.cookie
        .split('; ')
        .find((item) => item.startsWith('XSRF-TOKEN='));

    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
};

export default function ActaCreate({
    tipo,
    tipoLabel,
    grupo,
    esAuh,
    tipoAuh,
    firmantes,
    legajo: legajoInicial,
    datos: datosIniciales,
    barrios,
    escuelas,
}: Props) {
    const [legajo, setLegajo] = useState<LegajoResumen | null>(legajoInicial);
    const [personaId, setPersonaId] = useState<number | null>(null);
    const [datosActa, setDatosActa] = useState<DatosActa>(
        datosIniciales as DatosActa,
    );
    const [datosAuh, setDatosAuh] = useState<DatosPlanilla>(() => {
        const datos = datosIniciales as DatosPlanilla;

        return esAuh
            ? {
                  ...datos,
                  grupo_familiar: conFilasMinimas(datos.grupo_familiar ?? []),
              }
            : datos;
    });
    const [firmas, setFirmas] = useState<Record<string, FirmaEnCurso>>(() =>
        Object.fromEntries(
            Object.keys(firmantes).map((rol) => [
                rol,
                {
                    nombre:
                        FIRMANTES_RESPONSABLE.includes(rol) || esAuh
                            ? ''
                            : rol === 'equipo' || rol === 'autoridad'
                              ? (datosIniciales as DatosActa).operador
                              : '',
                    dibujo: '',
                },
            ]),
        ),
    );
    const [errores, setErrores] = useState<Record<string, string>>({});
    const [guardando, setGuardando] = useState(false);
    const [descargando, setDescargando] = useState(false);

    const setFirma = (rol: string, campo: keyof FirmaEnCurso, valor: string) =>
        setFirmas((actual) => ({
            ...actual,
            [rol]: { ...actual[rol], [campo]: valor },
        }));

    /** Al elegir una persona del sistema se completan sus datos y se vincula su legajo. */
    /**
     * Al elegir una persona del sistema se completan sus datos. Si tiene legajo,
     * el acta se vincula a él; si es un familiar registrado sin legajo (un adulto),
     * completa los datos de quien comparece o del responsable.
     */
    const elegirPersona = (persona: PersonaEncontrada) => {
        const esFamiliarExterno = persona.legajo === null;

        if (esAuh && !esFamiliarExterno) {
            router.get(
                actasRoutes.create.url(tipo, {
                    query: { legajo: persona.legajo!.id },
                }),
            );

            return;
        }

        if (!esFamiliarExterno) {
            setLegajo(persona.legajo);
            setPersonaId(persona.id);
        }

        if (esAuh) {
            setDatosAuh((actual) => ({
                ...actual,
                responsable: {
                    ...actual.responsable,
                    nombre: persona.nombre,
                    dni: persona.dni,
                    fecha_nacimiento: persona.fecha_nacimiento,
                    domicilio: persona.domicilio,
                    barrio: persona.barrio,
                    telefono: persona.telefono,
                },
            }));

            return;
        }

        const comoAdulto =
            esFamiliarExterno || tipo === 'citacion' || tipo === 'constancia';
        setDatosActa((actual) =>
            comoAdulto
                ? {
                      ...actual,
                      compareciente_nombre: persona.nombre,
                      compareciente_dni: persona.dni,
                      compareciente_domicilio: persona.domicilio,
                      compareciente_barrio: persona.barrio,
                      compareciente_telefono: persona.telefono,
                  }
                : {
                      ...actual,
                      nino_nombre: persona.nombre,
                      nino_dni: persona.dni,
                      nino_fecha_nacimiento: persona.fecha_nacimiento,
                  },
        );
    };

    const cuerpo = () =>
        JSON.parse(
            JSON.stringify({
                ...(esAuh ? datosAuh : datosActa),
                legajo_id: legajo?.id ?? null,
                persona_id: personaId,
                firmas,
            }),
        );

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setErrores({});
        router.post(actasRoutes.store.url(tipo), cuerpo(), {
            preserveScroll: true,
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false),
            onError: (errs) => setErrores(errs as Record<string, string>),
        });
    };

    const descargarBorrador = async () => {
        setDescargando(true);
        setErrores({});

        try {
            const sinFirmas = { ...cuerpo(), firmas: undefined };
            const response = await fetch(actasRoutes.borrador.url(tipo), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrfToken(),
                },
                body: JSON.stringify(sinFirmas),
            });

            if (response.status === 422) {
                const body = (await response.json()) as {
                    errors?: Record<string, string[]>;
                };
                setErrores(
                    Object.fromEntries(
                        Object.entries(body.errors ?? {}).map(
                            ([campo, mensajes]) => [campo, mensajes[0]],
                        ),
                    ),
                );

                return;
            }

            if (!response.ok) {
                throw new Error();
            }

            const url = URL.createObjectURL(await response.blob());
            const enlace = document.createElement('a');
            enlace.href = url;
            enlace.download = `${tipo}-borrador.docx`;
            document.body.appendChild(enlace);
            enlace.click();
            enlace.remove();
            URL.revokeObjectURL(url);
        } catch {
            setErrores({ general: 'No se pudo generar el borrador.' });
        } finally {
            setDescargando(false);
        }
    };

    const mensajesError = [...new Set(Object.values(errores))];

    return (
        <>
            <Head title={`Acta rápida · ${tipoLabel}`} />
            <main className="admin-page-shell min-h-screen bg-background px-4 py-6 sm:px-6 lg:px-8">
                <form
                    onSubmit={guardar}
                    className="mx-auto max-w-5xl space-y-5"
                >
                    <div className="flex items-center justify-between gap-3">
                        <Link
                            href={actasRoutes.index.url()}
                            className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                        >
                            <ArrowLeft className="size-4" />
                            Actas rápidas
                        </Link>
                    </div>

                    <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-primary uppercase">
                                {grupo === 'AUH' ? 'Planilla de AUH' : grupo}
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-tight text-foreground">
                                {tipoLabel}
                            </h1>
                        </div>
                        <div className="w-full sm:w-80">
                            <BuscadorPersonas
                                onElegir={elegirPersona}
                                placeholder="Buscar persona por DNI o nombre…"
                            />
                        </div>
                    </header>

                    <div className="flex flex-wrap items-center gap-2 rounded-xl border border-border bg-card px-4 py-3 text-sm">
                        <Link2 className="size-4 text-muted-foreground" />
                        {legajo ? (
                            <>
                                <span className="text-foreground">
                                    Se va a guardar en el legajo{' '}
                                    <Link
                                        href={legajo.url}
                                        className="font-semibold text-primary underline-offset-4 hover:underline"
                                    >
                                        {legajo.numero_legajo}
                                    </Link>
                                </span>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setLegajo(null);
                                        setPersonaId(null);
                                    }}
                                    className="ml-auto inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-muted-foreground hover:bg-accent hover:text-foreground"
                                >
                                    <X className="size-3" />
                                    Quitar
                                </button>
                            </>
                        ) : (
                            <span className="text-muted-foreground">
                                Sin legajo: quedará en «Actas sin legajo» para
                                vincularla después. Si la persona ya está en el
                                sistema, buscala arriba.
                            </span>
                        )}
                    </div>

                    <div className="rounded-2xl bg-[#EEF1EF] px-2 py-5 sm:px-6">
                        {esAuh && tipoAuh ? (
                            <AuhPlanillaHoja
                                barrios={barrios}
                                tipo={tipoAuh}
                                datos={datosAuh}
                                firmas={firmas}
                                errores={errores}
                                set={(campo, valor) =>
                                    setDatosAuh((actual) => ({
                                        ...actual,
                                        [campo]: valor,
                                    }))
                                }
                                setResponsable={(
                                    campo: keyof Responsable,
                                    valor,
                                ) =>
                                    setDatosAuh((actual) => ({
                                        ...actual,
                                        responsable: {
                                            ...actual.responsable,
                                            [campo]: valor,
                                        },
                                    }))
                                }
                                setMiembro={(index, campo, valor) =>
                                    setDatosAuh((actual) => ({
                                        ...actual,
                                        grupo_familiar:
                                            actual.grupo_familiar.map(
                                                (miembro, i) =>
                                                    i === index
                                                        ? {
                                                              ...miembro,
                                                              [campo]: valor,
                                                          }
                                                        : miembro,
                                            ),
                                    }))
                                }
                                agregarMiembro={() =>
                                    setDatosAuh((actual) => ({
                                        ...actual,
                                        grupo_familiar: [
                                            ...actual.grupo_familiar,
                                            { ...miembroVacio },
                                        ],
                                    }))
                                }
                                quitarMiembro={(index) =>
                                    setDatosAuh((actual) => ({
                                        ...actual,
                                        grupo_familiar:
                                            actual.grupo_familiar.filter(
                                                (_, i) => i !== index,
                                            ),
                                    }))
                                }
                                setFirma={setFirma}
                            />
                        ) : (
                            <ActaHoja
                                barrios={barrios}
                                escuelas={escuelas}
                                tipo={tipo as TipoActa}
                                datos={datosActa}
                                legajoNumero={legajo?.numero_legajo ?? null}
                                firmas={firmas}
                                errores={errores}
                                set={(campo, valor) =>
                                    setDatosActa((actual) => ({
                                        ...actual,
                                        [campo]: valor,
                                    }))
                                }
                                setFirma={setFirma}
                            />
                        )}
                    </div>

                    <footer className="sticky bottom-0 -mx-4 border-t border-border bg-background/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border">
                        {mensajesError.length > 0 && (
                            <ul className="mb-3 list-disc space-y-0.5 pl-5 text-sm text-destructive">
                                {mensajesError.map((mensaje) => (
                                    <li key={mensaje}>{mensaje}</li>
                                ))}
                            </ul>
                        )}
                        <div className="flex flex-wrap items-center justify-end gap-2">
                            {Object.keys(firmantes).length > 0 && (
                                <p className="mr-auto text-xs text-muted-foreground">
                                    Las firmas son opcionales: si se firma en
                                    papel, descargá el borrador.
                                </p>
                            )}
                            <Button asChild variant="ghost">
                                <Link href={actasRoutes.index.url()}>
                                    Cancelar
                                </Link>
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                disabled={guardando || descargando}
                                onClick={() => void descargarBorrador()}
                            >
                                <FileDown className="size-4" />
                                {descargando
                                    ? 'Generando…'
                                    : 'Descargar borrador'}
                            </Button>
                            <Button
                                type="submit"
                                disabled={guardando || descargando}
                            >
                                <Save className="size-4" />
                                {guardando ? 'Guardando…' : 'Guardar acta'}
                            </Button>
                        </div>
                    </footer>
                </form>
            </main>
        </>
    );
}
