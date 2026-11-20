import { router, usePage } from '@inertiajs/react';
import { FileDown, FileText, PenLine, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { FormEvent, MouseEvent, PointerEvent } from 'react';

import AuhPlanillaHoja from '@/components/auh-planilla-hoja';
import type { FirmaEnCurso } from '@/components/auh-planilla-hoja';
import { Button } from '@/components/ui/button';
import auhPlanillasRoutes from '@/routes/legajos/auh/planillas';

export type TipoPlanilla =
    'nota_elevacion' | 'declaracion_jurada' | 'informe_relacion';

export interface Responsable {
    nombre: string;
    dni: string;
    cuil: string;
    fecha_nacimiento: string;
    nacionalidad: string;
    domicilio: string;
    barrio: string;
    localidad: string;
    provincia: string;
    telefono: string;
}

export interface MiembroGrupo {
    nombre: string;
    vinculo: string;
    fecha_relacion: string;
    dni: string;
    fecha_nacimiento: string;
}

export interface DatosPlanilla {
    fecha: string;
    ciudad: string;
    provincia: string;
    nino_nombre: string;
    nino_dni: string;
    responsable: Responsable;
    no_conviviente_nombre: string;
    no_conviviente_dni: string;
    no_conviviente_desde: string;
    referencia_detalle: string;
    grupo_familiar: MiembroGrupo[];
    motivos: string;
}

export interface PersonaVinculada extends Responsable {
    id: string;
    vinculo: string;
}

export interface FormularioPlanillas {
    datos: DatosPlanilla;
    personas: PersonaVinculada[];
}

interface Props {
    legajoId: number;
    tipo: TipoPlanilla;
    titulo: string;
    formulario: FormularioPlanillas;
    firmantes: Record<string, string>;
    onClose: () => void;
}

const miembroVacio: MiembroGrupo = {
    nombre: '',
    vinculo: '',
    fecha_relacion: '',
    dni: '',
    fecha_nacimiento: '',
};

const camposResponsable: Array<keyof Responsable> = [
    'nombre',
    'dni',
    'cuil',
    'fecha_nacimiento',
    'nacionalidad',
    'telefono',
    'domicilio',
    'barrio',
    'localidad',
    'provincia',
];

/** El modelo impreso trae cinco filas en blanco: se muestran siempre. */
const conFilasMinimas = (miembros: MiembroGrupo[]): MiembroGrupo[] => [
    ...miembros,
    ...Array.from({ length: Math.max(0, 5 - miembros.length) }, () => ({
        ...miembroVacio,
    })),
];

/** Firmantes cuya aclaración es el adulto responsable (ver Auh::FIRMANTES_RESPONSABLE). */
const FIRMANTES_RESPONSABLE = ['responsable', 'solicitante'];

const xsrfToken = (): string => {
    const cookie = document.cookie
        .split('; ')
        .find((item) => item.startsWith('XSRF-TOKEN='));

    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
};

const nombreArchivo = (response: Response, fallback: string): string => {
    const disposition = response.headers.get('Content-Disposition') ?? '';
    const utf8 = disposition.match(/filename\*=UTF-8''([^;]+)/i);
    const simple = disposition.match(/filename="?([^";]+)"?/i);

    if (utf8) {
        return decodeURIComponent(utf8[1]);
    }

    return simple?.[1] ?? fallback;
};

