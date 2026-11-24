import { router } from '@inertiajs/react';
import { Link2 } from 'lucide-react';
import { useState } from 'react';

import BuscadorPersonas from '@/components/buscador-personas';
import type { PersonaEncontrada } from '@/components/buscador-personas';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

export interface ActaPorVincular {
    etiqueta: string;
    titulo: string;
    vincular_url: string;
}

interface Props {
    acta: ActaPorVincular | null;
    onClose: () => void;
}

/**
 * Vincula un acta a un legajo: se busca a la persona y solo se puede elegir
 * a quien ya tiene legajo.
 */
export default function VincularActaDialog({ acta, onClose }: Props) {
    const [elegida, setElegida] = useState<PersonaEncontrada | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [procesando, setProcesando] = useState(false);

    const cerrar = () => {
        setElegida(null);
        setError(null);
        onClose();
    };

    const confirmar = () => {
        if (!acta || !elegida?.legajo) {
            return;
        }

        router.patch(
            acta.vincular_url,
            { legajo_id: elegida.legajo.id },
            {
                preserveScroll: true,
                onStart: () => setProcesando(true),
                onFinish: () => setProcesando(false),
                onSuccess: cerrar,
                onError: (errs) =>
                    setError(Object.values(errs)[0] ?? 'No se pudo vincular.'),
            },
        );
    };

    return (
        <Dialog open={acta !== null} onOpenChange={(open) => !open && cerrar()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Vincular a un legajo</DialogTitle>
                    <DialogDescription>
                        {acta?.etiqueta} · {acta?.titulo}. Buscá a la persona;
                        solo se pueden elegir quienes ya tienen legajo.
                    </DialogDescription>
                </DialogHeader>
                <BuscadorPersonas
                    soloConLegajo
                    onElegir={(persona) => {
                        setElegida(persona);
                        setError(null);
                    }}
                />
                {elegida && (
                    <p className="rounded-lg bg-secondary px-3 py-2 text-sm text-secondary-foreground">
                        {elegida.nombre} · Legajo{' '}
                        {elegida.legajo?.numero_legajo}
                    </p>
                )}
                {error && <p className="text-sm text-destructive">{error}</p>}
                <DialogFooter>
                    <Button type="button" variant="ghost" onClick={cerrar}>
                        Cancelar
                    </Button>
                    <Button
                        type="button"
                        disabled={!elegida?.legajo || procesando}
                        onClick={confirmar}
                    >
                        <Link2 className="size-4" />
                        {procesando ? 'Vinculando…' : 'Vincular'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
