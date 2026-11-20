import { router } from '@inertiajs/react';
import {
    CheckCircle2,
    ChevronDown,
    CircleDashed,
    Download,
    FilePen,
    FileText,
    Plus,
    RefreshCw,
    Trash2,
    Upload,
} from 'lucide-react';
import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';

import AuhPlanillaDialog from '@/components/auh-planilla-dialog';
import type {
    FormularioPlanillas,
    TipoPlanilla,
} from '@/components/auh-planilla-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePlegable } from '@/hooks/use-plegable';
import auhArchivosRoutes from '@/routes/legajos/auh/archivos';

interface AuhArchivo {
    id: number;
    tipo: string;
    descripcion: string | null;
    nombre: string;
    tamanio: number;
    subido_en: string | null;
    descarga_url: string;
    eliminar_url: string;
    firmado: boolean;
    firmas: Array<{
        rol: string;
        rol_label: string;
        nombre: string;
        firmado_en: string;
    }>;
}

export interface AuhResumen {
    id: number | null;
    tipos: Record<string, string>;
    planillas: string[];
    documentacion: string[];
    tipo_otra: string;
    formulario: FormularioPlanillas;
    firmantes: Record<string, Record<string, string>>;
    archivos: AuhArchivo[];
}

interface Props {
    legajoId: number;
    auh: AuhResumen;
    puedeGestionar: boolean;
}

type Errores = Partial<
    Record<'tipo' | 'archivo' | 'descripcion' | 'auh', string>
>;

const ACCEPT =
    'application/pdf,image/jpeg,image/png,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';

const inputClass =
    'mt-2 block h-10 w-full rounded-lg border border-[#D5D1C6] bg-white px-3 text-sm text-[#2B2F36] outline-none transition placeholder:text-[#697586]/55 focus:border-[#2C4A5E] focus:ring-2 focus:ring-[#2C4A5E]/20';

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat('es-AR').format(new Date(`${value}T12:00:00`));

const formatSize = (bytes: number): string =>
    bytes < 1024 * 1024
        ? `${Math.max(1, Math.round(bytes / 1024))} KB`
        : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;

const primerError = (errores: Errores): string | undefined =>
    errores.archivo ?? errores.descripcion ?? errores.tipo ?? errores.auh;

