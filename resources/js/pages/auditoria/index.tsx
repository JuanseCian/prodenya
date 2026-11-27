import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    Award,
    CalendarDays,
    ChevronDown,
    ClipboardList,
    Code2,
    FileSignature,
    FileText,
    FolderOpen,
    Globe,
    ListChecks,
    Mail,
    Paperclip,
    Search,
    Settings2,
    UserRound,
    Users,
    X,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { useEffect, useMemo, useRef, useState } from 'react';

import Paginacion from '@/components/paginacion-listado';
import type { Paginador } from '@/components/paginacion-listado';
import auditoria from '@/routes/admin/auditoria';
import { show as verLegajo } from '@/routes/legajos';

interface Evento {
    id: number;
    tipo_movimiento: 'auditoria' | 'puntaje';
    evento: string;
    seccion: string;
    recurso: string;
    recurso_id: number | null;
    usuario: string;
    creado_en: string | null;
    datos_antes: Record<string, unknown> | null;
    datos_despues: Record<string, unknown> | null;
    puntos: number | null;
    descripcion: string | null;
    ip_origen: string | null;
}

type Referencias = Record<string, Record<string, string>>;

interface Props {
    eventos: Paginador<Evento>;
    filtros: { buscar: string | null; seccion: string };
    secciones: Array<{ value: string; label: string }>;
    referencias: Referencias;
}

interface Cambio {
    campo: string;
    etiqueta: string;
    antes: string | null;
    despues: string | null;
    esArchivo: boolean;
    esTextoLargo: boolean;
}

interface Dato {
    campo: string;
    etiqueta: string;
    valor: string;
    esTextoLargo: boolean;
}

/** Nombre legible y género gramatical de cada modelo auditado. */
const RECURSOS: Record<string, { nombre: string; femenino: boolean }> = {
    Acta: { nombre: 'acta', femenino: true },
    AgendaEvento: { nombre: 'evento de agenda', femenino: false },
    AuhArchivo: { nombre: 'archivo AUH', femenino: false },
    Conversacion: { nombre: 'conversación', femenino: true },
    CudArchivo: { nombre: 'archivo CUD', femenino: false },
    DerivacionCaso: { nombre: 'derivación', femenino: true },
    Intervencion: { nombre: 'intervención', femenino: true },
    Legajo: { nombre: 'legajo', femenino: false },
    MailMessage: { nombre: 'correo', femenino: false },
    Mensaje: { nombre: 'mensaje', femenino: false },
    MensajeRecurso: { nombre: 'adjunto de mensaje', femenino: false },
    Persona: { nombre: 'persona', femenino: true },
    Tarea: { nombre: 'tarea', femenino: true },
    User: { nombre: 'usuario', femenino: false },
};

/** Verbo en pasado para cada acción registrada. */
const VERBOS: Record<string, string> = {
    creado: 'creó',
    creada: 'creó',
    editado: 'editó',
    actualizada: 'actualizó',
    actualizado: 'actualizó',
    eliminado: 'eliminó',
    eliminada: 'eliminó',
    descargada: 'descargó',
    descargado: 'descargó',
    registrada: 'registró',
    vinculada: 'vinculó',
    firmada: 'firmó',
    leida: 'leyó',
    archivo_cargado: 'cargó',
    archivo_descargado: 'descargó',
    archivo_eliminado: 'eliminó',
    planilla_firmada: 'firmó la planilla de',
    planilla_generada: 'generó la planilla de',
    responsable_asignado: 'asignó un responsable al',
    reemplazado: 'reemplazó',
};

const ESTILO_SECCION: Record<string, { icon: LucideIcon; className: string }> =
    {
        usuarios: { icon: Users, className: 'bg-[#eef1fb] text-[#4b5fa8]' },
        legajos: {
            icon: FolderOpen,
            className: 'bg-[#e9f4ee] text-[#397461]',
        },
        personas: {
            icon: UserRound,
            className: 'bg-[#eaf3f7] text-[#2f6b86]',
        },
        intervenciones: {
            icon: ClipboardList,
            className: 'bg-[#f2eefa] text-[#6b4fa0]',
        },
        actas: {
            icon: FileSignature,
            className: 'bg-[#fbf0ea] text-[#a05a35]',
        },
        tareas: { icon: ListChecks, className: 'bg-[#edf6f6] text-[#2f7c7c]' },
        comunicaciones: {
            icon: Mail,
            className: 'bg-[#eef4fb] text-[#3a6ea5]',
        },
        puntaje: { icon: Award, className: 'bg-[#fff3dc] text-[#a56e24]' },
        sistema: { icon: Settings2, className: 'bg-[#f1f5f3] text-[#5e747d]' },
    };

