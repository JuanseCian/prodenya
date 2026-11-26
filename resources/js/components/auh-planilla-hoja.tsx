import { Plus, X } from 'lucide-react';
import { useContext, useState } from 'react';
import type { ReactNode } from 'react';

import type {
    DatosPlanilla,
    MiembroGrupo,
    Responsable,
    TipoPlanilla,
} from '@/components/auh-planilla-dialog';
import FirmaDigital from '@/components/firma-digital';
import { HojaSoloLecturaContext } from '@/components/hoja-solo-lectura-context';
import SelectorBarrio from '@/components/selector-barrio';

export { HojaSoloLecturaContext };

export interface FirmaEnCurso {
    nombre: string;
    dibujo: string;
}

interface Props {
    tipo: TipoPlanilla;
    datos: DatosPlanilla;
    firmas: Record<string, FirmaEnCurso>;
    errores: Record<string, string>;
    set: <K extends keyof DatosPlanilla>(
        campo: K,
        valor: DatosPlanilla[K],
    ) => void;
    setResponsable: (campo: keyof Responsable, valor: string) => void;
    setMiembro: (
        index: number,
        campo: keyof MiembroGrupo,
        valor: string,
    ) => void;
    agregarMiembro: () => void;
    quitarMiembro: (index: number) => void;
    setFirma: (rol: string, campo: keyof FirmaEnCurso, valor: string) => void;
    /** Barrios que se pueden elegir; sin lista, el barrio se escribe libre. */
    barrios?: string[];
}

const MESES = [
    'enero',
    'febrero',
    'marzo',
    'abril',
    'mayo',
    'junio',
    'julio',
    'agosto',
    'septiembre',
    'octubre',
    'noviembre',
    'diciembre',
];

/**
 * Espacio en blanco de la planilla: un campo en línea con el texto, punteado
 * como en el modelo impreso.
 */
export function Blank({
    value,
    onChange,
    placeholder,
    ancho = 'w-48',
    error,
    type = 'text',
    ...rest
}: {
    value: string;
    onChange: (valor: string) => void;
    placeholder: string;
    ancho?: string;
    error?: string;
    type?: string;
    min?: number;
    max?: number;
}) {
    const soloLectura = useContext(HojaSoloLecturaContext);

    if (soloLectura) {
        const texto =
            type === 'date' && value
                ? new Intl.DateTimeFormat('es-AR').format(
                      new Date(`${value}T12:00:00`),
                  )
                : value;

        return (
            <span
                className={`mx-1 border-b border-dotted border-[#2B2F36]/50 px-1 align-baseline font-semibold text-[#173247] ${
                    texto ? 'inline break-words' : `inline-block ${ancho}`
                }`}
            >
                {texto || '\u00a0'}
            </span>
        );
    }

    return (
        <input
            {...rest}
            type={type}
            value={value}
            onChange={(event) => onChange(event.target.value)}
            placeholder={placeholder}
            aria-label={placeholder}
            aria-invalid={error ? true : undefined}
            title={error ?? placeholder}
            className={`mx-1 inline-block max-w-full ${ancho} rounded-sm border-0 border-b-2 border-dotted px-1 py-0 align-baseline font-[inherit] leading-snug text-[#173247] text-[inherit] outline-none placeholder:font-normal placeholder:text-[#8A6A35]/60 focus:border-solid focus:border-[#2C4A5E] focus:bg-[#FFF3C4] ${
                error
                    ? 'border-[#9B5964] bg-[#FCF1F3]'
                    : 'border-[#2B2F36]/50 bg-[#FFFBEA]'
            }`}
        />
    );
}

/**
 * Espacio de firma: se firma sobre la línea, como en papel.
 */
