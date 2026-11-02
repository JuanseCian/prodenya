<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cud_certificados', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('legajo_id');
            $table->string('numero_cud', 80);
            $table->date('fecha_vigencia');
            $table->date('fecha_vencimiento');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reemplazado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
            $table->index(['legajo_id', 'activo']);
            $table->index('fecha_vencimiento');
        });

        Schema::create('cud_archivos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cud_certificado_id');
            $table->string('tipo', 20);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('tamanio_bytes');
            $table->boolean('activo')->default(true);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reemplazado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('cud_certificado_id')->references('id')->on('cud_certificados')->cascadeOnDelete();
            $table->index(['cud_certificado_id', 'tipo', 'activo']);
        });

        if (Schema::hasTable('cud') && Schema::hasTable('legajos')) {
            DB::table('cud')
                ->join('legajos', 'legajos.persona_id', '=', 'cud.persona_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('cud_certificados')
                        ->whereColumn('cud_certificados.legajo_id', 'legajos.id')
                        ->where('cud_certificados.activo', true);
                })
                ->select([
                    'legajos.id as legajo_id',
                    'legajos.creado_por',
                    'cud.numero_cud',
                    'cud.fecha_emision',
                    'cud.fecha_vencimiento',
                    'cud.observaciones',
                ])
                ->get()
                ->each(function (object $legacy): void {
                    DB::table('cud_certificados')->insert([
                        'legajo_id' => $legacy->legajo_id,
                        'numero_cud' => $legacy->numero_cud,
                        'fecha_vigencia' => $legacy->fecha_emision,
                        'fecha_vencimiento' => $legacy->fecha_vencimiento,
                        'observaciones' => $legacy->observaciones,
                        'creado_por' => $legacy->creado_por,
                        'creado_en' => now(),
                        'actualizado_en' => now(),
                    ]);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cud_archivos');
        Schema::dropIfExists('cud_certificados');
    }
};
