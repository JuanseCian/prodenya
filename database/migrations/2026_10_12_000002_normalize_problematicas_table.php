<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - `problematica` pasa a `problematicas` (plural, como el resto).
 * - Su id pasa a INT UNSIGNED, igual que las demás tablas del dominio
 *   (evita el error 3780 de claves foráneas incompatibles).
 * - updated_by / deleted_by quedan como BIGINT UNSIGNED y apuntan a users.
 */
return new class extends Migration
{
    private array $tablasConAuditoria = ['barrios', 'localidades', 'provincias', 'problematicas'];

    public function up(): void
    {
        // Renombrado y tipo de id
        Schema::table('legajo_problematica', fn (Blueprint $t) => $t->dropForeign(['problematica_id']));

        DB::statement('ALTER TABLE `problematica` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `legajo_problematica` MODIFY `problematica_id` INT UNSIGNED NOT NULL');

        Schema::rename('problematica', 'problematicas');

        Schema::table('legajo_problematica', function (Blueprint $t) {
            $t->foreign('problematica_id')->references('id')->on('problematicas')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        // Columnas de auditoría: mismo tipo que users.id y con clave foránea
        foreach ($this->tablasConAuditoria as $tabla) {
            foreach (['updated_by', 'deleted_by'] as $columna) {
                DB::statement("ALTER TABLE `{$tabla}` MODIFY `{$columna}` BIGINT UNSIGNED NULL");

                // Valores que no correspondan a un usuario existente se limpian
                DB::table($tabla)
                    ->whereNotNull($columna)
                    ->whereNotIn($columna, DB::table('users')->select('id'))
                    ->update([$columna => null]);

                Schema::table($tabla, function (Blueprint $t) use ($columna) {
                    $t->foreign($columna)->references('id')->on('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tablasConAuditoria as $tabla) {
            foreach (['updated_by', 'deleted_by'] as $columna) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign([$columna]));
            }
        }

        DB::statement('ALTER TABLE `problematicas` MODIFY `updated_by` INT UNSIGNED NULL');
        DB::statement('ALTER TABLE `problematicas` MODIFY `deleted_by` INT UNSIGNED NULL');

        Schema::table('legajo_problematica', fn (Blueprint $t) => $t->dropForeign(['problematica_id']));

        Schema::rename('problematicas', 'problematica');

        DB::statement('ALTER TABLE `problematica` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `legajo_problematica` MODIFY `problematica_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('legajo_problematica', function (Blueprint $t) {
            $t->foreign('problematica_id')->references('id')->on('problematica')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }
};
