import { Link, router, useForm } from '@inertiajs/react';
import {
    Check,
    ChevronDown,
    Link2,
    LoaderCircle,
    Pencil,
    Plus,
    Unlink,
    UsersRound,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';

import type { PersonaEncontrada } from '@/components/buscador-personas';
import SelectorBarrio from '@/components/selector-barrio';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import actasRoutes from '@/routes/actas';
import vinculosRoutes from '@/routes/legajos/vinculos';

export interface FamiliarVinculado {
    id: number;
    nombre: string;
    apellido: string;
    nombre_propio: string;
    dni: string | null;
    fecha_nacimiento: string | null;
    edad: number | null;
    genero: string | null;
    telefono: string | null;
    direccion_calle: string | null;
    direccion_numero: string | null;
    barrio: string | null;
    observaciones: string | null;
    externo: boolean;
    legajo: { id: number; numero_legajo: string } | null;
}

export interface VinculoFamiliar {
    id: number;
    tipo: string;
    grupo: string | null;
    persona: FamiliarVinculado;
}

interface DatosFamiliar {
    apellido: string;
    nombre: string;
    dni: string;
    telefono: string;
    fecha_nacimiento: string;
    genero: string;
    direccion_calle: string;
    direccion_numero: string;
    barrio: string;
    observaciones: string;
}

interface Props {
    legajoId: number;
    /** Persona del legajo: no se puede vincular consigo misma. */
    personaId: number | null;
    vinculos: VinculoFamiliar[];
    tiposVinculo: string[];
    barrios: string[];
    fontHeading?: string;
}

/** Vínculos que se eligen con un toque; el resto queda en «Otro vínculo…». */
const VINCULOS_FRECUENTES = [
    'madre',
    'padre',
    'abuelo/a',
    'tío/a',
    'hermano/a',
    'tutor/a',
    'otro responsable',
];

const familiarVacio: DatosFamiliar = {
    apellido: '',
    nombre: '',
    dni: '',
    telefono: '',
    fecha_nacimiento: '',
    genero: 'NO_ESPECIFICA',
    direccion_calle: '',
    direccion_numero: '',
    barrio: '',
    observaciones: '',
};

const GENEROS: Record<string, string> = {
    F: 'Femenino',
    M: 'Masculino',
    X: 'No binario / X',
    NO_ESPECIFICA: 'No especifica',
};

const capitalizar = (texto: string) =>
    texto.charAt(0).toUpperCase() + texto.slice(1);

const campoClass =
    'mt-1.5 block h-10 w-full rounded-lg border bg-white px-3 text-sm text-[#2B2F36] outline-none placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

const etiquetaClass = 'text-xs font-semibold text-[#2C4A5E]';

function ErrorCampo({ mensaje }: { mensaje?: string }) {
    return mensaje ? (
        <span className="mt-1 block text-xs text-[#A8493C]">{mensaje}</span>
    ) : null;
}

/**
 * Campos de un familiar: los esenciales a la vista y el resto en «Más datos».
 */
function CamposFamiliar({
    datos,
    set,
    errores,
    barrios,
    prefijoId,
    masDatosAbiertos = false,
}: {
    datos: DatosFamiliar;
    set: (campo: keyof DatosFamiliar, valor: string) => void;
    errores: Record<string, string | undefined>;
    barrios: string[];
    prefijoId: string;
    masDatosAbiertos?: boolean;
}) {
    const extrasConError = [
        'fecha_nacimiento',
        'direccion_calle',
        'direccion_numero',
        'barrio',
        'observaciones',
    ].some((campo) => errores[campo]);
    const [abiertos, setAbiertos] = useState(masDatosAbiertos);
    const mostrarExtras = abiertos || extrasConError;

    const texto = (
        campo: keyof DatosFamiliar,
        etiqueta: string,
        opciones: {
            tipo?: string;
            obligatorio?: boolean;
            placeholder?: string;
            inputMode?: 'numeric' | 'tel';
        } = {},
    ) => (
        <label className={etiquetaClass} htmlFor={`${prefijoId}-${campo}`}>
            {etiqueta}
            {opciones.obligatorio && <span className="text-[#A84635]"> *</span>}
            <input
                id={`${prefijoId}-${campo}`}
                type={opciones.tipo ?? 'text'}
                inputMode={opciones.inputMode}
                value={datos[campo]}
                onChange={(event) => set(campo, event.target.value)}
                placeholder={opciones.placeholder}
                aria-invalid={errores[campo] ? true : undefined}
                className={`${campoClass} ${errores[campo] ? 'border-[#D59A8E]' : 'border-[#D5D1C6]'}`}
            />
            <ErrorCampo mensaje={errores[campo]} />
        </label>
    );

    return (
        <div className="space-y-3">
            <div className="grid gap-3 sm:grid-cols-2">
                {texto('apellido', 'Apellido', { obligatorio: true })}
                {texto('nombre', 'Nombre', { obligatorio: true })}
                {texto('dni', 'DNI', {
                    placeholder: 'Sin puntos',
                    inputMode: 'numeric',
                })}
                {texto('telefono', 'Teléfono', { inputMode: 'tel' })}
            </div>

            <button
                type="button"
                onClick={() => setAbiertos(!abiertos)}
                aria-expanded={mostrarExtras}
                className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2C4A5E] hover:underline"
            >
                <ChevronDown
                    size={14}
                    className={`transition-transform ${mostrarExtras ? '' : '-rotate-90'}`}
                />
                Más datos (nacimiento, domicilio, barrio…)
            </button>

            {mostrarExtras && (
                <div className="grid gap-3 rounded-lg border border-[#E4E8E5] bg-[#FAFBFA] p-3 sm:grid-cols-2">
                    {texto('fecha_nacimiento', 'Fecha de nacimiento', {
                        tipo: 'date',
                    })}
                    <label
                        className={etiquetaClass}
                        htmlFor={`${prefijoId}-genero`}
                    >
                        Género
                        <select
                            id={`${prefijoId}-genero`}
                            value={datos.genero}
                            onChange={(event) =>
                                set('genero', event.target.value)
                            }
                            className={`${campoClass} border-[#D5D1C6]`}
                        >
                            {Object.entries(GENEROS).map(
                                ([valor, etiqueta]) => (
                                    <option key={valor} value={valor}>
                                        {etiqueta}
                                    </option>
                                ),
                            )}
                        </select>
                    </label>
                    {texto('direccion_calle', 'Calle')}
                    {texto('direccion_numero', 'Número')}
                    <div className="sm:col-span-2">
                        <label
                            className={etiquetaClass}
                            htmlFor={`${prefijoId}-barrio`}
                        >
                            Barrio
                        </label>
                        <div className="mt-1.5">
                            <SelectorBarrio
                                id={`${prefijoId}-barrio`}
                                variante="campo"
                                value={datos.barrio}
                                onChange={(valor) => set('barrio', valor)}
                                barrios={barrios}
                                placeholder="Escribí para buscar el barrio"
                                error={errores.barrio}
                            />
                        </div>
                        <ErrorCampo mensaje={errores.barrio} />
                    </div>
                    <label
                        className={`${etiquetaClass} sm:col-span-2`}
                        htmlFor={`${prefijoId}-observaciones`}
                    >
                        Observaciones
                        <textarea
                            id={`${prefijoId}-observaciones`}
                            value={datos.observaciones}
                            onChange={(event) =>
                                set('observaciones', event.target.value)
                            }
                            rows={2}
                            className={`${campoClass} h-auto border-[#D5D1C6] py-2`}
                        />
                    </label>
                </div>
            )}
        </div>
    );
}

