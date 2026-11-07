<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedidaAbrigoRequest;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\MedidaAbrigo;
use App\Models\MedidaAbrigoActa;
use App\Models\TipoIntervencion;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MedidaAbrigoController extends Controller
{
    public function store(StoreMedidaAbrigoRequest $request, Legajo $legajo): RedirectResponse
    {
        $datos = $request->validated();
        $rutas = [];
        $fechaInicio = CarbonImmutable::parse($datos['fecha_inicio']);
        $fechaFin = $fechaInicio->addDays((int) $datos['duracion_dias'] - 1);

        try {
            DB::transaction(function () use ($request, $legajo, $datos, $fechaInicio, $fechaFin, &$rutas): void {
                $tipoIntervencion = TipoIntervencion::firstOrCreate(
                    ['nombre' => 'Medida de abrigo'],
                    ['activo' => true],
                );

                $intervencion = Intervencion::create([
                    'legajo_id' => $legajo->id,
                    'tipo_intervencion_id' => $tipoIntervencion->id,
                    'profesional_id' => $request->user()->id,
                    'fecha_intervencion' => $fechaInicio->toDateString(),
                    'hora_intervencion' => null,
                    'detalle' => $this->detalleIntervencion($datos, $fechaFin),
                    'creado_por' => $request->user()->id,
                    'actualizado_por' => $request->user()->id,
                ]);

                $medida = $legajo->medidasAbrigo()->create([
                    'intervencion_id' => $intervencion->id,
                    'ubicacion' => $datos['ubicacion'],
                    'duracion_dias' => $datos['duracion_dias'],
                    'fecha_inicio' => $fechaInicio->toDateString(),
                    'fecha_fin' => $fechaFin->toDateString(),
                    'observaciones' => $datos['observaciones'] ?? null,
                    'creado_por' => $request->user()->id,
                    'actualizado_por' => $request->user()->id,
                ]);

                foreach ($datos['actas'] as $acta) {
                    $this->guardarActa($medida, $acta['tipo'], $acta['archivo'], $request->user()->id, $rutas);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($rutas);
            report($exception);

            return back()->withErrors(['medida_abrigo' => 'No se pudo guardar la medida de abrigo. Intentá nuevamente.']);
        }

        return back()->with('status', 'La medida de abrigo se registró y quedó asentada como intervención.');
    }

    public function download(Request $request, Legajo $legajo, MedidaAbrigoActa $acta): StreamedResponse
    {
        $this->authorizeLegajoSection($request->user(), $legajo, 'medidas_abrigo');
        $acta->load('medida');
        abort_unless($acta->medida?->legajo_id === $legajo->id, 404);
        abort_unless(Storage::disk('local')->exists($acta->ruta_archivo), 404);

        return Storage::disk('local')->download($acta->ruta_archivo, $acta->nombre_archivo, [
            'Content-Type' => $acta->mime_type,
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function detalleIntervencion(array $datos, CarbonImmutable $fechaFin): string
    {
        $ubicacion = MedidaAbrigo::UBICACION_LABELS[$datos['ubicacion']];

        return sprintf(
            'Medida de abrigo en %s por %d días, desde %s hasta %s.%s',
            $ubicacion,
            $datos['duracion_dias'],
            CarbonImmutable::parse($datos['fecha_inicio'])->format('d/m/Y'),
            $fechaFin->format('d/m/Y'),
            filled($datos['observaciones'] ?? null) ? ' Observaciones: '.$datos['observaciones'] : '',
        );
    }

    /**
     * @param  array<int, string>  $rutas
     */
    private function guardarActa(
        MedidaAbrigo $medida,
        string $tipo,
        UploadedFile $archivo,
        int $usuarioId,
        array &$rutas,
    ): void {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'bin');
        $ruta = $archivo->storeAs(
            "legajos/{$medida->legajo_id}/medidas-abrigo/{$medida->id}",
            Str::uuid().'.'.$extension,
            'local',
        );

        if (! is_string($ruta)) {
            throw new \RuntimeException('No se pudo guardar el acta.');
        }

        $rutas[] = $ruta;
        $medida->actas()->create([
            'tipo' => $tipo,
            'nombre_archivo' => Str::limit($archivo->getClientOriginalName(), 255, ''),
            'ruta_archivo' => $ruta,
            'mime_type' => $archivo->getMimeType() ?: 'application/octet-stream',
            'tamanio_bytes' => (int) ($archivo->getSize() ?: 0),
            'subido_por' => $usuarioId,
        ]);
    }
}
