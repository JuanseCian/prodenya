<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legajos', function (Blueprint $table) {
            $table->date('retencion_hasta')->nullable()->after('estado')->index();
            $table->timestamp('archivado_legal_en')->nullable()->after('retencion_hasta');
            $table->timestamp('eliminacion_autorizada_en')->nullable()->after('archivado_legal_en');
            $table->foreignId('eliminacion_autorizada_por')->nullable()->after('eliminacion_autorizada_en')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('legajos', function (Blueprint $table) {
            $table->dropForeign(['eliminacion_autorizada_por']);
            $table->dropColumn([
                'retencion_hasta',
                'archivado_legal_en',
                'eliminacion_autorizada_en',
                'eliminacion_autorizada_por',
            ]);
        });
    }
};
