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
            <main className="min-h-screen bg-[#F4F6F5] px-4 py-8 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.14em] text-[#6B8F71] uppercase">Organización</p>
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#173247] sm:text-4xl">Agenda</h1>
                            <p className="mt-2 text-sm text-[#697586]">Elegí una fecha para consultar o cargar actividades.</p>
                        </div>
                        <Button onClick={() => openCreate()} className="w-fit gap-2 bg-[#2C4A5E] hover:bg-[#173247]"><Plus className="size-4" /> Nuevo evento</Button>
                    </div>

                    <div className="mb-5 flex w-fit rounded-lg border border-[#DCE3DF] bg-white p-1 shadow-sm" role="tablist" aria-label="Tipo de agenda">
                        <button type="button" role="tab" aria-selected={scope === 'personal'} onClick={() => setScope('personal')} className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition ${scope === 'personal' ? 'bg-[#EAF3ED] text-[#173247]' : 'text-[#697586] hover:bg-[#F4F6F5]'}`}>
                            <UserRound className="size-4" /> Mi agenda
                        </button>
                        <button type="button" role="tab" aria-selected={scope === 'general'} onClick={() => setScope('general')} className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition ${scope === 'general' ? 'bg-[#EAF3ED] text-[#173247]' : 'text-[#697586] hover:bg-[#F4F6F5]'}`}>
                            <UsersRound className="size-4" /> Agenda general
                        </button>
                    </div>

                    <div className="grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.75fr)]">
                        <Card className="overflow-hidden border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardContent className="p-0">
                                <div className="flex items-center justify-between border-b border-[#EEF0ED] px-4 py-4 sm:px-6">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.12em] text-[#6B8F71] uppercase">{scope === 'personal' ? 'Solo vos' : 'Compartida con todos'}</p>
                                        <h2 className="mt-1 text-lg font-semibold capitalize text-[#173247]">{formatMonth(month)}</h2>
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <Button type="button" variant="outline" size="icon" onClick={() => changeMonth(-1)} aria-label="Mes anterior"><ChevronLeft className="size-4" /></Button>
                                        <Button type="button" variant="outline" size="icon" onClick={() => changeMonth(1)} aria-label="Mes siguiente"><ChevronRight className="size-4" /></Button>
                                    </div>
                                </div>
                                <div className="overflow-x-auto">
                                    <div className="min-w-[560px]">
                                        <div className="grid grid-cols-7 border-b border-[#EEF0ED] bg-[#FBFCFB]">
                                            {weekDays.map((day) => <div key={day} className="px-1 py-3 text-center text-[10px] font-bold tracking-[0.08em] text-[#819197] uppercase sm:text-xs">{day}</div>)}
                                        </div>
                                        <div className="grid grid-cols-7">
                                            {days.map((day) => {
                                                const key = dateKey(day);
                                                const dayEvents = eventsByDate.get(key) ?? [];
                                                const isCurrentMonth = day.getMonth() === month.getMonth();
                                                const isSelected = key === selectedDate;
                                                const isToday = key === todayKey;

                                                return (
                                                    <button key={key} type="button" onClick={() => selectDate(day)} className={`min-h-24 border-b border-r border-[#EEF0ED] p-1.5 text-left transition last:border-r-0 hover:bg-[#F4F8F5] sm:min-h-28 sm:p-2 ${isSelected ? 'bg-[#EEF7F0] shadow-[inset_0_0_0_2px_#6B8F71]' : ''} ${!isCurrentMonth ? 'bg-[#FBFCFB] text-[#B4BFBC]' : ''}`} aria-label={`${key}${dayEvents.length ? `, ${dayEvents.length} eventos` : ''}`}>
                                                        <span className={`inline-flex size-7 items-center justify-center rounded-full text-xs font-semibold ${isToday ? 'bg-[#2C4A5E] text-white' : isSelected ? 'text-[#397461]' : isCurrentMonth ? 'text-[#49616D]' : 'text-[#B4BFBC]'}`}>{day.getDate()}</span>
                                                        <span className="mt-1 block space-y-1">
                                                            {dayEvents.slice(0, 2).map((evento) => <span key={evento.id} className="block truncate rounded bg-[#EAF3ED] px-1 py-0.5 text-[10px] font-semibold text-[#397461]">{evento.titulo}</span>)}
                                                            {dayEvents.length > 2 && <span className="block px-1 text-[10px] font-semibold text-[#697586]">+{dayEvents.length - 2} más</span>}
                                                        </span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="h-fit border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
                            <CardContent className="p-5 sm:p-6">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.12em] text-[#6B8F71] uppercase">{scope === 'personal' ? 'Mi agenda' : 'Agenda general'}</p>
                                        <h2 className="mt-2 text-xl font-semibold capitalize text-[#173247]">{formatSelectedDate(selectedDate)}</h2>
                                    </div>
                                    <Button type="button" variant="outline" size="icon" onClick={() => openCreate()} aria-label="Agregar evento en esta fecha"><Plus className="size-4" /></Button>
                                </div>
                                <div className="mt-5 space-y-3">
                                    {selectedEvents.length === 0 ? (
                                        <div className="rounded-lg border border-dashed border-[#DCE3DF] px-4 py-8 text-center">
                                            <CalendarDays className="mx-auto size-8 text-[#9AB8A3]" />
                                            <p className="mt-3 text-sm font-semibold text-[#2B2F36]">No hay eventos para esta fecha.</p>
                                            <button type="button" onClick={() => openCreate()} className="mt-2 text-sm font-semibold text-[#397461] hover:underline">Agregar actividad</button>
                                        </div>
                                    ) : selectedEvents.map((evento) => (
                                        <article key={evento.id} className="rounded-lg border border-[#E3EAE5] bg-[#FBFCFB] p-4">
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <h3 className="text-sm font-semibold text-[#173247]">{evento.titulo}</h3>
                                                        <span className="rounded-full bg-[#EAF3ED] px-2 py-1 text-[10px] font-semibold text-[#397461]">{evento.tipo}</span>
                                                    </div>
                                                    <div className="mt-3 space-y-1.5 text-xs text-[#697586]">
                                                        <p className="inline-flex items-center gap-1.5"><Clock3 className="size-3.5" />{formatEvent(evento.fecha_inicio)}</p>
                                                        {evento.lugar && <p className="inline-flex items-center gap-1.5"><MapPin className="size-3.5" />{evento.lugar}</p>}
                                                        {scope === 'general' && evento.creador && <p className="inline-flex items-center gap-1.5"><UserRound className="size-3.5" />Creado por {evento.creador.nombre}</p>}
                                                    </div>
                                                </div>
                                                {evento.puede_editar && <button type="button" onClick={() => eliminar(evento)} className="rounded-md p-2 text-[#9B5964] transition hover:bg-[#FCF1F3]" aria-label={`Eliminar ${evento.titulo}`}><Trash2 className="size-4" /></button>}
                                            </div>
                                            {evento.legajos.length > 0 && <p className="mt-3 border-t border-[#E3EAE5] pt-3 text-xs text-[#697586]">{evento.legajos.map((legajo) => `Legajo ${legajo.numero_legajo}`).join(' · ')}</p>}
                                        </article>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </main>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="border-[#DCE3DF] bg-[#FBFCFB] sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle className="text-[#173247]">Nuevo evento</DialogTitle>
                        <DialogDescription className="text-[#697586]">Elegí si el evento es solo tuyo o si querés compartirlo con todos los usuarios.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-2">
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Título<Input value={titulo} onChange={(event) => setTitulo(event.target.value)} placeholder="Ej. Entrevista de seguimiento" className="h-10 border-[#D5D1C6] bg-white font-normal" /></label>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Tipo<select value={tipo} onChange={(event) => setTipo(event.target.value)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal"><option value="reunion">Reunión</option><option value="entrevista">Entrevista</option><option value="visita">Visita</option><option value="turno_medico">Turno médico</option><option value="audiencia">Audiencia</option></select></label>
                            <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Fecha y hora<Input type="datetime-local" value={fechaInicio} onChange={(event) => setFechaInicio(event.target.value)} className="h-10 border-[#D5D1C6] bg-white font-normal" /></label>
                        </div>
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Visibilidad<select value={visibilidad} onChange={(event) => setVisibilidad(event.target.value as Scope)} className="h-10 rounded-md border border-[#D5D1C6] bg-white px-3 text-sm font-normal"><option value="personal">Personal · solo yo</option><option value="general">General · todos los usuarios</option></select></label>
                        <label className="grid gap-2 text-sm font-semibold text-[#2B2F36]">Lugar o enlace<Input value={lugar} onChange={(event) => setLugar(event.target.value)} placeholder="Ej. Sede central o enlace de videollamada" className="h-10 border-[#D5D1C6] bg-white font-normal" /></label>
                    </div>
                    <DialogFooter><Button type="button" variant="outline" onClick={() => setOpen(false)}>Cancelar</Button><Button type="button" disabled={!titulo.trim() || !fechaInicio} onClick={crearEvento} className="bg-[#2C4A5E] hover:bg-[#173247]">Agendar evento</Button></DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