/** Nombre que ve el usuario para cada campo guardado. */
const ETIQUETAS: Record<string, string> = {
    nombre: 'Nombre',
    apellido: 'Apellido',
    name: 'Nombre',
    dni: 'DNI',
    fecha_nacimiento: 'Fecha de nacimiento',
    genero: 'Género',
    email: 'Correo electrónico',
    telefono: 'Teléfono',
    profesion: 'Profesión',
    direccion_calle: 'Calle',
    direccion_numero: 'Número',
    direccion_piso_depto: 'Piso / depto.',
    barrio_id: 'Barrio',
    localidad_id: 'Localidad',
    observaciones: 'Observaciones',
    activo: 'Activo',
    estado: 'Estado',
    estado_cuenta: 'Estado de la cuenta',
    rol_id: 'Rol',
    nivel_riesgo: 'Nivel de riesgo',
    numero_legajo: 'Número de legajo',
    origen_derivacion: 'Quién lo derivó',
    profesional_id: 'Profesional a cargo',
    persona_id: 'Persona',
    legajo_id: 'Legajo',
    fecha_apertura: 'Fecha de apertura',
    fecha_cierre: 'Fecha de cierre',
    retencion_hasta: 'Conservar hasta',
    detalle: 'Detalle',
    fecha_intervencion: 'Fecha de la intervención',
    hora_intervencion: 'Hora de la intervención',
    tipo_intervencion_id: 'Tipo de intervención',
    firma_nombre: 'Firma del profesional',
    firma_aclaracion: 'Aclaración del profesional',
    firmada_en: 'Firmada el',
    firma_archivo_path: 'Firma del profesional',
    firma_entrevistado_nombre: 'Firma de la persona entrevistada',
    firma_entrevistado_aclaracion: 'Aclaración de la persona entrevistada',
    firma_entrevistado_en: 'Firmada por la persona entrevistada el',
    firma_entrevistado_archivo_path: 'Firma de la persona entrevistada',
    tipo: 'Tipo',
    firmado: 'Firmada',
    intervencion_id: 'Intervención',
    titulo: 'Título',
    lugar: 'Lugar',
    notas: 'Notas',
    enlace: 'Enlace',
    fecha_inicio: 'Empieza',
    fecha_fin: 'Termina',
    visibilidad: 'Visible para',
    participantes_externos: 'Participantes externos',
    modo: 'Modalidad',
    motivo: 'Motivo',
    motivo_rechazo: 'Motivo del rechazo',
    fecha_limite: 'Fecha límite',
    solicitado_por: 'Solicitada por',
    destinatario_id: 'Enviada a',
    aceptada_en: 'Aceptada el',
    rechazada_en: 'Rechazada el',
    cancelada_en: 'Cancelada el',
    responsables_reemplazados: 'Responsables reemplazados',
    responsables_anteriores: 'Responsables anteriores',
    usuario: 'Profesional asignado',
    firmantes: 'Firmantes',
    centro: 'Por el centro',
    solicitante: 'Solicitante',
    subject: 'Asunto',
    from_name: 'Remitente',
    from_email: 'Correo del remitente',
    to_email: 'Para',
    cc_email: 'Con copia a',
    is_read: 'Leído',
    is_starred: 'Destacado',
    has_attachments: 'Tiene adjuntos',
    folder: 'Carpeta',
    direction: 'Dirección',
    prioridad: 'Prioridad',
    clasificacion_categoria: 'Categoría',
    clasificacion_estado: 'Clasificación',
    received_at: 'Recibido el',
    sent_at: 'Enviado el',
    body_text: 'Mensaje',
    cuerpo: 'Mensaje',
    tipo_recurso: 'Se compartió',
    ultimo_mensaje_en: 'Último mensaje',
    descripcion: 'Descripción',
};

/** Campos internos que no le dicen nada a quien usa el sistema. */
const CAMPOS_OCULTOS = new Set([
    'id',
    'creado_en',
    'actualizado_en',
    'created_at',
    'updated_at',
    'deleted_at',
    'creado_por',
    'actualizado_por',
    'updated_by',
    'deleted_by',
    'password',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'avatar_path',
    'body_html',
    'preview',
    'thread_id',
    'message_id',
    'provider',
    'provider_message_id',
    'labels',
    'synced_at',
    'mailbox_uid',
    'in_reply_to',
    'attachments',
    'grupo_copia_id',
    'grupo_derivacion_id',
    'intervencion_origen_id',
    'clasificacion_metodo',
    'clasificacion_motivos',
    'clasificacion_confianza',
    'clasificado_en',
    'clasificado_por',
    'clasificacion_revisado_en',
    'clasificacion_revisado_por',
    'latitud',
    'longitud',
    'geocodificacion_precision',
    'geocodificacion_consulta_hash',
    'geocodificado_en',
    'mail_message_id',
    'derivacion_id',
    'conversacion_id',
    'mensaje_id',
    'recurso_id',
]);

/** Campos que guardan un identificador y se muestran con el nombre real. */
const CAMPOS_REFERENCIA: Record<string, string> = {
    profesional_id: 'usuarios',
    destinatario_id: 'usuarios',
    solicitado_por: 'usuarios',
    usuario_id: 'usuarios',
    aprobado_por: 'usuarios',
    eliminacion_autorizada_por: 'usuarios',
    legajo_id: 'legajos',
    persona_id: 'personas',
    localidad_id: 'localidades',
    barrio_id: 'barrios',
    tipo_intervencion_id: 'tipos_intervencion',
    rol_id: 'roles',
};

const CAMPOS_SI_NO = new Set([
    'activo',
    'firmado',
    'is_read',
    'is_starred',
    'has_attachments',
]);

