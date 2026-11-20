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
        Schema::table('mail_messages', function (Blueprint $table) {
            $table->string('clasificacion_categoria', 40)->default('pendiente')->after('estado')->index();
            $table->string('clasificacion_estado', 40)->default('pendiente')->after('clasificacion_categoria')->index();
            $table->decimal('clasificacion_confianza', 4, 3)->nullable()->after('clasificacion_estado');
            $table->string('clasificacion_metodo', 30)->nullable()->after('clasificacion_confianza');
            $table->timestamp('clasificacion_revisado_en')->nullable()->after('clasificacion_metodo');
            $table->foreignId('clasificacion_revisado_por')->nullable()->after('clasificacion_revisado_en')->constrained('users')->nullOnDelete();
            $table->json('clasificacion_motivos')->nullable()->after('clasificacion_revisado_por');
            $table->string('prioridad', 20)->default('normal')->after('clasificacion_motivos')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_messages', function (Blueprint $table) {
            $table->dropForeign(['clasificacion_revisado_por']);
            $table->dropColumn([
                'clasificacion_categoria',
                'clasificacion_estado',
                'clasificacion_confianza',
                'clasificacion_metodo',
                'clasificacion_revisado_en',
                'clasificacion_revisado_por',
                'clasificacion_motivos',
                'prioridad',
            ]);
        });
    }
};
