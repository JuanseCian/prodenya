<?php

namespace App\Http\Controllers;

use App\Http\Requests\FirmarAuhPlanillaRequest;
use App\Http\Requests\GenerarAuhPlanillaRequest;
use App\Http\Requests\StoreAuhArchivoRequest;
use App\Models\AuditoriaEvento;
use App\Models\Auh;
use App\Models\AuhArchivo;
use App\Models\Legajo;
use App\Services\AuhPlanillaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AuhController extends Controller
{
    /**
     * Carga una planilla o documento del trámite de AUH. Si el tipo admite un
     * solo archivo, el anterior queda inactivo y se conserva como historial.
     */
    public function store(StoreAuhArchivoRequest $request, Legajo $legajo): RedirectResponse
    {
        $datos = $request->validated();
        $usuarioId = $request->user()->id;
        $ruta = null;

        try {
            DB::transaction(function () use ($request, $legajo, $datos, $usuarioId, &$ruta): void {
                $auh = $this->auhDelLegajo($legajo, $usuarioId);

                if ($datos['tipo'] !== Auh::TIPO_OTRA) {
                    $this->desactivarActivos($auh, $datos['tipo']);
                }

                $ruta = $this->guardarArchivo($auh, $request->file('archivo'));

                $archivo = $auh->archivos()->create([
                    'tipo' => $datos['tipo'],
                    'descripcion' => $datos['tipo'] === Auh::TIPO_OTRA ? $datos['descripcion'] : null,
                    'nombre_archivo' => Str::limit($request->file('archivo')->getClientOriginalName(), 255, ''),
                    'ruta_archivo' => $ruta,
                    'mime_type' => $request->file('archivo')->getMimeType() ?: 'application/octet-stream',
                    'tamanio_bytes' => (int) ($request->file('archivo')->getSize() ?: 0),
                    'firmado' => array_key_exists($datos['tipo'], Auh::PLANILLAS),
                    'activo' => true,
                    'subido_por' => $usuarioId,
                ]);

                $auh->update(['actualizado_por' => $usuarioId]);

                AuditoriaEvento::registrar('auh.archivo_cargado', $archivo, null, $archivo->only(['tipo', 'descripcion', 'nombre_archivo']));
            });
        } catch (Throwable $exception) {
            if ($ruta) {
                Storage::disk('local')->delete($ruta);
            }

            report($exception);

            return back()->withErrors(['auh' => 'No se pudo guardar el archivo de AUH. Intentá nuevamente.']);
        }

        return back()->with('status', 'Se cargó "'.Auh::tipoLabels()[$datos['tipo']].'" en el trámite de AUH.');
    }

    /**
     * Genera la planilla modelo pre-llenada en Word y recuerda los datos
     * usados para la próxima vez.
     */
    public function generarPlanilla(
        GenerarAuhPlanillaRequest $request,
        Legajo $legajo,
        string $tipo,
        AuhPlanillaService $planillas,
    ): BinaryFileResponse {
        $datos = $request->validated();
        $usuarioId = $request->user()->id;

        $auh = $this->auhDelLegajo($legajo, $usuarioId);

        $auh->update([
            'datos_planillas' => array_replace($auh->datos_planillas ?? [], $datos),
            'actualizado_por' => $usuarioId,
        ]);

        AuditoriaEvento::registrar('auh.planilla_generada', $auh, null, ['tipo' => $tipo]);

        $nombre = Str::slug(Auh::PLANILLAS[$tipo]).'-'.Str::slug($legajo->numero_legajo).'.docx';

        return response()
            ->download($planillas->generar($tipo, $datos), $nombre, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->deleteFileAfterSend();
    }

    /**
     * Genera la planilla con las firmas dibujadas en el sistema y la guarda
     * en el legajo como la versión vigente de esa planilla.
     */
    public function firmarPlanilla(
        FirmarAuhPlanillaRequest $request,
        Legajo $legajo,
        string $tipo,
        AuhPlanillaService $planillas,
    ): RedirectResponse {
        $datos = Arr::except($request->validated(), ['firmas']);
        $firmas = $request->firmas();
        $usuarioId = $request->user()->id;
        $temporal = null;
        $ruta = null;

        try {
            $temporal = $planillas->generar($tipo, $datos, $firmas);

            DB::transaction(function () use ($legajo, $tipo, $datos, $firmas, $usuarioId, $temporal, &$ruta): void {
                $auh = $this->auhDelLegajo($legajo, $usuarioId);
                $auh->update([
                    'datos_planillas' => array_replace($auh->datos_planillas ?? [], $datos),
                    'actualizado_por' => $usuarioId,
                ]);

                $this->desactivarActivos($auh, $tipo);

                $ruta = "legajos/{$legajo->id}/auh/{$auh->id}/".Str::uuid().'.docx';

                if (! Storage::disk('local')->put($ruta, (string) file_get_contents($temporal))) {
                    throw new \RuntimeException('No se pudo guardar la planilla firmada.');
                }

                $archivo = $auh->archivos()->create([
                    'tipo' => $tipo,
                    'nombre_archivo' => Str::slug(Auh::PLANILLAS[$tipo]).'-firmada-'.Str::slug($legajo->numero_legajo).'.docx',
                    'ruta_archivo' => $ruta,
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'tamanio_bytes' => (int) Storage::disk('local')->size($ruta),
                    'firmado' => true,
                    'firmas' => collect($firmas)->map(fn (array $firma, string $rol) => [
                        'rol' => $rol,
                        'rol_label' => Auh::FIRMANTES[$tipo][$rol],
                        'nombre' => $firma['nombre'],
                        'firmado_en' => now()->toIso8601String(),
                    ])->values()->all(),
                    'activo' => true,
                    'subido_por' => $usuarioId,
                ]);

                AuditoriaEvento::registrar('auh.planilla_firmada', $archivo, null, [
                    'tipo' => $tipo,
                    'firmantes' => collect($archivo->firmas)->pluck('nombre', 'rol')->all(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($ruta) {
                Storage::disk('local')->delete($ruta);
            }

            report($exception);

            return back()->withErrors(['auh' => 'No se pudo firmar la planilla. Intentá nuevamente.']);
        } finally {
            if ($temporal) {
                @unlink($temporal);
            }
        }

        return back()->with('status', 'Se firmó "'.Auh::PLANILLAS[$tipo].'" y quedó guardada en el legajo.');
    }

    public function destroy(Request $request, Legajo $legajo, AuhArchivo $auhArchivo): RedirectResponse
    {
        abort_unless($request->user()->tieneRol(...Auh::ROLES_GESTION), 403);
        $this->authorizeLegajoSection($request->user(), $legajo, 'auh');
        $this->asegurarPerteneceAlLegajo($legajo, $auhArchivo);
        abort_unless($auhArchivo->activo, 404);

        $antes = $auhArchivo->only(['tipo', 'descripcion', 'nombre_archivo', 'activo']);
        $auhArchivo->update(['activo' => false, 'reemplazado_en' => now()]);
        AuditoriaEvento::registrar('auh.archivo_eliminado', $auhArchivo, $antes, $auhArchivo->only(['tipo', 'activo']));

        return back()->with('status', 'El archivo se quitó del trámite de AUH y quedó conservado en el historial.');
    }

    public function download(Request $request, Legajo $legajo, AuhArchivo $auhArchivo): StreamedResponse
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'auh');
        $this->asegurarPerteneceAlLegajo($legajo, $auhArchivo);
        abort_unless(Storage::disk('local')->exists($auhArchivo->ruta_archivo), 404);

        AuditoriaEvento::registrar('auh.archivo_descargado', $auhArchivo);

        return Storage::disk('local')->download($auhArchivo->ruta_archivo, $auhArchivo->nombre_archivo, [
            'Content-Type' => $auhArchivo->mime_type,
        ]);
    }

    private function auhDelLegajo(Legajo $legajo, int $usuarioId): Auh
    {
        return $legajo->auhActual()->lockForUpdate()->first()
            ?? $legajo->auhs()->create([
                'estado' => Auh::ESTADO_ACTIVO,
                'activo' => true,
                'creado_por' => $usuarioId,
                'actualizado_por' => $usuarioId,
            ]);
    }

    private function desactivarActivos(Auh $auh, string $tipo): void
    {
        $auh->archivos()
            ->where('tipo', $tipo)
            ->where('activo', true)
            ->update(['activo' => false, 'reemplazado_en' => now()]);
    }

    private function asegurarPerteneceAlLegajo(Legajo $legajo, AuhArchivo $auhArchivo): void
    {
        $auhArchivo->loadMissing('auh');

        abort_unless($auhArchivo->auh?->legajo_id === $legajo->id, 404);
    }

    private function guardarArchivo(Auh $auh, UploadedFile $archivo): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'bin');
        $ruta = $archivo->storeAs(
            "legajos/{$auh->legajo_id}/auh/{$auh->id}",
            Str::uuid().'.'.$extension,
            'local',
        );

        if (! is_string($ruta)) {
            throw new \RuntimeException('No se pudo guardar el archivo de AUH.');
        }

        return $ruta;
    }
}