export function Firma({
    rol,
    firmas,
    setFirma,
    errores,
    children,
    alineacion = 'items-start text-left',
}: {
    rol: string;
    firmas: Record<string, FirmaEnCurso>;
    setFirma: Props['setFirma'];
    errores: Record<string, string>;
    children: ReactNode;
    alineacion?: string;
}) {
    const error = errores[`firmas.${rol}.dibujo`];
    const soloLectura = useContext(HojaSoloLecturaContext);

    if (soloLectura) {
        const firmado = soloLectura.firmados.includes(rol);
        const imagen = soloLectura.imagenes[rol];

        return (
            <div className={`flex w-full max-w-72 flex-col ${alineacion}`}>
                <div className="flex min-h-20 w-full flex-col justify-end pb-1">
                    {firmado && imagen ? (
                        <img
                            src={imagen}
                            alt={`Firma de ${firmas[rol]?.nombre || rol}`}
                            className="h-[60px] w-auto max-w-full object-contain object-left"
                        />
                    ) : firmado ? (
                        <span className="text-xs font-semibold text-[#31765b]">
                            ✓ Firmado digitalmente
                            {firmas[rol]?.nombre
                                ? ` por ${firmas[rol].nombre}`
                                : ''}
                        </span>
                    ) : (
                        <span className="text-xs text-[#697586] italic">
                            Sin firmar
                        </span>
                    )}
                </div>
                <span className="mt-1 block w-full border-t border-dashed border-black" />
                {children}
            </div>
        );
    }

    return (
        <div className={`flex w-full max-w-72 flex-col ${alineacion}`}>
            <div
                className={`w-full rounded-md ${error ? 'ring-2 ring-[#9B5964]' : ''}`}
            >
                <FirmaDigital
                    label="Firmar aquí"
                    value={firmas[rol]?.dibujo ?? ''}
                    onChange={(valor) => setFirma(rol, 'dibujo', valor)}
                />
            </div>
            <span className="mt-1 block w-full border-t border-dashed border-black" />
            {children}
        </div>
    );
}

export function Hoja({
    children,
    className = '',
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={`mx-auto w-full max-w-[794px] bg-white px-5 py-8 text-black shadow-[0_8px_30px_rgba(23,50,71,0.12)] ring-1 ring-black/5 sm:px-14 sm:py-12 ${className}`}
        >
            {children}
        </div>
    );
}

