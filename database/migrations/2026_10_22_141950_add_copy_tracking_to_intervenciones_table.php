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
        Schema::table('intervenciones', function (Blueprint $table) {
            $table->uuid('grupo_copia_id')->nullable()->after('legajo_id');
            $table->unsignedInteger('intervencion_origen_id')->nullable()->after('grupo_copia_id');
            $table->foreign('intervencion_origen_id')->references('id')->on('intervenciones')->nullOnDelete();
            $table->index('grupo_copia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervenciones', function (Blueprint $table) {
            $table->dropForeign(['intervencion_origen_id']);
            $table->dropIndex(['grupo_copia_id']);
            $table->dropColumn(['grupo_copia_id', 'intervencion_origen_id']);
        });
    }
};