export default function AuhPlanillaDialog({
    legajoId,
    tipo,
    titulo,
    formulario,
    firmantes,
    onClose,
}: Props) {
    const { auth } = usePage().props;
    const [datos, setDatos] = useState<DatosPlanilla>(() => ({
        ...formulario.datos,
        grupo_familiar: conFilasMinimas(formulario.datos.grupo_familiar),
    }));
    const [errores, setErrores] = useState<Record<string, string>>({});
    const [generando, setGenerando] = useState(false);
    const [firmando, setFirmando] = useState(false);
    const [firmas, setFirmas] = useState<Record<string, FirmaEnCurso>>(() =>
        Object.fromEntries(
            Object.keys(firmantes).map((rol) => [
                rol,
                {
                    // La aclaración del responsable/solicitante la toma el servidor de sus datos.
                    nombre: FIRMANTES_RESPONSABLE.includes(rol)
                        ? ''
                        : auth.user.name,
                    dibujo: '',
                },
            ]),
        ),
    );

    const setFirma = (rol: string, campo: keyof FirmaEnCurso, valor: string) =>
        setFirmas((actual) => ({
            ...actual,
            [rol]: { ...actual[rol], [campo]: valor },
        }));

    const firmar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setErrores({});
        router.post(
            auhPlanillasRoutes.firmar.url({ legajo: legajoId, tipo }),
            JSON.parse(JSON.stringify({ ...datos, firmas })),
            {
                preserveScroll: true,
                onStart: () => setFirmando(true),
                onFinish: () => setFirmando(false),
                onSuccess: onClose,
                onError: (errs) => setErrores(errs as Record<string, string>),
            },
        );
    };

    const set = <K extends keyof DatosPlanilla>(
        campo: K,
        valor: DatosPlanilla[K],
    ) => setDatos((actual) => ({ ...actual, [campo]: valor }));

    const setResponsable = (campo: keyof Responsable, valor: string) =>
        setDatos((actual) => ({
            ...actual,
            responsable: { ...actual.responsable, [campo]: valor },
        }));

    const usarPersona = (id: string) => {
        const persona = formulario.personas.find((item) => item.id === id);

        if (!persona) {
            return;
        }

        setDatos((actual) => ({
            ...actual,
            responsable: {
                ...actual.responsable,
                ...Object.fromEntries(
                    camposResponsable
                        .map((campo) => [campo, persona[campo]])
                        .filter(([, valor]) => valor !== ''),
                ),
            },
        }));
    };

    const setMiembro = (
        index: number,
        campo: keyof MiembroGrupo,
        valor: string,
    ) =>
        set(
            'grupo_familiar',
            datos.grupo_familiar.map((miembro, i) =>
                i === index ? { ...miembro, [campo]: valor } : miembro,
            ),
        );

    const descargarSinFirmar = async () => {
        setGenerando(true);
        setErrores({});

        try {
            const response = await fetch(
                auhPlanillasRoutes.generar.url({ legajo: legajoId, tipo }),
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': xsrfToken(),
                    },
                    body: JSON.stringify(datos),
                },
            );

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
                setErrores({
                    general:
                        response.status === 403
                            ? 'No tenés permiso para generar esta planilla.'
                            : 'No se pudo generar la planilla. Intentá nuevamente.',
                });

                return;
            }

            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const enlace = document.createElement('a');
            enlace.href = url;
            enlace.download = nombreArchivo(response, `${tipo}.docx`);
            document.body.appendChild(enlace);
            enlace.click();
            enlace.remove();
            URL.revokeObjectURL(url);

            router.reload({ only: ['auh'] });
            onClose();
        } catch {
            setErrores({
                general: 'No se pudo generar la planilla. Revisá tu conexión.',
            });
        } finally {
            setGenerando(false);
        }
    };

    const mensajesError = [...new Set(Object.values(errores))];

    const [datosIniciales] = useState(() => JSON.stringify(datos));
    const hayCambios =
        JSON.stringify(datos) !== datosIniciales ||
        Object.values(firmas).some((firma) => firma.dibujo !== '');

    const cerrar = () => {
        if (generando || firmando) {
            return;
        }

        if (
            hayCambios &&
            !window.confirm(
                '¿Cerrar la planilla? Se pierden los datos y las firmas que no guardaste.',
            )
        ) {
            return;
        }

        onClose();
    };

    useEffect(() => {
        const alPresionarTecla = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                cerrar();
            }
        };

        window.addEventListener('keydown', alPresionarTecla);

        return () => window.removeEventListener('keydown', alPresionarTecla);
    });

    /**
     * Cierra solo si el toque empieza y termina fuera de la hoja: así un trazo
     * de firma que se sale del recuadro no cierra la planilla.
     */
    const inicioFuera = useRef(false);
    const alPresionarFuera = (event: PointerEvent<HTMLElement>) => {
        inicioFuera.current = event.target === event.currentTarget;
    };
    const alTocarFuera = (event: MouseEvent<HTMLElement>) => {
        if (inicioFuera.current && event.target === event.currentTarget) {
            cerrar();
        }

        inicioFuera.current = false;
    };

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-[#173247]/35 p-2 sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="auh-planilla-title"
            onPointerDown={alPresionarFuera}
            onClick={alTocarFuera}
        >
            <form
                onSubmit={firmar}
                className="flex max-h-[96vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-[#EEF1EF] shadow-2xl"
            >
                <header className="border-b border-[#DCE3DF] bg-[#FBFCFB] px-4 py-3 sm:px-5">
                    <div className="flex items-center justify-between gap-3">
                        <div className="flex min-w-0 items-center gap-3">
                            <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#EAF6EE] text-[#397461]">
                                <FileText className="size-5" />
                            </span>
                            <div className="min-w-0">
                                <p className="text-[11px] font-semibold tracking-[0.08em] text-[#6B8F71] uppercase">
                                    Planilla AUH
                                </p>
                                <h2
                                    id="auh-planilla-title"
                                    className="truncate text-base font-semibold text-[#2C4A5E] sm:text-lg"
                                >
                                    {titulo}
                                </h2>
                            </div>
                        </div>
                        <button
                            type="button"
                            onClick={cerrar}
                            className="shrink-0 rounded-lg p-2 text-[#697586] hover:bg-[#F1F5F3]"
                            aria-label="Cerrar planilla"
                        >
                            <X className="size-5" />
                        </button>
                    </div>
                    <div className="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-sm text-[#697586]">
                            Completá los espacios resaltados y firmá sobre cada
                            línea. Lo vacío sale punteado en el Word.
                        </p>
                        {formulario.personas.length > 0 && (
                            <select
                                defaultValue=""
                                onChange={(event) =>
                                    usarPersona(event.target.value)
                                }
                                className="h-9 w-full shrink-0 rounded-lg border border-[#D5D1C6] bg-white px-2 text-xs text-[#2C4A5E] sm:w-64"
                                aria-label="Completar el responsable con una persona vinculada"
                            >
                                <option value="" disabled>
                                    Completar responsable con…
                                </option>
                                {formulario.personas.map((persona) => (
                                    <option key={persona.id} value={persona.id}>
                                        {persona.nombre} ({persona.vinculo})
                                    </option>
                                ))}
                            </select>
                        )}
                    </div>
                </header>

                <div
                    className="flex-1 overflow-y-auto px-2 py-5 sm:px-6"
                    onPointerDown={alPresionarFuera}
                    onClick={alTocarFuera}
                >
                    <AuhPlanillaHoja
                        tipo={tipo}
                        datos={datos}
                        firmas={firmas}
                        errores={errores}
                        set={set}
                        setResponsable={setResponsable}
                        setMiembro={setMiembro}
                        agregarMiembro={() =>
                            set('grupo_familiar', [
                                ...datos.grupo_familiar,
                                { ...miembroVacio },
                            ])
                        }
                        quitarMiembro={(index) =>
                            set(
                                'grupo_familiar',
                                datos.grupo_familiar.filter(
                                    (_, i) => i !== index,
                                ),
                            )
                        }
                        setFirma={setFirma}
                    />
                </div>

                <footer className="border-t border-[#DCE3DF] bg-[#FBFCFB] px-5 py-4">
                    {mensajesError.length > 0 && (
                        <ul className="mb-3 list-disc space-y-0.5 pl-5 text-sm text-[#9B5964]">
                            {mensajesError.map((mensaje) => (
                                <li key={mensaje}>{mensaje}</li>
                            ))}
                        </ul>
                    )}
                    <div className="flex flex-wrap justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={cerrar}
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={generando || firmando}
                            onClick={() => void descargarSinFirmar()}
                        >
                            <FileDown className="size-4" />
                            {generando
                                ? 'Generando...'
                                : 'Descargar sin firmar'}
                        </Button>
                        <Button
                            type="submit"
                            disabled={generando || firmando}
                            className="bg-[#2C4A5E] text-white hover:bg-[#173247]"
                        >
                            <PenLine className="size-4" />
                            {firmando
                                ? 'Guardando...'
                                : 'Firmar y guardar en el legajo'}
                        </Button>
                    </div>
                </footer>
            </form>
        </div>
    );
}
