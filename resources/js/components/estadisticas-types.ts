export type Periodo = 'dia' | 'mes' | 'anio';

export interface Intervalo {
    key: string;
    label: string;
}

export interface Problematica {
    id: number;
    nombre: string;
    total: number;
}

export interface EstadisticasData {
    filtros: {
        periodo: Periodo;
        anio: number;
        mes: number;
        problematica: number | null;
    };
    rango: {
        desde: string;
        hasta: string;
        descripcion: string;
    };
    intervalos: Intervalo[];
    kpis: {
        totalLegajos: number;
        legajosAbiertos: number;
        legajosCerrados: number;
        problematicasConCasos: number;
    };
    legajosPorEstado: {
        abiertos: number;
        cerrados: number;
        archivados: number;
    };
    legajosPorPeriodo: number[];
    problematicas: Problematica[];
    problematicaSeleccionada: { id: number; nombre: string } | null;
}