const CAMPOS_CODIGO = new Set([
    'genero',
    'estado',
    'estado_cuenta',
    'tipo',
    'modo',
    'visibilidad',
    'folder',
    'direction',
    'prioridad',
    'clasificacion_categoria',
    'clasificacion_estado',
    'tipo_recurso',
    'nivel_riesgo',
]);

const CAMPOS_TEXTO_LARGO = new Set([
    'detalle',
    'observaciones',
    'body_text',
    'cuerpo',
    'motivo',
    'motivo_rechazo',
    'notas',
    'descripcion',
]);

/** Traducción de los códigos internos más comunes. */
const CODIGOS: Record<string, string> = {
    NO_ESPECIFICA: 'No especifica',
    MASCULINO: 'Masculino',
    FEMENINO: 'Femenino',
    reunion: 'Reunión',
    programado: 'Programado',
    personal: 'Solo para mí',
    equipo: 'Todo el equipo',
    adicional: 'Se suma a los responsables actuales',
    reemplazo: 'Reemplaza a los responsables actuales',
    pendiente: 'Pendiente',
    aceptada: 'Aceptada',
    rechazada: 'Rechazada',
    cancelada: 'Cancelada',
    inbox: 'Bandeja de entrada',
    sent: 'Enviados',
    incoming: 'Recibido',
    outgoing: 'Enviado',
    directa: 'Conversación directa',
    nota_elevacion: 'Nota de elevación',
    inicial: 'Inicial',
    sugerido_automaticamente: 'Sugerida automáticamente',
    otras_comunicaciones: 'Otras comunicaciones',
};

const VERBOS_EDICION = new Set([
    'editado',
    'actualizado',
    'actualizada',
    'reemplazado',
]);

const capitalizar = (texto: string) =>
    texto.charAt(0).toUpperCase() + texto.slice(1);

const etiquetaCampo = (campo: string) =>
    ETIQUETAS[campo] ??
    capitalizar(
        campo
            .replace(/_(id|path)$/, '')
            .replace(/_en$/, '')
            .replace(/_/g, ' '),
    );

