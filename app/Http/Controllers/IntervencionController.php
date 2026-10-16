<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarIntervencionRequest;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\TipoIntervencion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class IntervencionController extends Controller
{
    private const DISCO = 'local'; // privado: storage/app/private (no accesible por URL)

    public function create(Request $request, Legajo $legajo): Response
    {
        return Inertia::render('intervenciones/create', [
            'legajo' => $this->resumenLegajo($legajo),
            'tipos' => $this->tipos(),
            'profesionales' => $this->profesionales(),
            'profesionalPorDefecto' => $request->user()->id,
            'hoy' => now()->toDateString(),
        ]);
    }

    public function store(GuardarIntervencionRequest $request, Legajo $legajo)
    {
        $datos = $request->validated();
        $rutas = [];

        try {
            DB::transaction(function () use ($request, $legajo, $datos, &$rutas) {
                $intervencion = Intervencion::create([
                    'legajo_id' => $legajo->id,
                    'tipo_intervencion_id' => $datos['tipo_intervencion_id'],
                    'profesional_id' => $datos['profesional_id'],
                    'fecha_intervencion' => $datos['fecha_intervencion'],
                    'detalle' => $datos['detalle'],
                    'creado_por' => $request->user()->id,
                ]);

                $this->guardarArchivos($request, $legajo, $intervencion, $rutas);
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISCO)->delete($rutas);
            report($e);

            return back()->withErrors([
                'general' => $this->mensajeError($e, 'No se pudo registrar la intervención.'),
            ]);
        }

        return redirect()->route('legajos.show', $legajo)
            ->with('status', 'Intervención registrada correctamente.');
    }

    public function edit(Intervencion $intervencion): Response
    {
        $legajo = $intervencion->legajo()->with('persona')->firstOrFail();

        return Inertia::render('intervenciones/edit', [
            'legajo' => $this->resumenLegajo($legajo),
            'intervencion' => [
                'id' => $intervencion->id,
                'fecha_intervencion' => $intervencion->fecha_intervencion?->format('Y-m-d'),
                'tipo_intervencion_id' => $intervencion->tipo_intervencion_id,
                'profesional_id' => $intervencion->profesional_id,
                'detalle' => $intervencion->detalle,
            ],
            'adjuntos' => $this->adjuntosDe($intervencion->id),
            'tipos' => $this->tipos(),
            'profesionales' => $this->profesionales(),
            'hoy' => now()->toDateString(),
        ]);
    }

    public function update(GuardarIntervencionRequest $request, Intervencion $intervencion)
    {
        $datos = $request->validated();
        $legajo = $intervencion->legajo()->firstOrFail();
        $rutas = [];

        try {
            DB::transaction(function () use ($request, $legajo, $intervencion, $datos, &$rutas) {
                $intervencion->update([
                    'tipo_intervencion_id' => $datos['tipo_intervencion_id'],
                    'profesional_id' => $datos['profesional_id'],
                    'fecha_intervencion' => $datos['fecha_intervencion'],
                    'detalle' => $datos['detalle'],
                    'actualizado_por' => $request->user()->id,
                ]);

                $this->guardarArchivos($request, $legajo, $intervencion, $rutas);
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISCO)->delete($rutas);
            report($e);

            return back()->withErrors([
                'general' => $this->mensajeError($e, 'No se pudieron guardar los cambios.'),
            ]);
        }

        return redirect()->route('legajos.show', $legajo)
            ->with('status', 'Intervención actualizada correctamente.');
    }

    /**
     * Descarga de un adjunto de intervención, con registro en auditoria_descargas.
     */
    public function descargarAdjunto(Request $request, int $documento)
    {
        $archivo = DB::table('documentos')
            ->where('id', $documento)
            ->whereNotNull('intervencion_id')
            ->where('activo', 1)
            ->first();

        abort_if(! $archivo, 404);
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

    private function resumenLegajo(Legajo $legajo): array
    {
        $legajo->loadMissing('persona');

        return [
            'id' => $legajo->id,
            'numero_legajo' => $legajo->numero_legajo,
            'persona' => $legajo->persona
                ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}"
                : null,
        ];
    }

    private function tipos()
    {
        return TipoIntervencion::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    private function profesionales()
    {
        return User::where('activo', true)->orderBy('name')->get(['id', 'name']);
    }

    private function mensajeError(Throwable $e, string $base): string
    {
        return config('app.debug')
            ? "{$base} {$e->getMessage()}"
            : "{$base} Intentá nuevamente; si el problema continúa, avisá al administrador.";
    }
}