function DeclaracionJurada(props: Props) {
    const { datos, errores, set, setResponsable } = props;
    const [anio = '', mes = '', dia = ''] = datos.fecha
        ? datos.fecha.split('-')
        : [];
    const [partes, setPartes] = useState({
        dia: dia ? String(Number(dia)) : '',
        mes: mes ? String(Number(mes)) : '',
        anio,
    });

    const setParte = (parte: keyof typeof partes, valor: string) => {
        const nuevas = { ...partes, [parte]: valor };
        setPartes(nuevas);
        set(
            'fecha',
            nuevas.dia && nuevas.mes && nuevas.anio.length === 4
                ? `${nuevas.anio}-${nuevas.mes.padStart(2, '0')}-${nuevas.dia.padStart(2, '0')}`
                : '',
        );
    };

    return (
        <Hoja className="font-['Arial',sans-serif] text-[length:calc(14px_+_2px)] leading-8 font-bold">
            <p className="text-center">Declaración jurada</p>
            <p className="text-center">
                (No convivencia con el núcleo familiar)
            </p>

            <div className="mt-10 space-y-1">
                <p>
                    En la ciudad de
                    <Blank
                        value={datos.ciudad}
                        onChange={(valor) => set('ciudad', valor)}
                        placeholder="ciudad"
                        ancho="w-52"
                    />
                    Provincia de
                    <Blank
                        value={datos.provincia}
                        onChange={(valor) => set('provincia', valor)}
                        placeholder="provincia"
                        ancho="w-44"
                    />
                </p>
                <p>
                    A los
                    <Blank
                        type="number"
                        min={1}
                        max={31}
                        value={partes.dia}
                        onChange={(valor) => setParte('dia', valor)}
                        placeholder="día"
                        ancho="w-16"
                        error={errores.fecha}
                    />
                    días, del mes de
                    <select
                        value={partes.mes}
                        onChange={(event) =>
                            setParte('mes', event.target.value)
                        }
                        aria-label="mes"
                        className="mx-1 inline-block w-36 rounded-sm border-0 border-b-2 border-dotted border-[#2B2F36]/50 bg-[#FFFBEA] px-1 py-0 font-[inherit] text-[#173247] text-[inherit] outline-none focus:border-solid focus:border-[#2C4A5E]"
                    >
                        <option value="">mes</option>
                        {MESES.map((nombre, index) => (
                            <option key={nombre} value={String(index + 1)}>
                                {nombre}
                            </option>
                        ))}
                    </select>
                    del año
                    <Blank
                        type="number"
                        min={2000}
                        max={2100}
                        value={partes.anio}
                        onChange={(valor) => setParte('anio', valor)}
                        placeholder="año"
                        ancho="w-20"
                    />
                </p>
            </div>

            <div className="mt-8 space-y-1">
                <p>
                    Por la presente,
                    <Blank
                        value={datos.responsable.nombre}
                        onChange={(valor) => setResponsable('nombre', valor)}
                        placeholder="apellido y nombre del adulto responsable"
                        ancho="w-80"
                        error={errores['firmas.responsable.nombre']}
                    />
                    , DNI
                    <Blank
                        value={datos.responsable.dni}
                        onChange={(valor) => setResponsable('dni', valor)}
                        placeholder="DNI"
                        ancho="w-32"
                    />
                </p>
                <p>
                    Declara bajo juramento que
                    <Blank
                        value={datos.no_conviviente_nombre}
                        onChange={(valor) =>
                            set('no_conviviente_nombre', valor)
                        }
                        placeholder="apellido y nombre de quien no convive"
                        ancho="w-80"
                    />
                    , DNI
                    <Blank
                        value={datos.no_conviviente_dni}
                        onChange={(valor) => set('no_conviviente_dni', valor)}
                        placeholder="DNI"
                        ancho="w-32"
                    />
                </p>
                <p>
                    no convive con mi núcleo familiar desde el año
                    <Blank
                        type="number"
                        min={1900}
                        max={new Date().getFullYear()}
                        value={String(datos.no_conviviente_desde ?? '')}
                        onChange={(valor) => set('no_conviviente_desde', valor)}
                        placeholder="año"
                        ancho="w-20"
                        error={errores.no_conviviente_desde}
                    />
                    , no aporta ingresos ni bienes para la manutención de mis
                    hijos/menores a cargo y/o desconozco paradero.
                </p>
            </div>

            <div className="mt-14 grid gap-10 sm:grid-cols-2">
                <Firma
                    rol="mds"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={props.errores}
                >
                    <span>Firma por MDS</span>
                    <span className="mt-1 text-[length:calc(13px_+_2px)]">
                        Aclaración
                        <Blank
                            value={props.firmas.mds?.nombre ?? ''}
                            onChange={(valor) =>
                                props.setFirma('mds', 'nombre', valor)
                            }
                            placeholder="aclaración"
                            ancho="w-44"
                            error={errores['firmas.mds.nombre']}
                        />
                    </span>
                </Firma>
                <Firma
                    rol="responsable"
                    alineacion="items-start text-left sm:ml-auto"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={props.errores}
                >
                    <span>Firma del adulto responsable</span>
                    <span className="mt-1 text-[length:calc(13px_+_2px)]">
                        Aclaración
                        <Blank
                            value={datos.responsable.nombre}
                            onChange={(valor) =>
                                setResponsable('nombre', valor)
                            }
                            placeholder="aclaración"
                            ancho="w-44"
                            error={errores['firmas.responsable.nombre']}
                        />
                    </span>
                    <span className="text-[length:calc(13px_+_2px)]">
                        DNI
                        <Blank
                            value={datos.responsable.dni}
                            onChange={(valor) => setResponsable('dni', valor)}
                            placeholder="DNI"
                            ancho="w-44"
                        />
                    </span>
                </Firma>
            </div>
        </Hoja>
    );
}

/**
 * Renglón vacío del modelo (párrafo en blanco), para respetar las alturas.
 */
export function Renglon({ alto = 'h-7' }: { alto?: string }) {
    return <div aria-hidden className={alto} />;
}

/**
 * Recuadro para firmar en pantalla, sin etiqueta propia: la planilla ya dice
 * qué se firma en cada lugar.
 */
