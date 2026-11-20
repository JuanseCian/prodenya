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
        Schema::table('auh', function (Blueprint $table) {
            // Últimos datos usados para generar cada planilla, para no volver a tipearlos.
            $table->json('datos_planillas')->nullable()->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auh', function (Blueprint $table) {
            $table->dropColumn('datos_planillas');
        });
    }
};