export default function AuhModule({ legajoId, auh, puedeGestionar }: Props) {
    const [subiendoTipo, setSubiendoTipo] = useState<string | null>(null);
    const [errorPorTipo, setErrorPorTipo] = useState<Record<string, string>>(
        {},
    );
    const [descripcionOtra, setDescripcionOtra] = useState('');
    const [archivoOtra, setArchivoOtra] = useState<File | null>(null);
    const [otraInputKey, setOtraInputKey] = useState(0);
    const [planillaAbierta, setPlanillaAbierta] = useState<TipoPlanilla | null>(
        null,
    );

    const archivoPorTipo = (tipo: string): AuhArchivo | undefined =>
        auh.archivos.find((archivo) => archivo.tipo === tipo);
    const otros = auh.archivos.filter(
        (archivo) => archivo.tipo === auh.tipo_otra,
    );
    const obligatorios = [...auh.planillas, ...auh.documentacion];
    const cargados = obligatorios.filter((tipo) => archivoPorTipo(tipo)).length;
    const completo = cargados === obligatorios.length;
    const planillasSinFirmar = auh.planillas.filter(
        (tipo) => !archivoPorTipo(tipo),
    ).length;
    const [abierta, alternar] = usePlegable('auh');

    const subir = (
        tipo: string,
        archivo: File,
        descripcion?: string,
        onSuccess?: () => void,
    ) => {
        setErrorPorTipo((actual) => ({ ...actual, [tipo]: '' }));
        router.post(
            auhArchivosRoutes.store.url(legajoId),
            {
                tipo,
                archivo,
                descripcion: descripcion ?? '',
                confirma_firma: auh.planillas.includes(tipo) ? '1' : '',
            },
            {
                forceFormData: true,
                preserveScroll: true,
                onStart: () => setSubiendoTipo(tipo),
                onFinish: () => setSubiendoTipo(null),
                onSuccess,
                onError: (errores) =>
                    setErrorPorTipo((actual) => ({
                        ...actual,
                        [tipo]:
                            primerError(errores as Errores) ??
                            'No se pudo cargar el archivo.',
                    })),
            },
        );
    };

    const alElegirArchivo =
        (tipo: string) => (event: ChangeEvent<HTMLInputElement>) => {
            const archivo = event.target.files?.[0];
            event.target.value = '';

            if (!archivo) {
                return;
            }

            if (auh.planillas.includes(tipo)) {
                const firmantes = Object.values(auh.firmantes[tipo] ?? {});

                if (
                    !window.confirm(
                        `¿La planilla escaneada está firmada por: ${firmantes.join(' y ')}? Solo se aceptan planillas firmadas.`,
                    )
                ) {
                    return;
                }
            }

            subir(tipo, archivo);
        };

    const quitar = (archivo: AuhArchivo) => {
        if (
            !window.confirm(
                `¿Quitar "${archivo.nombre}" del trámite de AUH? Quedará guardado en el historial.`,
            )
        ) {
            return;
        }

        router.delete(archivo.eliminar_url, { preserveScroll: true });
    };

    const agregarOtra = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!archivoOtra) {
            setErrorPorTipo((actual) => ({
                ...actual,
                [auh.tipo_otra]: 'Seleccioná el archivo.',
            }));

            return;
        }

        subir(auh.tipo_otra, archivoOtra, descripcionOtra, () => {
            setDescripcionOtra('');
            setArchivoOtra(null);
            setOtraInputKey((key) => key + 1);
        });
    };

    const botonArchivo = (archivo: AuhArchivo) => (
        <a
            href={archivo.descarga_url}
            className="inline-flex max-w-full items-center gap-2 rounded-lg border border-[#DCE3DF] bg-white px-3 py-2 text-xs font-semibold text-[#2C4A5E] transition hover:bg-[#F4F6F5]"
        >
            <Download className="size-3.5 shrink-0" />
            <span className="truncate">{archivo.nombre}</span>
            <span className="shrink-0 font-normal text-[#697586]">
                {formatSize(archivo.tamanio)}
                {archivo.subido_en && ` · ${formatDate(archivo.subido_en)}`}
            </span>
        </a>
    );

    const filaDocumento = (tipo: string) => {
        const archivo = archivoPorTipo(tipo);
        const subiendo = subiendoTipo === tipo;
        const inputId = `auh-archivo-${tipo}`;
        const esPlanilla = auh.planillas.includes(tipo);

        return (
            <li
                key={tipo}
                className="flex flex-col gap-3 rounded-xl border border-[#DCE3DF] bg-[#FBFCFB] p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div className="min-w-0 space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                        {archivo ? (
                            <CheckCircle2 className="size-4 text-[#397461]" />
                        ) : (
                            <CircleDashed className="size-4 text-[#B08D57]" />
                        )}
                        <span className="text-sm font-semibold text-[#173247]">
                            {auh.tipos[tipo]}
                        </span>
                        <Badge
                            className={
                                archivo
                                    ? 'border-0 bg-[#EAF6EE] text-[#397461]'
                                    : 'border-0 bg-[#FBF4E8] text-[#8A6A35]'
                            }
                        >
                            {archivo
                                ? esPlanilla
                                    ? 'Firmada'
                                    : 'Cargado'
                                : esPlanilla
                                  ? 'Falta firmar'
                                  : 'Pendiente'}
                        </Badge>
                    </div>
                    {esPlanilla && (
                        <p className="text-xs text-[#697586]">
                            {archivo
                                ? archivo.firmas.length > 0
                                    ? `Firmada en el sistema por ${archivo.firmas
                                          .map(
                                              (firma) =>
                                                  `${firma.nombre} (${firma.rol_label})`,
                                          )
                                          .join(' y ')}.`
                                    : 'Escaneo firmado en papel.'
                                : `Firma: ${Object.values(auh.firmantes[tipo] ?? {}).join(' y ')}.`}
                        </p>
                    )}
                    {archivo && botonArchivo(archivo)}
                    {errorPorTipo[tipo] && (
                        <p className="text-xs text-[#9B5964]">
                            {errorPorTipo[tipo]}
                        </p>
                    )}
                </div>

                {puedeGestionar && (
                    <div className="flex shrink-0 items-center gap-2">
                        {auh.planillas.includes(tipo) && (
                            <button
                                type="button"
                                onClick={() =>
                                    setPlanillaAbierta(tipo as TipoPlanilla)
                                }
                                className="inline-flex h-9 items-center gap-2 rounded-lg border border-[#DCE3DF] bg-white px-3 text-xs font-semibold text-[#2C4A5E] transition hover:bg-[#F4F6F5]"
                            >
                                <FilePen className="size-3.5" />
                                {archivo
                                    ? 'Volver a firmar'
                                    : 'Completar y firmar'}
                            </button>
                        )}
                        <input
                            id={inputId}
                            type="file"
                            accept={ACCEPT}
                            className="sr-only"
                            disabled={subiendoTipo !== null}
                            onChange={alElegirArchivo(tipo)}
                        />
                        <label
                            htmlFor={inputId}
                            className={`inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg px-3 text-xs font-semibold transition ${
                                archivo
                                    ? 'border border-[#DCE3DF] bg-white text-[#2C4A5E] hover:bg-[#F4F6F5]'
                                    : 'bg-[#2C4A5E] text-white hover:bg-[#173247]'
                            } ${subiendoTipo !== null ? 'pointer-events-none opacity-60' : ''}`}
                        >
                            {archivo ? (
                                <RefreshCw className="size-3.5" />
                            ) : (
                                <Upload className="size-3.5" />
                            )}
                            {subiendo
                                ? 'Subiendo...'
                                : esPlanilla
                                  ? 'Subir escaneo firmado'
                                  : archivo
                                    ? 'Reemplazar'
                                    : 'Subir archivo'}
                        </label>
                        {archivo && (
                            <button
                                type="button"
                                onClick={() => quitar(archivo)}
                                className="rounded-lg p-2 text-[#9B5964] hover:bg-[#FCF1F3]"
                                aria-label={`Quitar ${auh.tipos[tipo]}`}
                            >
                                <Trash2 className="size-4" />
                            </button>
                        )}
                    </div>
                )}
            </li>
        );
    };

    return (
        <Card className="gap-0 overflow-hidden rounded-2xl border-[#DCE3DF] !bg-white py-0 shadow-[0_14px_35px_rgba(23,50,71,0.06)]">
            <CardHeader
                className={`bg-[#F8FAF9] px-5 py-4 ${abierta ? 'border-b border-[#E4E8E5]' : ''}`}
            >
                <button
                    type="button"
                    onClick={alternar}
                    aria-expanded={abierta}
                    aria-controls="seccion-auh"
                    className="-m-1 flex w-full min-w-0 flex-wrap items-center gap-2 rounded-lg p-1 text-left transition hover:bg-[#EEF3F0]"
                >
                    <ChevronDown
                        className={`size-4 shrink-0 text-[#697586] transition-transform ${abierta ? '' : '-rotate-90'}`}
                    />
                    <FileText size={18} className="text-[#6B8F71]" />
                    <CardTitle className="text-base font-semibold text-[#2C4A5E]">
                        Asignación Universal por Hijo (AUH)
                    </CardTitle>
                    <Badge
                        className={
                            completo
                                ? 'ml-1 border-0 bg-[#EAF6EE] text-[#397461]'
                                : 'ml-1 border-0 bg-[#F2F7FB] text-[#35627A]'
                        }
                    >
                        {completo
                            ? 'Documentación completa'
                            : `${cargados} de ${obligatorios.length} obligatorios`}
                    </Badge>
                    {planillasSinFirmar > 0 && (
                        <Badge className="border-0 bg-[#FBF4E8] text-[#8A6A35]">
                            {planillasSinFirmar} planilla
                            {planillasSinFirmar === 1 ? '' : 's'} sin firmar
                        </Badge>
                    )}
                </button>
                {abierta && (
                    <p className="mt-1 text-sm text-[#2B2F36]/60">
                        Completá y firmá cada planilla en el sistema, o subí el
                        escaneo firmado en papel. Sumá la documentación del
                        niño/a y su responsable (PDF, Word, JPG o PNG de hasta
                        20 MB).
                    </p>
                )}
            </CardHeader>

            <CardContent
                id="seccion-auh"
                hidden={!abierta}
                className="space-y-6 px-5 py-5"
            >
                <section>
                    <h3 className="text-sm font-semibold text-[#2C4A5E]">
                        Planillas
                    </h3>
                    <ul className="mt-3 space-y-3">
                        {auh.planillas.map(filaDocumento)}
                    </ul>
                </section>

                <section>
                    <h3 className="text-sm font-semibold text-[#2C4A5E]">
                        Documentación
                    </h3>
                    <ul className="mt-3 space-y-3">
                        {auh.documentacion.map(filaDocumento)}
                    </ul>
                </section>

                <section>
                    <h3 className="text-sm font-semibold text-[#2C4A5E]">
                        {auh.tipos[auh.tipo_otra]}
                    </h3>
                    <p className="mt-1 text-xs text-[#697586]">
                        Constancias de CUIL, escolaridad, controles de salud u
                        otro respaldo del trámite.
                    </p>

                    {otros.length > 0 ? (
                        <ul className="mt-3 space-y-2">
                            {otros.map((archivo) => (
                                <li
                                    key={archivo.id}
                                    className="flex flex-col gap-2 rounded-xl border border-[#DCE3DF] bg-[#FBFCFB] p-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="min-w-0 space-y-2">
                                        <p className="text-sm font-semibold text-[#173247]">
                                            {archivo.descripcion}
                                        </p>
                                        {botonArchivo(archivo)}
                                    </div>
                                    {puedeGestionar && (
                                        <button
                                            type="button"
                                            onClick={() => quitar(archivo)}
                                            className="self-start rounded-lg p-2 text-[#9B5964] hover:bg-[#FCF1F3] sm:self-center"
                                            aria-label={`Quitar ${archivo.descripcion ?? archivo.nombre}`}
                                        >
                                            <Trash2 className="size-4" />
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    ) : (
                        !puedeGestionar && (
                            <p className="mt-3 rounded-lg border border-dashed border-[#DCE3DF] bg-[#FAFBFA] px-4 py-4 text-sm text-[#697586]">
                                No hay otra documentación cargada.
                            </p>
                        )
                    )}

                    {puedeGestionar && (
                        <form
                            onSubmit={agregarOtra}
                            className="mt-3 grid gap-3 rounded-xl border border-dashed border-[#DCE3DF] bg-[#FAFBFA] p-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end"
                        >
                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                Descripción
                                <input
                                    type="text"
                                    required
                                    maxLength={255}
                                    value={descripcionOtra}
                                    onChange={(event) =>
                                        setDescripcionOtra(event.target.value)
                                    }
                                    placeholder="Ej.: Constancia de CUIL"
                                    className={inputClass}
                                />
                            </label>
                            <label className="grid gap-1 text-xs font-semibold text-[#2C4A5E]">
                                Archivo
                                <input
                                    key={otraInputKey}
                                    type="file"
                                    required
                                    accept={ACCEPT}
                                    onChange={(event) =>
                                        setArchivoOtra(
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                    className={`${inputClass} py-2 text-xs`}
                                />
                            </label>
                            <Button
                                type="submit"
                                variant="outline"
                                disabled={subiendoTipo !== null}
                            >
                                <Plus className="size-4" />
                                {subiendoTipo === auh.tipo_otra
                                    ? 'Subiendo...'
                                    : 'Agregar'}
                            </Button>
                            {errorPorTipo[auh.tipo_otra] && (
                                <p className="text-xs text-[#9B5964] sm:col-span-3">
                                    {errorPorTipo[auh.tipo_otra]}
                                </p>
                            )}
                        </form>
                    )}
                </section>
            </CardContent>

            {planillaAbierta && (
                <AuhPlanillaDialog
                    legajoId={legajoId}
                    tipo={planillaAbierta}
                    titulo={auh.tipos[planillaAbierta]}
                    formulario={auh.formulario}
                    firmantes={auh.firmantes[planillaAbierta] ?? {}}
                    onClose={() => setPlanillaAbierta(null)}
                />
            )}
        </Card>
    );
}
