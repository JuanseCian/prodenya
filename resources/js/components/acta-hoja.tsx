import { useContext, useState } from 'react';
import type { ReactNode } from 'react';

import {
    Blank,
    Firma,
    Hoja,
    HojaSoloLecturaContext,
} from '@/components/auh-planilla-hoja';
import type { FirmaEnCurso } from '@/components/auh-planilla-hoja';
import SelectorBarrio from '@/components/selector-barrio';

export type TipoActa =
    'inicial' | 'domiciliaria' | 'telefonica' | 'citacion' | 'constancia';

export interface DatosActa {
    fecha: string;
    hora: string;
    operador: string;
    compareciente_nombre: string;
    compareciente_dni: string;
    compareciente_domicilio: string;
    compareciente_barrio: string;
    compareciente_telefono: string;
    vinculo: string;
    nino_nombre: string;
    nino_dni: string;
    nino_fecha_nacimiento: string;
    nino_escuela: string;
    relato: string;
    estrategias: string;
    citacion_dia: string;
    citacion_horario: string;
}

interface Props {
    tipo: TipoActa;
    datos: DatosActa;
    legajoNumero: string | null;
    firmas: Record<string, FirmaEnCurso>;
    errores: Record<string, string>;
    set: (campo: keyof DatosActa, valor: string) => void;
    setFirma: (rol: string, campo: keyof FirmaEnCurso, valor: string) => void;
    /** Barrios que se pueden elegir. */
    barrios: string[];
    /** Escuelas que se pueden elegir, incluidas las inactivas. */
    escuelas: string[];
    /**
     * Responsable del acta: figura como operador/a y firma por el Servicio, sin poder
     * cambiarse. Cadena vacía: todavía no se eligió. null o ausente (p. ej. un
     * administrativo en Acta rápida): se indica a mano.
     */
    responsableFijo?: string | null;
}

/** Roles que firman por el Servicio: los firma el responsable del acta. */
const FIRMANTES_DEL_SERVICIO = ['equipo', 'autoridad'];

/** Nombre del responsable, fijo: se muestra como un dato completado del acta. */
function ResponsableFijo({ nombre }: { nombre: string }) {
    return (
        <span
            className={`mx-1 inline border-b border-dotted border-[#2B2F36]/50 px-1 align-baseline ${
                nombre
                    ? 'font-semibold text-[#173247]'
                    : 'text-[#8A6A35]/70 italic'
            }`}
            title="Responsable del acta"
        >
            {nombre || 'elegí el profesional'}
        </span>
    );
}

const tieneResponsableFijo = (
    props: Props,
): props is Props & {
    responsableFijo: string;
} => typeof props.responsableFijo === 'string';

