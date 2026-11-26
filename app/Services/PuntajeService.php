<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\Intervencion;
use App\Models\PuntajeMovimiento;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PuntajeService
{
    /** @var array<int, string> */
    public const ROLES_RANKING = ['operador', 'oficios', 'profesional', 'administrativo'];

    public function registrarTareaCompletada(Tarea $tarea): ?PuntajeMovimiento
    {
        $usuario = $tarea->asignado;

        if (! $usuario) {
            return null;
        }

        $puntos = $tarea->prioridad === 'urgente' ? 5 : 3;

        return $this->registrar(
            $usuario,
            $puntos,
            'tarea.completada',
            "tarea:{$tarea->id}:completada",
            $tarea,
            $tarea->prioridad === 'urgente'
                ? 'Tarea urgente completada.'
                : 'Tarea completada.',
            ['prioridad' => $tarea->prioridad],
        );
    }

    public function revertirTareaCompletada(Tarea $tarea): ?PuntajeMovimiento
    {
        $original = PuntajeMovimiento::query()
            ->where('clave', "tarea:{$tarea->id}:completada")
            ->first();

        if (! $original) {
            return null;
        }

        return $this->registrar(
            $original->usuario,
            -$original->puntos,
            'tarea.reabierta',
            "tarea:{$tarea->id}:reabierta",
            $tarea,
            'Se revirtió el puntaje de una tarea reabierta.',
        );
    }

    public function registrarIntervencion(Intervencion $intervencion): ?PuntajeMovimiento
    {
        $usuario = $intervencion->profesional;

        if (! $usuario) {
            return null;
        }

        return $this->registrar(
            $usuario,
            3,
            'intervencion.registrada',
            "intervencion:{$intervencion->id}:registrada",
            $intervencion,
            'Intervención registrada.',
        );
    }

    public function registrarActa(Acta $acta, User $usuario): void
    {
        $this->registrar(
            $usuario,
            2,
            'acta.registrada',
            "acta:{$acta->id}:registrada",
            $acta,
            'Acta registrada.',
        );

        if ($acta->firmado) {
            $this->registrar(
                $usuario,
                2,
                'acta.firmada',
                "acta:{$acta->id}:firmada",
                $acta,
                'Acta registrada con todas sus firmas.',
            );
        }

        if ($acta->legajo_id !== null) {
            $this->registrarActaVinculada($acta, $usuario);
        }
    }

    public function registrarActaVinculada(Acta $acta, User $usuario): ?PuntajeMovimiento
    {
        return $this->registrar(
            $usuario,
            2,
            'acta.vinculada',
            "acta:{$acta->id}:vinculada",
            $acta,
            'Acta vinculada a un legajo.',
        );
    }

    public function registrar(
        User $usuario,
        int $puntos,
        string $evento,
        string $clave,
        ?Model $origen,
        string $descripcion,
        array $metadata = [],
    ): ?PuntajeMovimiento {
        if ($puntos === 0) {
            return null;
        }

        return PuntajeMovimiento::query()->firstOrCreate(
            ['clave' => $clave],
            [
                'usuario_id' => $usuario->id,
                'puntos' => $puntos,
                'evento' => $evento,
                'descripcion' => $descripcion,
                'origen_type' => $origen?->getMorphClass(),
                'origen_id' => $origen?->getKey(),
                'metadata' => $metadata === [] ? null : $metadata,
            ],
        );
    }
}
