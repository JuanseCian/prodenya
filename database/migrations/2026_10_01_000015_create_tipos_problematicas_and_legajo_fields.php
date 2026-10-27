<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problematica', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 190);

            $table->timestamp('created_at')
                ->useCurrent();

            $table->unsignedInteger('updated_by')
                ->nullable();

            $table->unsignedInteger('deleted_by')
                ->nullable();

            $table->timestamp('updated_at')
                ->nullable();

            $table->softDeletes();
        });

        Schema::table('legajos', function (Blueprint $table) {
            $table->enum('nivel_riesgo', ['Bajo', 'Medio', 'Alto', 'Critico'])->after('observaciones');
            $table->string('origen_derivacion', 255)->after('nivel_riesgo');
            $table->unsignedInteger('profesional_id')->nullable()->after('origen_derivacion');

            $table->foreign('profesional_id')->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('legajo_problematica', function (Blueprint $table) {
            $table->unsignedInteger('legajo_id');
            $table->unsignedBigInteger('problematica_id');

            $table->primary(['legajo_id', 'problematica_id']);
            $table->foreign('legajo_id')->references('id')->on('legajos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('problematica_id')->references('id')->on('problematica')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legajo_problematica');

        Schema::table('legajos', function (Blueprint $table) {
            $table->dropForeign(['profesional_id']);
            $table->dropColumn(['nivel_riesgo', 'origen_derivacion', 'profesional_id']);
        });

        Schema::dropIfExists('problematica');
    }
};
