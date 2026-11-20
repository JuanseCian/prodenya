import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    BarChart3,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    Copy,
    FileDown,
    FileText,
    Link2,
    Lock,
    LockOpen,
    MapPin,
    MoreHorizontal,
    Pencil,
    Plus,
    Send,
    Unlink,
    UserRound,
    UsersRound,
    XCircle,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';

import AuhModule from '@/components/auh-module';
import type { AuhResumen } from '@/components/auh-module';
import CoberturaSaludModule from '@/components/cobertura-salud-module';
import type { CoberturaResumen } from '@/components/cobertura-salud-module';
import CudModule from '@/components/cud-module';
import type { CudResumen } from '@/components/cud-module';
import {
    AvisoEstado,
    Dato,
    FONT_BODY,
    FONT_HEADING,
    NivelBadge,
} from '@/components/detalle';
import MedidasAbrigoModule from '@/components/medidas-abrigo-module';
import type { MedidaAbrigoResumen } from '@/components/medidas-abrigo-module';
import ResumenErrores from '@/components/resumen-errores';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import derivacionesRoutes from '@/routes/derivaciones';
import legajosRoutes from '@/routes/legajos';

interface LegajoDetalle {
    id: number;
    numero_legajo: string;
    nivel_riesgo: string | null;
    estado: string | null;
    origen_derivacion: string | null;
    observaciones: string | null;
    fecha_apertura: string | null;
    profesional: string | null;
    problematicas: string[];
    persona: {
        id: number;
        nombre_completo: string;
        dni: string;
        edad: number | null;
    } | null;
}

interface ProblematicaOpcion {
    id: number;
    nombre: string;
}

interface Intervencion {
    id: number;
    fecha: string | null;
    hora: string | null;
    tipo: string | null;
    detalle: string;
    profesional: string | null;
    creado_por: string | null;
    firma_nombre: string | null;
    firma_aclaracion: string | null;
    tiene_firma_responsable: boolean;
    firma_entrevistado_nombre: string | null;
    firma_entrevistado_aclaracion: string | null;
    tiene_firma_entrevistado: boolean;
}

interface Vinculo {
    id: number;
    tipo: string;
    grupo: string | null;
    persona: {
        id: number;
        nombre: string;
        numero_legajo: string | null;
    };
}

interface PersonaDisponible {
    id: number;
    nombre: string;
    numero_legajo: string | null;
}

interface ResponsableDisponible {
    id: number;
    name: string;
    profesion: string | null;
    rol: string | null;
}

interface Responsable {
    id: number;
    usuario_id: number;
    nombre: string | null;
    profesion: string | null;
    rol: string | null;
    es_principal: boolean;
}

interface Derivacion {
    id: number;
    estado: string;
    modo: string;
    motivo: string;
    detalle: string | null;
    fecha_limite: string | null;
    destinatario_id: number;
    destinatario: string | null;
    solicitante: string | null;
    motivo_rechazo: string | null;
}

interface Secciones {
    persona: boolean;
    seguimiento: boolean;
    situacion: boolean;
    vinculos: boolean;
    intervenciones: boolean;
    derivaciones: boolean;
    medidas_abrigo: boolean;
    auh: boolean;
    estadisticas: boolean;
}

interface Props {
    legajo: LegajoDetalle;
    secciones: Secciones;
    cud?: CudResumen | null;
    coberturaSalud?: CoberturaResumen | null;
    coberturaSaludHistorial?: CoberturaResumen[];
    medidasAbrigo?: MedidaAbrigoResumen[];
    auh?: AuhResumen | null;
    puedeGestionarAuh?: boolean;
    status?: string | null;
    intervenciones?: Intervencion[];
    puedeGestionarIntervenciones?: boolean;
    puedeGestionarDerivaciones?: boolean;
    puedeEditarLegajo?: boolean;
    puedeCambiarEstado?: boolean;
    puedeExportarWord?: boolean;
    puedeGestionarCud: boolean;
    puedeGestionarCoberturaSalud?: boolean;
    puedeGestionarMedidasAbrigo?: boolean;
    usuarioActualId: number;
    responsablesDisponibles?: ResponsableDisponible[];
    responsables?: Responsable[];
    derivaciones?: Derivacion[];
    puedeVerVinculos?: boolean;
    vinculos?: Vinculo[];
    personasDisponibles?: PersonaDisponible[];
    problematicasDisponibles?: ProblematicaOpcion[];
    problematicasSeleccionadas?: number[];
}

const tituloCard = 'text-base font-semibold text-[#2C4A5E]';
const subtituloGrupo =
    'text-xs font-bold tracking-[0.1em] text-[#2C4A5E] uppercase';

function GrupoTitulo({ id, children }: { id: string; children: ReactNode }) {
    return (
        <h2
            id={id}
            className="mb-3 text-[0.7rem] font-bold tracking-[0.14em] text-[#697586] uppercase dark:text-muted-foreground"
        >
            {children}
        </h2>
    );
}
const MAX_RESPONSABLES_ACTIVOS = 3;
const actionPrimaryClass =
    'inline-flex h-10 items-center gap-2 rounded-xl bg-[#CDE8D6] px-4 text-sm font-bold text-[#1E5143] transition duration-200 hover:-translate-y-0.5 hover:bg-white focus-visible:ring-2 focus-visible:ring-[#CDE8D6] focus-visible:ring-offset-2 focus-visible:ring-offset-[#173247]';
const actionSecondaryClass =
    'inline-flex h-10 items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-[#CDE8D6] focus-visible:ring-offset-2 focus-visible:ring-offset-[#173247]';