export function PadFirma({
    rol,
    firmas,
    setFirma,
    errores,
    className = '',
}: {
    rol: string;
    firmas: Record<string, FirmaEnCurso>;
    setFirma: Props['setFirma'];
    errores: Record<string, string>;
    className?: string;
}) {
    return (
        <div
            className={`w-full max-w-64 rounded-md ${errores[`firmas.${rol}.dibujo`] ? 'ring-2 ring-[#9B5964]' : ''} ${className}`}
        >
            <FirmaDigital
                label="Firmar aquí"
                value={firmas[rol]?.dibujo ?? ''}
                onChange={(valor) => setFirma(rol, 'dibujo', valor)}
            />
        </div>
    );
}

/**
 * Réplica de "Nota de elevación.docx", renglón por renglón: los párrafos con
 * interlineado 1,5 del modelo usan renglones más altos.
 */
function NotaElevacion(props: Props) {
    const { datos, errores, set, setResponsable, firmas, setFirma } = props;
    const renglon15 = 'leading-10';

    return (
        <Hoja className="font-['Arial',sans-serif] text-[length:calc(14px_+_2px)] leading-7 font-bold">
            <p>Nota de elevación</p>
            <Renglon />
            <Renglon />
            <p className="text-right">
                Buenos Aires,
                <Blank
                    type="date"
                    value={datos.fecha}
                    onChange={(valor) => set('fecha', valor)}
                    placeholder="fecha"
                    ancho="w-44"
                    error={errores.fecha}
                />
            </p>
            <Renglon />

            <div className={renglon15}>
                <p>Administración Nacional de la Seguridad Social (ANSES)</p>
                <p>Sres. Unidad Atención Integral (UDAI)</p>
                <Renglon alto="h-10" />
                <Renglon alto="h-10" />
                <p>
                    Por el presente se eleva para su conocimiento e
                    intervención.
                </p>
                <p>
                    (Referencia) Cambio de titularidad Sr/Sra
                    <Blank
                        value={datos.responsable.nombre}
                        onChange={(valor) => setResponsable('nombre', valor)}
                        placeholder="apellido y nombre del nuevo titular"
                        ancho="w-80"
                    />
                </p>
                <p>
                    DNI
                    <Blank
                        value={datos.responsable.dni}
                        onChange={(valor) => setResponsable('dni', valor)}
                        placeholder="DNI"
                        ancho="w-32"
                    />
                    , Niño/a:
                    <Blank
                        value={datos.nino_nombre}
                        onChange={(valor) => set('nino_nombre', valor)}
                        placeholder="apellido y nombre del niño/a"
                        ancho="w-72"
                    />
                </p>
                <p>
                    DNI Niño/a:
                    <Blank
                        value={datos.nino_dni}
                        onChange={(valor) => set('nino_dni', valor)}
                        placeholder="DNI"
                        ancho="w-32"
                    />
                    .
                    <Blank
                        value={datos.referencia_detalle}
                        onChange={(valor) => set('referencia_detalle', valor)}
                        placeholder="detalle adicional (opcional)"
                        ancho="w-72"
                        error={errores.referencia_detalle}
                    />
                </p>
                <p className="text-center">Saludo atte.</p>
                <Renglon alto="h-10" />
                <p>Se adjunta:</p>
                <ul className="list-disc pl-10">
                    <li>
                        Informe social – Informe de Reconversión de relación –
                        AUH
                    </li>
                    <li>
                        Declaración jurada (No convivencia con el núcleo
                        familiar)
                    </li>
                    <li>
                        Documento del organismo de aplicación de la medida
                        administrativa adoptada.
                    </li>
                    <li>
                        Fotocopia de documento de identidad – partida de
                        nacimiento.
                    </li>
                </ul>
                <Renglon alto="h-10" />
            </div>
            <Renglon />
            <Renglon />
            <Renglon />

            {/* Pie de firmas: solicitante a cobrar a la izquierda y Centro de referencia a la derecha, con ambas firmas en el mismo renglón (igual que el Word). */}
            <div className="grid grid-cols-2 items-end gap-x-4 sm:gap-x-10">
                <div className="flex items-end gap-2">
                    <span className="pb-1">X</span>
                    <PadFirma
                        rol="solicitante"
                        firmas={firmas}
                        setFirma={setFirma}
                        errores={errores}
                    />
                </div>
                <PadFirma
                    rol="centro"
                    firmas={firmas}
                    setFirma={setFirma}
                    errores={errores}
                    className="ml-auto"
                />

                <div />
                <p className="text-right">…………………………………….</p>

                <p>
                    X Aclaración:
                    <Blank
                        value={datos.responsable.nombre}
                        onChange={(valor) => setResponsable('nombre', valor)}
                        placeholder="apellido y nombre"
                        ancho="w-48"
                        error={errores['firmas.solicitante.nombre']}
                    />
                </p>
                <p className="text-right">CENTRO DE REFERENCIA</p>

                <div />
                <p className="text-right">FIRMA Y ACLARACION</p>

                <p>
                    X DNI:
                    <Blank
                        value={datos.responsable.dni}
                        onChange={(valor) => setResponsable('dni', valor)}
                        placeholder="DNI"
                        ancho="w-36"
                    />
                </p>
                <p className="text-right">
                    <Blank
                        value={firmas.centro?.nombre ?? ''}
                        onChange={(valor) =>
                            setFirma('centro', 'nombre', valor)
                        }
                        placeholder="aclaración"
                        ancho="w-56"
                        error={errores['firmas.centro.nombre']}
                    />
                </p>
            </div>
        </Hoja>
    );
}

