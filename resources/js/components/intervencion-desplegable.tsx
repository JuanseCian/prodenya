import { Link } from '@inertiajs/react';
import { ChevronDown, Eye, FileText, Pencil } from 'lucide-react';
import { useId, useState } from 'react';

import FirmaGuardada from '@/components/firma-guardada';

export interface IntervencionDesplegableData {
    id: number;
    fecha: string | null;
    hora: string | null;
    tipo: string | null;
    detalle: string | null;
    profesional: string | null;
    creado_por: string | null;
    firma_nombre: string | null;
    firma_aclaracion: string | null;
    tiene_firma_responsable: boolean;
    firma_entrevistado_nombre: string | null;
    firma_entrevistado_aclaracion: string | null;
    tiene_firma_entrevistado: boolean;
    acta: {
        etiqueta: string;
        firmado: boolean;
        requiere_firma: boolean;
        hoja_url: string;
    } | null;
}

interface Props {
    intervencion: IntervencionDesplegableData;
    legajoId: number;
    puedeEditar: boolean;
    onVerActa: (hojaUrl: string) => void;
}

const botonAccion =
    'inline-flex h-9 items-center gap-1.5 rounded-lg border px-2.5 text-xs font-semibold transition focus-visible:ring-2 focus-visible:ring-[#2C4A5E]/30 focus-visible:outline-none';

/**
 * Intervención compacta: una fila con lo esencial que se despliega para ver
 * el detalle completo y las firmas.
 */
