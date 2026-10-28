<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVinculoFamiliarRequest;
use App\Models\GrupoFamiliar;
use App\Models\GrupoFamiliarMiembro;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\VinculoFamiliar;
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

        DB::transaction(function () use ($request, $persona, $personaRelacionada, $datos): void {
            $grupo = $persona->gruposFamiliares()->first();

            if (! $grupo) {
                $grupo = GrupoFamiliar::create([
                    'nombre' => "Familia {$persona->apellido}",
                    'creado_por' => $request->user()->id,
                ]);
            }

            $this->activarMiembro($grupo, $persona, $request->user()->id);
            $this->activarMiembro($grupo, $personaRelacionada, $request->user()->id);

            $grupo->vinculos()->create([
                'persona_id' => $persona->id,
                'persona_relacionada_id' => $personaRelacionada->id,
                'tipo_vinculo' => $datos['tipo_vinculo'],
                'creado_por' => $request->user()->id,
            ]);
        });

        return back()->with('status', 'Vínculo familiar agregado.');
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
