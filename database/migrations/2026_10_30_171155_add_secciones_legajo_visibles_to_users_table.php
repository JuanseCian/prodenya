<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NULL significa "sin restricción": el usuario ve todas las secciones del legajo.
            // Un array (aunque esté vacío) limita la vista a esas secciones puntuales.
            $table->json('secciones_legajo_visibles')->nullable()->after('rol_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('secciones_legajo_visibles');
        });
    }
};