function InformeRelacion(props: Props) {
    const { datos, errores, setResponsable, setMiembro } = props;
    const linea = (
        etiqueta: string,
        campo: keyof Responsable,
        ancho = 'w-40',
        type = 'text',
    ) => (
        <span className="whitespace-nowrap">
            {etiqueta}
            {campo === 'barrio' && props.barrios?.length ? (
                <SelectorBarrio
                    value={datos.responsable.barrio}
                    onChange={(valor) => setResponsable('barrio', valor)}
                    barrios={props.barrios}
                    ancho={ancho}
                    error={errores['responsable.barrio']}
                />
            ) : (
                <Blank
                    type={type}
                    value={datos.responsable[campo]}
                    onChange={(valor) => setResponsable(campo, valor)}
                    placeholder={etiqueta.replace(/[:.]$/, '').toLowerCase()}
                    ancho={ancho}
                    error={errores[`responsable.${campo}`]}
                />
            )}
        </span>
    );

    return (
        <Hoja className="font-['Calibri','Carlito','Arial',sans-serif] text-[length:calc(15px_+_2px)] leading-9">
            <p className="text-center font-bold underline">
                INFORME DE RECOVERSION DE RELACION – AUH
            </p>

            <p className="mt-6 underline">DATOS DEL SOLICITANTE</p>
            <div className="space-y-1">
                <p>{linea('Apellido y Nombre:', 'nombre', 'w-96')}</p>
                <p className="grid gap-x-6 sm:grid-cols-2">
                    {linea('DNI:', 'dni')}
                    {linea('CUIL:', 'cuil')}
                </p>
                <p className="grid gap-x-6 sm:grid-cols-2">
                    {linea('FECHA DE NAC.', 'fecha_nacimiento', 'w-40', 'date')}
                    {linea('NACIONALIDAD:', 'nacionalidad', 'w-32')}
                </p>
                <p className="grid gap-x-6 sm:grid-cols-2">
                    {linea('DOMICILIO:', 'domicilio')}
                    {linea('BARRIO:', 'barrio')}
                </p>
                <p className="grid gap-x-6 sm:grid-cols-2">
                    {linea('LOCALIDAD:', 'localidad')}
                    {linea('PROVINCIA:', 'provincia')}
                </p>
                <p>{linea('TELEFONO:', 'telefono')}</p>
            </div>

            <p className="mt-6 underline">GRUPO FAMILIAR CONVIVIENTE</p>
            <div className="overflow-x-auto">
                <table className="w-full min-w-[620px] border-collapse text-[length:calc(13px_+_2px)] leading-5">
                    <thead>
                        <tr>
                            {[
                                'APELLIDO Y NOMBRE',
                                'VINCULO',
                                'FECHA DE RELACION',
                                'DNI',
                                'FECHA DE NAC',
                            ].map((titulo) => (
                                <th
                                    key={titulo}
                                    className="border border-black px-2 py-1 text-left font-normal underline"
                                >
                                    {titulo}
                                </th>
                            ))}
                            <th className="w-8" />
                        </tr>
                    </thead>
                    <tbody>
                        {datos.grupo_familiar.map((miembro, index) => (
                            <tr key={index}>
                                {(
                                    [
                                        ['nombre', 'text'],
                                        ['vinculo', 'text'],
                                        ['fecha_relacion', 'date'],
                                        ['dni', 'text'],
                                        ['fecha_nacimiento', 'date'],
                                    ] as Array<[keyof MiembroGrupo, string]>
                                ).map(([campo, type]) => (
                                    <td
                                        key={campo}
                                        className="border border-black p-1"
                                    >
                                        <input
                                            type={type}
                                            value={miembro[campo]}
                                            onChange={(event) =>
                                                setMiembro(
                                                    index,
                                                    campo,
                                                    event.target.value,
                                                )
                                            }
                                            aria-label={`${campo} fila ${index + 1}`}
                                            className={`h-9 w-full rounded-sm px-1 text-[length:calc(13px_+_2px)] text-[#173247] outline-none focus:bg-[#FFF3C4] ${
                                                errores[
                                                    `grupo_familiar.${index}.${campo}`
                                                ]
                                                    ? 'bg-[#FCF1F3]'
                                                    : 'bg-[#FFFBEA]'
                                            }`}
                                        />
                                    </td>
                                ))}
                                <td className="pl-1">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            props.quitarMiembro(index)
                                        }
                                        className="rounded p-1 text-[#9B5964] hover:bg-[#FCF1F3]"
                                        aria-label={`Quitar fila ${index + 1}`}
                                    >
                                        <X className="size-4" />
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <button
                type="button"
                onClick={props.agregarMiembro}
                className="mt-2 inline-flex items-center gap-1 rounded-md border border-dashed border-[#B9C5BD] px-2 py-1 text-xs font-semibold text-[#2C4A5E] hover:bg-[#F4F6F5]"
            >
                <Plus className="size-3.5" />
                Agregar fila
            </button>

            <p className="mt-6 leading-6 underline">
                MOTIVOS DE INTERVENCION (Consignar la necesidad de reconversión
                de relación u otras situaciones de vulnerabilidad de grupo
                familiar)
            </p>
            <textarea
                value={datos.motivos}
                onChange={(event) => props.set('motivos', event.target.value)}
                rows={6}
                aria-label="Motivos de intervención"
                placeholder="Escribí los motivos de intervención…"
                className={`mt-1 w-full resize-y rounded-sm px-1 text-[length:calc(15px_+_2px)] leading-9 text-[#173247] outline-none placeholder:text-[#8A6A35]/60 focus:bg-[#FFF3C4] ${
                    errores.motivos ? 'bg-[#FCF1F3]' : 'bg-[#FFFBEA]'
                }`}
                style={{
                    backgroundImage:
                        'repeating-linear-gradient(transparent, transparent 35px, rgba(43,47,54,0.45) 35px, rgba(43,47,54,0.45) 36px)',
                }}
            />

            <div className="mt-12 flex justify-end">
                <Firma
                    rol="profesional"
                    alineacion="items-end text-right"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={props.errores}
                >
                    <span>
                        Firma y aclaración del profesional interviniente
                    </span>
                    <Blank
                        value={props.firmas.profesional?.nombre ?? ''}
                        onChange={(valor) =>
                            props.setFirma('profesional', 'nombre', valor)
                        }
                        placeholder="aclaración"
                        ancho="w-56"
                        error={errores['firmas.profesional.nombre']}
                    />
                </Firma>
            </div>
        </Hoja>
    );
}

export default function AuhPlanillaHoja(props: Props) {
    if (props.tipo === 'declaracion_jurada') {
        return <DeclaracionJurada {...props} />;
    }

    if (props.tipo === 'nota_elevacion') {
        return <NotaElevacion {...props} />;
    }

    return <InformeRelacion {...props} />;
}
