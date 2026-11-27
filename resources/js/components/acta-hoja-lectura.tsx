import ActaContenido from '@/components/acta-contenido';
import ActaHoja from '@/components/acta-hoja';
import type { DatosActa, TipoActa } from '@/components/acta-hoja';
import { HojaSoloLecturaContext } from '@/components/auh-planilla-hoja';

/** Acta guardada, tal como la devuelve el servidor para mostrar su hoja. */
export interface HojaActaGuardada {
    id: number;
    tipo: string;
    etiqueta: string;
    titulo?: string;
    es_auh: boolean;
    fecha?: string | null;
    creado_por?: string | null;
    datos: Record<string, unknown>;
    firmantes: Record<string, string>;
    /** Nombre de quien firmó, por rol. */
    firmas: Record<string, string>;
    /** Roles que ya firmaron. */
    firmados: string[];
    /** Imagen de cada firma (data URI), sacada del Word guardado. */
    firmas_imagenes?: Record<string, string>;
    firmado: boolean;
    requiere_firma: boolean;
    editable: boolean;
    /** Responsable del acta (quien la cargó), si es fijo. */
    responsable_fijo?: string | null;
    legajo_numero: string | null;
    descarga_url: string;
    descarga_pdf_url?: string;
    secciones?: {
        titulo: string;
        campos: { label: string; valor: string }[];
        texto: boolean;
    }[];
}

const sinCambios = () => undefined;

/**
 * Hoja del acta en modo lectura: el mismo formato que el Word, sin poder editarla.
 * Las actas de AUH no tienen hoja propia acá y se muestran como resumen.
 */
export default function ActaHojaLectura({ hoja }: { hoja: HojaActaGuardada }) {
    if (hoja.es_auh) {
        return (
            <div className="mx-auto max-w-[794px] rounded-lg bg-white shadow-sm">
                <ActaContenido
                    acta={{
                        requiere_firma: hoja.requiere_firma,
                        firmas: Object.entries(hoja.firmas).map(
                            ([rol, nombre]) => ({
                                rol_label: hoja.firmantes[rol] ?? rol,
                                nombre,
                            }),
                        ),
                        secciones: hoja.secciones ?? [],
                    }}
                />
            </div>
        );
    }

    return (
        <HojaSoloLecturaContext.Provider
            value={{
                firmados: hoja.firmados,
                imagenes: hoja.firmas_imagenes ?? {},
            }}
        >
            <fieldset
                disabled
                aria-label={`${hoja.etiqueta} (solo lectura)`}
                className="m-0 min-w-0 border-0 p-0 [&_input]:!cursor-default [&_input]:!bg-transparent [&_input::placeholder]:!text-transparent [&_select]:!cursor-default [&_select]:!appearance-none [&_select]:!bg-transparent [&_textarea]:!cursor-default [&_textarea]:!resize-none [&_textarea]:!bg-transparent [&_textarea::placeholder]:!text-transparent"
            >
                <ActaHoja
                    tipo={hoja.tipo as TipoActa}
                    datos={hoja.datos as unknown as DatosActa}
                    legajoNumero={hoja.legajo_numero}
                    firmas={Object.fromEntries(
                        Object.keys(hoja.firmantes).map((rol) => [
                            rol,
                            { nombre: hoja.firmas[rol] ?? '', dibujo: '' },
                        ]),
                    )}
                    errores={{}}
                    set={sinCambios}
                    setFirma={sinCambios}
                    barrios={[]}
                    escuelas={[]}
                />
            </fieldset>
        </HojaSoloLecturaContext.Provider>
    );
}
