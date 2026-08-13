<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
            $table->unsignedInteger('provincia_id');

            $table->foreign('provincia_id')->references('id')->on('provincias')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['nombre', 'provincia_id'], 'uq_localidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localidades');
    }
};
