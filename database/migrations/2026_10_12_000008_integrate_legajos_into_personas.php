<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnas = [
            'numero_legajo' => fn (Blueprint $table) => $table->string('numero_legajo', 40)->nullable(),
            'motivo_ingreso' => fn (Blueprint $table) => $table->text('motivo_ingreso')->nullable(),
            'nivel_riesgo' => fn (Blueprint $table) => $table->string('nivel_riesgo', 20)->nullable(),
            'origen_derivacion' => fn (Blueprint $table) => $table->string('origen_derivacion', 255)->nullable(),
            'profesional_id' => fn (Blueprint $table) => $table->unsignedBigInteger('profesional_id')->nullable(),
            'fecha_apertura' => fn (Blueprint $table) => $table->date('fecha_apertura')->nullable(),
            'estado' => fn (Blueprint $table) => $table->string('estado', 20)->nullable(),
            'observaciones_legajo' => fn (Blueprint $table) => $table->text('observaciones_legajo')->nullable(),
        ];

        foreach ($columnas as $nombre => $definicion) {
            if (! Schema::hasColumn('personas', $nombre)) {
                Schema::table('personas', $definicion);
            }
        }

        if (! Schema::hasIndex('personas', 'personas_numero_legajo_unique')) {
            Schema::table('personas', fn (Blueprint $table) => $table->unique('numero_legajo', 'personas_numero_legajo_unique'));
        }

        if (! Schema::hasTable('persona_problematica')) {
            Schema::create('persona_problematica', function (Blueprint $table) {
                $table->unsignedInteger('persona_id');
                $table->unsignedInteger('problematica_id');

                $table->primary(['persona_id', 'problematica_id']);
                $table->foreign('persona_id')->references('id')->on('personas')->cascadeOnDelete();
                $table->foreign('problematica_id')->references('id')->on('problematicas')->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('legajos')) {
            return;
        }

        foreach (DB::table('legajos')->orderBy('id')->get() as $legajo) {
            $persona = DB::table('personas')->where('id', $legajo->persona_id)->first();

            if (! $persona) {
                continue;
            }

            if ($persona->numero_legajo === null) {
                DB::table('personas')->where('id', $persona->id)->update([
                    'numero_legajo' => $legajo->numero_legajo,
                    'motivo_ingreso' => $legajo->motivo_ingreso,
                    'nivel_riesgo' => $legajo->nivel_riesgo,
                    'origen_derivacion' => $legajo->origen_derivacion,
                    'profesional_id' => $legajo->profesional_id,
                    'fecha_apertura' => $legajo->fecha_apertura,
                    'estado' => $legajo->estado,
                    'observaciones_legajo' => $legajo->observaciones,
                    'creado_por' => $persona->creado_por ?? $legajo->creado_por,
                ]);
            }

            if (Schema::hasTable('legajo_problematica')) {
                foreach (DB::table('legajo_problematica')->where('legajo_id', $legajo->id)->get() as $relacion) {
                    DB::table('persona_problematica')->insertOrIgnore([
                        'persona_id' => $persona->id,
                        'problematica_id' => $relacion->problematica_id,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_problematica');

        foreach (['numero_legajo', 'motivo_ingreso', 'nivel_riesgo', 'origen_derivacion', 'profesional_id', 'fecha_apertura', 'estado', 'observaciones_legajo'] as $columna) {
            if (Schema::hasColumn('personas', $columna)) {
                Schema::table('personas', fn (Blueprint $table) => $table->dropColumn($columna));
            }
        }
    }
};
