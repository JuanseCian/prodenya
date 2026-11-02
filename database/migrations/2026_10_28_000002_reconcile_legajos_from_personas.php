<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `personas` quedó con una copia de los datos del legajo (numero_legajo,
 * motivo_ingreso, nivel_riesgo, etc. — ver 2026_10_12_000008) pero algunos
 * caminos del código (p. ej. el flujo histórico de alta de persona) solo
 * escribían esas columnas en `personas` sin crear la fila correspondiente en
 * `legajos`, y las problemáticas quedaron solo en `persona_problematica` sin
 * reflejarse en `legajo_problematica`. Antes de eliminar las columnas
 * duplicadas de `personas` (ver la migración siguiente), esta reconcilia los
 * datos para que `legajos` sea una fuente de verdad completa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $personasConLegajo = DB::table('personas')
            ->whereNotNull('numero_legajo')
            ->get();
        $usuarioPorDefecto = DB::table('users')->orderBy('id')->value('id');
        $personaTieneMotivoIngresoId = Schema::hasColumn('personas', 'motivo_ingreso_id');

        foreach ($personasConLegajo as $persona) {
            $motivoIngresoId = $personaTieneMotivoIngresoId ? $persona->motivo_ingreso_id : null;
            $legajo = DB::table('legajos')->where('persona_id', $persona->id)->first();

            if (! $legajo) {
                $legajoId = DB::table('legajos')->insertGetId([
                    'persona_id' => $persona->id,
                    'numero_legajo' => $persona->numero_legajo,
                    // nivel_riesgo, origen_derivacion y motivo_ingreso son NOT NULL en
                    // `legajos` pero nullable en `personas`: se completan con un valor
                    // por defecto en el caso (no debería ocurrir en la práctica) de que
                    // una persona tenga numero_legajo sin el resto de los datos del legajo.
                    'motivo_ingreso' => $persona->motivo_ingreso ?: 'Sin especificar',
                    'motivo_ingreso_id' => $motivoIngresoId,
                    'nivel_riesgo' => $persona->nivel_riesgo ?: 'Bajo',
                    'origen_derivacion' => $persona->origen_derivacion ?: 'Sin especificar',
                    'profesional_id' => $persona->profesional_id,
                    'fecha_apertura' => $persona->fecha_apertura ?? today()->toDateString(),
                    'fecha_cierre' => $persona->fecha_cierre,
                    'estado' => $persona->estado ?: 'Abierto',
                    'observaciones' => $persona->observaciones_legajo,
                    'creado_por' => $persona->creado_por ?? $usuarioPorDefecto,
                    'creado_en' => $persona->creado_en ?? now(),
                    'actualizado_en' => $persona->actualizado_en ?? now(),
                ]);
            } elseif ($legajo->motivo_ingreso_id === null && $motivoIngresoId !== null) {
                $legajoId = $legajo->id;
                DB::table('legajos')->where('id', $legajoId)->update([
                    'motivo_ingreso_id' => $motivoIngresoId,
                ]);
            } else {
                $legajoId = $legajo->id;
            }

            $problematicaIds = DB::table('persona_problematica')
                ->where('persona_id', $persona->id)
                ->pluck('problematica_id');

            foreach ($problematicaIds as $problematicaId) {
                DB::table('legajo_problematica')->insertOrIgnore([
                    'legajo_id' => $legajoId,
                    'problematica_id' => $problematicaId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Reconciliación de datos: no se puede saber qué filas vinieron de acá.
    }
};