const formatoFecha = new Intl.DateTimeFormat('es-AR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

const formatoFechaHora = new Intl.DateTimeFormat('es-AR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

/** Los textos JSON guardados como string se leen igual que los objetos. */
const interpretar = (valor: unknown): unknown => {
    if (typeof valor !== 'string') {
        return valor;
    }

    const texto = valor.trim();

    if (/^[[{]/.test(texto)) {
        try {
            return JSON.parse(texto);
        } catch {
            return valor;
        }
    }

    return valor;
};

const esVacio = (valor: unknown) =>
    valor === null ||
    valor === undefined ||
    valor === '' ||
    (Array.isArray(valor) && valor.length === 0) ||
    (typeof valor === 'object' &&
        !Array.isArray(valor) &&
        Object.keys(valor as object).length === 0);

const formatearTexto = (texto: string): string => {
    const fecha = texto.match(
        /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::\d{2})?(\.\d+)?(Z)?)?$/,
    );

    if (fecha) {
        const [, anio, mes, dia, hora, minuto, , utc] = fecha;
        const soloFecha = !hora || (hora === '00' && minuto === '00');

        if (soloFecha) {
            return formatoFecha.format(
                new Date(Number(anio), Number(mes) - 1, Number(dia)),
            );
        }

        const instante = utc
            ? new Date(texto)
            : new Date(
                  Number(anio),
                  Number(mes) - 1,
                  Number(dia),
                  Number(hora),
                  Number(minuto),
              );

        return formatoFechaHora.format(instante);
    }

    const hora = texto.match(/^(\d{2}):(\d{2})(:\d{2})?$/);

    if (hora) {
        return `${hora[1]}:${hora[2]} h`;
    }

    if (/^-?\d+\.\d+$/.test(texto)) {
        return String(Number(texto));
    }

    return texto;
};

/** Convierte cualquier valor guardado en un texto que entiende cualquier persona. */
const formatearValor = (
    campo: string,
    valorCrudo: unknown,
    referencias: Referencias,
): string | null => {
    const valor = interpretar(valorCrudo);

    if (esVacio(valor)) {
        return null;
    }

    if (campo.endsWith('_path')) {
        return 'Cargada';
    }

    const tablaReferencia = CAMPOS_REFERENCIA[campo];

    if (tablaReferencia && /^\d+$/.test(String(valor))) {
        return (
            referencias[tablaReferencia]?.[String(valor)] ??
            `Registro n.º ${valor} (ya no existe)`
        );
    }

    if (
        typeof valor === 'boolean' ||
        (CAMPOS_SI_NO.has(campo) && /^[01]$/.test(String(valor)))
    ) {
        return valor === true || String(valor) === '1' ? 'Sí' : 'No';
    }

    if (Array.isArray(valor)) {
        return valor
            .map((item) => formatearValor(campo, item, referencias))
            .filter(Boolean)
            .join(', ');
    }

    if (typeof valor === 'object') {
        return Object.entries(valor as Record<string, unknown>)
            .map(([clave, item]) => {
                const texto = formatearValor(clave, item, referencias);

                return texto ? `${etiquetaCampo(clave)}: ${texto}` : null;
            })
            .filter(Boolean)
            .join(' · ');
    }

    const texto = String(valor);

    if (CAMPOS_CODIGO.has(campo)) {
        return (
            CODIGOS[texto] ??
            capitalizar(texto.toLowerCase().replace(/_/g, ' '))
        );
    }

    return formatearTexto(texto);
};

const camposVisibles = (datos: Record<string, unknown>) =>
    Object.keys(datos).filter(
        (campo) =>
            !CAMPOS_OCULTOS.has(campo) &&
            !(campo === 'usuario_id' && 'usuario' in datos),
    );

const esEdicion = (evento: Evento) => {
    const accion = evento.evento.split('.').at(-1) ?? '';

    return (
        VERBOS_EDICION.has(accion) &&
        evento.datos_antes !== null &&
        evento.datos_despues !== null
    );
};

const calcularCambios = (
    evento: Evento,
    referencias: Referencias,
): Cambio[] => {
    const antes = evento.datos_antes ?? {};
    const despues = evento.datos_despues ?? {};
    const campos = Array.from(
        new Set([...camposVisibles(antes), ...camposVisibles(despues)]),
    );

    return campos.flatMap((campo) => {
        const esArchivo = campo.endsWith('_path');
        const valorAntes = formatearValor(campo, antes[campo], referencias);
        const valorDespues = formatearValor(campo, despues[campo], referencias);
        const cambio = esArchivo
            ? (antes[campo] ?? null) !== (despues[campo] ?? null)
            : valorAntes !== valorDespues;

        if (!cambio) {
            return [];
        }

        return [
            {
                campo,
                etiqueta: etiquetaCampo(campo),
                antes: valorAntes,
                despues: valorDespues,
                esArchivo,
                esTextoLargo: CAMPOS_TEXTO_LARGO.has(campo),
            },
        ];
    });
};

const listarDatos = (
    datos: Record<string, unknown> | null,
    referencias: Referencias,
): Dato[] =>
    datos
        ? camposVisibles(datos).flatMap((campo) => {
              const valor = formatearValor(campo, datos[campo], referencias);

              return valor
                  ? [
                        {
                            campo,
                            etiqueta: etiquetaCampo(campo),
                            valor,
                            esTextoLargo:
                                CAMPOS_TEXTO_LARGO.has(campo) ||
                                valor.length > 90,
                        },
                    ]
                  : [];
          })
        : [];

const nombreDelRecurso = (evento: Evento, referencias: Referencias): string => {
    const datos = { ...evento.datos_antes, ...evento.datos_despues };
    const id = evento.recurso_id ? String(evento.recurso_id) : null;
    const texto = (valor: unknown) =>
        typeof valor === 'string' && valor.trim() ? valor.trim() : null;

    const nombre = (() => {
        switch (evento.recurso) {
            case 'Persona':
                return (
                    (id && referencias.personas?.[id]) ||
                    texto(
                        [datos.nombre, datos.apellido]
                            .filter(Boolean)
                            .join(' '),
                    )
                );
            case 'Legajo':
                return (
                    (id && referencias.legajos?.[id]) ||
                    texto(datos.numero_legajo)
                );
            case 'User':
                return (id && referencias.usuarios?.[id]) || texto(datos.name);
            case 'MailMessage': {
                const asunto = texto(datos.subject);

                return asunto ? `“${asunto}”` : null;
            }
            case 'AgendaEvento': {
                const titulo = texto(datos.titulo);

                return titulo ? `“${titulo}”` : null;
            }
            default:
                return null;
        }
    })();

    return nombre ?? (id ? `n.º ${id}` : '');
};

const legajoRelacionado = (
    evento: Evento,
    referencias: Referencias,
): { id: number; numero: string } | null => {
    if (evento.recurso === 'Legajo') {
        return null;
    }

    const valor =
        evento.datos_despues?.legajo_id ?? evento.datos_antes?.legajo_id;

    if (!valor || !/^\d+$/.test(String(valor))) {
        return null;
    }

    const numero = referencias.legajos?.[String(valor)];

    return numero ? { id: Number(valor), numero } : null;
};

const describirAccion = (evento: Evento): string => {
    const accion = evento.evento.includes('.')
        ? evento.evento.split('.').slice(1).join('.')
        : evento.evento;

    return VERBOS[accion] ?? accion.replace(/_/g, ' ');
};

const inicioDelDia = (fecha: Date) =>
    new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate()).getTime();

const etiquetaDia = (valor: string | null): string => {
    if (!valor) {
        return 'Sin fecha';
    }

    const fecha = new Date(valor);
    const dias = Math.round(
        (inicioDelDia(new Date()) - inicioDelDia(fecha)) / 86_400_000,
    );

    if (dias === 0) {
        return 'Hoy';
    }

    if (dias === 1) {
        return 'Ayer';
    }

    return capitalizar(
        new Intl.DateTimeFormat('es-AR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year:
                fecha.getFullYear() === new Date().getFullYear()
                    ? undefined
                    : 'numeric',
        }).format(fecha),
    );
};

const formatoHora = (valor: string | null) =>
    valor
        ? new Intl.DateTimeFormat('es-AR', { timeStyle: 'short' }).format(
              new Date(valor),
          )
        : '';

const formatoCompleto = (valor: string | null) =>
    valor
        ? capitalizar(
              new Intl.DateTimeFormat('es-AR', {
                  dateStyle: 'full',
                  timeStyle: 'short',
              }).format(new Date(valor)),
          )
        : 'Sin fecha';

