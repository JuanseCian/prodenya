<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_maltrato', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 80)->unique();
        });

        Schema::table('legajos', function (Blueprint $table) {
            $table->enum('nivel_riesgo', ['Bajo', 'Medio', 'Alto', 'Critico'])->after('motivo_ingreso');
            $table->string('origen_derivacion', 255)->after('nivel_riesgo');
            $table->unsignedInteger('profesional_id')->nullable()->after('origen_derivacion');

            $table->foreign('profesional_id')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('legajo_tipo_maltrato', function (Blueprint $table) {
            $table->unsignedInteger('legajo_id');
            $table->unsignedInteger('tipo_maltrato_id');

            $table->primary(['legajo_id', 'tipo_maltrato_id']);
            $table->foreign('legajo_id')->references('id')->on('legajos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tipo_maltrato_id')->references('id')->on('tipos_maltrato')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legajo_tipo_maltrato');

        Schema::table('legajos', function (Blueprint $table) {
            $table->dropForeign(['profesional_id']);
            $table->dropColumn(['nivel_riesgo', 'origen_derivacion', 'profesional_id']);
        });

        Schema::dropIfExists('tipos_maltrato');
    }
};
