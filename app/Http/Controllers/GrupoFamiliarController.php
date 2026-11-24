<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVinculoFamiliarRequest;
use App\Models\GrupoFamiliar;
use App\Models\GrupoFamiliarMiembro;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\VinculoFamiliar;
use App\Support\Barrios;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoFamiliarController extends Controller
{
    public function store(StoreVinculoFamiliarRequest $request, Legajo $legajo): RedirectResponse
    {
        $persona = $legajo->persona;

        abort_unless($persona?->activo, 422, 'La persona debe tener un legajo activo.');

        $datos = $request->validated();

        if ($request->registraFamiliarNuevo()) {
            return $this->registrarFamiliar($request, $legajo, $persona, $datos);
        }

        $personaRelacionada = Persona::findOrFail($datos['persona_relacionada_id']);

        abort_if($personaRelacionada->is($persona), 422, 'Una persona no puede vincularse consigo misma.');

        $yaVinculadas = VinculoFamiliar::query()
            ->whereNull('desvinculado_en')
            ->where(function ($query) use ($persona, $personaRelacionada) {
                $query
                    ->where(function ($query) use ($persona, $personaRelacionada) {
                        $query->where('persona_id', $persona->id)
                            ->where('persona_relacionada_id', $personaRelacionada->id);
                    })
                    ->orWhere(function ($query) use ($persona, $personaRelacionada) {
                        $query->where('persona_id', $personaRelacionada->id)
                            ->where('persona_relacionada_id', $persona->id);
                    });
            })
            ->exists();

        abort_if($yaVinculadas, 422, 'Estas personas ya tienen un vínculo activo.');

        DB::transaction(fn () => $this->vincular($persona, $personaRelacionada, $datos['tipo_vinculo'], $request->user()->id));

        return back()->with('status', 'Vínculo familiar agregado.');
    }

    /**
     * Actualiza los datos de un familiar registrado sin legajo, para que se
     * reutilicen correctos en actas y planillas.
     */
    public function actualizarFamiliar(Request $request, Legajo $legajo, VinculoFamiliar $vinculo): RedirectResponse
    {
        abort_unless($request->user()->tieneRol('admin', 'coordinador', 'profesional', 'administrativo'), 403);
        abort_unless(
            $vinculo->desvinculado_en === null
                && ($vinculo->persona_id === $legajo->persona_id || $vinculo->persona_relacionada_id === $legajo->persona_id),
            404,
        );

        $familiar = $vinculo->persona_id === $legajo->persona_id ? $vinculo->personaRelacionada : $vinculo->persona;

        abort_if($familiar === null || $familiar->legajos()->exists(), 422, 'Los datos de una persona con legajo se editan desde su legajo.');

        $datos = $request->validate(
            StoreVinculoFamiliarRequest::reglasFamiliar(ignorarPersonaId: $familiar->id),
            StoreVinculoFamiliarRequest::mensajesFamiliar(),
        );

        $familiar->update($this->datosPersona($datos));

        return back()->with('status', 'Datos del familiar actualizados.');
    }

    /**
     * Registra a un familiar que no está en el sistema y lo vincula al legajo.
     *
     * @param  array<string, mixed>  $datos
     */
    private function registrarFamiliar(StoreVinculoFamiliarRequest $request, Legajo $legajo, Persona $persona, array $datos): RedirectResponse
    {
        DB::transaction(function () use ($request, $persona, $datos): void {
            $familiar = Persona::create($this->datosPersona($datos['familiar']) + [
                'localidad_id' => $persona->localidad_id,
                'activo' => true,
            ]);

            $this->vincular($persona, $familiar, $datos['tipo_vinculo'], $request->user()->id);
        });

        return back()->with('status', 'Familiar registrado y vinculado al legajo.');
    }

    private function vincular(Persona $persona, Persona $personaRelacionada, string $tipoVinculo, int $usuarioId): void
    {
        $grupo = $persona->gruposFamiliares()->first();

        if (! $grupo) {
            $grupo = GrupoFamiliar::create([
                'nombre' => "Familia {$persona->apellido}",
                'creado_por' => $usuarioId,
            ]);
        }

        $this->activarMiembro($grupo, $persona, $usuarioId);
        $this->activarMiembro($grupo, $personaRelacionada, $usuarioId);

        $grupo->vinculos()->create([
            'persona_id' => $persona->id,
            'persona_relacionada_id' => $personaRelacionada->id,
            'tipo_vinculo' => $tipoVinculo,
            'creado_por' => $usuarioId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function datosPersona(array $datos): array
    {
        return [
            'apellido' => $datos['apellido'],
            'nombre' => $datos['nombre'],
            'dni' => $datos['dni'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'genero' => $datos['genero'] ?? 'NO_ESPECIFICA',
            'telefono' => $datos['telefono'] ?? null,
            'direccion_calle' => $datos['direccion_calle'] ?? null,
            'direccion_numero' => $datos['direccion_numero'] ?? null,
            'barrio_id' => Barrios::idPorNombre($datos['barrio'] ?? null),
            'observaciones' => $datos['observaciones'] ?? null,
        ];
    }

    public function destroy(Request $request, Legajo $legajo, VinculoFamiliar $vinculo): RedirectResponse
    {
        abort_unless(
            $vinculo->desvinculado_en === null
                && ($vinculo->persona_id === $legajo->persona_id || $vinculo->persona_relacionada_id === $legajo->persona_id),
            404,
        );

        $vinculo->update([
            'desvinculado_en' => now(),
            'desvinculado_por' => $request->user()->id,
        ]);

        return back()->with('status', 'Vínculo familiar desvinculado.');
    }

    private function activarMiembro(GrupoFamiliar $grupo, Persona $persona, int $usuarioId): void
    {
        $miembro = GrupoFamiliarMiembro::query()
            ->where('grupo_familiar_id', $grupo->id)
            ->where('persona_id', $persona->id)
            ->whereNull('desvinculado_en')
            ->first();

        if ($miembro) {
            return;
        }

        GrupoFamiliarMiembro::create([
            'grupo_familiar_id' => $grupo->id,
            'persona_id' => $persona->id,
            'creado_por' => $usuarioId,
        ]);
    }
}
