<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `legajos` nunca recibió el `motivo_ingreso_id` que sí se agregó a `personas`
 * (ver 2026_10_15_000002_add_motivo_ingreso_id_to_personas_table). Lo agregamos
 * acá para que `legajos` pueda ser la única fuente de verdad del legajo.
 *
 * `motivos_ingreso` figura como ya migrada en algunas bases (la fila quedó en
 * `migrations` de una corrida anterior) pero la tabla no llegó a crearse, así
 * que la creamos aquí también si falta en vez de depender de que
 * 2026_10_15_000001_create_motivos_ingreso_table se vuelva a ejecutar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('motivos_ingreso')) {
            Schema::create('motivos_ingreso', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 190)->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('legajos', 'motivo_ingreso_id')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->foreignId('motivo_ingreso_id')
                    ->nullable()
                    ->after('motivo_ingreso')
                    ->constrained('motivos_ingreso')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('legajos', 'motivo_ingreso_id')) {
            $tieneForeign = collect(Schema::getForeignKeys('legajos'))
                ->contains(fn (array $fk) => $fk['columns'] === ['motivo_ingreso_id']);

            Schema::table('legajos', function (Blueprint $table) use ($tieneForeign) {
                if ($tieneForeign) {
                    $table->dropForeign(['motivo_ingreso_id']);
                }

                $table->dropColumn('motivo_ingreso_id');
            });
        }
    }
};
