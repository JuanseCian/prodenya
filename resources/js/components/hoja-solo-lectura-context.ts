import { createContext } from 'react';

/**
 * Hoja de acta en modo lectura: los campos se muestran como texto y las firmas
 * no se dibujan, se muestra la firma guardada.
 */
export const HojaSoloLecturaContext = createContext<{
    /** Roles que ya firmaron el documento guardado. */
    firmados: string[];
    /** Imagen de cada firma (data URI) por rol, si se pudo recuperar. */
    imagenes: Record<string, string>;
} | null>(null);
