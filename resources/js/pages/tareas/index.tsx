import { Head, router } from '@inertiajs/react';
import { CalendarClock, CheckCircle2, Circle, Plus, UserRound } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import tareasRoutes from '@/routes/tareas';

interface Tarea {
    id: number;
    titulo: string;
    descripcion: string | null;
    tipo: string;
    prioridad: string;
    estado: string;
    fecha_limite: string | null;
    asignado: { id: number; name: string } | null;
    legajo: { id: number; numero_legajo: string } | null;
}

interface Responsable {
    id: number;
    name: string;
    profesion: string | null;
}

interface Props {
    tareas: Tarea[];
    responsables: Responsable[];
    puedeAsignarOtros: boolean;
    usuarioActualId: number;
}

const priorityClass: Record<string, string> = {
    baja: 'bg-[#F1F5F3] text-[#60756B]',
    normal: 'bg-[#F2F7FB] text-[#35627A]',
    alta: 'bg-[#FBF5EA] text-[#9A6C2D]',
    urgente: 'bg-[#FCF1F3] text-[#9B5964]',
};

export default function Tareas({
    tareas,
    responsables,
    puedeAsignarOtros,
    usuarioActualId,
}: Props) {
    const [open, setOpen] = useState(false);
    const [titulo, setTitulo] = useState('');
    const [prioridad, setPrioridad] = useState('normal');
    const [asignadoA, setAsignadoA] = useState(String(usuarioActualId));
    const [fechaLimite, setFechaLimite] = useState('');

    const crearTarea = () => {
        router.post(
            tareasRoutes.store.url(),
            {
                titulo,
                prioridad,
                asignado_a: Number(asignadoA),
                ...(fechaLimite ? { fecha_limite: fechaLimite } : {}),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setOpen(false);
                    setTitulo('');
                    setAsignadoA(String(usuarioActualId));
                    setFechaLimite('');
                },
            },
        );
    };

    const cambiarEstado = (tarea: Tarea) => {
        router.patch(tareasRoutes.update.url(tarea.id), {
            estado: tarea.estado === 'completada' ? 'pendiente' : 'completada',
        }, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Tareas" />
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-5xl">
                    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">Seguimiento</p>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">Tareas</h1>
                            <p className="mt-2 text-sm text-[#697586]">Tus pendientes de trabajo en un solo lugar.</p>
                        </div>
                        <Button onClick={() => setOpen(true)} className="w-fit gap-2 bg-[#2C4A5E] hover:bg-[#173247]"><Plus className="size-4" /> Nueva tarea</Button>
                    </div>

                    <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                        <CardContent className="p-0">
                            {tareas.length === 0 ? (
                                <div className="px-6 py-20 text-center">
                                    <CheckCircle2 className="mx-auto size-9 text-[#6B8F71]" />
                                    <p className="mt-3 text-sm font-semibold text-[#2B2F36]">No hay tareas para mostrar.</p>
                                    <p className="mt-1 text-sm text-[#697586]">Las derivaciones del Mailbox aparecerán aquí.</p>
                                </div>
                            ) : (
                                <div className="divide-y divide-[#EEF0ED]">
                                    {tareas.map((tarea) => (
                                        <article key={tarea.id} className="flex items-start gap-4 px-5 py-5 sm:px-6">
                                            <button type="button" onClick={() => cambiarEstado(tarea)} className="mt-0.5 shrink-0 text-[#6B8F71]" aria-label={tarea.estado === 'completada' ? 'Reabrir tarea' : 'Completar tarea'}>
                                                {tarea.estado === 'completada' ? <CheckCircle2 className="size-5" /> : <Circle className="size-5" />}
                                            </button>
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h2 className={`text-sm font-semibold ${tarea.estado === 'completada' ? 'text-[#8A9692] line-through' : 'text-[#173247]'}`}>{tarea.titulo}</h2>
                                                    <span className={`rounded-full px-2 py-1 text-[10px] font-bold uppercase ${priorityClass[tarea.prioridad] ?? priorityClass.normal}`}>{tarea.prioridad}</span>
                                                </div>
                                                <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#697586]">
                                                    {tarea.asignado && <span className="inline-flex items-center gap-1"><UserRound className="size-3.5" />{tarea.asignado.name}</span>}
                                                    {tarea.fecha_limite && <span className="inline-flex items-center gap-1"><CalendarClock className="size-3.5" />Vence {new Intl.DateTimeFormat('es-AR').format(new Date(`${tarea.fecha_limite}T12:00:00`))}</span>}
                                                    {tarea.legajo && <span>Legajo {tarea.legajo.numero_legajo}</span>}
                                                </div>
                                            </div>
                                            <span className="hidden rounded-full bg-[#F4F6F5] px-2.5 py-1 text-[10px] font-semibold text-[#697586] sm:inline-flex">{tarea.estado.replace('_', ' ')}</span>
                                        </article>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </main>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle className="text-[#173247]">Nueva tarea</DialogTitle>
                        <DialogDescription className="text-[#697586]">
                            {puedeAsignarOtros
                                ? 'Creá un pendiente para vos o asignalo a otra persona del equipo.'
                                : 'Creá un pendiente para vos. Solo coordinación y administración pueden asignarlo a otra persona.'}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-2">
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Título<Input value={titulo} onChange={(event) => setTitulo(event.target.value)} placeholder="Ej. Revisar informe del caso" className="h-10 border-[#D5D1C6] bg-white font-normal" /></label>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Prioridad<select value={prioridad} onChange={(event) => setPrioridad(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal"><option value="baja">Baja</option><option value="normal">Normal</option><option value="alta">Alta</option><option value="urgente">Urgente</option></select></label>
                            <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Vencimiento<Input type="date" value={fechaLimite} onChange={(event) => setFechaLimite(event.target.value)} className="h-10 border-[#D5D1C6] bg-white font-normal" /></label>
                        </div>
                            {puedeAsignarOtros && (
                                <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Responsable<select value={asignadoA} onChange={(event) => setAsignadoA(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal">{responsables.map((responsable) => <option key={responsable.id} value={responsable.id}>{responsable.name}{responsable.profesion ? ` · ${responsable.profesion}` : ''}</option>)}</select></label>
                            )}
                    </div>
                    <DialogFooter><Button type="button" variant="outline" onClick={() => setOpen(false)}>Cancelar</Button><Button type="button" disabled={!titulo.trim()} onClick={crearTarea} className="bg-[#2C4A5E] hover:bg-[#173247]">Crear tarea</Button></DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
