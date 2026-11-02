export type Periodo = 'dia' | 'mes' | 'anio';
export type Preset = 'hoy' | 'mes_actual' | 'anio_actual' | 'personalizado';

export interface Intervalo {
    key: string;
    label: string;
}

export interface NombreTotal {
    nombre: string;
    total: number;
}

export interface Problematica {
    id: number;
    nombre: string;
    total: number;
}

export interface Catalogos {
    profesionales: Array<{
        id: number;
        name: string;
        profesion: string | null;
    }>;
    responsables: Array<{
        id: number;
        name: string;
        profesion: string | null;
    }>;
    tiposIntervencion: Array<{ id: number; nombre: string }>;
    generos: Array<{ value: string; label: string }>;
    edades: Array<{ value: string; label: string }>;
}

export interface EstadisticasData {
    modulosDisponibles: Array<
        | 'resumen'
        | 'legajos'
        | 'problematicas'
        | 'intervenciones'
        | 'tareas'
        | 'agenda'
    >;
    filtros: {
        preset: Preset;
        comparar: 'ninguno' | 'anterior' | 'anio_anterior';
        desde: string | null;
        hasta: string | null;
        periodo: Periodo;
        anio: number;
        mes: number;
        estado: string | null;
        problematica: number | null;
        profesional: number | null;
        responsable: number | null;
        edad: string | null;
        genero: string | null;
        tipo_intervencion: number | null;
        tarea_estado: string | null;
        agenda_estado: string | null;
    };
    rango: {
        desde: string;
        hasta: string;
        descripcion: string;
    };
    intervalos: Intervalo[];
    kpis: {
        personasAltas: number;
        legajosAltas: number;
        legajosBajas: number;
        totalLegajos: number;
        legajosAbiertos: number;
        legajosCerrados: number;
        problematicasConCasos: number;
        intervenciones: number;
        tareas: number;
        tareasCompletadas: number;
        agenda: number;
        modificaciones: number;
    };
    legajosPorEstado: {
        abiertos: number;
        cerrados: number;
        archivados: number;
    };
    personasPorPeriodo: number[];
    legajosPorPeriodo: number[];
    bajasPorPeriodo: number[];
    modificacionesPorPeriodo: number[];
    intervencionesPorPeriodo: number[];
    tareasPorPeriodo: number[];
    agendaPorPeriodo: number[];
    problematicas: Problematica[];
    problematicaSeleccionada: { id: number; nombre: string } | null;
    intervencionesPorTipo: Array<{ id: number; nombre: string; total: number }>;
    intervencionesPorProfesional: Array<{
        id: number | null;
        nombre: string;
        total: number;
    }>;
    tareasPorEstado: NombreTotal[];
    agendaPorTipo: NombreTotal[];
    agendaPorEstado: NombreTotal[];
    barrios: Array<{ id: number; nombre: string; total: number }>;
    mapaBarrios: {
        disponible: boolean;
        mensaje: string;
        zonas: Array<{
            id: string;
            latitud: number;
            longitud: number;
            total: number;
            problematicas: Array<{
                id: number;
                nombre: string;
                total: number;
            }>;
        }>;
    };
    modificacionesTimeline: TimelineItem[];
    actividadTimeline: TimelineItem[];
    agendaTimeline: TimelineItem[];
    comparacion: {
        rango: { desde: string; hasta: string };
        kpis: EstadisticasData['kpis'];
    } | null;
    catalogos: Catalogos;
}

export interface TimelineItem {
    id: number | string;
    fecha: string | null;
    [key: string]: string | number | null | undefined;
}
