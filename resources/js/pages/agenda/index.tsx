import { Head, router } from '@inertiajs/react';
import { CalendarDays, ChevronLeft, ChevronRight, Clock3, MapPin, Plus, Trash2, UserRound, UsersRound } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

type Scope = 'personal' | 'general';

interface Evento {
    id: number;
    titulo: string;
    tipo: string;
    visibilidad: Scope;
    fecha_inicio: string;
    fecha_fin: string | null;
    lugar: string | null;
    enlace: string | null;
    estado: string;
    creador: { id: number; nombre: string } | null;
    puede_editar: boolean;
    legajos: { id: number; numero_legajo: string }[];
}

interface Props {
    eventos: Evento[];
}

const weekDays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

const pad = (value: number) => String(value).padStart(2, '0');

const dateKey = (date: Date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

const dateFromKey = (key: string) => {
    const [year, month, day] = key.split('-').map(Number);

    return new Date(year, month - 1, day);
};

const eventDateKey = (event: Evento) => dateKey(new Date(event.fecha_inicio));

const formatEvent = (date: string) => new Intl.DateTimeFormat('es-AR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date));

const formatSelectedDate = (key: string) => new Intl.DateTimeFormat('es-AR', { dateStyle: 'full' }).format(dateFromKey(key));

const formatMonth = (date: Date) => new Intl.DateTimeFormat('es-AR', { month: 'long', year: 'numeric' }).format(date);

const calendarDays = (month: Date) => {
    const firstDay = new Date(month.getFullYear(), month.getMonth(), 1);
    const mondayOffset = (firstDay.getDay() + 6) % 7;
    const start = new Date(month.getFullYear(), month.getMonth(), 1 - mondayOffset);

    return Array.from({ length: 42 }, (_, index) => {
        const day = new Date(start);
        day.setDate(start.getDate() + index);

        return day;
    });
};

export default function Agenda({ eventos }: Props) {
    const today = new Date();
    const todayKey = dateKey(today);
    const [scope, setScope] = useState<Scope>('personal');
    const [month, setMonth] = useState(new Date(today.getFullYear(), today.getMonth(), 1));
    const [selectedDate, setSelectedDate] = useState(todayKey);
    const [open, setOpen] = useState(false);
    const [titulo, setTitulo] = useState('');
    const [tipo, setTipo] = useState('reunion');
    const [fechaInicio, setFechaInicio] = useState(`${todayKey}T09:00`);
    const [lugar, setLugar] = useState('');
    const [visibilidad, setVisibilidad] = useState<Scope>('personal');

    const scopedEvents = eventos.filter((evento) => evento.visibilidad === scope);
    const eventsByDate = new Map<string, Evento[]>();
    scopedEvents.forEach((evento) => {
        const key = eventDateKey(evento);
        eventsByDate.set(key, [...(eventsByDate.get(key) ?? []), evento]);
    });
    const selectedEvents = eventsByDate.get(selectedDate) ?? [];
    const days = calendarDays(month);

    const selectDate = (date: Date) => {
        setSelectedDate(dateKey(date));
        setMonth(new Date(date.getFullYear(), date.getMonth(), 1));
    };

    const changeMonth = (amount: number) => {
        const nextMonth = new Date(month.getFullYear(), month.getMonth() + amount, 1);
        setMonth(nextMonth);
        setSelectedDate(dateKey(nextMonth));
    };

    const openCreate = (date = selectedDate) => {
        setFechaInicio(`${date}T09:00`);
        setOpen(true);
    };

    const crearEvento = () => {
        router.post('/agenda', { titulo, tipo, visibilidad, fecha_inicio: fechaInicio, ...(lugar ? { lugar } : {}) }, {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);
                setTitulo('');
                setTipo('reunion');
                setLugar('');
                setVisibilidad('personal');
            },
        });
    };

    const eliminar = (evento: Evento) => {
        if (window.confirm(`¿Eliminar “${evento.titulo}”?`)) {
            router.delete(`/agenda/${evento.id}`, { preserveScroll: true });
        }
    };

    return (
        <>
            <Head title="Agenda" />
            <main className="min-h-screen bg-background px-4 py-8 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-primary uppercase">Organización</p>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Agenda</h1>
                            <p className="mt-2 text-sm text-muted-foreground">Elegí una fecha para consultar o cargar actividades.</p>
                        </div>
                        <Button onClick={() => openCreate()} className="w-fit gap-2"><Plus className="size-4" /> Nuevo evento</Button>
                    </div>

                    <div className="mb-5 flex w-fit rounded-lg border bg-card p-1 shadow-sm" role="tablist" aria-label="Tipo de agenda">
                        <button type="button" role="tab" aria-selected={scope === 'personal'} onClick={() => setScope('personal')} className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition ${scope === 'personal' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-muted'}`}>
                            <UserRound className="size-4" /> Mi agenda
                        </button>
                        <button type="button" role="tab" aria-selected={scope === 'general'} onClick={() => setScope('general')} className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition ${scope === 'general' ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-muted'}`}>
                            <UsersRound className="size-4" /> Agenda general
                        </button>
                    </div>

                    <div className="grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.75fr)]">
                        <Card className="overflow-hidden py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardContent className="p-0">
                                <div className="flex items-center justify-between border-b px-4 py-4 sm:px-6">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.12em] text-primary uppercase">{scope === 'personal' ? 'Solo vos' : 'Compartida con todos'}</p>
                                        <h2 className="mt-1 text-lg font-semibold capitalize">{formatMonth(month)}</h2>
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <Button type="button" variant="outline" size="icon" onClick={() => changeMonth(-1)} aria-label="Mes anterior"><ChevronLeft className="size-4" /></Button>
                                        <Button type="button" variant="outline" size="icon" onClick={() => changeMonth(1)} aria-label="Mes siguiente"><ChevronRight className="size-4" /></Button>
                                    </div>
                                </div>
                                <div className="overflow-x-auto">
                                    <div className="min-w-[560px]">
                                        <div className="grid grid-cols-7 border-b bg-muted/40">
                                            {weekDays.map((day) => <div key={day} className="px-1 py-3 text-center text-[10px] font-bold tracking-[0.08em] text-muted-foreground uppercase sm:text-xs">{day}</div>)}
                                        </div>
                                        <div className="grid grid-cols-7">
                                            {days.map((day) => {
                                                const key = dateKey(day);
                                                const dayEvents = eventsByDate.get(key) ?? [];
                                                const isCurrentMonth = day.getMonth() === month.getMonth();
                                                const isSelected = key === selectedDate;
                                                const isToday = key === todayKey;

                                                return (
                                                    <button key={key} type="button" onClick={() => selectDate(day)} className={`min-h-24 border-b border-r p-1.5 text-left transition last:border-r-0 hover:bg-muted/60 sm:min-h-28 sm:p-2 ${isSelected ? 'bg-accent shadow-[inset_0_0_0_2px_var(--primary)]' : ''} ${!isCurrentMonth ? 'bg-muted/30 text-muted-foreground/60' : ''}`} aria-label={`${key}${dayEvents.length ? `, ${dayEvents.length} eventos` : ''}`}>
                                                        <span className={`inline-flex size-7 items-center justify-center rounded-full text-xs font-semibold ${isToday ? 'bg-primary text-primary-foreground' : isSelected ? 'text-primary' : isCurrentMonth ? 'text-foreground' : 'text-muted-foreground/60'}`}>{day.getDate()}</span>
                                                        <span className="mt-1 block space-y-1">
                                                            {dayEvents.slice(0, 2).map((evento) => <span key={evento.id} className="block truncate rounded bg-accent px-1 py-0.5 text-[10px] font-semibold text-accent-foreground">{evento.titulo}</span>)}
                                                            {dayEvents.length > 2 && <span className="block px-1 text-[10px] font-semibold text-muted-foreground">+{dayEvents.length - 2} más</span>}
                                                        </span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="h-fit py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardContent className="p-5 sm:p-6">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.12em] text-primary uppercase">{scope === 'personal' ? 'Mi agenda' : 'Agenda general'}</p>
                                        <h2 className="mt-2 text-xl font-semibold capitalize">{formatSelectedDate(selectedDate)}</h2>
                                    </div>
                                    <Button type="button" variant="outline" size="icon" onClick={() => openCreate()} aria-label="Agregar evento en esta fecha"><Plus className="size-4" /></Button>
                                </div>
                                <div className="mt-5 space-y-3">
                                    {selectedEvents.length === 0 ? (
                                        <div className="rounded-lg border border-dashed px-4 py-8 text-center">
                                            <CalendarDays className="mx-auto size-8 text-muted-foreground" />
                                            <p className="mt-3 text-sm font-semibold">No hay eventos para esta fecha.</p>
                                            <button type="button" onClick={() => openCreate()} className="mt-2 text-sm font-semibold text-primary hover:underline">Agregar actividad</button>
                                        </div>
                                    ) : selectedEvents.map((evento) => (
                                        <article key={evento.id} className="rounded-lg border bg-muted/30 p-4">
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <h3 className="text-sm font-semibold">{evento.titulo}</h3>
                                                        <span className="rounded-full bg-accent px-2 py-1 text-[10px] font-semibold text-accent-foreground">{evento.tipo}</span>
                                                    </div>
                                                    <div className="mt-3 space-y-1.5 text-xs text-muted-foreground">
                                                        <p className="inline-flex items-center gap-1.5"><Clock3 className="size-3.5" />{formatEvent(evento.fecha_inicio)}</p>
                                                        {evento.lugar && <p className="inline-flex items-center gap-1.5"><MapPin className="size-3.5" />{evento.lugar}</p>}
                                                        {scope === 'general' && evento.creador && <p className="inline-flex items-center gap-1.5"><UserRound className="size-3.5" />Creado por {evento.creador.nombre}</p>}
                                                    </div>
                                                </div>
                                                {evento.puede_editar && <button type="button" onClick={() => eliminar(evento)} className="rounded-md p-2 text-destructive transition hover:bg-destructive/10" aria-label={`Eliminar ${evento.titulo}`}><Trash2 className="size-4" /></button>}
                                            </div>
                                            {evento.legajos.length > 0 && <p className="mt-3 border-t pt-3 text-xs text-muted-foreground">{evento.legajos.map((legajo) => `Legajo ${legajo.numero_legajo}`).join(' · ')}</p>}
                                        </article>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </main>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Nuevo evento</DialogTitle>
                        <DialogDescription>Elegí si el evento es solo tuyo o si querés compartirlo con todos los usuarios.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-2">
                        <label className="grid gap-2 text-sm font-semibold">Título<Input value={titulo} onChange={(event) => setTitulo(event.target.value)} placeholder="Ej. Entrevista de seguimiento" className="h-10 font-normal" /></label>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="grid gap-2 text-sm font-semibold">Tipo<select value={tipo} onChange={(event) => setTipo(event.target.value)} className="h-10 rounded-md border border-input bg-transparent px-3 text-sm font-normal"><option value="reunion">Reunión</option><option value="entrevista">Entrevista</option><option value="visita">Visita</option><option value="turno_medico">Turno médico</option><option value="audiencia">Audiencia</option></select></label>
                            <label className="grid gap-2 text-sm font-semibold">Fecha y hora<Input type="datetime-local" value={fechaInicio} onChange={(event) => setFechaInicio(event.target.value)} className="h-10 font-normal" /></label>
                        </div>
                        <label className="grid gap-2 text-sm font-semibold">Visibilidad<select value={visibilidad} onChange={(event) => setVisibilidad(event.target.value as Scope)} className="h-10 rounded-md border border-input bg-transparent px-3 text-sm font-normal"><option value="personal">Personal · solo yo</option><option value="general">General · todos los usuarios</option></select></label>
                        <label className="grid gap-2 text-sm font-semibold">Lugar o enlace<Input value={lugar} onChange={(event) => setLugar(event.target.value)} placeholder="Ej. Sede central o enlace de videollamada" className="h-10 font-normal" /></label>
                    </div>
                    <DialogFooter><Button type="button" variant="outline" onClick={() => setOpen(false)}>Cancelar</Button><Button type="button" disabled={!titulo.trim() || !fechaInicio} onClick={crearEvento}>Agendar evento</Button></DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
