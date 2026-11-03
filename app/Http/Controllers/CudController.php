<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCudRequest;
use App\Models\AuditoriaEvento;
use App\Models\CudArchivo;
use App\Models\CudCertificado;
use App\Models\Legajo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CudController extends Controller
{
    public function show(Request $request, Legajo $legajo): Response
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'persona');

        $actual = $legajo->cudActual()->with('archivos')->first();
        $historial = $legajo->cuds()
            ->where('activo', false)
            ->with('archivos')
            ->latest('creado_en')
            ->get();

        return Inertia::render('legajos/documentacion', [
            'legajo' => [
                'id' => $legajo->id,
                'numero_legajo' => $legajo->numero_legajo,
                'persona' => $legajo->persona ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}" : null,
            ],
            'cud' => $actual ? $this->certificado($actual, $legajo) : null,
            'historial' => $historial->map(fn (CudCertificado $certificado) => $this->certificado($certificado, $legajo))->values(),
            'puedeGestionar' => $request->user()->esAdmin() || $request->user()->tieneRol('coordinador'),
            'status' => session('status'),
        ]);
    }

    public function store(StoreCudRequest $request, Legajo $legajo): RedirectResponse
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'persona');

        $datos = $request->validated();
        $rutas = [];
        $actual = $legajo->cudActual()->with('archivos')->first();

        if ($actual && ! $this->hayCambios($actual, $datos, $request)) {
            return back()->with('status', 'La documentación del CUD ya está actualizada. No se creó una nueva versión.');
        }

        try {
            DB::transaction(function () use ($request, $legajo, $datos, $actual, &$rutas): void {
                $antes = $actual?->only([
                    'numero_cud',
                    'fecha_vigencia',
                    'fecha_vencimiento',
                    'observaciones',
                ]);

                if ($actual) {
                    $actual->update([
                        'activo' => false,
                        'reemplazado_en' => now(),
                        'actualizado_por' => $request->user()->id,
                    ]);
                }

                $certificado = $legajo->cuds()->create([
                    'numero_cud' => $datos['numero_cud'],
                    'fecha_vigencia' => $datos['fecha_vigencia'],
                    'fecha_vencimiento' => $datos['fecha_vencimiento'],
                    'observaciones' => $datos['observaciones'] ?? null,
                    'activo' => true,
                    'creado_por' => $request->user()->id,
                    'actualizado_por' => $request->user()->id,
                ]);

                foreach ($actual?->archivos->where('activo', true) ?? [] as $archivo) {
                    $certificado->archivos()->create([
                        'tipo' => $archivo->tipo,
                        'nombre_archivo' => $archivo->nombre_archivo,
                        'ruta_archivo' => $archivo->ruta_archivo,
                        'mime_type' => $archivo->mime_type,
                        'tamanio_bytes' => $archivo->tamanio_bytes,
                        'activo' => true,
                        'subido_por' => $archivo->subido_por,
                    ]);
                }

                foreach (['frente', 'dorso'] as $tipo) {
                    if ($request->hasFile($tipo)) {
                        $certificado->archivos()->where('tipo', $tipo)->where('activo', true)->update([
                            'activo' => false,
                            'reemplazado_en' => now(),
                        ]);
                        $this->guardarArchivo($certificado, $tipo, $request->file($tipo), $request->user()->id, $rutas);
                    }
                }

                foreach ((array) $request->file('recetas', []) as $receta) {
                    $this->guardarArchivo($certificado, 'receta', $receta, $request->user()->id, $rutas);
                }

                AuditoriaEvento::registrar(
                    $actual ? 'cud.reemplazado' : 'cud.creado',
                    $certificado,
                    $antes,
                    $certificado->only(['numero_cud', 'fecha_vigencia', 'fecha_vencimiento', 'observaciones']),
                );
            });
        } catch (Throwable $exception) {
            foreach ($rutas as $ruta) {
                Storage::disk('local')->delete($ruta);
            }

            report($exception);

            return back()->withErrors(['cud' => 'No se pudo guardar el CUD. Intentá nuevamente.']);
        }

        return back()->with('status', 'La documentación del CUD se guardó correctamente.');
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function hayCambios(CudCertificado $actual, array $datos, Request $request): bool
    {
        return $actual->numero_cud !== $datos['numero_cud']
            || $actual->fecha_vigencia?->toDateString() !== $datos['fecha_vigencia']
            || $actual->fecha_vencimiento?->toDateString() !== $datos['fecha_vencimiento']
            || trim((string) $actual->observaciones) !== trim((string) ($datos['observaciones'] ?? ''))
            || $request->hasFile('frente')
            || $request->hasFile('dorso')
            || $request->hasFile('recetas');
    }

    public function deleteFile(Request $request, Legajo $legajo, CudArchivo $cudArchivo): RedirectResponse
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'persona');

        $cudArchivo->load('certificado');
        abort_unless(
            $cudArchivo->certificado?->legajo_id === $legajo->id
                && $cudArchivo->certificado->activo
                && $cudArchivo->activo,
            404,
        );

        $antes = $cudArchivo->only(['tipo', 'nombre_archivo', 'ruta_archivo', 'activo']);
        $cudArchivo->update(['activo' => false, 'reemplazado_en' => now()]);
        AuditoriaEvento::registrar('cud.archivo_eliminado', $cudArchivo, $antes, $cudArchivo->only(['tipo', 'nombre_archivo', 'activo']));

        return back()->with('status', 'El archivo se quitó del CUD actual y quedó conservado en el historial.');
    }

    public function download(Request $request, Legajo $legajo, CudArchivo $cudArchivo): StreamedResponse
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'persona');

        $cudArchivo->load('certificado');
        abort_unless($cudArchivo->certificado?->legajo_id === $legajo->id, 404);
        abort_unless(Storage::disk('local')->exists($cudArchivo->ruta_archivo), 404);

        $legajo->registrarAcceso($request->user(), 'descarga_cud');
        AuditoriaEvento::registrar('cud.archivo_descargado', $cudArchivo);

        return Storage::disk('local')->download($cudArchivo->ruta_archivo, $cudArchivo->nombre_archivo, [
            'Content-Type' => $cudArchivo->mime_type,
        ]);
    }

    /**
     * @param  array<int, string>  $rutas
     */
    private function guardarArchivo(CudCertificado $certificado, string $tipo, UploadedFile $archivo, int $usuarioId, array &$rutas): void
    {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'bin');
        $ruta = $archivo->storeAs(
            "legajos/{$certificado->legajo_id}/cud/{$certificado->id}",
            Str::uuid().'.'.$extension,
            'local',
        );

        if (! is_string($ruta)) {
            throw new \RuntimeException('No se pudo guardar el archivo del CUD.');
        }

        $rutas[] = $ruta;
        $certificado->archivos()->create([
            'tipo' => $tipo,
            'nombre_archivo' => Str::limit($archivo->getClientOriginalName(), 255, ''),
            'ruta_archivo' => $ruta,
            'mime_type' => $archivo->getMimeType() ?: 'application/octet-stream',
            'tamanio_bytes' => (int) ($archivo->getSize() ?: 0),
            'activo' => true,
            'subido_por' => $usuarioId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function certificado(CudCertificado $certificado, Legajo $legajo): array
    {
        return [
            'id' => $certificado->id,
            'numero_cud' => $certificado->numero_cud,
            'fecha_vigencia' => $certificado->fecha_vigencia?->toDateString(),
            'fecha_vencimiento' => $certificado->fecha_vencimiento?->toDateString(),
            'observaciones' => $certificado->observaciones,
            'estado' => $certificado->estado(),
            'activo' => $certificado->activo,
            'creado_en' => $certificado->creado_en?->toIso8601String(),
            'archivos' => $certificado->archivos
                ->where('activo', true)
                ->map(fn (CudArchivo $archivo) => [
                    'id' => $archivo->id,
                    'tipo' => $archivo->tipo,
                    'nombre' => $archivo->nombre_archivo,
                    'mime' => $archivo->mime_type,
                    'tamanio' => $archivo->tamanio_bytes,
                    'descarga_url' => route('legajos.cud.archivos.descargar', [$legajo, $archivo]),
                ])
                ->values()
                ->all(),
        ];
    }
}
