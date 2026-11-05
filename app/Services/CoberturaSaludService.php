<?php

namespace App\Services;

use App\Models\AuditoriaEvento;
use App\Models\CoberturaSalud;
use App\Models\CoberturaSaludArchivo;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CoberturaSaludService
{
    /**
     * @return Collection<int, CoberturaSalud>
     */
    public function historial(Persona $persona): Collection
    {
        return $persona->coberturasSalud()
            ->where('activo', false)
            ->with('archivos')
            ->latest('fecha_desde')
            ->get();
    }

    public function actual(Persona $persona): ?CoberturaSalud
    {
        return $persona->coberturaSaludActual()
            ->with('archivos')
            ->latest('fecha_desde')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function resumen(CoberturaSalud $cobertura, Legajo $legajo): array
    {
        $cobertura->loadMissing('archivos');

        return [
            'id' => $cobertura->id,
            'tipo' => $cobertura->tipo,
            'origen' => $cobertura->origen,
            'programa' => $cobertura->programa,
            'entidad' => $cobertura->entidad,
            'plan' => $cobertura->plan,
            'numero_afiliado' => $cobertura->numero_afiliado,
            'persona_es_titular' => $cobertura->persona_es_titular,
            'titular_nombre' => $cobertura->titular_nombre,
            'titular_dni' => $cobertura->titular_dni,
            'titular_cuil' => $cobertura->titular_cuil,
            'parentesco_titular' => $cobertura->parentesco_titular,
            'fecha_desde' => $cobertura->fecha_desde?->toDateString(),
            'fecha_hasta' => $cobertura->fecha_hasta?->toDateString(),
            'observaciones' => $cobertura->observaciones,
            'estado' => $cobertura->estado(),
            'archivos' => $cobertura->archivos
                ->where('activo', true)
                ->map(fn (CoberturaSaludArchivo $archivo) => [
                    'id' => $archivo->id,
                    'tipo' => $archivo->tipo,
                    'nombre' => $archivo->nombre_archivo,
                    'mime' => $archivo->mime_type,
                    'tamanio' => $archivo->tamanio_bytes,
                    'descarga_url' => route('legajos.cobertura.archivos.descargar', [$legajo, $archivo]),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, UploadedFile>  $constancias
     */
    public function reemplazar(
        Persona $persona,
        array $datos,
        ?UploadedFile $credencial,
        array $constancias,
        User $usuario,
    ): CoberturaSalud {
        $rutas = [];

        try {
            return DB::transaction(function () use ($persona, $datos, $credencial, $constancias, $usuario, &$rutas): CoberturaSalud {
                $actual = $this->actual($persona);
                $antes = $actual?->only([
                    'tipo',
                    'origen',
                    'entidad',
                    'plan',
                    'numero_afiliado',
                    'fecha_desde',
                    'fecha_hasta',
                ]);
                $fechaDesde = Carbon::parse($datos['fecha_desde']);

                if ($actual) {
                    $fechaHasta = $actual->fecha_hasta;

                    if ($fechaHasta === null && $actual->fecha_desde?->lt($fechaDesde)) {
                        $fechaHasta = $fechaDesde->copy()->subDay();
                    }

                    $actual->update([
                        'activo' => false,
                        'fecha_hasta' => $fechaHasta,
                        'reemplazado_en' => now(),
                        'actualizado_por' => $usuario->id,
                    ]);
                }

                if ($datos['tipo'] === 'municipal') {
                    $datos['entidad'] = 'Municipalidad de San Nicolás de los Arroyos';
                    $datos['origen'] = 'municipal';
                }

                if ($datos['tipo'] === 'sin_cobertura') {
                    $datos['origen'] = 'no_aplica';
                    $datos['programa'] = null;
                    $datos['entidad'] = null;
                    $datos['plan'] = null;
                    $datos['numero_afiliado'] = null;
                    $datos['persona_es_titular'] = true;
                    $datos['titular_nombre'] = null;
                    $datos['titular_dni'] = null;
                    $datos['titular_cuil'] = null;
                    $datos['parentesco_titular'] = null;
                }

                $cobertura = $persona->coberturasSalud()->create([
                    'tipo' => $datos['tipo'],
                    'origen' => $datos['origen'],
                    'programa' => $datos['programa'] ?? null,
                    'entidad' => $datos['entidad'] ?? null,
                    'plan' => $datos['plan'] ?? null,
                    'numero_afiliado' => $datos['numero_afiliado'] ?? null,
                    'persona_es_titular' => $datos['persona_es_titular'],
                    'titular_nombre' => $datos['titular_nombre'] ?? null,
                    'titular_dni' => $datos['titular_dni'] ?? null,
                    'titular_cuil' => $datos['titular_cuil'] ?? null,
                    'parentesco_titular' => $datos['parentesco_titular'] ?? null,
                    'fecha_desde' => $fechaDesde,
                    'fecha_hasta' => $datos['fecha_hasta'] ?? null,
                    'observaciones' => $datos['observaciones'] ?? null,
                    'activo' => true,
                    'creado_por' => $usuario->id,
                    'actualizado_por' => $usuario->id,
                ]);

                if ($credencial) {
                    $this->guardarArchivo($cobertura, 'credencial', $credencial, $usuario->id, $rutas);
                }

                foreach ($constancias as $constancia) {
                    $this->guardarArchivo($cobertura, 'constancia', $constancia, $usuario->id, $rutas);
                }

                AuditoriaEvento::registrar(
                    $actual ? 'cobertura_salud.reemplazada' : 'cobertura_salud.creada',
                    $cobertura,
                    $antes,
                    $cobertura->only(['tipo', 'origen', 'entidad', 'plan', 'numero_afiliado', 'fecha_desde', 'fecha_hasta']),
                );

                return $cobertura;
            });
        } catch (Throwable $exception) {
            foreach ($rutas as $ruta) {
                Storage::disk('local')->delete($ruta);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<int, string>  $rutas
     */
    private function guardarArchivo(
        CoberturaSalud $cobertura,
        string $tipo,
        UploadedFile $archivo,
        int $usuarioId,
        array &$rutas,
    ): void {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'bin');
        $ruta = $archivo->storeAs(
            "personas/{$cobertura->persona_id}/coberturas/{$cobertura->id}",
            Str::uuid().'.'.$extension,
            'local',
        );

        if (! is_string($ruta)) {
            throw new \RuntimeException('No se pudo guardar el archivo de la cobertura.');
        }

        $rutas[] = $ruta;
        $cobertura->archivos()->create([
            'tipo' => $tipo,
            'nombre_archivo' => Str::limit($archivo->getClientOriginalName(), 255, ''),
            'ruta_archivo' => $ruta,
            'mime_type' => $archivo->getMimeType() ?: 'application/octet-stream',
            'tamanio_bytes' => (int) ($archivo->getSize() ?: 0),
            'activo' => true,
            'subido_por' => $usuarioId,
        ]);
    }
}