export default function IntervencionDesplegable({
    intervencion,
    legajoId,
    puedeEditar,
    onVerActa,
}: Props) {
    const [abierta, setAbierta] = useState(false);
    const panelId = useId();
    const { acta } = intervencion;

    const ver = () => {
        if (acta) {
            onVerActa(acta.hoja_url);

            return;
        }

        setAbierta(true);
    };

    return (
        <li
            className={`transition-colors ${abierta ? 'bg-[#FAFBFA]' : 'bg-white hover:bg-[#FBFCFB]'}`}
        >
            <div className="flex items-center gap-2 px-3 py-2.5 sm:gap-3 sm:px-4">
                <button
                    type="button"
                    onClick={() => setAbierta((actual) => !actual)}
                    aria-expanded={abierta}
                    aria-controls={panelId}
                    className="flex min-w-0 flex-1 items-center gap-3 rounded-lg py-1 text-left focus-visible:ring-2 focus-visible:ring-[#2C4A5E]/30 focus-visible:outline-none"
                >
                    <ChevronDown
                        size={16}
                        className={`shrink-0 text-[#697586] transition-transform duration-200 ease-[cubic-bezier(0.2,0,0,1)] ${abierta ? 'rotate-180' : ''}`}
                        aria-hidden="true"
                    />
                    <span className="w-[4.75rem] shrink-0 text-xs leading-tight font-semibold text-[#2C4A5E] tabular-nums">
                        {intervencion.fecha ?? '—'}
                        {intervencion.hora && (
                            <span className="block font-normal text-[#697586]">
                                {intervencion.hora.slice(0, 5)}
                            </span>
                        )}
                    </span>
                    <span className="min-w-0 flex-1">
                        <span className="flex flex-wrap items-center gap-1.5">
                            <span className="truncate text-sm font-semibold text-[#173247]">
                                {intervencion.tipo ?? 'Registro'}
                            </span>
                            {acta && (
                                <span className="inline-flex items-center gap-1 rounded-md bg-[#EDF3EF] px-1.5 py-0.5 text-[length:calc(0.68rem_+_2px)] font-semibold text-[#41634A]">
                                    <FileText size={11} aria-hidden="true" />
                                    {acta.requiere_firma
                                        ? acta.firmado
                                            ? 'Firmada'
                                            : 'Sin firmar'
                                        : 'Acta'}
                                </span>
                            )}
                        </span>
                        <span className="mt-0.5 block truncate text-xs text-[#697586]">
                            {intervencion.profesional ?? 'Sin responsable'}
                            {!abierta && intervencion.detalle
                                ? ` · ${intervencion.detalle}`
                                : ''}
                        </span>
                    </span>
                </button>

                <div className="flex shrink-0 items-center gap-1.5">
                    <button
                        type="button"
                        onClick={ver}
                        className={`${botonAccion} border-[#DCE3DF] bg-white text-[#2C4A5E] hover:border-[#B9CBC0] hover:bg-[#EDF3EF]`}
                        aria-label={
                            acta
                                ? `Ver ${acta.etiqueta}`
                                : 'Ver detalle de la intervención'
                        }
                        title={acta ? 'Ver acta' : 'Ver detalle'}
                    >
                        <Eye size={15} aria-hidden="true" />
                        <span className="hidden sm:inline">Ver</span>
                    </button>
                    {puedeEditar && (
                        <Link
                            href={`/legajos/${legajoId}/intervenciones/${intervencion.id}/edit`}
                            className={`${botonAccion} border-[#2C4A5E] bg-[#2C4A5E] text-white hover:bg-[#173247]`}
                            aria-label="Editar intervención"
                            title="Editar"
                        >
                            <Pencil size={14} aria-hidden="true" />
                            <span className="hidden sm:inline">Editar</span>
                        </Link>
                    )}
                </div>
            </div>

            <div
                id={panelId}
                className={`grid transition-[grid-template-rows] duration-250 ease-[cubic-bezier(0.32,0.72,0,1)] ${abierta ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'}`}
            >
                <div className="overflow-hidden" inert={!abierta}>
                    <div className="border-t border-[#E4E8E5] px-4 pt-3 pb-4 sm:pl-[3.25rem]">
                        <p className="text-sm leading-6 whitespace-pre-line text-[#2B2F36]">
                            {intervencion.detalle || 'Sin detalle.'}
                        </p>
                        <dl className="mt-3 grid gap-x-6 gap-y-2 text-xs text-[#697586] sm:grid-cols-2">
                            <div>
                                <dt className="inline font-semibold text-[#2B2F36]">
                                    Responsable:{' '}
                                </dt>
                                <dd className="inline">
                                    {intervencion.profesional ?? 'Sin asignar'}
                                </dd>
                            </div>
                            <div>
                                <dt className="inline font-semibold text-[#2B2F36]">
                                    Registró:{' '}
                                </dt>
                                <dd className="inline">
                                    {intervencion.creado_por ?? 'Sin datos'}
                                </dd>
                            </div>
                        </dl>
                        {/* Las actas muestran sus firmas en «Ver»; las demás intervenciones, acá. */}
                        {!acta &&
                            (intervencion.firma_nombre ||
                                intervencion.tiene_firma_responsable ||
                                intervencion.firma_entrevistado_nombre ||
                                intervencion.tiene_firma_entrevistado) && (
                                <div className="mt-3 grid max-w-2xl gap-3 sm:grid-cols-2">
                                    <FirmaGuardada
                                        compacta
                                        titulo="Firma del responsable"
                                        nombre={intervencion.firma_nombre}
                                        aclaracion={
                                            intervencion.firma_aclaracion
                                        }
                                        url={
                                            intervencion.tiene_firma_responsable
                                                ? `/legajos/${legajoId}/intervenciones/${intervencion.id}/firma/responsable/descargar`
                                                : null
                                        }
                                    />
                                    {(intervencion.firma_entrevistado_nombre ||
                                        intervencion.tiene_firma_entrevistado) && (
                                        <FirmaGuardada
                                            compacta
                                            titulo="Firma del entrevistado"
                                            nombre={
                                                intervencion.firma_entrevistado_nombre
                                            }
                                            aclaracion={
                                                intervencion.firma_entrevistado_aclaracion
                                            }
                                            url={
                                                intervencion.tiene_firma_entrevistado
                                                    ? `/legajos/${legajoId}/intervenciones/${intervencion.id}/firma/entrevistado/descargar`
                                                    : null
                                            }
                                        />
                                    )}
                                </div>
                            )}
                    </div>
                </div>
            </div>
        </li>
    );
}
