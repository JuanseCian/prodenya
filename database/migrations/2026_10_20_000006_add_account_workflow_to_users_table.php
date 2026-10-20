<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 30)->nullable()->unique()->after('name');
            $table->string('telefono', 40)->nullable()->after('dni');
            $table->string('estado_cuenta', 20)->default('aprobado')->index()->after('activo');
            $table->dateTime('aprobado_en')->nullable()->after('estado_cuenta');
            $table->foreignId('aprobado_por')->nullable()->after('aprobado_en')->constrained('users')->nullOnDelete();
            $table->dateTime('rechazado_en')->nullable()->after('aprobado_por');
            $table->foreignId('rechazado_por')->nullable()->after('rechazado_en')->constrained('users')->nullOnDelete();
            $table->text('motivo_rechazo')->nullable()->after('rechazado_por');
            $table->dateTime('suspendido_en')->nullable()->after('motivo_rechazo');
            $table->foreignId('suspendido_por')->nullable()->after('suspendido_en')->constrained('users')->nullOnDelete();
            $table->text('motivo_suspension')->nullable()->after('suspendido_por');
            $table->dateTime('archivado_en')->nullable()->after('motivo_suspension');
            $table->foreignId('archivado_por')->nullable()->after('archivado_en')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['aprobado_por']);
            $table->dropForeign(['rechazado_por']);
            $table->dropForeign(['suspendido_por']);
            $table->dropForeign(['archivado_por']);
            $table->dropUnique(['dni']);
            $table->dropColumn([
                'dni',
                'telefono',
                'estado_cuenta',
                'aprobado_en',
                'aprobado_por',
                'rechazado_en',
                'rechazado_por',
                'motivo_rechazo',
                'suspendido_en',
                'suspendido_por',
                'motivo_suspension',
                'archivado_en',
                'archivado_por',
            ]);
        });
    }
};
