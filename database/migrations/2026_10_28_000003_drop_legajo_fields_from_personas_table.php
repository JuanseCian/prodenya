<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `legajos` vuelve a ser la única fuente de verdad de un legajo. La migración
 * anterior (reconcile_legajos_from_personas) ya garantiza que todo lo que
 * hubiera quedado solo en `personas` o en `persona_problematica` esté
 * reflejado en `legajos` / `legajo_problematica`.
 */
return new class extends Migration
{
    private const COLUMNAS = [
        'numero_legajo',
        'motivo_ingreso',
        'motivo_ingreso_id',
        'nivel_riesgo',
        'origen_derivacion',
        'profesional_id',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        'observaciones_legajo',
    ];

    public function up(): void
    {
        if (Schema::hasColumn('personas', 'motivo_ingreso_id')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropForeign(['motivo_ingreso_id']);
            });
        }

        $presentes = array_filter(self::COLUMNAS, fn (string $columna) => Schema::hasColumn('personas', $columna));

        if ($presentes !== []) {
            Schema::table('personas', function (Blueprint $table) use ($presentes) {
                $table->dropColumn($presentes);
            });
        }

        Schema::dropIfExists('persona_problematica');
    }

    public function down(): void
    {
        if (! Schema::hasTable('persona_problematica')) {
            Schema::create('persona_problematica', function (Blueprint $table) {
                $table->unsignedInteger('persona_id');
                $table->unsignedInteger('problematica_id');

                $table->primary(['persona_id', 'problematica_id']);
                $table->foreign('persona_id')->references('id')->on('personas')->cascadeOnDelete();
                $table->foreign('problematica_id')->references('id')->on('problematicas')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('personas', 'numero_legajo')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->string('numero_legajo', 40)->nullable();
                $table->text('motivo_ingreso')->nullable();
                $table->string('nivel_riesgo', 20)->nullable();
                $table->string('origen_derivacion', 255)->nullable();
                $table->unsignedBigInteger('profesional_id')->nullable();
                $table->date('fecha_apertura')->nullable();
                $table->date('fecha_cierre')->nullable();
                $table->string('estado', 20)->nullable();
                $table->text('observaciones_legajo')->nullable();
                $table->foreignId('motivo_ingreso_id')->nullable()->constrained('motivos_ingreso')->nullOnDelete();
                $table->unique('numero_legajo', 'personas_numero_legajo_unique');
            });
        }
    }
};
