<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barrios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
            $table->unsignedInteger('localidad_id');

            $table->foreign('localidad_id')->references('id')->on('localidades')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['nombre', 'localidad_id'], 'uq_barrio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barrios');
    }
};