/**
 * Personas del sistema que coinciden con lo que se va escribiendo
 * (por DNI si tiene al menos 5 números; si no, por apellido y nombre).
 */
function useCoincidencias(datos: DatosFamiliar): {
    coincidencias: PersonaEncontrada[];
    buscando: boolean;
} {
    const dni = datos.dni.replace(/\D/g, '');
    const termino =
        dni.length >= 5
            ? dni
            : datos.apellido.trim().length >= 2
              ? `${datos.apellido} ${datos.nombre}`.trim()
              : '';
    const [resultado, setResultado] = useState<{
        termino: string;
        personas: PersonaEncontrada[];
    }>({ termino: '', personas: [] });

    useEffect(() => {
        if (termino === '') {
            return;
        }

        const controller = new AbortController();
        const timeout = setTimeout(() => {
            fetch(actasRoutes.personas.url({ query: { q: termino } }), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            })
                .then((response) => response.json())
                .then((personas: PersonaEncontrada[]) =>
                    setResultado({ termino, personas }),
                )
                .catch(() => undefined);
        }, 300);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [termino]);

    return {
        coincidencias:
            termino !== '' && resultado.termino === termino
                ? resultado.personas
                : [],
        buscando: termino !== '' && resultado.termino !== termino,
    };
}

function AgregarFamiliar({
    legajoId,
    personaId,
    vinculadas,
    tiposVinculo,
    barrios,
    onClose,
}: {
    legajoId: number;
    personaId: number | null;
    vinculadas: Set<number>;
    tiposVinculo: string[];
    barrios: string[];
    onClose: () => void;
}) {
    const [elegida, setElegida] = useState<PersonaEncontrada | null>(null);
    const form = useForm({
        tipo_vinculo: '',
        persona_relacionada_id: '',
        familiar: { ...familiarVacio },
    });
    const errores = form.errors as Record<string, string | undefined>;
    const erroresFamiliar = Object.fromEntries(
        Object.entries(errores)
            .filter(([campo]) => campo.startsWith('familiar.'))
            .map(([campo, mensaje]) => [
                campo.slice('familiar.'.length),
                mensaje,
            ]),
    );
    const { coincidencias, buscando } = useCoincidencias(form.data.familiar);
    const dniEscrito = form.data.familiar.dni.replace(/\D/g, '');
    const otrosVinculos = tiposVinculo.filter(
        (tipo) => !VINCULOS_FRECUENTES.includes(tipo),
    );

    const usar = (persona: PersonaEncontrada) => {
        setElegida(persona);
        form.setData('persona_relacionada_id', String(persona.id));
        form.clearErrors();
    };

    const cambiarPersona = () => {
        setElegida(null);
        form.setData('persona_relacionada_id', '');
    };

    const listo =
        form.data.tipo_vinculo !== '' &&
        (elegida !== null ||
            (form.data.familiar.apellido.trim() !== '' &&
                form.data.familiar.nombre.trim() !== ''));

    const agregar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.transform((datos) =>
            elegida
                ? {
                      tipo_vinculo: datos.tipo_vinculo,
                      persona_relacionada_id: datos.persona_relacionada_id,
                  }
                : {
                      tipo_vinculo: datos.tipo_vinculo,
                      familiar: datos.familiar,
                  },
        );
        form.post(vinculosRoutes.store.url(legajoId), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    const botonVinculo = (tipo: string) => {
        const activo = form.data.tipo_vinculo === tipo;

        return (
            <button
                key={tipo}
                type="button"
                onClick={() => form.setData('tipo_vinculo', tipo)}
                aria-pressed={activo}
                className={`inline-flex h-9 items-center gap-1.5 rounded-full border px-3.5 text-sm transition ${
                    activo
                        ? 'border-[#2C4A5E] bg-[#2C4A5E] text-white shadow-sm'
                        : 'border-[#D5D1C6] bg-white text-[#2B2F36] hover:border-[#2C4A5E]/50 hover:bg-[#F4F7F5]'
                }`}
            >
                {activo && <Check size={14} strokeWidth={2.5} />}
                {capitalizar(tipo)}
            </button>
        );
    };

    return (
        <Dialog open onOpenChange={(abierto) => !abierto && onClose()}>
            <DialogContent className="max-h-[92vh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Agregar familiar</DialogTitle>
                    <DialogDescription>
                        Si ya está en el sistema lo vas a ver mientras escribís;
                        si no, queda registrado para reutilizarlo.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={agregar} className="space-y-6">
                    <fieldset>
                        <legend className="text-sm font-semibold text-[#173247]">
                            1. ¿Quién es?
                        </legend>
                        <div className="mt-2 flex flex-wrap gap-2">
                            {VINCULOS_FRECUENTES.filter((tipo) =>
                                tiposVinculo.includes(tipo),
                            ).map(botonVinculo)}
                            {otrosVinculos.length > 0 && (
                                <select
                                    value={
                                        otrosVinculos.includes(
                                            form.data.tipo_vinculo,
                                        )
                                            ? form.data.tipo_vinculo
                                            : ''
                                    }
                                    onChange={(event) =>
                                        form.setData(
                                            'tipo_vinculo',
                                            event.target.value,
                                        )
                                    }
                                    aria-label="Otro vínculo"
                                    className={`h-9 rounded-full border px-3 text-sm outline-none focus:ring-2 focus:ring-[#2C4A5E]/20 ${
                                        otrosVinculos.includes(
                                            form.data.tipo_vinculo,
                                        )
                                            ? 'border-[#2C4A5E] bg-[#2C4A5E] text-white'
                                            : 'border-[#D5D1C6] bg-white text-[#697586]'
                                    }`}
                                >
                                    <option value="">Otro vínculo…</option>
                                    {otrosVinculos.map((tipo) => (
                                        <option key={tipo} value={tipo}>
                                            {capitalizar(tipo)}
                                        </option>
                                    ))}
                                </select>
                            )}
                        </div>
                        <ErrorCampo mensaje={errores.tipo_vinculo} />
                    </fieldset>

                    <fieldset className="space-y-3">
                        <legend className="text-sm font-semibold text-[#173247]">
                            2. Datos de la persona
                        </legend>

                        {elegida ? (
                            <div className="flex items-start justify-between gap-3 rounded-xl border border-[#CFE1D4] bg-[#EAF3ED] px-4 py-3">
                                <div className="min-w-0 text-sm">
                                    <p className="font-semibold text-[#173247]">
                                        {elegida.nombre}
                                    </p>
                                    <p className="mt-0.5 text-xs text-[#41634A]">
                                        {[
                                            elegida.dni && `DNI ${elegida.dni}`,
                                            elegida.legajo
                                                ? `Legajo ${elegida.legajo.numero_legajo}`
                                                : 'Registrada sin legajo',
                                        ]
                                            .filter(Boolean)
                                            .join(' · ')}
                                    </p>
                                    <p className="mt-1 text-xs text-[#41634A]/80">
                                        Se usa la persona que ya está en el
                                        sistema, con sus datos.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={cambiarPersona}
                                    className="shrink-0 text-xs font-semibold text-[#2C4A5E] hover:underline"
                                >
                                    Cambiar
                                </button>
                            </div>
                        ) : (
                            <>
                                <CamposFamiliar
                                    datos={form.data.familiar}
                                    set={(campo, valor) =>
                                        form.setData('familiar', {
                                            ...form.data.familiar,
                                            [campo]: valor,
                                        })
                                    }
                                    errores={erroresFamiliar}
                                    barrios={barrios}
                                    prefijoId="familiar-nuevo"
                                />

                                {(buscando || coincidencias.length > 0) && (
                                    <section
                                        aria-live="polite"
                                        className="rounded-xl border border-[#DCE3DF] bg-white"
                                    >
                                        <h4 className="flex items-center gap-2 border-b border-[#E4E8E5] px-4 py-2 text-xs font-semibold text-[#2C4A5E]">
                                            Ya está en el sistema
                                            {buscando && (
                                                <LoaderCircle
                                                    size={13}
                                                    className="animate-spin text-[#697586]"
                                                />
                                            )}
                                        </h4>
                                        <ul className="divide-y divide-[#E4E8E5]">
                                            {coincidencias.map((persona) => {
                                                const esElLegajo =
                                                    persona.id === personaId;
                                                const yaVinculada =
                                                    vinculadas.has(persona.id);
                                                const mismoDni =
                                                    dniEscrito !== '' &&
                                                    persona.dni === dniEscrito;

                                                return (
                                                    <li
                                                        key={persona.id}
                                                        className={`flex items-center justify-between gap-3 px-4 py-2.5 ${mismoDni ? 'bg-[#FFF8E6]' : ''}`}
                                                    >
                                                        <div className="min-w-0 text-sm">
                                                            <p className="truncate font-medium text-[#173247]">
                                                                {persona.nombre}
                                                                {mismoDni && (
                                                                    <span className="ml-2 text-xs font-semibold text-[#8A6A35]">
                                                                        Coincide
                                                                        el DNI
                                                                    </span>
                                                                )}
                                                            </p>
                                                            <p className="text-xs text-[#697586]">
                                                                {[
                                                                    persona.dni &&
                                                                        `DNI ${persona.dni}`,
                                                                    persona.legajo
                                                                        ? `Legajo ${persona.legajo.numero_legajo}`
                                                                        : 'Registrada sin legajo',
                                                                ]
                                                                    .filter(
                                                                        Boolean,
                                                                    )
                                                                    .join(
                                                                        ' · ',
                                                                    )}
                                                            </p>
                                                        </div>
                                                        {esElLegajo ||
                                                        yaVinculada ? (
                                                            <span className="shrink-0 text-xs text-[#697586]">
                                                                {esElLegajo
                                                                    ? 'Es este legajo'
                                                                    : 'Ya vinculada'}
                                                            </span>
                                                        ) : (
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() =>
                                                                    usar(
                                                                        persona,
                                                                    )
                                                                }
                                                            >
                                                                Usar
                                                            </Button>
                                                        )}
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    </section>
                                )}
                            </>
                        )}
                        <ErrorCampo mensaje={errores.persona_relacionada_id} />
                    </fieldset>

                    <DialogFooter>
                        <Button type="button" variant="ghost" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            disabled={!listo || form.processing}
                            className="bg-[#2C4A5E] text-white hover:bg-[#1F394B]"
                        >
                            <Plus size={15} />
                            {form.processing
                                ? 'Guardando…'
                                : 'Agregar familiar'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function datosDe(persona: FamiliarVinculado): DatosFamiliar {
    return {
        apellido: persona.apellido ?? '',
        nombre: persona.nombre_propio ?? '',
        dni: persona.dni ?? '',
        telefono: persona.telefono ?? '',
        fecha_nacimiento: persona.fecha_nacimiento ?? '',
        genero: persona.genero ?? 'NO_ESPECIFICA',
        direccion_calle: persona.direccion_calle ?? '',
        direccion_numero: persona.direccion_numero ?? '',
        barrio: persona.barrio ?? '',
        observaciones: persona.observaciones ?? '',
    };
}

function EditarFamiliar({
    legajoId,
    vinculo,
    barrios,
    onClose,
}: {
    legajoId: number;
    vinculo: VinculoFamiliar;
    barrios: string[];
    onClose: () => void;
}) {
    const form = useForm<DatosFamiliar>(datosDe(vinculo.persona));

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(
            vinculosRoutes.familiar.url({
                legajo: legajoId,
                vinculo: vinculo.id,
            }),
            { preserveScroll: true, onSuccess: onClose },
        );
    };

    return (
        <Dialog open onOpenChange={(abierto) => !abierto && onClose()}>
            <DialogContent className="max-h-[92vh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Datos de {vinculo.persona.nombre}</DialogTitle>
                    <DialogDescription>
                        Registrada sin legajo. Estos datos se reutilizan en
                        actas y planillas.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={guardar} className="space-y-4">
                    <CamposFamiliar
                        datos={form.data}
                        set={(campo, valor) => form.setData(campo, valor)}
                        errores={form.errors}
                        barrios={barrios}
                        prefijoId={`editar-${vinculo.id}`}
                        masDatosAbiertos
                    />
                    <DialogFooter>
                        <Button type="button" variant="ghost" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? 'Guardando…' : 'Guardar datos'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

/**
 * Vínculos familiares del legajo: familiares con legajo propio y adultos
 * registrados sin legajo (padres, abuelos, tutores…), reutilizables en el sistema.
 */
export default function RedFamiliarModule({
    legajoId,
    personaId,
    vinculos,
    tiposVinculo,
    barrios,
    fontHeading,
}: Props) {
    const [agregando, setAgregando] = useState(false);
    const [editando, setEditando] = useState<VinculoFamiliar | null>(null);
    const vinculadas = new Set(vinculos.map((vinculo) => vinculo.persona.id));

    const desvincular = (vinculo: VinculoFamiliar) => {
        if (
            !window.confirm(
                `¿Desvincular a ${vinculo.persona.nombre} de este legajo?`,
            )
        ) {
            return;
        }

        router.delete(
            vinculosRoutes.destroy.url({
                legajo: legajoId,
                vinculo: vinculo.id,
            }),
            { preserveScroll: true },
        );
    };

    return (
        <Card className="border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
            <CardHeader className="border-b border-[#E4E8E5] bg-[#F8FAF9] px-5 py-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                        <UsersRound size={18} className="text-[#6B8F71]" />
                        <CardTitle
                            className="text-base font-semibold text-[#2C4A5E]"
                            style={{ fontFamily: fontHeading }}
                        >
                            Vínculos familiares
                        </CardTitle>
                    </div>
                    <Button
                        type="button"
                        onClick={() => setAgregando(true)}
                        className="bg-[#2C4A5E] text-white hover:bg-[#1F394B]"
                    >
                        <Plus size={15} />
                        Agregar familiar
                    </Button>
                </div>
            </CardHeader>
            <CardContent className="px-5 py-5">
                {vinculos.length > 0 ? (
                    <ul className="grid gap-3 sm:grid-cols-2">
                        {vinculos.map((vinculo) => {
                            const persona = vinculo.persona;
                            const detalle = [
                                persona.dni && `DNI ${persona.dni}`,
                                persona.edad !== null && `${persona.edad} años`,
                                persona.telefono && `Tel. ${persona.telefono}`,
                                [
                                    persona.direccion_calle,
                                    persona.direccion_numero,
                                ]
                                    .filter(Boolean)
                                    .join(' '),
                                persona.barrio,
                            ].filter(Boolean);

                            return (
                                <li
                                    key={vinculo.id}
                                    className="flex items-start justify-between gap-3 rounded-lg border border-[#DCE3DF] bg-[#FBFCFB] p-3"
                                >
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2 text-xs font-semibold text-[#6B8F71]">
                                            <Link2 size={13} />
                                            {capitalizar(vinculo.tipo)}
                                            {persona.externo && (
                                                <Badge className="border-0 bg-[#F2F7FB] text-[length:calc(10px_+_2px)] text-[#35627A]">
                                                    Externo
                                                </Badge>
                                            )}
                                        </div>
                                        {persona.legajo ? (
                                            <Link
                                                href={`/legajos/${persona.legajo.id}`}
                                                className="mt-1 block truncate text-sm font-semibold text-[#2C4A5E] hover:underline"
                                            >
                                                {persona.nombre}
                                            </Link>
                                        ) : (
                                            <p className="mt-1 truncate text-sm font-semibold text-[#2C4A5E]">
                                                {persona.nombre}
                                            </p>
                                        )}
                                        <p className="mt-1 text-xs leading-5 text-[#697586]">
                                            {persona.legajo
                                                ? `Legajo ${persona.legajo.numero_legajo}`
                                                : detalle.length > 0
                                                  ? detalle.join(' · ')
                                                  : 'Sin datos de contacto'}
                                        </p>
                                    </div>
                                    <div className="flex shrink-0 items-center">
                                        {persona.externo && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setEditando(vinculo)
                                                }
                                                className="rounded-md p-2 text-[#697586] transition hover:bg-[#EEF3F0] hover:text-[#2C4A5E]"
                                                aria-label={`Editar datos de ${persona.nombre}`}
                                            >
                                                <Pencil size={15} />
                                            </button>
                                        )}
                                        <button
                                            type="button"
                                            onClick={() => desvincular(vinculo)}
                                            className="rounded-md p-2 text-[#8A6A63] transition hover:bg-[#FFF1EF] hover:text-[#A8493C]"
                                            aria-label={`Desvincular a ${persona.nombre}`}
                                        >
                                            <Unlink size={15} />
                                        </button>
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                ) : (
                    <div className="rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-6 text-center text-sm text-[#697586]">
                        <p>Todavía no hay familiares vinculados.</p>
                        <button
                            type="button"
                            onClick={() => setAgregando(true)}
                            className="mt-2 font-semibold text-[#2C4A5E] hover:underline"
                        >
                            Agregar el primero
                        </button>
                    </div>
                )}
            </CardContent>

            {agregando && (
                <AgregarFamiliar
                    legajoId={legajoId}
                    personaId={personaId}
                    vinculadas={vinculadas}
                    tiposVinculo={tiposVinculo}
                    barrios={barrios}
                    onClose={() => setAgregando(false)}
                />
            )}

            {editando && (
                <EditarFamiliar
                    legajoId={legajoId}
                    vinculo={editando}
                    barrios={barrios}
                    onClose={() => setEditando(null)}
                />
            )}
        </Card>
    );
}
