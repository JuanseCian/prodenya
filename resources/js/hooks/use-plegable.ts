import { useState } from 'react';

const PREFIJO = 'legajo-seccion-abierta:';

const leer = (clave: string, abiertaPorDefecto: boolean): boolean => {
    try {
        const valor = window.localStorage.getItem(PREFIJO + clave);

        return valor === null ? abiertaPorDefecto : valor === '1';
    } catch {
        return abiertaPorDefecto;
    }
};

/**
 * Estado abierto/plegado de una sección del legajo. Se recuerda por navegador
 * para que cada usuario encuentre las secciones como las dejó.
 */
export function usePlegable(
    clave: string,
    abiertaPorDefecto = false,
): [boolean, () => void, (abierta: boolean) => void] {
    const [abierta, setAbierta] = useState(() =>
        leer(clave, abiertaPorDefecto),
    );

    const fijar = (valor: boolean) => {
        setAbierta(valor);

        try {
            window.localStorage.setItem(PREFIJO + clave, valor ? '1' : '0');
        } catch {
            // Sin almacenamiento disponible: el estado vive solo en esta visita.
        }
    };

    return [abierta, () => fijar(!abierta), fijar];
}