export default function LegajoShow({
    legajo,
    secciones,
    cud = null,
    coberturaSalud = null,
    coberturaSaludHistorial = [],
    medidasAbrigo = [],
    auh = null,
    puedeGestionarAuh = false,
    status,
    intervenciones = [],
    puedeGestionarIntervenciones = false,
    puedeGestionarDerivaciones = false,
    puedeEditarLegajo = false,
    puedeCambiarEstado = false,
    puedeExportarWord = false,
    puedeGestionarCud,
    puedeGestionarCoberturaSalud = false,
    puedeGestionarMedidasAbrigo = false,
    usuarioActualId,
    responsablesDisponibles = [],
    responsables = [],
    derivaciones = [],
    puedeVerVinculos = false,
    vinculos = [],
    personasDisponibles = [],
    problematicasDisponibles = [],
    problematicasSeleccionadas = [],
}: Props) {
    const errors = (usePage().props.errors ?? {}) as Record<string, string>;
    const cerrado = legajo.estado === 'Cerrado';
    const alcanzoMaximoResponsables =
        responsables.length >= MAX_RESPONSABLES_ACTIVOS;
    const [derivacionesOpen, setDerivacionesOpen] = useState(false);
    const [filtroTipoIntervencion, setFiltroTipoIntervencion] =
        useState('todas');
    const tiposIntervencionDisponibles = useMemo(
        () =>
            Array.from(
                new Set(
                    intervenciones
                        .map((intervencion) => intervencion.tipo)
                        .filter((tipo): tipo is string => Boolean(tipo)),
                ),
            ).sort((a, b) => a.localeCompare(b)),
        [intervenciones],
    );
    const intervencionesFiltradas = useMemo(
        () =>
            filtroTipoIntervencion === 'todas'
                ? intervenciones
                : intervenciones.filter(
                      (intervencion) =>
                          intervencion.tipo === filtroTipoIntervencion,
                  ),
        [intervenciones, filtroTipoIntervencion],
    );
    const vinculoForm = useForm({
        persona_relacionada_id: '',
        tipo_vinculo: 'hermano/a',
    });
    const derivacionForm = useForm({
        destinatario_id: '',
        modo: 'adicional',
        motivo: '',
        detalle: '',
        fecha_limite: '',
    });
    const legajoEditForm = useForm({
        nivel_riesgo: legajo.nivel_riesgo ?? '',
        origen_derivacion: legajo.origen_derivacion ?? '',
        observaciones: legajo.observaciones ?? '',
        problematicas: problematicasSeleccionadas,
    });
    const [editandoLegajo, setEditandoLegajo] = useState(false);
    const resumenErrors = {
        ...errors,
        ...legajoEditForm.errors,
    } as Record<string, string>;

    useEffect(() => {
        if (
            alcanzoMaximoResponsables &&
            derivacionForm.data.modo === 'adicional'
        ) {
            derivacionForm.setData('modo', 'reemplazo');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [alcanzoMaximoResponsables]);

    const cambiarEstado = () => {
        const accion = cerrado ? 'reabrir' : 'cerrar';

        if (
            window.confirm(
                `¿Querés ${accion} el legajo ${legajo.numero_legajo}?`,
            )
        ) {
            router.patch(
                `/legajos/${legajo.id}/${accion}`,
                {},
                { preserveScroll: true },
            );
        }
    };

    const agregarVinculo = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        vinculoForm.post(`/legajos/${legajo.id}/vinculos`, {
            preserveScroll: true,
            onSuccess: () => vinculoForm.reset(),
        });
    };

    const quitarVinculo = (id: number) => {
        if (!window.confirm('¿Querés desvincular estas personas?')) {
            return;
        }

        router.delete(`/legajos/${legajo.id}/vinculos/${id}`, {
            preserveScroll: true,
        });
    };

    const crearDerivacion = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        derivacionForm.post(legajosRoutes.derivaciones.store.url(legajo.id), {
            preserveScroll: true,
            onSuccess: () => derivacionForm.reset(),
        });
    };

    const actualizarDerivacion = (
        id: number,
        accion: 'aceptar' | 'rechazar' | 'cancelar',
    ) => {
        const motivoRechazo =
            accion === 'rechazar'
                ? window.prompt('Indicá el motivo del rechazo:')
                : null;

        if (accion === 'rechazar' && !motivoRechazo?.trim()) {
            return;
        }

        router.patch(
            derivacionesRoutes[accion].url(id),
            accion === 'rechazar' ? { motivo_rechazo: motivoRechazo } : {},
            { preserveScroll: true },
        );
    };

    const iniciarEdicionLegajo = () => {
        legajoEditForm.setData('nivel_riesgo', legajo.nivel_riesgo ?? '');
        legajoEditForm.setData(
            'origen_derivacion',
            legajo.origen_derivacion ?? '',
        );
        legajoEditForm.setData('observaciones', legajo.observaciones ?? '');
        legajoEditForm.setData('problematicas', problematicasSeleccionadas);
        legajoEditForm.clearErrors();
        setEditandoLegajo(true);
    };

    const cancelarEdicionLegajo = () => {
        legajoEditForm.clearErrors();
        setEditandoLegajo(false);
    };

    const guardarLegajo = () => {
        legajoEditForm.put(legajosRoutes.update.url(legajo.id), {
            preserveScroll: true,
            onSuccess: () => setEditandoLegajo(false),
        });
    };

    const [numeroCopiado, setNumeroCopiado] = useState(false);
    const copiarNumero = () => {
        void navigator.clipboard
            ?.writeText(legajo.numero_legajo)
            .then(() => {
                setNumeroCopiado(true);
                window.setTimeout(() => setNumeroCopiado(false), 1800);
            })
            .catch(() => setNumeroCopiado(false));
    };

    const hayAccionesSecundarias =
        puedeExportarWord || secciones.estadisticas || puedeCambiarEstado;

    const datosClave: Array<{ etiqueta: string; valor: ReactNode }> = [
        ...(secciones.persona
            ? [
                  {
                      etiqueta: 'Documento',
                      valor: (
                          <span className="font-mono">
                              DNI {legajo.persona?.dni ?? '—'}
                          </span>
                      ),
                  },
                  {
                      etiqueta: 'Edad',
                      valor:
                          legajo.persona?.edad !== null &&
                          legajo.persona?.edad !== undefined
                              ? `${legajo.persona.edad} años`
                              : 'Sin informar',
                  },
              ]
            : []),
        ...(secciones.seguimiento
            ? [
                  {
                      etiqueta: 'Apertura',
                      valor: (
                          <>
                              <CalendarDays
                                  size={14}
                                  className="text-[#9BD2B3]"
                              />
                              {legajo.fecha_apertura ?? 'Sin fecha'}
                          </>
                      ),
                  },
                  {
                      etiqueta: 'Profesional',
                      valor: legajo.profesional ?? 'Sin asignar',
                  },
              ]
            : []),
        ...(secciones.intervenciones
            ? [
                  {
                      etiqueta: 'Intervenciones',
                      valor: `${intervenciones.length} registrada${intervenciones.length === 1 ? '' : 's'}`,
                  },
              ]
            : []),
    ];

    const seccionesNavegables = [
        {
            id: 'resumen',
            label: 'Resumen',
            visible:
                secciones.persona ||
                secciones.seguimiento ||
                secciones.situacion,
        },
        {
            id: 'salud',
            label: 'Salud y documentación',
            visible: secciones.persona,
        },
        {
            id: 'tramites',
            label: 'Medidas y AUH',
            visible:
                secciones.medidas_abrigo || (secciones.auh && auh !== null),
        },
        { id: 'vinculos', label: 'Red familiar', visible: puedeVerVinculos },
        {
            id: 'intervenciones',
            label: 'Intervenciones',
            visible: secciones.intervenciones,
        },
    ].filter((seccion) => seccion.visible);

    return (
        <>
            <Head title={`Legajo ${legajo.numero_legajo}`} />

            <main
                className="admin-page-shell legajo-detail-page min-h-screen px-4 py-6 sm:px-6 sm:py-8 lg:px-8"
                style={{ fontFamily: FONT_BODY }}
            >
                <section className="relative mb-6 overflow-hidden rounded-3xl bg-[#173247] text-white shadow-[0_18px_45px_rgba(23,50,71,0.16)]">
                    <div className="pointer-events-none absolute -top-32 -right-24 size-80 rounded-full border border-white/10" />
                    <div className="pointer-events-none absolute -bottom-44 -left-24 size-80 rounded-full bg-[#6B8F71]/15 blur-2xl" />
                    <div className="relative p-5 sm:p-7">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Link
                                href="/legajos"
                                className="inline-flex items-center gap-1.5 text-sm font-semibold text-white/65 transition hover:text-[#CDE8D6]"
                            >
                                <ArrowLeft size={15} />
                                Volver al listado
                            </Link>
                            {editandoLegajo && (
                                <span className="inline-flex items-center gap-2 rounded-full bg-[#F6D58E]/15 px-3 py-1 text-xs font-bold text-[#F6D58E] ring-1 ring-[#F6D58E]/30">
                                    <span className="size-2 animate-pulse rounded-full bg-[#F6D58E]" />
                                    Editando legajo
                                </span>
                            )}
                        </div>

                        <div className="mt-6 flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div className="grid size-14 shrink-0 place-items-center rounded-2xl bg-[#CDE8D6] text-[#1E5143] shadow-[0_8px_20px_rgba(0,0,0,0.12)]">
                                    <FileText size={25} strokeWidth={1.7} />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-sm font-medium text-[#9BD2B3]">
                                        Legajo de protección
                                    </p>
                                    <h1
                                        className="mt-1 text-2xl leading-tight font-semibold tracking-tight break-words sm:text-3xl"
                                        style={{ fontFamily: FONT_HEADING }}
                                    >
                                        {secciones.persona
                                            ? (legajo.persona
                                                  ?.nombre_completo ??
                                              'Persona sin asociar')
                                            : `Legajo ${legajo.numero_legajo}`}
                                    </h1>
                                    <div className="mt-3 flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            onClick={copiarNumero}
                                            className="inline-flex h-7 items-center gap-1.5 rounded-lg bg-white/10 px-2.5 font-mono text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15"
                                            title="Copiar número de legajo"
                                        >
                                            N° {legajo.numero_legajo}
                                            {numeroCopiado ? (
                                                <Check
                                                    size={13}
                                                    className="text-[#9BD2B3]"
                                                />
                                            ) : (
                                                <Copy
                                                    size={13}
                                                    className="text-white/55"
                                                />
                                            )}
                                            <span
                                                className="sr-only"
                                                aria-live="polite"
                                            >
                                                {numeroCopiado
                                                    ? 'Número copiado'
                                                    : ''}
                                            </span>
                                        </button>
                                        {editandoLegajo ? (
                                            <label className="grid gap-1 text-[0.65rem] font-bold tracking-[0.08em] text-white/60 uppercase">
                                                Nivel de riesgo
                                                <select
                                                    value={
                                                        legajoEditForm.data
                                                            .nivel_riesgo
                                                    }
                                                    onChange={(event) =>
                                                        legajoEditForm.setData(
                                                            'nivel_riesgo',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="h-9 rounded-lg border border-white/20 bg-white/10 px-2 text-sm font-semibold tracking-normal text-white normal-case outline-none focus:border-[#CDE8D6] focus:ring-2 focus:ring-[#CDE8D6]/30"
                                                >
                                                    <option
                                                        value=""
                                                        className="text-[#2B2F36]"
                                                    >
                                                        Seleccionar
                                                    </option>
                                                    <option
                                                        value="Bajo"
                                                        className="text-[#2B2F36]"
                                                    >
                                                        Bajo
                                                    </option>
                                                    <option
                                                        value="Medio"
                                                        className="text-[#2B2F36]"
                                                    >
                                                        Medio
                                                    </option>
                                                    <option
                                                        value="Alto"
                                                        className="text-[#2B2F36]"
                                                    >
                                                        Alto
                                                    </option>
                                                    <option
                                                        value="Critico"
                                                        className="text-[#2B2F36]"
                                                    >
                                                        Crítico
                                                    </option>
                                                </select>
                                            </label>
                                        ) : (
                                            legajo.nivel_riesgo && (
                                                <NivelBadge
                                                    nivel={legajo.nivel_riesgo}
                                                />
                                            )
                                        )}
                                        {legajo.estado && (
                                            <Badge className="gap-1.5 border border-white/15 bg-white/10 text-white">
                                                <span
                                                    className={`size-1.5 rounded-full ${cerrado ? 'bg-white/50' : 'bg-[#9BD2B3]'}`}
                                                />
                                                {legajo.estado}
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-wrap items-center gap-2 lg:justify-end">
                                {editandoLegajo ? (
                                    <>
                                        <button
                                            type="button"
                                            onClick={cancelarEdicionLegajo}
                                            className={actionSecondaryClass}
                                        >
                                            <XCircle size={15} />
                                            Cancelar
                                        </button>
                                        <button
                                            type="button"
                                            onClick={guardarLegajo}
                                            disabled={legajoEditForm.processing}
                                            className={`${actionPrimaryClass} disabled:cursor-not-allowed disabled:opacity-60`}
                                        >
                                            <CheckCircle2 size={15} />
                                            {legajoEditForm.processing
                                                ? 'Guardando...'
                                                : 'Guardar cambios'}
                                        </button>
                                    </>
                                ) : (
                                    <>
                                        {puedeEditarLegajo && (
                                            <button
                                                type="button"
                                                onClick={iniciarEdicionLegajo}
                                                className={actionPrimaryClass}
                                            >
                                                <Pencil size={15} />
                                                Editar legajo
                                            </button>
                                        )}
                                        {secciones.derivaciones && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setDerivacionesOpen(true)
                                                }
                                                className={actionSecondaryClass}
                                            >
                                                <Send size={15} />
                                                Derivar
                                                {responsables.length > 0 && (
                                                    <span className="rounded-full bg-white/15 px-1.5 text-xs">
                                                        {responsables.length}
                                                    </span>
                                                )}
                                            </button>
                                        )}
                                        {hayAccionesSecundarias && (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <button
                                                        type="button"
                                                        className={
                                                            actionSecondaryClass
                                                        }
                                                    >
                                                        <MoreHorizontal
                                                            size={15}
                                                        />
                                                        Más
                                                    </button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent
                                                    align="end"
                                                    className="w-60"
                                                >
                                                    {puedeExportarWord && (
                                                        <DropdownMenuItem
                                                            asChild
                                                        >
                                                            <Link
                                                                href={legajosRoutes.previsualizarWord.url(
                                                                    legajo.id,
                                                                )}
                                                                className="cursor-pointer"
                                                            >
                                                                <FileDown />
                                                                Previsualizar
                                                                documento
                                                            </Link>
                                                        </DropdownMenuItem>
                                                    )}
                                                    {secciones.estadisticas && (
                                                        <DropdownMenuItem
                                                            asChild
                                                        >
                                                            <Link
                                                                href={legajosRoutes.estadisticas.url(
                                                                    legajo.id,
                                                                )}
                                                                className="cursor-pointer"
                                                            >
                                                                <BarChart3 />
                                                                Ver estadísticas
                                                            </Link>
                                                        </DropdownMenuItem>
                                                    )}
                                                    {puedeCambiarEstado && (
                                                        <>
                                                            {(puedeExportarWord ||
                                                                secciones.estadisticas) && (
                                                                <DropdownMenuSeparator />
                                                            )}
                                                            <DropdownMenuItem
                                                                variant={
                                                                    cerrado
                                                                        ? 'default'
                                                                        : 'destructive'
                                                                }
                                                                onSelect={
                                                                    cambiarEstado
                                                                }
                                                                className="cursor-pointer"
                                                            >
                                                                {cerrado ? (
                                                                    <LockOpen />
                                                                ) : (
                                                                    <Lock />
                                                                )}
                                                                {cerrado
                                                                    ? 'Reabrir legajo'
                                                                    : 'Cerrar legajo'}
                                                            </DropdownMenuItem>
                                                        </>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
                                    </>
                                )}
                            </div>
                        </div>

                        {datosClave.length > 0 && (
                            <dl className="mt-7 flex flex-wrap gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10">
                                {datosClave.map((dato) => (
                                    <div
                                        key={dato.etiqueta}
                                        className="min-w-[150px] flex-1 bg-[#173247]/80 px-4 py-3"
                                    >
                                        <dt className="text-[0.65rem] font-bold tracking-[0.12em] text-white/45 uppercase">
                                            {dato.etiqueta}
                                        </dt>
                                        <dd className="mt-1 flex items-center gap-1.5 truncate text-sm font-semibold text-white">
                                            {dato.valor}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        )}
                    </div>
                </section>

                <AvisoEstado mensaje={status} />
                <div className="mb-6">
                    <ResumenErrores errors={resumenErrors} />
                </div>

                <nav
                    aria-label="Secciones del legajo"
                    className="sticky top-0 z-20 -mx-4 mb-6 border-y border-[#DCE3DF] bg-[#F4F6F5]/90 px-4 py-2 backdrop-blur sm:mx-0 sm:rounded-xl sm:border dark:border-border dark:bg-background/90"
                >
                    <ul className="flex gap-1.5 overflow-x-auto">
                        {seccionesNavegables.map((seccion) => (
                            <li key={seccion.id} className="shrink-0">
                                <a
                                    href={`#${seccion.id}`}
                                    className="inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold text-[#2C4A5E] transition hover:bg-white hover:text-[#173247] dark:text-foreground dark:hover:bg-white/10"
                                >
                                    {seccion.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>

                <div className="space-y-8">
                    {(secciones.persona ||
                        secciones.seguimiento ||
                        secciones.situacion) && (
                        <section
                            id="resumen"
                            aria-labelledby="titulo-resumen"
                            className="scroll-mt-20"
                        >
                            <GrupoTitulo id="titulo-resumen">
                                Resumen del caso
                            </GrupoTitulo>
                            <div
                                className={`grid gap-5 ${
                                    (secciones.persona ||
                                        secciones.seguimiento) &&
                                    secciones.situacion
                                        ? 'lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]'
                                        : ''
                                }`}
                            >
                                {(secciones.persona ||
                                    secciones.seguimiento) && (
                                    <Card className="rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                            <div className="flex items-center gap-2">
                                                <UserRound
                                                    size={17}
                                                    className="text-[#6B8F71]"
                                                />
                                                <CardTitle
                                                    className={tituloCard}
                                                    style={{
                                                        fontFamily:
                                                            FONT_HEADING,
                                                    }}
                                                >
                                                    Datos del caso
                                                </CardTitle>
                                            </div>
                                            <p className="mt-1 text-sm text-[#2B2F36]/60">
                                                Persona titular y seguimiento
                                                del legajo.
                                            </p>
                                        </CardHeader>
                                        <CardContent className="space-y-5 px-5 py-5">
                                            {secciones.persona && (
                                                <div>
                                                    <p
                                                        className={
                                                            subtituloGrupo
                                                        }
                                                    >
                                                        Persona titular
                                                    </p>
                                                    {legajo.persona ? (
                                                        <dl className="mt-3 grid gap-4 sm:grid-cols-2">
                                                            <div className="sm:col-span-2">
                                                                <Dato etiqueta="Nombre">
                                                                    <Link
                                                                        href={`/personas/${legajo.persona.id}`}
                                                                        className="font-semibold text-[#2C4A5E] underline"
                                                                    >
                                                                        {
                                                                            legajo
                                                                                .persona
                                                                                .nombre_completo
                                                                        }
                                                                    </Link>
                                                                </Dato>
                                                            </div>
                                                            <Dato etiqueta="DNI">
                                                                {
                                                                    legajo
                                                                        .persona
                                                                        .dni
                                                                }
                                                            </Dato>
                                                            <Dato etiqueta="Edad">
                                                                {legajo.persona
                                                                    .edad !==
                                                                null
                                                                    ? `${legajo.persona.edad} años`
                                                                    : null}
                                                            </Dato>
                                                        </dl>
                                                    ) : (
                                                        <p className="mt-3 text-sm text-[#2B2F36]/60">
                                                            Sin persona
                                                            asociada.
                                                        </p>
                                                    )}
                                                </div>
                                            )}
                                            {secciones.seguimiento && (
                                                <div
                                                    className={
                                                        secciones.persona
                                                            ? 'border-t border-[#E4E8E5] pt-5'
                                                            : ''
                                                    }
                                                >
                                                    <p
                                                        className={
                                                            subtituloGrupo
                                                        }
                                                    >
                                                        Seguimiento
                                                    </p>
                                                    <dl className="mt-3 grid gap-4 sm:grid-cols-2">
                                                        <Dato etiqueta="Fecha de apertura">
                                                            {
                                                                legajo.fecha_apertura
                                                            }
                                                        </Dato>
                                                        <Dato etiqueta="Profesional asignado">
                                                            {legajo.profesional}
                                                        </Dato>
                                                        <div className="sm:col-span-2">
                                                            <Dato etiqueta="Origen de la derivación">
                                                                {editandoLegajo ? (
                                                                    <div className="grid gap-1.5">
                                                                        <Input
                                                                            value={
                                                                                legajoEditForm
                                                                                    .data
                                                                                    .origen_derivacion
                                                                            }
                                                                            onChange={(
                                                                                event,
                                                                            ) =>
                                                                                legajoEditForm.setData(
                                                                                    'origen_derivacion',
                                                                                    event
                                                                                        .target
                                                                                        .value,
                                                                                )
                                                                            }
                                                                            className="h-10 border-[#D5D1C6] bg-white text-sm font-normal text-[#2B2F36] shadow-none"
                                                                        />
                                                                        {legajoEditForm
                                                                            .errors
                                                                            .origen_derivacion && (
                                                                            <span className="text-xs font-medium text-[#A84635]">
                                                                                {
                                                                                    legajoEditForm
                                                                                        .errors
                                                                                        .origen_derivacion
                                                                                }
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                ) : (
                                                                    legajo.origen_derivacion
                                                                )}
                                                            </Dato>
                                                        </div>
                                                    </dl>
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>
                                )}

                                {secciones.situacion && (
                                    <Card className="rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                                        <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                            <div className="flex items-center gap-2">
                                                <MapPin
                                                    size={17}
                                                    className="text-[#6B8F71]"
                                                />
                                                <CardTitle
                                                    className={tituloCard}
                                                    style={{
                                                        fontFamily:
                                                            FONT_HEADING,
                                                    }}
                                                >
                                                    Situación del caso
                                                </CardTitle>
                                            </div>
                                            <p className="mt-1 text-sm text-[#2B2F36]/60">
                                                Contexto, problemáticas y notas
                                                relevantes para la intervención.
                                            </p>
                                        </CardHeader>
                                        <CardContent className="px-5 py-5">
                                            <dl className="grid gap-4 sm:grid-cols-2">
                                                <Dato etiqueta="Problemáticas">
                                                    {editandoLegajo ? (
                                                        <div className="grid gap-2 rounded-lg border border-[#D5D1C6] bg-white p-3 sm:col-span-2">
                                                            {problematicasDisponibles.map(
                                                                (
                                                                    problematica,
                                                                ) => (
                                                                    <label
                                                                        key={
                                                                            problematica.id
                                                                        }
                                                                        className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-[#2B2F36] transition hover:bg-[#F4F6F5]"
                                                                    >
                                                                        <input
                                                                            type="checkbox"
                                                                            checked={legajoEditForm.data.problematicas.includes(
                                                                                problematica.id,
                                                                            )}
                                                                            onChange={() =>
                                                                                legajoEditForm.setData(
                                                                                    'problematicas',
                                                                                    legajoEditForm.data.problematicas.includes(
                                                                                        problematica.id,
                                                                                    )
                                                                                        ? legajoEditForm.data.problematicas.filter(
                                                                                              (
                                                                                                  actual,
                                                                                              ) =>
                                                                                                  actual !==
                                                                                                  problematica.id,
                                                                                          )
                                                                                        : [
                                                                                              ...legajoEditForm
                                                                                                  .data
                                                                                                  .problematicas,
                                                                                              problematica.id,
                                                                                          ],
                                                                                )
                                                                            }
                                                                            className="size-4 rounded border-[#B9C5BD] text-[#2C4A5E] focus:ring-[#2C4A5E]/30"
                                                                        />
                                                                        {
                                                                            problematica.nombre
                                                                        }
                                                                    </label>
                                                                ),
                                                            )}
                                                            {legajoEditForm
                                                                .errors
                                                                .problematicas && (
                                                                <span className="text-xs font-medium text-[#A84635]">
                                                                    {
                                                                        legajoEditForm
                                                                            .errors
                                                                            .problematicas
                                                                    }
                                                                </span>
                                                            )}
                                                        </div>
                                                    ) : (
                                                        legajo.problematicas
                                                            .length > 0 && (
                                                            <span className="flex flex-wrap gap-1.5">
                                                                {legajo.problematicas.map(
                                                                    (
                                                                        nombre,
                                                                    ) => (
                                                                        <Badge
                                                                            key={
                                                                                nombre
                                                                            }
                                                                            className="border-0 bg-[#EDF3EF] text-[#2C4A5E]"
                                                                        >
                                                                            {
                                                                                nombre
                                                                            }
                                                                        </Badge>
                                                                    ),
                                                                )}
                                                            </span>
                                                        )
                                                    )}
                                                </Dato>
                                                <Dato etiqueta="Observaciones">
                                                    {editandoLegajo ? (
                                                        <div className="grid gap-1.5">
                                                            <textarea
                                                                value={
                                                                    legajoEditForm
                                                                        .data
                                                                        .observaciones
                                                                }
                                                                onChange={(
                                                                    event,
                                                                ) =>
                                                                    legajoEditForm.setData(
                                                                        'observaciones',
                                                                        event
                                                                            .target
                                                                            .value,
                                                                    )
                                                                }
                                                                rows={4}
                                                                className="w-full resize-y rounded-lg border border-[#D5D1C6] bg-white px-3 py-2 text-sm font-normal text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                                            />
                                                            {legajoEditForm
                                                                .errors
                                                                .observaciones && (
                                                                <span className="text-xs font-medium text-[#A84635]">
                                                                    {
                                                                        legajoEditForm
                                                                            .errors
                                                                            .observaciones
                                                                    }
                                                                </span>
                                                            )}
                                                        </div>
                                                    ) : (
                                                        legajo.observaciones
                                                    )}
                                                </Dato>
                                            </dl>
                                            {editandoLegajo && (
                                                <div className="mt-5 flex flex-col gap-2 border-t border-[#E4E8E5] pt-5 sm:flex-row sm:justify-end">
                                                    <button
                                                        type="button"
                                                        onClick={
                                                            cancelarEdicionLegajo
                                                        }
                                                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#D5D1C6] px-4 text-sm font-semibold text-[#2B2F36] transition hover:bg-[#F4F6F5]"
                                                    >
                                                        Cancelar
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={guardarLegajo}
                                                        disabled={
                                                            legajoEditForm.processing
                                                        }
                                                        className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B] disabled:cursor-not-allowed disabled:opacity-60"
                                                    >
                                                        <CheckCircle2
                                                            size={15}
                                                        />
                                                        {legajoEditForm.processing
                                                            ? 'Guardando...'
                                                            : 'Guardar cambios'}
                                                    </button>
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>
                                )}
                            </div>
                        </section>
                    )}

                    {secciones.persona && (
                        <section
                            id="salud"
                            aria-labelledby="titulo-salud"
                            className="scroll-mt-20"
                        >
                            <GrupoTitulo id="titulo-salud">
                                Salud y documentación
                            </GrupoTitulo>
                            <div className="grid gap-5 xl:grid-cols-2">
                                <CudModule
                                    legajoId={legajo.id}
                                    cud={cud}
                                    puedeGestionar={puedeGestionarCud}
                                />
                                <CoberturaSaludModule
                                    legajoId={legajo.id}
                                    cobertura={coberturaSalud}
                                    historial={coberturaSaludHistorial}
                                    puedeGestionar={
                                        puedeGestionarCoberturaSalud
                                    }
                                />
                            </div>
                        </section>
                    )}

                    {(secciones.medidas_abrigo || (secciones.auh && auh)) && (
                        <section
                            id="tramites"
                            aria-labelledby="titulo-tramites"
                            className="scroll-mt-20"
                        >
                            <GrupoTitulo id="titulo-tramites">
                                Medidas y trámites
                            </GrupoTitulo>
                            <div className="space-y-4">
                                {secciones.medidas_abrigo && (
                                    <MedidasAbrigoModule
                                        legajoId={legajo.id}
                                        medidas={medidasAbrigo}
                                        puedeGestionar={
                                            puedeGestionarMedidasAbrigo
                                        }
                                    />
                                )}

                                {secciones.auh && auh && (
                                    <AuhModule
                                        auh={auh}
                                        legajoId={legajo.id}
                                        puedeGestionar={puedeGestionarAuh}
                                    />
                                )}
                            </div>
                        </section>
                    )}

                    {puedeVerVinculos && (
                        <section
                            id="vinculos"
                            aria-labelledby="titulo-vinculos"
                            className="scroll-mt-20"
                        >
                            <GrupoTitulo id="titulo-vinculos">
                                Red familiar
                            </GrupoTitulo>
                            <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                                <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                                    <div className="flex items-center gap-2">
                                        <UsersRound
                                            size={18}
                                            className="text-[#6B8F71]"
                                        />
                                        <CardTitle
                                            className={tituloCard}
                                            style={{ fontFamily: FONT_HEADING }}
                                        >
                                            Vínculos familiares
                                        </CardTitle>
                                    </div>
                                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                                        Relacioná este legajo con otros sin
                                        mezclar sus historiales.
                                    </p>
                                </CardHeader>
                                <CardContent className="space-y-5 px-5 py-5">
                                    {vinculos.length > 0 ? (
                                        <div className="grid gap-3 sm:grid-cols-2">
                                            {vinculos.map((vinculo) => (
                                                <div
                                                    key={vinculo.id}
                                                    className="flex items-center justify-between gap-3 rounded-lg border border-[#DCE3DF] bg-[#FBFCFB] p-3"
                                                >
                                                    <div className="min-w-0">
                                                        <div className="flex items-center gap-2 text-xs font-semibold text-[#6B8F71]">
                                                            <Link2 size={13} />
                                                            {vinculo.tipo}
                                                        </div>
                                                        <Link
                                                            href={`/legajos/${vinculo.persona.id}`}
                                                            className="mt-1 block truncate text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                        >
                                                            {
                                                                vinculo.persona
                                                                    .nombre
                                                            }
                                                        </Link>
                                                        <p className="mt-1 font-mono text-xs text-[#697586]">
                                                            {vinculo.persona
                                                                .numero_legajo ??
                                                                'Sin número de legajo'}
                                                        </p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            quitarVinculo(
                                                                vinculo.id,
                                                            )
                                                        }
                                                        className="shrink-0 rounded-md p-2 text-[#8A6A63] transition hover:bg-[#FFF1EF] hover:text-[#A8493C]"
                                                        aria-label={`Desvincular a ${vinculo.persona.nombre}`}
                                                    >
                                                        <Unlink size={16} />
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-5 text-sm text-[#697586]">
                                            Todavía no hay vínculos registrados.
                                        </p>
                                    )}

                                    {personasDisponibles.length > 0 && (
                                        <form
                                            onSubmit={agregarVinculo}
                                            className="grid gap-3 border-t border-[#E4E8E5] pt-5 sm:grid-cols-[minmax(0,1fr)_190px_auto] sm:items-end"
                                        >
                                            <div>
                                                <label
                                                    htmlFor="persona_relacionada_id"
                                                    className="text-xs font-semibold text-[#2C4A5E]"
                                                >
                                                    Legajo relacionado
                                                </label>
                                                <select
                                                    id="persona_relacionada_id"
                                                    value={
                                                        vinculoForm.data
                                                            .persona_relacionada_id
                                                    }
                                                    onChange={(event) =>
                                                        vinculoForm.setData(
                                                            'persona_relacionada_id',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="mt-2 h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                                    required
                                                >
                                                    <option value="">
                                                        Seleccionar persona...
                                                    </option>
                                                    {personasDisponibles.map(
                                                        (persona) => (
                                                            <option
                                                                key={persona.id}
                                                                value={
                                                                    persona.id
                                                                }
                                                            >
                                                                {persona.nombre}{' '}
                                                                ·{' '}
                                                                {
                                                                    persona.numero_legajo
                                                                }
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    htmlFor="tipo_vinculo"
                                                    className="text-xs font-semibold text-[#2C4A5E]"
                                                >
                                                    Tipo de vínculo
                                                </label>
                                                <select
                                                    id="tipo_vinculo"
                                                    value={
                                                        vinculoForm.data
                                                            .tipo_vinculo
                                                    }
                                                    onChange={(event) =>
                                                        vinculoForm.setData(
                                                            'tipo_vinculo',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="mt-2 h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                                >
                                                    <option>hermano/a</option>
                                                    <option>primo/a</option>
                                                    <option>tío/a</option>
                                                    <option>sobrino/a</option>
                                                    <option>madre</option>
                                                    <option>padre</option>
                                                    <option>hijo/a</option>
                                                    <option>abuelo/a</option>
                                                    <option>nieto/a</option>
                                                    <option>tutor/a</option>
                                                    <option>
                                                        referente afectivo
                                                    </option>
                                                    <option>pareja</option>
                                                    <option>
                                                        otro familiar
                                                    </option>
                                                </select>
                                            </div>
                                            <button
                                                type="submit"
                                                disabled={
                                                    vinculoForm.processing
                                                }
                                                className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B] disabled:cursor-not-allowed disabled:opacity-60"
                                            >
                                                <Plus size={15} />
                                                {vinculoForm.processing
                                                    ? 'Guardando...'
                                                    : 'Agregar'}
                                            </button>
                                        </form>
                                    )}
                                </CardContent>
                            </Card>
                        </section>
                    )}

                    {secciones.derivaciones && (
                        <Dialog
                            open={derivacionesOpen}
                            onOpenChange={setDerivacionesOpen}
                        >
                            <DialogContent className="max-h-[90vh] overflow-y-auto border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-2xl">
                                <DialogHeader>
                                    <div className="flex items-center gap-2">
                                        <Send
                                            size={18}
                                            className="text-[#6B8F71]"
                                        />
                                        <DialogTitle
                                            className={tituloCard}
                                            style={{ fontFamily: FONT_HEADING }}
                                        >
                                            Derivaciones y responsables
                                        </DialogTitle>
                                    </div>
                                    <DialogDescription className="mt-1 text-sm text-[#2B2F36]/60">
                                        Enviá el caso a otro profesional y
                                        conservá el historial de decisiones.
                                    </DialogDescription>
                                </DialogHeader>
                                <div className="space-y-5 px-1 py-2">
                                    {responsables.length > 0 && (
                                        <div className="overflow-x-auto rounded-xl border border-[#DCE3DF]">
                                            <table className="w-full min-w-[560px] text-left text-sm">
                                                <thead className="bg-[#F3F7F4] text-[0.68rem] font-bold tracking-[0.12em] text-[#6B8F71] uppercase">
                                                    <tr>
                                                        <th
                                                            scope="col"
                                                            className="px-4 py-3"
                                                        >
                                                            Usuario
                                                        </th>
                                                        <th
                                                            scope="col"
                                                            className="px-4 py-3"
                                                        >
                                                            Profesión
                                                        </th>
                                                        <th
                                                            scope="col"
                                                            className="px-4 py-3"
                                                        >
                                                            Rol
                                                        </th>
                                                        <th
                                                            scope="col"
                                                            className="px-4 py-3"
                                                        >
                                                            Tipo
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-[#EEF0ED] bg-white">
                                                    {responsables.map(
                                                        (responsable) => (
                                                            <tr
                                                                key={
                                                                    responsable.id
                                                                }
                                                            >
                                                                <td className="px-4 py-3 font-semibold text-[#2C4A5E]">
                                                                    {responsable.nombre ??
                                                                        'Usuario sin nombre'}
                                                                </td>
                                                                <td className="px-4 py-3 text-[#2B2F36]/75">
                                                                    {responsable.profesion ??
                                                                        'Sin profesión'}
                                                                </td>
                                                                <td className="px-4 py-3 text-[#2B2F36]/75">
                                                                    {responsable.rol ??
                                                                        'Sin rol'}
                                                                </td>
                                                                <td className="px-4 py-3">
                                                                    <Badge className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                                                        {responsable.es_principal
                                                                            ? 'Principal'
                                                                            : 'Adicional'}
                                                                    </Badge>
                                                                </td>
                                                            </tr>
                                                        ),
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}

                                    {derivaciones.length > 0 ? (
                                        <div className="space-y-3">
                                            {derivaciones.map((derivacion) => (
                                                <article
                                                    key={derivacion.id}
                                                    className="rounded-lg border border-[#DCE3DF] bg-[#FBFCFB] p-4"
                                                >
                                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                        <div>
                                                            <div className="flex flex-wrap items-center gap-2">
                                                                <Badge className="border-0 bg-[#F2F7FB] text-[#35627A]">
                                                                    {
                                                                        derivacion.estado
                                                                    }
                                                                </Badge>
                                                                <span className="text-xs text-[#697586]">
                                                                    {
                                                                        derivacion.modo
                                                                    }
                                                                </span>
                                                            </div>
                                                            <p className="mt-2 text-sm font-semibold text-[#173247]">
                                                                {
                                                                    derivacion.motivo
                                                                }
                                                            </p>
                                                            <p className="mt-1 text-xs text-[#697586]">
                                                                Para{' '}
                                                                {derivacion.destinatario ??
                                                                    'sin destinatario'}{' '}
                                                                · solicitó{' '}
                                                                {derivacion.solicitante ??
                                                                    'sin datos'}
                                                            </p>
                                                            {derivacion.detalle && (
                                                                <p className="mt-3 text-sm leading-6 whitespace-pre-line text-[#2B2F36]/75">
                                                                    {
                                                                        derivacion.detalle
                                                                    }
                                                                </p>
                                                            )}
                                                        </div>
                                                        <div className="flex shrink-0 gap-2">
                                                            {derivacion.estado ===
                                                                'pendiente' &&
                                                                derivacion.destinatario_id ===
                                                                    usuarioActualId && (
                                                                    <>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() =>
                                                                                actualizarDerivacion(
                                                                                    derivacion.id,
                                                                                    'aceptar',
                                                                                )
                                                                            }
                                                                            className="inline-flex items-center gap-1.5 rounded-md border border-[#C7DED0] bg-[#F2F9F4] px-3 py-2 text-xs font-semibold text-[#397461]"
                                                                        >
                                                                            <CheckCircle2
                                                                                size={
                                                                                    14
                                                                                }
                                                                            />{' '}
                                                                            Aceptar
                                                                        </button>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() =>
                                                                                actualizarDerivacion(
                                                                                    derivacion.id,
                                                                                    'rechazar',
                                                                                )
                                                                            }
                                                                            className="inline-flex items-center gap-1.5 rounded-md border border-[#E8D7DA] bg-[#FCF1F3] px-3 py-2 text-xs font-semibold text-[#9B5964]"
                                                                        >
                                                                            <XCircle
                                                                                size={
                                                                                    14
                                                                                }
                                                                            />{' '}
                                                                            Rechazar
                                                                        </button>
                                                                    </>
                                                                )}
                                                            {derivacion.estado ===
                                                                'pendiente' &&
                                                                puedeGestionarDerivaciones && (
                                                                    <button
                                                                        type="button"
                                                                        onClick={() =>
                                                                            actualizarDerivacion(
                                                                                derivacion.id,
                                                                                'cancelar',
                                                                            )
                                                                        }
                                                                        className="inline-flex items-center gap-1.5 rounded-md border border-[#E8D7DA] bg-[#FCF1F3] px-3 py-2 text-xs font-semibold text-[#9B5964]"
                                                                    >
                                                                        <XCircle
                                                                            size={
                                                                                14
                                                                            }
                                                                        />{' '}
                                                                        Cancelar
                                                                    </button>
                                                                )}
                                                        </div>
                                                    </div>
                                                    {derivacion.motivo_rechazo && (
                                                        <p className="mt-3 text-xs text-[#9B5964]">
                                                            Motivo del rechazo:{' '}
                                                            {
                                                                derivacion.motivo_rechazo
                                                            }
                                                        </p>
                                                    )}
                                                </article>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-5 text-sm text-[#697586]">
                                            Todavía no hay derivaciones
                                            registradas.
                                        </p>
                                    )}

                                    {puedeGestionarDerivaciones && (
                                        <form
                                            onSubmit={crearDerivacion}
                                            className="grid gap-3 border-t border-[#E4E8E5] pt-5"
                                        >
                                            <div className="grid gap-3 sm:grid-cols-2">
                                                <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">
                                                    Destinatario
                                                    <select
                                                        value={
                                                            derivacionForm.data
                                                                .destinatario_id
                                                        }
                                                        onChange={(event) =>
                                                            derivacionForm.setData(
                                                                'destinatario_id',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm font-normal text-[#2B2F36]"
                                                        required
                                                    >
                                                        <option value="">
                                                            Seleccionar
                                                            profesional...
                                                        </option>
                                                        {responsablesDisponibles
                                                            .filter(
                                                                (responsable) =>
                                                                    responsable.id !==
                                                                    usuarioActualId,
                                                            )
                                                            .map(
                                                                (
                                                                    responsable,
                                                                ) => (
                                                                    <option
                                                                        key={
                                                                            responsable.id
                                                                        }
                                                                        value={
                                                                            responsable.id
                                                                        }
                                                                    >
                                                                        {
                                                                            responsable.name
                                                                        }
                                                                        {responsable.profesion
                                                                            ? ` · ${responsable.profesion}`
                                                                            : ''}
                                                                    </option>
                                                                ),
                                                            )}
                                                    </select>
                                                </label>
                                                <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">
                                                    Modo
                                                    <select
                                                        value={
                                                            derivacionForm.data
                                                                .modo
                                                        }
                                                        onChange={(event) =>
                                                            derivacionForm.setData(
                                                                'modo',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm font-normal text-[#2B2F36]"
                                                    >
                                                        <option
                                                            value="adicional"
                                                            disabled={
                                                                alcanzoMaximoResponsables
                                                            }
                                                        >
                                                            Sumar responsable
                                                        </option>
                                                        <option value="reemplazo">
                                                            Reemplazar
                                                            responsables
                                                        </option>
                                                    </select>
                                                    {alcanzoMaximoResponsables && (
                                                        <span className="text-xs font-normal text-[#B3541E]">
                                                            Este legajo ya tiene{' '}
                                                            {
                                                                MAX_RESPONSABLES_ACTIVOS
                                                            }{' '}
                                                            responsables
                                                            activos, el máximo
                                                            permitido. Elegí
                                                            "Reemplazar
                                                            responsables" para
                                                            sumar a otra
                                                            persona.
                                                        </span>
                                                    )}
                                                </label>
                                            </div>
                                            <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px]">
                                                <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">
                                                    Motivo
                                                    <Input
                                                        value={
                                                            derivacionForm.data
                                                                .motivo
                                                        }
                                                        onChange={(event) =>
                                                            derivacionForm.setData(
                                                                'motivo',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        placeholder="Ej. Seguimiento especializado"
                                                        className="h-10 border-[#D5D1C6] bg-white text-sm font-normal"
                                                        required
                                                    />
                                                </label>
                                                <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">
                                                    Fecha límite
                                                    <Input
                                                        type="date"
                                                        value={
                                                            derivacionForm.data
                                                                .fecha_limite
                                                        }
                                                        onChange={(event) =>
                                                            derivacionForm.setData(
                                                                'fecha_limite',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="h-10 border-[#D5D1C6] bg-white text-sm font-normal"
                                                    />
                                                </label>
                                            </div>
                                            <label className="grid gap-2 text-xs font-semibold text-[#2C4A5E]">
                                                Detalle
                                                <textarea
                                                    value={
                                                        derivacionForm.data
                                                            .detalle
                                                    }
                                                    onChange={(event) =>
                                                        derivacionForm.setData(
                                                            'detalle',
                                                            event.target.value,
                                                        )
                                                    }
                                                    rows={3}
                                                    className="rounded-lg border border-[#D5D1C6] bg-white px-3 py-2 text-sm font-normal text-[#2B2F36] outline-none focus:border-[#2C4A5E]"
                                                    placeholder="Información que la otra persona necesita para tomar el caso."
                                                />
                                            </label>
                                            <button
                                                type="submit"
                                                disabled={
                                                    derivacionForm.processing
                                                }
                                                className="inline-flex h-10 w-fit items-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B] disabled:opacity-60"
                                            >
                                                <Send size={15} />{' '}
                                                {derivacionForm.processing
                                                    ? 'Enviando...'
                                                    : 'Enviar derivación'}
                                            </button>
                                        </form>
                                    )}
                                </div>
                            </DialogContent>
                        </Dialog>
                    )}
                </div>

                {secciones.intervenciones && (
                    <section
                        id="intervenciones"
                        aria-labelledby="titulo-intervenciones"
                        className="mt-8 scroll-mt-20"
                    >
                        <GrupoTitulo id="titulo-intervenciones">
                            Historial
                        </GrupoTitulo>
                        <Card className="rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardHeader className="flex flex-col gap-3 border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div>
                                    <CardTitle
                                        className={tituloCard}
                                        style={{ fontFamily: FONT_HEADING }}
                                    >
                                        Intervenciones
                                    </CardTitle>
                                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                                        Actuaciones y decisiones que forman
                                        parte del historial del legajo.
                                    </p>
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    {tiposIntervencionDisponibles.length >
                                        0 && (
                                        <label className="text-xs font-bold tracking-wide text-[#697586] uppercase">
                                            <span className="sr-only">
                                                Filtrar por tipo
                                            </span>
                                            <select
                                                value={filtroTipoIntervencion}
                                                onChange={(event) =>
                                                    setFiltroTipoIntervencion(
                                                        event.target.value,
                                                    )
                                                }
                                                className="h-10 rounded-md border border-[#DCE3DF] bg-white px-3 text-sm font-medium text-[#2C4A5E] transition outline-none focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20"
                                            >
                                                <option value="todas">
                                                    Todos los tipos
                                                </option>
                                                {tiposIntervencionDisponibles.map(
                                                    (tipo) => (
                                                        <option
                                                            key={tipo}
                                                            value={tipo}
                                                        >
                                                            {tipo}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </label>
                                    )}
                                    {puedeGestionarIntervenciones && (
                                        <Link
                                            href={`/legajos/${legajo.id}/intervenciones/create`}
                                            className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#2C4A5E] px-4 text-sm font-semibold text-white transition hover:bg-[#1F394B]"
                                        >
                                            <Plus size={16} />
                                            Registrar intervención
                                        </Link>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent className="px-5 py-5 sm:px-6">
                                {intervenciones.length === 0 ? (
                                    <div className="rounded-md border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-5 py-8 text-center">
                                        <p className="text-sm font-semibold text-[#2C4A5E]">
                                            Este legajo todavía no tiene
                                            intervenciones.
                                        </p>
                                        {puedeGestionarIntervenciones && (
                                            <p className="mt-1 text-sm text-[#2B2F36]/60">
                                                Registrá la primera actuación
                                                para dejar asentado el
                                                seguimiento.
                                            </p>
                                        )}
                                    </div>
                                ) : intervencionesFiltradas.length === 0 ? (
                                    <div className="rounded-md border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-5 py-8 text-center">
                                        <p className="text-sm font-semibold text-[#2C4A5E]">
                                            No hay intervenciones del tipo
                                            seleccionado.
                                        </p>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setFiltroTipoIntervencion(
                                                    'todas',
                                                )
                                            }
                                            className="mt-1 text-sm font-semibold text-[#2C4A5E] hover:underline"
                                        >
                                            Quitar filtro
                                        </button>
                                    </div>
                                ) : (
                                    <div className="relative space-y-4 border-l border-[#DCE3DF] pl-5 sm:pl-7">
                                        {intervencionesFiltradas.map(
                                            (intervencion) => (
                                                <div
                                                    key={intervencion.id}
                                                    className="relative"
                                                >
                                                    <span
                                                        className="absolute -top-0.5 -left-[2rem] grid size-7 place-items-center rounded-full border-4 border-[#F4F6F5] bg-[#DDEBE1] text-[#41634A] sm:-left-[2.75rem]"
                                                        aria-hidden="true"
                                                    >
                                                        <Clock3 size={12} />
                                                    </span>
                                                    <article className="rounded-2xl border border-[#DCE3DF] bg-white p-4 shadow-[0_8px_24px_rgba(23,50,71,0.04)] sm:p-5">
                                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                            <div>
                                                                <div className="flex flex-wrap items-center gap-2">
                                                                    <Badge className="border-0 bg-[#EDF3EF] text-[#2C4A5E]">
                                                                        {intervencion.tipo ??
                                                                            'Registro'}
                                                                    </Badge>
                                                                    <span className="inline-flex items-center gap-1 text-xs font-semibold text-[#697586]">
                                                                        <Clock3
                                                                            size={
                                                                                13
                                                                            }
                                                                        />
                                                                        {
                                                                            intervencion.fecha
                                                                        }
                                                                        {intervencion.hora
                                                                            ? ` · ${intervencion.hora.slice(0, 5)}`
                                                                            : ''}
                                                                    </span>
                                                                </div>
                                                                <p className="mt-3 text-sm leading-6 whitespace-pre-line text-[#2B2F36]">
                                                                    {
                                                                        intervencion.detalle
                                                                    }
                                                                </p>
                                                            </div>
                                                            {puedeGestionarIntervenciones && (
                                                                <Link
                                                                    href={`/legajos/${legajo.id}/intervenciones/${intervencion.id}/edit`}
                                                                    className="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-[#2C4A5E] hover:underline"
                                                                >
                                                                    <Pencil
                                                                        size={
                                                                            14
                                                                        }
                                                                    />
                                                                    Editar
                                                                </Link>
                                                            )}
                                                        </div>
                                                        <div className="mt-4 grid gap-3 border-t border-[#E4E8E5] pt-3 text-xs text-[#697586] sm:grid-cols-2 lg:grid-cols-4">
                                                            <p>
                                                                <strong className="text-[#2B2F36]">
                                                                    Responsable:{' '}
                                                                </strong>
                                                                {intervencion.profesional ??
                                                                    'Sin asignar'}
                                                            </p>
                                                            <p>
                                                                <strong className="text-[#2B2F36]">
                                                                    Registró:{' '}
                                                                </strong>
                                                                {intervencion.creado_por ??
                                                                    'Sin datos'}
                                                            </p>
                                                            <p>
                                                                <strong className="text-[#2B2F36]">
                                                                    Firma:{' '}
                                                                </strong>
                                                                {intervencion.firma_nombre
                                                                    ? `${intervencion.firma_nombre} · ${intervencion.firma_aclaracion ?? ''}`
                                                                    : 'Sin firma'}
                                                                {intervencion.tiene_firma_responsable && (
                                                                    <a
                                                                        href={`/legajos/${legajo.id}/intervenciones/${intervencion.id}/firma/responsable/descargar`}
                                                                        className="ml-1 underline"
                                                                    >
                                                                        (descargar)
                                                                    </a>
                                                                )}
                                                            </p>
                                                            <p>
                                                                <strong className="text-[#2B2F36]">
                                                                    Entrevistado:{' '}
                                                                </strong>
                                                                {intervencion.firma_entrevistado_nombre
                                                                    ? `${intervencion.firma_entrevistado_nombre} · ${intervencion.firma_entrevistado_aclaracion ?? ''}`
                                                                    : 'Sin firma'}
                                                                {intervencion.tiene_firma_entrevistado && (
                                                                    <a
                                                                        href={`/legajos/${legajo.id}/intervenciones/${intervencion.id}/firma/entrevistado/descargar`}
                                                                        className="ml-1 underline"
                                                                    >
                                                                        (descargar)
                                                                    </a>
                                                                )}
                                                            </p>
                                                        </div>
                                                    </article>
                                                </div>
                                            ),
                                        )}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </section>
                )}
            </main>
        </>
    );
}