function TextoLargo({
    texto,
    className = '',
}: {
    texto: string;
    className?: string;
}) {
    const [expandido, setExpandido] = useState(false);
    const esLargo = texto.length > 220;

    return (
        <div className={className}>
            <p
                className={`text-sm leading-relaxed whitespace-pre-line text-[#294455] ${esLargo && !expandido ? 'line-clamp-3' : ''}`}
            >
                {texto}
            </p>
            {esLargo && (
                <button
                    type="button"
                    onClick={() => setExpandido(!expandido)}
                    className="mt-1 text-xs font-semibold text-[#397461] hover:underline"
                >
                    {expandido ? 'Ver menos' : 'Ver texto completo'}
                </button>
            )}
        </div>
    );
}

function DescripcionArchivo({ cambio }: { cambio: Cambio }) {
    const quien = cambio.etiqueta.toLowerCase();

    if (!cambio.antes) {
        return <p className="text-sm text-[#294455]">Se cargó la {quien}.</p>;
    }

    if (!cambio.despues) {
        return <p className="text-sm text-[#9b5964]">Se quitó la {quien}.</p>;
    }

    return <p className="text-sm text-[#294455]">Se reemplazó la {quien}.</p>;
}

function ListaCambios({ cambios }: { cambios: Cambio[] }) {
    return (
        <ul className="divide-y divide-[#edf3f0]">
            {cambios.map((cambio) => (
                <li
                    key={cambio.campo}
                    className="grid gap-1 px-4 py-2.5 sm:grid-cols-[11rem_1fr] sm:gap-4"
                >
                    <span className="text-xs font-semibold text-[#5e747d] sm:pt-0.5">
                        {cambio.etiqueta}
                    </span>
                    {cambio.esArchivo ? (
                        <DescripcionArchivo cambio={cambio} />
                    ) : cambio.esTextoLargo ? (
                        <div className="space-y-2">
                            <div className="rounded-md border-l-2 border-[#e3c1c7] bg-[#fdf6f7] px-3 py-2">
                                <p className="mb-0.5 text-[10px] font-bold tracking-wider text-[#9b5964] uppercase">
                                    Decía
                                </p>
                                <TextoLargo
                                    texto={cambio.antes ?? 'Estaba vacío'}
                                />
                            </div>
                            <div className="rounded-md border-l-2 border-[#9fd0b3] bg-[#f2faf5] px-3 py-2">
                                <p className="mb-0.5 text-[10px] font-bold tracking-wider text-[#39875e] uppercase">
                                    Ahora dice
                                </p>
                                <TextoLargo
                                    texto={cambio.despues ?? 'Quedó vacío'}
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="flex flex-wrap items-center gap-2 text-sm">
                            {cambio.antes ? (
                                <span className="rounded-md bg-[#fdf1f3] px-2 py-0.5 text-[#9b5964] line-through decoration-[#9b5964]/40">
                                    {cambio.antes}
                                </span>
                            ) : (
                                <span className="text-xs text-[#9aa9ae] italic">
                                    Sin dato
                                </span>
                            )}
                            <ArrowRight
                                className="size-3.5 text-[#a7b4b9]"
                                aria-label="cambió a"
                            />
                            {cambio.despues ? (
                                <span className="rounded-md bg-[#e8f5ed] px-2 py-0.5 font-medium text-[#2c6e4b]">
                                    {cambio.despues}
                                </span>
                            ) : (
                                <span className="text-xs text-[#9b5964] italic">
                                    Se borró
                                </span>
                            )}
                        </div>
                    )}
                </li>
            ))}
        </ul>
    );
}

function ListaDatos({ datos }: { datos: Dato[] }) {
    const cortos = datos.filter((dato) => !dato.esTextoLargo);
    const largos = datos.filter((dato) => dato.esTextoLargo);

    return (
        <div className="space-y-3 px-4 py-3">
            {cortos.length > 0 && (
                <dl className="grid gap-x-6 gap-y-2.5 sm:grid-cols-2">
                    {cortos.map((dato) => (
                        <div key={dato.campo} className="min-w-0">
                            <dt className="text-[11px] font-semibold text-[#8a9aa1]">
                                {dato.etiqueta}
                            </dt>
                            <dd className="truncate text-sm text-[#294455]">
                                {dato.valor}
                            </dd>
                        </div>
                    ))}
                </dl>
            )}
            {largos.map((dato) => (
                <div key={dato.campo}>
                    <p className="text-[11px] font-semibold text-[#8a9aa1]">
                        {dato.etiqueta}
                    </p>
                    <TextoLargo
                        texto={dato.valor}
                        className="mt-0.5 rounded-md bg-white px-3 py-2 ring-1 ring-[#e8f0ec]"
                    />
                </div>
            ))}
        </div>
    );
}

