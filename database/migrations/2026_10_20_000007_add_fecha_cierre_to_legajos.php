<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('personas', 'estado') && ! Schema::hasColumn('personas', 'fecha_cierre')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->date('fecha_cierre')->nullable()->after('fecha_apertura');
                $table->index('fecha_cierre', 'idx_persona_fecha_cierre');
            });
        }

        if (Schema::hasTable('legajos') && ! Schema::hasColumn('legajos', 'fecha_cierre')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->date('fecha_cierre')->nullable()->after('fecha_apertura');
                $table->index('fecha_cierre', 'idx_legajo_fecha_cierre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('legajos') && Schema::hasColumn('legajos', 'fecha_cierre')) {
            Schema::table('legajos', function (Blueprint $table) {
                if (Schema::hasIndex('legajos', 'idx_legajo_fecha_cierre')) {
                    $table->dropIndex('idx_legajo_fecha_cierre');
                }

                $table->dropColumn('fecha_cierre');
            });
        }

        if (Schema::hasColumn('personas', 'fecha_cierre')) {
            Schema::table('personas', function (Blueprint $table) {
                if (Schema::hasIndex('personas', 'idx_persona_fecha_cierre')) {
                    $table->dropIndex('idx_persona_fecha_cierre');
                }

                $table->dropColumn('fecha_cierre');
            });
        }
    }
};
