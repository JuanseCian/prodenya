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
        Schema::create('mail_classification_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_message_id')->constrained('mail_messages')->cascadeOnDelete();
            $table->string('categoria_anterior', 40)->nullable();
            $table->string('estado_anterior', 40)->nullable();
            $table->string('categoria', 40);
            $table->string('estado', 40);
            $table->decimal('confianza', 4, 3)->nullable();
            $table->string('metodo', 30);
            $table->json('motivos')->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['mail_message_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_classification_events');
    }
};
