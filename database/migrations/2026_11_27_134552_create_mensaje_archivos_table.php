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
        Schema::create('mensaje_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('mensajes')->cascadeOnDelete();
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes')->default(0);
            $table->timestamps();

            $table->index('mensaje_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensaje_archivos');
    }
};