function DetalleEvento({
    evento,
    referencias,
    cambios,
}: {
    evento: Evento;
    referencias: Referencias;
    cambios: Cambio[];
}) {
    const [verTecnico, setVerTecnico] = useState(false);
    const edicion = esEdicion(evento);
    const datosNuevos = useMemo(
        () => listarDatos(evento.datos_despues, referencias),
        [evento, referencias],
    );
    const datosPrevios = useMemo(
        () => (edicion ? [] : listarDatos(evento.datos_antes, referencias)),
        [edicion, evento, referencias],
    );
    const esCreacion = evento.datos_antes === null;
    const esPuntaje = evento.tipo_movimiento === 'puntaje';

    let titulo = 'Información de la acción';
    let contenido: ReactNode;

    if (edicion) {
        titulo =
            cambios.length === 1
                ? 'Se modificó 1 dato'
                : `Se modificaron ${cambios.length} datos`;
        contenido =
            cambios.length > 0 ? (
                <ListaCambios cambios={cambios} />
            ) : (
                <p className="px-4 py-3 text-sm text-[#71828a]">
                    El sistema guardó el registro de nuevo, pero ningún dato
                    visible cambió. Suele pasar con actualizaciones automáticas,
                    como la sincronización del correo.
                </p>
            );
    } else if (datosNuevos.length > 0 || datosPrevios.length > 0) {
        titulo = esPuntaje
            ? 'Datos del movimiento'
            : esCreacion
              ? 'Datos que se cargaron'
              : 'Información de la acción';
        contenido = (
            <>
                <ListaDatos datos={datosNuevos} />
                {datosPrevios.length > 0 && (
                    <div className="border-t border-[#edf3f0]">
                        <p className="px-4 pt-3 text-[11px] font-bold tracking-wider text-[#8a9aa1] uppercase">
                            Cómo estaba antes
                        </p>
                        <ListaDatos datos={datosPrevios} />
                    </div>
                )}
            </>
        );
    } else {
        contenido = (
            <p className="px-4 py-3 text-sm text-[#71828a]">
                {esPuntaje
                    ? `El sistema otorgó estos puntos automáticamente${evento.descripcion ? ` por: ${evento.descripcion.replace(/\.$/, '').toLowerCase()}` : ''}.`
                    : 'Esta acción no modificó ningún dato. Quedó registrada para saber quién la hizo y cuándo.'}
            </p>
        );
    }

    return (
        <div className="mx-4 mb-3 overflow-hidden rounded-lg border border-[#e1ece6] bg-[#fafcfb] sm:mr-5 sm:ml-[4.25rem]">
            <p className="border-b border-[#e8f0ec] bg-white px-4 py-2 text-xs font-bold text-[#294455]">
                {titulo}
            </p>
            {contenido}
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-[#e8f0ec] px-4 py-2 text-xs text-[#8a9aa1]">
                <span className="inline-flex items-center gap-1">
                    <CalendarDays className="size-3.5" />
                    {formatoCompleto(evento.creado_en)}
                </span>
                {evento.ip_origen && (
                    <span
                        className="inline-flex items-center gap-1"
                        title="Dirección de la conexión desde donde se hizo la acción"
                    >
                        <Globe className="size-3.5" />
                        Desde la conexión {evento.ip_origen}
                    </span>
                )}
                {(evento.datos_antes || evento.datos_despues) && (
                    <button
                        type="button"
                        onClick={() => setVerTecnico(!verTecnico)}
                        className="ml-auto inline-flex items-center gap-1 hover:text-[#397461]"
                    >
                        <Code2 className="size-3.5" />
                        {verTecnico
                            ? 'Ocultar registro técnico'
                            : 'Registro técnico'}
                    </button>
                )}
            </div>
            {verTecnico && (
                <div className="grid gap-3 border-t border-[#e8f0ec] bg-white p-3 sm:grid-cols-2">
                    <p className="text-xs text-[#8a9aa1] sm:col-span-2">
                        Datos tal como los guarda el sistema. Útil solo para
                        soporte técnico.
                    </p>
                    {(['datos_antes', 'datos_despues'] as const).map(
                        (clave) => (
                            <div key={clave} className="min-w-0">
                                <p className="text-[10px] font-bold tracking-wider text-[#8a9aa1] uppercase">
                                    {clave === 'datos_antes'
                                        ? 'Antes'
                                        : 'Después'}
                                </p>
                                <pre className="mt-1 max-h-56 overflow-auto rounded bg-[#f6f8f7] p-2 font-mono text-[11px] leading-4 break-all whitespace-pre-wrap text-[#5e747d]">
                                    {evento[clave]
                                        ? JSON.stringify(evento[clave], null, 2)
                                        : 'Sin datos'}
                                </pre>
                            </div>
                        ),
                    )}
                </div>
            )}
        </div>
    );
}

function FilaEvento({
    evento,
    referencias,
    abierto,
    onToggle,
}: {
    evento: Evento;
    referencias: Referencias;
    abierto: boolean;
    onToggle: () => void;
}) {
    const estilo = ESTILO_SECCION[evento.seccion] ?? ESTILO_SECCION.sistema;
    const Icono = estilo.icon;
    const edicion = esEdicion(evento);
    const cambios = useMemo(
        () => (edicion ? calcularCambios(evento, referencias) : []),
        [edicion, evento, referencias],
    );
    const info = RECURSOS[evento.recurso];
    const articulo = info?.femenino ? 'la' : 'el';
    const nombreTipo = info?.nombre ?? evento.recurso.toLowerCase();
    const nombre = nombreDelRecurso(evento, referencias);
    const legajo = legajoRelacionado(evento, referencias);
    const esPuntaje = evento.tipo_movimiento === 'puntaje';
    const puntos = evento.puntos ?? 0;

    const resumen = edicion
        ? cambios.length === 0
            ? 'Actualización automática, sin cambios visibles'
            : `Cambió ${cambios
                  .slice(0, 3)
                  .map((cambio) => cambio.etiqueta.toLowerCase())
                  .join(
                      ', ',
                  )}${cambios.length > 3 ? ` y ${cambios.length - 3} más` : ''}`
        : null;

    return (
        <li>
            <div className="flex items-center gap-3 px-4 py-2.5 transition-colors hover:bg-[#f6fbf8] sm:px-5">
                <span
                    className={`flex size-8 shrink-0 items-center justify-center rounded-lg ${estilo.className}`}
                >
                    <Icono className="size-4" />
                </span>
                <button
                    type="button"
                    onClick={onToggle}
                    aria-expanded={abierto}
                    className="min-w-0 flex-1 text-left"
                >
                    <span className="block truncate text-sm text-[#405a66]">
                        <strong className="font-semibold text-[#162b3a]">
                            {evento.usuario}
                        </strong>{' '}
                        {esPuntaje ? (
                            <>
                                {puntos < 0 ? 'perdió' : 'sumó'}{' '}
                                <strong
                                    className={`font-semibold ${puntos < 0 ? 'text-[#9b5964]' : 'text-[#39875e]'}`}
                                >
                                    {Math.abs(puntos)}{' '}
                                    {Math.abs(puntos) === 1
                                        ? 'punto'
                                        : 'puntos'}
                                </strong>
                                {evento.descripcion
                                    ? ` por ${evento.descripcion.replace(/\.$/, '').toLowerCase()}`
                                    : ''}
                            </>
                        ) : (
                            <>
                                {describirAccion(evento)} {articulo}{' '}
                                {nombreTipo}{' '}
                                <span className="font-medium text-[#162b3a]">
                                    {nombre}
                                </span>
                            </>
                        )}
                    </span>
                    {(resumen || legajo) && (
                        <span className="mt-0.5 block truncate text-xs text-[#8a9aa1]">
                            {legajo && (
                                <span className="inline-flex items-center gap-1 text-[#5e747d]">
                                    <Paperclip className="size-3" />
                                    Legajo {legajo.numero}
                                </span>
                            )}
                            {legajo && resumen ? ' · ' : ''}
                            {resumen}
                        </span>
                    )}
                </button>
                {legajo && (
                    <Link
                        href={verLegajo(legajo.id).url}
                        className="hidden shrink-0 rounded-md px-2 py-1 text-xs font-semibold text-[#397461] hover:bg-[#e9f4ee] sm:inline-flex"
                    >
                        Abrir legajo
                    </Link>
                )}
                <time
                    dateTime={evento.creado_en ?? undefined}
                    title={formatoCompleto(evento.creado_en)}
                    className="shrink-0 text-xs text-[#8a9aa1] tabular-nums"
                >
                    {formatoHora(evento.creado_en)}
                </time>
                <button
                    type="button"
                    onClick={onToggle}
                    aria-label={abierto ? 'Ocultar detalle' : 'Ver detalle'}
                    className="shrink-0 rounded p-0.5 text-[#a7b4b9] hover:bg-[#eef4f1] hover:text-[#397461]"
                >
                    <ChevronDown
                        className={`size-4 transition-transform ${abierto ? 'rotate-180' : ''}`}
                    />
                </button>
            </div>
            {abierto && (
                <DetalleEvento
                    evento={evento}
                    referencias={referencias}
                    cambios={cambios}
                />
            )}
        </li>
    );
}

export default function Auditoria({
    eventos,
    filtros,
    secciones,
    referencias,
}: Props) {
    const [buscar, setBuscar] = useState(filtros.buscar ?? '');
    const [eventoAbierto, setEventoAbierto] = useState<string | null>(null);
    const primerRender = useRef(true);
    const seccion = filtros.seccion;

    const navegar = (nextBuscar: string, nextSeccion: string) => {
        const query: Record<string, string> = {};

        if (nextBuscar.trim()) {
            query.buscar = nextBuscar.trim();
        }

        if (nextSeccion !== 'todas') {
            query.seccion = nextSeccion;
        }

        router.get(
            auditoria.index.url({ query }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    useEffect(() => {
        if (primerRender.current) {
            primerRender.current = false;

            return;
        }

        const temporizador = window.setTimeout(
            () => navegar(buscar, seccion),
            350,
        );

        return () => window.clearTimeout(temporizador);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [buscar]);

    const grupos = useMemo(() => {
        const resultado: Array<{ dia: string; eventos: Evento[] }> = [];

        eventos.data.forEach((evento) => {
            const dia = etiquetaDia(evento.creado_en);
            const ultimo = resultado.at(-1);

            if (ultimo && ultimo.dia === dia) {
                ultimo.eventos.push(evento);
            } else {
                resultado.push({ dia, eventos: [evento] });
            }
        });

        return resultado;
    }, [eventos.data]);

    const hayFiltros = Boolean(filtros.buscar) || seccion !== 'todas';
    const nombreSeccion =
        secciones.find((item) => item.value === seccion)?.label ?? '';

    return (
        <>
            <Head title="Auditoría" />
            <main className="admin-page-shell min-h-screen bg-background px-4 py-6 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-5xl">
                    <header className="mb-5 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight text-[#162b3a]">
                                Auditoría
                            </h1>
                            <p className="mt-1 text-sm text-[#71828a]">
                                Quién hizo qué y cuándo. Tocá un movimiento para
                                ver exactamente qué cambió.
                            </p>
                        </div>
                        <span className="rounded-full bg-[#e9f4ee] px-3 py-1 text-xs font-semibold text-[#397461] tabular-nums">
                            {eventos.total.toLocaleString('es-AR')}{' '}
                            {eventos.total === 1 ? 'movimiento' : 'movimientos'}
                        </span>
                    </header>

                    <div className="overflow-hidden rounded-xl border border-[#dce8e3] bg-white shadow-[0_10px_30px_rgba(21,52,72,0.05)]">
                        <div className="sticky top-0 z-10 space-y-2.5 border-b border-[#e1ece6] bg-white/95 px-4 py-3 backdrop-blur sm:px-5">
                            <div className="relative">
                                <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-[#397461]/60" />
                                <input
                                    type="search"
                                    value={buscar}
                                    onChange={(event) =>
                                        setBuscar(event.target.value)
                                    }
                                    placeholder="Buscar por usuario, acción o tipo de registro…"
                                    className="h-9 w-full rounded-lg border border-[#dce8e3] bg-[#f8fbf9] pr-9 pl-9 text-sm text-[#294455] outline-none placeholder:text-[#9aa9ae] focus:border-[#7db493] focus:bg-white focus:ring-2 focus:ring-[#d8eee0]"
                                />
                                {buscar && (
                                    <button
                                        type="button"
                                        onClick={() => setBuscar('')}
                                        aria-label="Limpiar búsqueda"
                                        className="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-[#8a9aa1] hover:bg-[#eef4f1] hover:text-[#397461]"
                                    >
                                        <X className="size-3.5" />
                                    </button>
                                )}
                            </div>
                            <div className="-mx-1 flex gap-1 overflow-x-auto px-1 pb-0.5">
                                {secciones.map((item) => {
                                    const activa = seccion === item.value;
                                    const Icono =
                                        ESTILO_SECCION[item.value]?.icon;

                                    return (
                                        <button
                                            key={item.value}
                                            type="button"
                                            onClick={() =>
                                                navegar(buscar, item.value)
                                            }
                                            className={`inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition ${activa ? 'bg-[#397461] text-white' : 'text-[#5e747d] hover:bg-[#eef6f1] hover:text-[#397461]'}`}
                                        >
                                            {Icono && (
                                                <Icono className="size-3.5" />
                                            )}
                                            {item.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {eventos.data.length === 0 ? (
                            <div className="px-6 py-16 text-center">
                                <FileText className="mx-auto size-8 text-[#a7c7b6]" />
                                <p className="mt-3 text-sm font-semibold text-[#294455]">
                                    {hayFiltros
                                        ? 'No encontramos movimientos con esos filtros'
                                        : 'Todavía no hay movimientos registrados'}
                                </p>
                                <p className="mt-1 text-xs text-[#71828a]">
                                    {hayFiltros
                                        ? `Probá con otra búsqueda${seccion !== 'todas' ? ` o salí de la sección “${nombreSeccion}”` : ''}.`
                                        : 'Cada acción que se realice en el sistema va a aparecer acá.'}
                                </p>
                                {hayFiltros && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setBuscar('');
                                            navegar('', 'todas');
                                        }}
                                        className="mt-4 rounded-md border border-[#dce8e3] px-3 py-1.5 text-xs font-semibold text-[#397461] hover:bg-[#eef6f1]"
                                    >
                                        Quitar filtros
                                    </button>
                                )}
                            </div>
                        ) : (
                            grupos.map((grupo, indice) => (
                                <section key={`${grupo.dia}-${indice}`}>
                                    <h2 className="border-b border-[#edf3f0] bg-[#f6faf8] px-4 py-1.5 text-[11px] font-bold tracking-[0.08em] text-[#71828a] uppercase sm:px-5">
                                        {grupo.dia}
                                        <span className="ml-2 font-medium tracking-normal text-[#a7b4b9] normal-case">
                                            {grupo.eventos.length}
                                        </span>
                                    </h2>
                                    <ul className="divide-y divide-[#f0f5f2]">
                                        {grupo.eventos.map((evento) => {
                                            const clave = `${evento.tipo_movimiento}-${evento.id}`;

                                            return (
                                                <FilaEvento
                                                    key={clave}
                                                    evento={evento}
                                                    referencias={referencias}
                                                    abierto={
                                                        eventoAbierto === clave
                                                    }
                                                    onToggle={() =>
                                                        setEventoAbierto(
                                                            eventoAbierto ===
                                                                clave
                                                                ? null
                                                                : clave,
                                                        )
                                                    }
                                                />
                                            );
                                        })}
                                    </ul>
                                </section>
                            ))
                        )}
                        <Paginacion paginador={eventos} unidad="movimientos" />
                    </div>
                </div>
            </main>
        </>
    );
}
