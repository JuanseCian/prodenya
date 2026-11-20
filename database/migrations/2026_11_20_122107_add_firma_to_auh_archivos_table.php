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
        Schema::table('auh_archivos', function (Blueprint $table) {
            $table->boolean('firmado')->default(false)->after('tamanio_bytes');
            // Firmantes de la planilla: [{rol, rol_label, nombre, firmado_en}]. Null para escaneos.
            $table->json('firmas')->nullable()->after('firmado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auh_archivos', function (Blueprint $table) {
            $table->dropColumn(['firmado', 'firmas']);
        });
    }
};