/** Operador/a del acta: fijo si hay responsable; si no, se escribe y se copia a la firma del Servicio. */
function operador(props: Props, placeholder: string, ancho: string) {
    if (tieneResponsableFijo(props)) {
        return <ResponsableFijo nombre={props.responsableFijo} />;
    }

    return (
        <Blank
            value={props.datos.operador}
            onChange={(valor) => {
                const anterior = props.datos.operador;
                props.set('operador', valor);

                for (const rol of FIRMANTES_DEL_SERVICIO) {
                    const actual = props.firmas[rol];

                    if (
                        actual &&
                        (actual.nombre === '' || actual.nombre === anterior)
                    ) {
                        props.setFirma(rol, 'nombre', valor);
                    }
                }
            }}
            placeholder={placeholder}
            ancho={ancho}
            error={props.errores.operador}
        />
    );
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

const ESCUDO = '/images/escudo-municipalidad.png';

const fechaCorta = (valor: string): string =>
    valor
        ? new Intl.DateTimeFormat('es-AR').format(new Date(`${valor}T12:00:00`))
        : '';

const fechaLarga = (valor: string): string => {
    if (!valor) {
        return '';
    }

    const [anio, mes, dia] = valor.split('-').map(Number);

    return `${dia} de ${MESES[mes - 1]} de ${anio}`;
};

const partesDe = (fecha: string) => {
    const [anio = '', mes = '', dia = ''] = fecha ? fecha.split('-') : [];

    return {
        dia: dia ? String(Number(dia)) : '',
        mes: mes ? String(Number(mes)) : '',
        anio,
    };
};

/**
 * Día, mes y año del cuerpo del acta, atados a la fecha del acta.
 */
function FechaEnPartes({
    fecha,
    onChange,
    error,
    conDe = false,
}: {
    fecha: string;
    onChange: (fecha: string) => void;
    error?: string;
    conDe?: boolean;
}) {
    const [partes, setPartes] = useState(() => partesDe(fecha));
    const [fechaPrevia, setFechaPrevia] = useState(fecha);
    const soloLectura = useContext(HojaSoloLecturaContext);

    // Si la fecha cambia desde afuera (p. ej. el campo «Fecha» del encabezado), se refleja acá.
    if (fecha !== fechaPrevia) {
        setFechaPrevia(fecha);

        if (fecha) {
            setPartes(partesDe(fecha));
        }
    }

    const cambiar = (parte: keyof typeof partes, valor: string) => {
        const nuevas = { ...partes, [parte]: valor };
        setPartes(nuevas);
        onChange(
            nuevas.dia && nuevas.mes && nuevas.anio.length === 4
                ? `${nuevas.anio}-${nuevas.mes.padStart(2, '0')}-${nuevas.dia.padStart(2, '0')}`
                : '',
        );
    };

    return (
        <>
            a los
            <Blank
                type="number"
                min={1}
                max={31}
                value={partes.dia}
                onChange={(valor) => cambiar('dia', valor)}
                placeholder="día"
                ancho="w-14"
                error={error}
            />
            días del mes{conDe ? ' de' : ''}
            {soloLectura ? (
                <Blank
                    value={partes.mes ? MESES[Number(partes.mes) - 1] : ''}
                    onChange={() => undefined}
                    placeholder="mes"
                    ancho="w-32"
                />
            ) : (
                <select
                    value={partes.mes}
                    onChange={(event) => cambiar('mes', event.target.value)}
                    aria-label="mes"
                    className="mx-1 inline-block w-32 rounded-sm border-0 border-b-2 border-dotted border-[#2B2F36]/50 bg-[#FFFBEA] px-1 py-0 font-[inherit] text-[#173247] text-[inherit] outline-none focus:border-solid focus:border-[#2C4A5E]"
                >
                    <option value="">mes</option>
                    {MESES.map((nombre, index) => (
                        <option key={nombre} value={String(index + 1)}>
                            {nombre}
                        </option>
                    ))}
                </select>
            )}
            del año
            <Blank
                type="number"
                min={2000}
                max={2100}
                value={partes.anio}
                onChange={(valor) => cambiar('anio', valor)}
                placeholder="año"
                ancho="w-20"
            />
        </>
    );
}

/**
 * Recuadro de texto con renglones, para relatos y estrategias.
 */
function Relato({
    value,
    onChange,
    placeholder,
    error,
    filas = 8,
}: {
    value: string;
    onChange: (valor: string) => void;
    placeholder: string;
    error?: string;
    filas?: number;
}) {
    const soloLectura = useContext(HojaSoloLecturaContext);

    if (soloLectura) {
        return (
            <div
                className="mt-1 w-full px-1 font-[inherit] leading-8 break-words whitespace-pre-wrap text-[#173247] text-[inherit]"
                style={{
                    minHeight: `${filas * 32}px`,
                    backgroundImage:
                        'repeating-linear-gradient(transparent, transparent 31px, rgba(43,47,54,0.35) 31px, rgba(43,47,54,0.35) 32px)',
                }}
            >
                {value}
            </div>
        );
    }

    return (
        <textarea
            value={value}
            onChange={(event) => onChange(event.target.value)}
            rows={filas}
            aria-label={placeholder}
            placeholder={placeholder}
            className={`mt-1 w-full resize-y rounded-sm px-1 font-[inherit] leading-8 text-[#173247] text-[inherit] outline-none placeholder:text-[#8A6A35]/60 focus:bg-[#FFF3C4] ${
                error ? 'bg-[#FCF1F3]' : 'bg-[#FFFBEA]'
            }`}
            style={{
                backgroundImage:
                    'repeating-linear-gradient(transparent, transparent 31px, rgba(43,47,54,0.35) 31px, rgba(43,47,54,0.35) 32px)',
            }}
        />
    );
}

function Membrete() {
    return (
        <img
            src={ESCUDO}
            alt="Municipalidad de San Nicolás de los Arroyos"
            className="mx-auto h-24 w-auto"
        />
    );
}

function Aclaracion({
    rol,
    props,
    children,
}: {
    rol: string;
    props: Props;
    children?: ReactNode;
}) {
    if (tieneResponsableFijo(props) && FIRMANTES_DEL_SERVICIO.includes(rol)) {
        return (
            <span className="mt-1 text-[length:calc(13px_+_2px)]">
                {children ?? 'Aclaración'}
                <ResponsableFijo nombre={props.responsableFijo} />
            </span>
        );
    }

    return (
        <span className="mt-1 text-[length:calc(13px_+_2px)]">
            {children ?? 'Aclaración'}
            <Blank
                value={props.firmas[rol]?.nombre ?? ''}
                onChange={(valor) => props.setFirma(rol, 'nombre', valor)}
                placeholder="aclaración"
                ancho="w-44"
                error={props.errores[`firmas.${rol}.nombre`]}
            />
        </span>
    );
}

/** Datos de quien se presenta y del niño/a, comunes a varias actas. */
function campo(
    props: Props,
    nombre: keyof DatosActa,
    placeholder: string,
    ancho = 'w-48',
    type = 'text',
) {
    return (
        <Blank
            type={type}
            value={props.datos[nombre]}
            onChange={(valor) => props.set(nombre, valor)}
            placeholder={placeholder}
            ancho={ancho}
            error={props.errores[nombre]}
        />
    );
}

function Inicial(props: Props) {
    const { datos, legajoNumero, set, errores } = props;

    return (
        <Hoja className="font-['Verdana','Arial',sans-serif] text-[length:calc(14px_+_2px)] leading-8">
            <p className="text-center font-['Times_New_Roman',serif] text-lg font-bold underline">
                ACTA
            </p>
            <div className="mt-6 ml-auto w-fit space-y-1 font-bold">
                <p>
                    Legajo n°:{' '}
                    <span className="font-normal text-[#173247]">
                        {legajoNumero ?? 'se completa al vincular'}
                    </span>
                </p>
                <p>
                    Fecha:
                    <Blank
                        type="date"
                        value={datos.fecha}
                        onChange={(valor) => set('fecha', valor)}
                        placeholder="fecha"
                        ancho="w-40"
                        error={errores.fecha}
                    />
                </p>
                <p>
                    Op. Responsable:
                    {operador(props, 'operador/a', 'w-44')}
                </p>
            </div>

            <p className="mt-8 text-justify">
                En la ciudad de San Nicolás de los Arroyos en sede del Servicio
                Local de Promoción y Protección de Derechos del Niño de San
                Nicolás de los Arroyos, ubicado en Lazarte y Brown,{' '}
                <FechaEnPartes
                    fecha={datos.fecha}
                    onChange={(valor) => set('fecha', valor)}
                    error={errores.fecha}
                />{' '}
                a las {campo(props, 'hora', 'hora', 'w-28', 'time')} HS, se hace
                presente el Sr./a{' '}
                {campo(
                    props,
                    'compareciente_nombre',
                    'apellido y nombre',
                    'w-72',
                )}
                , quien acredita su identidad con DNI{' '}
                {campo(props, 'compareciente_dni', 'DNI', 'w-32')}, domiciliado
                en calle{' '}
                {campo(
                    props,
                    'compareciente_domicilio',
                    'calle y número',
                    'w-56',
                )}
                , en barrio{' '}
                <SelectorBarrio
                    value={datos.compareciente_barrio}
                    onChange={(valor) => set('compareciente_barrio', valor)}
                    barrios={props.barrios}
                    ancho="w-40"
                    error={errores.compareciente_barrio}
                />{' '}
                de la ciudad de San Nicolás de los Arroyos, teléfono{' '}
                {campo(props, 'compareciente_telefono', 'teléfono', 'w-36')}. En
                relación al niño/a{' '}
                {campo(
                    props,
                    'nino_nombre',
                    'apellido y nombre del niño/a',
                    'w-72',
                )}{' '}
                DNI Nº {campo(props, 'nino_dni', 'DNI', 'w-32')} fecha de
                Nacimiento{' '}
                {campo(props, 'nino_fecha_nacimiento', 'fecha', 'w-40', 'date')}{' '}
                que concurre a la escuela{' '}
                <SelectorBarrio
                    value={datos.nino_escuela}
                    onChange={(valor) => set('nino_escuela', valor)}
                    barrios={props.barrios}
                    opciones={props.escuelas}
                    etiqueta="escuela"
                    placeholder="escuela"
                    ancho="w-48"
                    error={errores.nino_escuela}
                />{' '}
                y acredita que el vínculo de{' '}
                {campo(props, 'vinculo', 'vínculo', 'w-36')}.
            </p>
            <p className="mt-4 text-justify">
                Quien puesto en conocimiento de los principios rectores del
                Sistema de Promoción y Protección de derechos creado por ley
                13.298 y el decreto 300/05 hace la siguiente manifestación:
            </p>

            <p className="mt-8 font-['Times_New_Roman',serif] font-bold">
                MANIFESTACIÓN / RELATO:
            </p>
            <Relato
                value={datos.relato}
                onChange={(valor) => set('relato', valor)}
                placeholder="Escribí la manifestación o el relato…"
                error={errores.relato}
            />
            <p className="mt-6 font-bold">ESTRATEGIAS A SEGUIR:</p>
            <Relato
                value={datos.estrategias}
                onChange={(valor) => set('estrategias', valor)}
                placeholder="Escribí las estrategias a seguir…"
                error={errores.estrategias}
                filas={5}
            />
            <p className="mt-6 font-bold">CONSTANCIA Y CIERRE:</p>
            <p className="text-justify">
                Se deja constancia que la persona interviniente manifiesta haber
                comprendido la información brindada y que el presente acta se
                incorpora al legajo correspondiente.
            </p>

            <div className="mt-10 grid gap-10 sm:grid-cols-2">
                <Firma
                    rol="compareciente"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={errores}
                >
                    <span>Firma compareciente</span>
                    <Aclaracion rol="compareciente" props={props} />
                </Firma>
                <Firma
                    rol="equipo"
                    alineacion="items-start text-left sm:ml-auto"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={errores}
                >
                    <span>Firma equipo técnico</span>
                    <Aclaracion rol="equipo" props={props} />
                </Firma>
            </div>
        </Hoja>
    );
}

function Domiciliaria(props: Props) {
    const { datos, legajoNumero, set, errores } = props;

    return (
        <Hoja className="font-['Verdana','Arial',sans-serif] text-[length:calc(14px_+_2px)] leading-8">
            <div className="border border-black px-3 py-4 sm:px-5">
                <p className="text-center font-bold underline">
                    ACTA VISITA DOMICILIARIA
                </p>
                <div className="mt-6 space-y-1 font-bold underline">
                    <p>
                        FECHA:{' '}
                        <span className="font-normal no-underline">
                            {fechaCorta(datos.fecha)}
                        </span>
                    </p>
                    <p>
                        LEGAJO:{' '}
                        <span className="font-normal text-[#173247] no-underline">
                            {legajoNumero ?? 'se completa al vincular'}
                        </span>
                    </p>
                    <p>
                        Operador/es interviniente/s:
                        <span className="no-underline">
                            {operador(props, 'operador/es', 'w-56')}
                        </span>
                    </p>
                </div>
                <p className="mt-8 text-justify">
                    En la ciudad de San Nicolás de los Arroyos,{' '}
                    <FechaEnPartes
                        fecha={datos.fecha}
                        onChange={(valor) => set('fecha', valor)}
                        error={errores.fecha}
                    />{' '}
                    a las {campo(props, 'hora', 'hora', 'w-28', 'time')} HS,
                    personal del Servicio Local de Promoción y Protección de
                    niños, niñas y adolescentes se hace presente en el domicilio
                    del Sr./a{' '}
                    {campo(
                        props,
                        'compareciente_nombre',
                        'apellido y nombre',
                        'w-72',
                    )}
                    , quien acredita su identidad con DNI nº{' '}
                    {campo(props, 'compareciente_dni', 'DNI', 'w-32')},
                    domiciliado en calle{' '}
                    {campo(
                        props,
                        'compareciente_domicilio',
                        'calle y número',
                        'w-56',
                    )}
                    , de barrio{' '}
                    <SelectorBarrio
                        value={datos.compareciente_barrio}
                        onChange={(valor) => set('compareciente_barrio', valor)}
                        barrios={props.barrios}
                        ancho="w-40"
                        error={errores.compareciente_barrio}
                    />
                    , teléfono{' '}
                    {campo(props, 'compareciente_telefono', 'teléfono', 'w-36')}
                    . En relación al NNYA{' '}
                    {campo(
                        props,
                        'nino_nombre',
                        'apellido y nombre del NNyA',
                        'w-72',
                    )}
                    , que concurre a la{' '}
                    <SelectorBarrio
                        value={datos.nino_escuela}
                        onChange={(valor) => set('nino_escuela', valor)}
                        barrios={props.barrios}
                        opciones={props.escuelas}
                        etiqueta="escuela"
                        placeholder="escuela"
                        ancho="w-56"
                        error={errores.nino_escuela}
                    />{' '}
                    acreditando el vínculo de{' '}
                    {campo(props, 'vinculo', 'vínculo', 'w-36')}.
                </p>
                <p className="mt-4 text-justify">
                    Quien puesto en conocimiento de los principios rectores del
                    Sistema de Promoción y Protección de derechos creado por ley
                    13.298 y el decreto 300/05 hace la siguiente manifestación:
                </p>
            </div>
            <div className="mt-3 border border-black px-3 py-2 sm:px-5">
                <Relato
                    value={datos.relato}
                    onChange={(valor) => set('relato', valor)}
                    placeholder="Escribí la manifestación…"
                    error={errores.relato}
                    filas={10}
                />
            </div>
            <div className="mt-10 grid gap-10 font-bold sm:grid-cols-2">
                <Firma
                    rol="entrevistado"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={errores}
                >
                    <span>Firma del Sr./a</span>
                    <Aclaracion rol="entrevistado" props={props} />
                </Firma>
                <Firma
                    rol="autoridad"
                    alineacion="items-start text-left sm:ml-auto"
                    firmas={props.firmas}
                    setFirma={props.setFirma}
                    errores={errores}
                >
                    <span>Firma y sello de la autoridad del Servicio</span>
                    <Aclaracion rol="autoridad" props={props} />
                </Firma>
            </div>
        </Hoja>
    );
}

function Telefonica(props: Props) {
    const { datos, set, errores } = props;

    return (
        <Hoja className="font-['Arial',sans-serif] text-[length:calc(14px_+_2px)] leading-8">
            <Membrete />
            <p className="mt-6 text-center font-bold underline">
                ACTA TELEFÓNICA
            </p>
            <p className="mt-2 font-bold">
                Op. Responsable:
                <span className="font-normal">
                    {operador(props, 'operador/a', 'w-56')}
                </span>
            </p>
            <p className="mt-2 text-justify indent-12">
                En la ciudad de San Nicolás de los Arroyos en sede del Servicio
                Local de Promoción y Protección de Derechos de Niños y
                Adolescentes de San Nicolás de los Arroyos, ubicado en calle
                Brown y Lazarte,{' '}
                <FechaEnPartes
                    fecha={datos.fecha}
                    onChange={(valor) => set('fecha', valor)}
                    error={errores.fecha}
                    conDe
                />
                , a las {campo(props, 'hora', 'hora', 'w-28', 'time')} horas, se
                mantiene comunicación telefónica con{' '}
                {campo(
                    props,
                    'compareciente_nombre',
                    'apellido y nombre',
                    'w-72',
                )}
                , DNI Nº {campo(props, 'compareciente_dni', 'DNI', 'w-32')},
                teléfono{' '}
                {campo(props, 'compareciente_telefono', 'teléfono', 'w-36')},{' '}
                {campo(props, 'vinculo', 'vínculo (ej.: madre)', 'w-40')} del
                NNyA{' '}
                {campo(
                    props,
                    'nino_nombre',
                    'apellido y nombre del NNyA',
                    'w-72',
                )}{' '}
                a los fines de formular la siguiente manifestación:
            </p>
            <div className="mt-6 border border-black px-3 py-2">
                <Relato
                    value={datos.relato}
                    onChange={(valor) => set('relato', valor)}
                    placeholder="Escribí la manifestación…"
                    error={errores.relato}
                    filas={12}
                />
            </div>
        </Hoja>
    );
}

function Citacion(props: Props) {
    const { datos, set, errores } = props;

    return (
        <Hoja className="font-['Arial',sans-serif] text-[length:calc(14px_+_2px)] leading-7">
            <Membrete />
            <p className="mt-8 text-center font-['Times_New_Roman',serif] font-bold">
                SERVICIO LOCAL DE NIÑEZ Y ADOLESCENCIA
            </p>
            <p className="mt-2 text-center font-['Times_New_Roman',serif] font-bold">
                (PRODENYA)
            </p>
            <p className="mt-10 text-right">
                San Nicolás,
                <Blank
                    type="date"
                    value={datos.fecha}
                    onChange={(valor) => set('fecha', valor)}
                    placeholder="fecha"
                    ancho="w-40"
                    error={errores.fecha}
                />
                <span className="sr-only">{fechaLarga(datos.fecha)}</span>
            </p>
            <p className="mt-4 font-bold">De nuestra mayor consideración:</p>
            <p className="mt-1 text-justify indent-24">
                Nos dirigimos a Usted a efectos de hacerle saber que deberá
                presentarse a este Servicio de Promoción y Protección de los
                Derechos de los Niños, Niñas y Adolescencia de la Municipalidad
                de San Nicolás de los Arroyos, dependiente de la Dirección de
                Acción Social, el cual funciona en <b>Lazarte y Brown</b>, día{' '}
                <Blank
                    type="date"
                    value={datos.citacion_dia}
                    onChange={(valor) => set('citacion_dia', valor)}
                    placeholder="día de la citación"
                    ancho="w-40"
                    error={errores.citacion_dia}
                />{' '}
                en el horario de{' '}
                {campo(props, 'citacion_horario', 'ej.: 9 a 11 hs', 'w-36')} a
                fin de tratar un tema de su interés. Presentarse con el niño/a o
                adolescente y DNI.
            </p>
            <p className="mt-6 text-justify indent-24 font-bold">
                En caso de no poder concurrir comunicarse al teléfono 4489181 en
                el mismo horario, para posibilitar la concreción de una nueva
                cita.
            </p>
            <p className="mt-4 text-right">
                Sin otro particular le saludamos muy atentamente.
            </p>
        </Hoja>
    );
}

function Constancia(props: Props) {
    const { datos, set, errores } = props;

    return (
        <Hoja className="font-['Times_New_Roman',serif] text-[length:calc(16px_+_2px)] leading-7">
            <Membrete />
            <p className="mt-8 text-center font-bold">
                Municipalidad de San Nicolás
            </p>
            <p className="mt-2 text-center font-bold">
                Servicio Local de Niñez y Adolescencia
            </p>
            <p className="mt-8 text-right">
                San Nicolás,
                <Blank
                    type="date"
                    value={datos.fecha}
                    onChange={(valor) => set('fecha', valor)}
                    placeholder="fecha"
                    ancho="w-40"
                    error={errores.fecha}
                />
            </p>
            <p className="mt-4">A QUIEN CORRESPONDA:</p>
            <p className="mt-3 text-justify indent-12">
                Desde el Servicio Local de Niñez y Adolescencia (PRODENyA) San
                Nicolás de los Arroyos, nos dirigimos a usted a efectos de
                informar que{' '}
                <b>
                    {campo(
                        props,
                        'compareciente_nombre',
                        'APELLIDO Y NOMBRE',
                        'w-72',
                    )}{' '}
                    {campo(props, 'compareciente_dni', 'DNI', 'w-32')}
                </b>{' '}
                asistió a sede de este Servicio a entrevista con el Equipo
                Técnico, para tratar temas de su interés.-
            </p>
            <p className="mt-4 text-right">Sin más, saludo atentamente.</p>
        </Hoja>
    );
}

export default function ActaHoja(props: Props) {
    switch (props.tipo) {
        case 'inicial':
            return <Inicial {...props} />;
        case 'domiciliaria':
            return <Domiciliaria {...props} />;
        case 'telefonica':
            return <Telefonica {...props} />;
        case 'citacion':
            return <Citacion {...props} />;
        default:
            return <Constancia {...props} />;
    }
}
