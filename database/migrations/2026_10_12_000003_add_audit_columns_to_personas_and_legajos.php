<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registra quién creó y quién modificó por última vez cada persona y cada legajo.
 * (legajos ya tiene creado_por.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('creado_por')->nullable()->after('activo');
            $table->unsignedBigInteger('actualizado_por')->nullable()->after('creado_por');

            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actualizado_por')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('legajos', function (Blueprint $table) {
            $table->unsignedBigInteger('actualizado_por')->nullable()->after('creado_por');

            $table->foreign('actualizado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('legajos', function (Blueprint $table) {
            $table->dropForeign(['actualizado_por']);
            $table->dropColumn('actualizado_por');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropForeign(['actualizado_por']);
            $table->dropColumn(['creado_por', 'actualizado_por']);
        });
    }
};
