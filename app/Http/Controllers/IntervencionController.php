<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarIntervencionRequest;
use App\Models\Acta;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\TipoIntervencion;
use App\Models\User;
use App\Models\VinculoFamiliar;
use App\Services\ActaService;
use App\Services\PuntajeService;
use App\Support\Barrios;
use App\Support\Escuelas;
use App\Support\ValidacionActa;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class IntervencionController extends Controller
{
    private const DISCO = 'local'; // privado: storage/app/private (no accesible por URL)

    public function create(Request $request, Legajo $legajo, ActaService $actas): Response
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'intervenciones');

        return Inertia::render('intervenciones/create', [
            'legajo' => $this->resumenLegajo($legajo),
            'tipos' => $this->tipos(),
            'actas' => $actas->configuracionParaHojas($legajo, $request->user()),
            'barrios' => Barrios::nombres(),
            'escuelas' => Escuelas::nombres(),
            'profesionales' => $this->profesionales(),
            'profesionalPorDefecto' => $request->user()->id,
            'hoy' => now()->toDateString(),
            'horaAhora' => now()->format('H:i'),
            'legajosRelacionados' => $request->user()->tieneRol('admin', 'coordinador', 'profesional', 'administrativo')
                ? $this->legajosRelacionados($legajo)
                : [],
        ]);
    }

    public function store(GuardarIntervencionRequest $request, Legajo $legajo, ActaService $actas, PuntajeService $puntaje)
    {
        $datos = $request->validated();
        $tipoActa = $request->generaActa() ? $request->tipoActa() : null;
        // El responsable del acta es el profesional elegido para la intervención.
        $profesional = User::find($datos['profesional_id']);
        $acta = $tipoActa ? $this->datosDelActa($request, $tipoActa, $datos, $actas, $profesional) : null;
        $legajosDestino = $this->legajosDestinoValidos($legajo, $datos['legajos_destino_ids'] ?? []);

        if ($legajosDestino->count() !== count($datos['legajos_destino_ids'] ?? [])) {
            throw ValidationException::withMessages([
                'legajos_destino_ids' => 'Solo podés copiar la intervención a legajos vinculados y habilitados.',
            ]);
        }

        $grupoCopiaId = $legajosDestino->isNotEmpty() ? (string) Str::uuid() : null;
        $rutas = [];
        $rutasObsoletas = [];
        $intervencionPrincipalId = null;

        try {
            DB::transaction(function () use ($request, $legajo, $datos, $legajosDestino, $grupoCopiaId, $acta, $tipoActa, $actas, $profesional, &$rutas, &$rutasObsoletas, &$intervencionPrincipalId) {
                $intervencion = Intervencion::create([
                    'legajo_id' => $legajo->id,
                    'grupo_copia_id' => $grupoCopiaId,
                    'tipo_intervencion_id' => $datos['tipo_intervencion_id'],
                    'profesional_id' => $datos['profesional_id'],
                    'fecha_intervencion' => $datos['fecha_intervencion'],
                    'hora_intervencion' => $datos['hora_intervencion'],
                    'detalle' => $acta ? $acta['detalle'] : $datos['detalle'],
                    'firma_nombre' => $acta ? $acta['firmante'] : $datos['firma_nombre'],
                    'firma_aclaracion' => $acta ? $acta['firmante'] : $datos['firma_aclaracion'],
                    'firmada_en' => $acta && $acta['firmante'] ? now() : null,
                    'firma_entrevistado_nombre' => $acta ? null : ($datos['firma_entrevistado_nombre'] ?? null),
                    'firma_entrevistado_aclaracion' => $acta ? null : ($datos['firma_entrevistado_aclaracion'] ?? null),
                    'creado_por' => $request->user()->id,
                ]);
                $intervencionPrincipalId = $intervencion->id;

                $this->guardarArchivos($request, $legajo, $intervencion, $rutas);

                if ($acta) {
                    $actas->registrar($tipoActa, $acta['datos'], $acta['firmas'], $request->user(), $legajo, null, $intervencion, $rutas, $profesional);
                } else {
                    $this->guardarFirmas($request, $intervencion, $rutas, $rutasObsoletas);
                }

                foreach ($legajosDestino as $legajoDestino) {
                    $copia = Intervencion::create([
                        'legajo_id' => $legajoDestino->id,
                        'grupo_copia_id' => $grupoCopiaId,
                        'intervencion_origen_id' => $intervencion->id,
                        'tipo_intervencion_id' => $datos['tipo_intervencion_id'],
                        'profesional_id' => $datos['profesional_id'],
                        'fecha_intervencion' => $datos['fecha_intervencion'],
                        'hora_intervencion' => $datos['hora_intervencion'],
                        'detalle' => $acta ? $acta['detalle'] : $datos['detalle'],
                        // El acta copiada es el mismo documento firmado: conserva quién firmó por el Servicio.
                        'firma_nombre' => $acta['firmante'] ?? null,
                        'firma_aclaracion' => $acta['firmante'] ?? null,
                        'firmada_en' => ($acta['firmante'] ?? null) ? now() : null,
                        'creado_por' => $request->user()->id,
                    ]);

                    $this->copiarAdjuntos($intervencion, $legajoDestino, $copia, $request->user()->id, $rutas);

                    if ($acta) {
                        // Cada legajo tiene su propia acta, con su número de legajo en el documento.
                        $actas->registrar($tipoActa, $acta['datos'], $acta['firmas'], $request->user(), $legajoDestino, null, $copia, $rutas, $profesional);
                    }
                }
            });
            Storage::disk(self::DISCO)->delete($rutasObsoletas);

            if ($acta === null && $intervencionPrincipalId !== null) {
                $puntaje->registrarIntervencion(Intervencion::findOrFail($intervencionPrincipalId));
            }
        } catch (Throwable $e) {
            Storage::disk(self::DISCO)->delete($rutas);
            report($e);

            return back()->withErrors([
                'general' => $this->mensajeError($e, 'No se pudo registrar la intervención.'),
            ]);
        }

        $mensaje = match (true) {
            $acta !== null && $legajosDestino->isNotEmpty() => 'Intervención registrada con su '.mb_strtolower(Acta::etiquetaDe($tipoActa)).' y copiada en los legajos de los familiares seleccionados.',
            $acta !== null => 'Intervención registrada con su '.mb_strtolower(Acta::etiquetaDe($tipoActa)).'.',
            $legajosDestino->isEmpty() => 'Intervención registrada correctamente.',
            default => 'Intervención registrada y copiada en los legajos vinculados seleccionados.',
        };

        return redirect()->route('legajos.show', $legajo->id)->with('status', $mensaje);
    }

    public function edit(Legajo $legajo, Intervencion $intervencion, ActaService $actas): Response
    {
        $this->authorizeLegajoSection(request()->user(), $legajo, 'intervenciones');
        abort_unless($intervencion->legajo_id === $legajo->id, 404);
        $legajo = $intervencion->legajo()->with('persona')->firstOrFail();

        return Inertia::render('intervenciones/edit', [
            'legajo' => $this->resumenLegajo($legajo),
            'intervencion' => [
                'id' => $intervencion->id,
                'fecha_intervencion' => $intervencion->fecha_intervencion?->format('Y-m-d'),
                'hora_intervencion' => $intervencion->hora_intervencion
                    ? substr($intervencion->hora_intervencion, 0, 5)
                    : null,
                'tipo_intervencion_id' => $intervencion->tipo_intervencion_id,
                'profesional_id' => $intervencion->profesional_id,
                'detalle' => $intervencion->detalle,
                'firma_nombre' => $intervencion->firma_nombre,
                'firma_aclaracion' => $intervencion->firma_aclaracion,
                'tiene_firma_responsable' => filled($intervencion->firma_archivo_path),
                'firma_entrevistado_nombre' => $intervencion->firma_entrevistado_nombre,
                'firma_entrevistado_aclaracion' => $intervencion->firma_entrevistado_aclaracion,
                'tiene_firma_entrevistado' => filled($intervencion->firma_entrevistado_archivo_path),
            ],
            'adjuntos' => $this->adjuntosDe($intervencion->id),
            'acta' => ($acta = $intervencion->acta) ? [
                'etiqueta' => $acta->etiqueta(),
                'firmado' => $acta->firmado,
                'requiere_firma' => Acta::firmantesDe($acta->tipo) !== [],
                'descarga_url' => route('actas.descargar', $acta),
                'vista_url' => route('actas.vista', $acta),
            ] : null,
            // La hoja del acta se muestra igual que el Word: editable mientras no tenga firmas.
            'actaHoja' => $intervencion->acta && ! Acta::esAuh($intervencion->acta->tipo)
                ? $actas->hoja($intervencion->acta)
                : null,
            'barrios' => Barrios::nombres(),
            'escuelas' => Escuelas::nombres(),
            'tipos' => $this->tipos($intervencion->tipo_intervencion_id),
            'profesionales' => $this->profesionales(),
            'hoy' => now()->toDateString(),
            'horaAhora' => now()->format('H:i'),
            'legajosRelacionados' => [],
        ]);
    }

    public function update(GuardarIntervencionRequest $request, Legajo $legajo, Intervencion $intervencion, ActaService $actas)
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'intervenciones');
        abort_unless($intervencion->legajo_id === $legajo->id, 404);
        $datos = $request->validated();
        $legajo = $intervencion->legajo()->firstOrFail();
        $actaEditable = $request->actaEditable();
        $profesional = User::find($datos['profesional_id']);
        $hojaActa = $actaEditable ? $this->datosDelActa($request, $actaEditable->tipo, $datos, $actas, $profesional) : null;
        // Una firma ya registrada no se cambia al editar: ni la imagen ni a quién pertenece.
        $responsableFirmado = filled($intervencion->firma_archivo_path);
        $entrevistadoFirmado = filled($intervencion->firma_entrevistado_archivo_path);
        $rutas = [];
        $rutasObsoletas = [];

        try {
            DB::transaction(function () use ($request, $legajo, $intervencion, $datos, $actaEditable, $hojaActa, $actas, $profesional, $responsableFirmado, $entrevistadoFirmado, &$rutas, &$rutasObsoletas) {
                $intervencion->update([
                    'tipo_intervencion_id' => $datos['tipo_intervencion_id'],
                    'profesional_id' => $datos['profesional_id'],
                    'fecha_intervencion' => $datos['fecha_intervencion'],
                    'hora_intervencion' => $datos['hora_intervencion'],
                    'detalle' => $hojaActa ? $hojaActa['detalle'] : ($datos['detalle'] ?? $intervencion->detalle),
                    'firma_nombre' => match (true) {
                        $responsableFirmado => $intervencion->firma_nombre,
                        $hojaActa !== null => $hojaActa['firmante'] ?? $intervencion->firma_nombre,
                        default => $datos['firma_nombre'] ?? $intervencion->firma_nombre,
                    },
                    'firma_aclaracion' => match (true) {
                        $responsableFirmado => $intervencion->firma_aclaracion,
                        $hojaActa !== null => $hojaActa['firmante'] ?? $intervencion->firma_aclaracion,
                        default => $datos['firma_aclaracion'] ?? $intervencion->firma_aclaracion,
                    },
                    'firma_entrevistado_nombre' => $entrevistadoFirmado
                        ? $intervencion->firma_entrevistado_nombre
                        : ($datos['firma_entrevistado_nombre'] ?? null),
                    'firma_entrevistado_aclaracion' => $entrevistadoFirmado
                        ? $intervencion->firma_entrevistado_aclaracion
                        : ($datos['firma_entrevistado_aclaracion'] ?? null),
                    'actualizado_por' => $request->user()->id,
                ]);

                if (
                    ! $entrevistadoFirmado
                    && blank($datos['firma_entrevistado_nombre'] ?? null)
                    && blank($datos['firma_entrevistado_aclaracion'] ?? null)
                    && ! $request->hasFile('firma_entrevistado_archivo')
                    && ! $request->filled('firma_entrevistado_dibujo')
                    && $intervencion->firma_entrevistado_archivo_path
                ) {
                    $rutasObsoletas[] = $intervencion->firma_entrevistado_archivo_path;
                    $intervencion->update([
                        'firma_entrevistado_archivo_path' => null,
                        'firma_entrevistado_en' => null,
                    ]);
                }

                if ($actaEditable && $hojaActa) {
                    $actas->actualizar($actaEditable, $hojaActa['datos'], $hojaActa['firmas'], $request->user(), $rutas, $rutasObsoletas, $profesional);

                    if ($hojaActa['firmante'] && ! $intervencion->firmada_en) {
                        $intervencion->update(['firmada_en' => now()]);
                    }
                }

                $this->guardarArchivos($request, $legajo, $intervencion, $rutas);
                $this->guardarFirmas($request, $intervencion, $rutas, $rutasObsoletas, conservarExistentes: true);
            });
            Storage::disk(self::DISCO)->delete($rutasObsoletas);
        } catch (Throwable $e) {
            Storage::disk(self::DISCO)->delete($rutas);
            report($e);

            return back()->withErrors([
                'general' => $this->mensajeError($e, 'No se pudieron guardar los cambios.'),
            ]);
        }

        return redirect()->route('legajos.show', $legajo->id)
            ->with('status', 'Intervención actualizada correctamente.');
    }

    public function descargarFirma(Request $request, Legajo $legajo, Intervencion $intervencion, string $firma)
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'intervenciones');
        abort_unless($intervencion->legajo_id === $legajo->id, 404);

        $columna = match ($firma) {
            'responsable' => 'firma_archivo_path',
            'entrevistado' => 'firma_entrevistado_archivo_path',
            default => abort(404),
        };
        $ruta = $intervencion->{$columna};

        abort_unless($ruta && Storage::disk(self::DISCO)->exists($ruta), 404);

        return Storage::disk(self::DISCO)->download($ruta, "firma-{$firma}-intervencion-{$intervencion->id}.".pathinfo($ruta, PATHINFO_EXTENSION));
    }

    /**
     * Descarga de un adjunto de intervención, con registro en auditoria_descargas.
     */
    public function descargarAdjunto(Request $request, int $documento)
    {
        $archivo = DB::table('documentos')
            ->join('intervenciones', 'intervenciones.id', '=', 'documentos.intervencion_id')
            ->where('documentos.id', $documento)
            ->whereNotNull('documentos.intervencion_id')
            ->where('documentos.activo', 1)
            ->select('documentos.*', 'intervenciones.legajo_id')
            ->first();

        abort_if(! $archivo, 404);
        $legajo = Legajo::findOrFail($archivo->legajo_id);
        $this->authorizeLegajoSection($request->user(), $legajo, 'intervenciones');
        abort_unless(Storage::disk(self::DISCO)->exists($archivo->ruta_archivo), 404);

        DB::table('auditoria_descargas')->insert([
            'documento_id' => $archivo->id,
            'usuario_id' => $request->user()->id,
            'ip_origen' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return Storage::disk(self::DISCO)->download($archivo->ruta_archivo, $archivo->nombre_archivo);
    }

    /**
     * Guarda los archivos nuevos en disco y en `documentos`.
     * Cada ruta guardada se agrega a $rutas para poder borrarla si algo falla.
     */
    private function guardarArchivos(Request $request, Legajo $legajo, Intervencion $intervencion, array &$rutas): void
    {
        $archivos = $request->file('archivos', []);

        if (empty($archivos)) {
            return;
        }

        $tipoDocumentoId = DB::table('tipos_documento')->where('nombre', 'Otro')->value('id');

        if ($tipoDocumentoId === null) {
            throw new RuntimeException('Falta el tipo de documento "Otro": ejecutá TiposDocumentoSeeder.');
        }

        foreach ($archivos as $archivo) {
            $nombre = Str::limit($archivo->getClientOriginalName(), 255, '');
            $tamanio = $archivo->getSize();

            $ruta = $archivo->store("legajos/{$legajo->id}/intervenciones/{$intervencion->id}", self::DISCO);
            if (! is_string($ruta)) {
                throw new RuntimeException('No se pudo guardar uno de los adjuntos.');
            }
            $rutas[] = $ruta;

            DB::table('documentos')->insert([
                'persona_id' => $legajo->persona_id,
                'legajo_id' => $legajo->id,
                'intervencion_id' => $intervencion->id,
                'tipo_documento_id' => $tipoDocumentoId,
                'nombre_archivo' => $nombre,
                'ruta_archivo' => $ruta,
                'tamanio_bytes' => $tamanio,
                'subido_por' => $request->user()->id,
            ]);
        }
    }

    /**
     * @param  bool  $conservarExistentes  Al editar, una firma ya guardada no se reemplaza.
     */
    private function guardarFirmas(Request $request, Intervencion $intervencion, array &$rutas, array &$rutasObsoletas, bool $conservarExistentes = false): void
    {
        $firmas = [
            'responsable' => [
                'dibujo' => 'firma_responsable_dibujo',
                'archivo' => 'firma_responsable_archivo',
                'columna' => 'firma_archivo_path',
                'fecha' => 'firmada_en',
            ],
            'entrevistado' => [
                'dibujo' => 'firma_entrevistado_dibujo',
                'archivo' => 'firma_entrevistado_archivo',
                'columna' => 'firma_entrevistado_archivo_path',
                'fecha' => 'firma_entrevistado_en',
            ],
        ];

        foreach ($firmas as $nombre => $firma) {
            $ruta = null;

            if ($conservarExistentes && filled($intervencion->{$firma['columna']})) {
                continue;
            }

            if ($request->hasFile($firma['archivo'])) {
                $archivo = $request->file($firma['archivo']);
                $extension = strtolower($archivo->getClientOriginalExtension() ?: 'bin');
                $ruta = $archivo->storeAs(
                    "legajos/{$intervencion->legajo_id}/intervenciones/{$intervencion->id}/firmas",
                    "{$nombre}-".Str::uuid().".{$extension}",
                    self::DISCO,
                );
                if (! is_string($ruta)) {
                    throw new RuntimeException('No se pudo guardar el archivo de firma.');
                }
                $rutas[] = $ruta;
            } elseif ($request->filled($firma['dibujo'])) {
                $data = (string) $request->input($firma['dibujo']);
                $contenido = base64_decode((string) Str::after($data, 'base64,'), true);

                if ($contenido === false) {
                    throw new RuntimeException('La firma dibujada no pudo procesarse.');
                }

                $ruta = "legajos/{$intervencion->legajo_id}/intervenciones/{$intervencion->id}/firmas/{$nombre}-".Str::uuid().'.png';
                if (! Storage::disk(self::DISCO)->put($ruta, $contenido)) {
                    throw new RuntimeException('No se pudo guardar la firma dibujada.');
                }
                $rutas[] = $ruta;
            }

            if (! $ruta) {
                continue;
            }

            if ($intervencion->{$firma['columna']}) {
                $rutasObsoletas[] = $intervencion->{$firma['columna']};
            }

            $intervencion->update([
                $firma['columna'] => $ruta,
                $firma['fecha'] => now(),
            ]);
        }
    }

    private function adjuntosDe(int $intervencionId): array
    {
        return DB::table('documentos')
            ->where('intervencion_id', $intervencionId)
            ->where('activo', 1)
            ->orderBy('id')
            ->get(['id', 'nombre_archivo', 'tamanio_bytes'])
            ->map(fn ($adjunto) => [
                'id' => $adjunto->id,
                'nombre' => $adjunto->nombre_archivo,
                'tamanio' => $adjunto->tamanio_bytes,
            ])
            ->all();
    }

    private function legajosRelacionados(Legajo $legajo): array
    {
        $personaIds = VinculoFamiliar::query()
            ->whereNull('desvinculado_en')
            ->where(function ($query) use ($legajo) {
                $query->where('persona_id', $legajo->persona_id)
                    ->orWhere('persona_relacionada_id', $legajo->persona_id);
            })
            ->get(['persona_id', 'persona_relacionada_id'])
            ->flatMap(fn (VinculoFamiliar $vinculo) => [$vinculo->persona_id, $vinculo->persona_relacionada_id])
            ->reject(fn (int $personaId) => $personaId === $legajo->persona_id)
            ->unique()
            ->values();

        return Legajo::query()
            ->with('persona')
            ->whereIn('persona_id', $personaIds)
            ->get()
            ->map(fn (Legajo $relacionado) => [
                'id' => $relacionado->id,
                'numero_legajo' => $relacionado->numero_legajo,
                'persona' => $relacionado->persona
                    ? "{$relacionado->persona->apellido}, {$relacionado->persona->nombre}"
                    : null,
            ])
            ->values()
            ->all();
    }

    private function legajosDestinoValidos(Legajo $legajo, array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $permitidos = collect($this->legajosRelacionados($legajo))->pluck('id');

        return Legajo::query()
            ->with('persona')
            ->whereIn('id', $permitidos->intersect($ids))
            ->get();
    }

    private function copiarAdjuntos(Intervencion $origen, Legajo $legajo, Intervencion $copia, int $usuarioId, array &$rutas): void
    {
        $adjuntos = DB::table('documentos')
            ->where('intervencion_id', $origen->id)
            ->where('activo', 1)
            ->get();

        foreach ($adjuntos as $adjunto) {
            $nombre = basename($adjunto->ruta_archivo);
            $ruta = "legajos/{$legajo->id}/intervenciones/{$copia->id}/{$nombre}";

            if (! Storage::disk(self::DISCO)->copy($adjunto->ruta_archivo, $ruta)) {
                throw new RuntimeException('No se pudo copiar uno de los adjuntos.');
            }

            $rutas[] = $ruta;

            DB::table('documentos')->insert([
                'persona_id' => $legajo->persona_id,
                'legajo_id' => $legajo->id,
                'intervencion_id' => $copia->id,
                'tipo_documento_id' => $adjunto->tipo_documento_id,
                'nombre_archivo' => $adjunto->nombre_archivo,
                'ruta_archivo' => $ruta,
                'tamanio_bytes' => $adjunto->tamanio_bytes,
                'subido_por' => $usuarioId,
            ]);
        }
    }

    private function resumenLegajo(Legajo $legajo): array
    {
        $legajo->loadMissing('persona');

        return [
            'id' => $legajo->id,
            'persona_id' => $legajo->persona_id,
            'numero_legajo' => $legajo->numero_legajo,
            'persona' => $legajo->persona
                ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}"
                : null,
        ];
    }

    /**
     * @return Collection<int, array{id: int, nombre: string, acta: string|null}>
     */
    private function tipos(?int $incluirId = null)
    {
        return TipoIntervencion::query()
            ->where(fn ($query) => $query->where('activo', true)->when($incluirId, fn ($actual, int $id) => $actual->orWhere('id', $id)))
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (TipoIntervencion $tipo) => [
                'id' => $tipo->id,
                'nombre' => $tipo->nombre,
                'acta' => Acta::tipoDesdeIntervencion($tipo->nombre),
            ]);
    }

    /**
     * Datos del acta de una intervención: la fecha y hora son las de la intervención.
     *
     * @param  array<string, mixed>  $datos
     * @return array{datos: array<string, mixed>, firmas: array<string, array{nombre: string, png: string}>, detalle: string, firmante: string|null}
     */
    private function datosDelActa(GuardarIntervencionRequest $request, string $tipo, array $datos, ActaService $actas, ?User $autor): array
    {
        $datosActa = ValidacionActa::datos($datos['acta'] ?? []);
        $datosActa['fecha'] = $datos['fecha_intervencion'];

        if (! Acta::esAuh($tipo)) {
            $datosActa['hora'] = $datos['hora_intervencion'];
        }

        $firmas = ValidacionActa::firmas($tipo, (array) $request->input('acta', []));
        $actas->aplicarResponsable($tipo, $datosActa, $firmas, $autor);

        return [
            'datos' => $datosActa,
            'firmas' => $firmas,
            'detalle' => $actas->resumen($tipo, $datosActa),
            'firmante' => $actas->firmanteDelServicio(
                collect($firmas)->map(fn (array $firma, string $rol) => ['rol' => $rol, 'nombre' => $firma['nombre']])->values()->all(),
            ),
        ];
    }

    private function profesionales()
    {
        return User::query()
            ->where('activo', true)
            ->where('estado_cuenta', User::ESTADO_APROBADO)
            ->whereHas('rol', fn ($query) => $query->where('nombre', 'profesional'))
            ->orderBy('name')
            ->get(['id', 'name', 'profesion']);
    }

    private function mensajeError(Throwable $e, string $base): string
    {
        return config('app.debug')
            ? "{$base} {$e->getMessage()}"
            : "{$base} Intentá nuevamente; si el problema continúa, avisá al administrador.";
    }
}
