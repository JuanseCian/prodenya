<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legajos', function (Blueprint $table) {
            $table->text('motivo_ingreso')->after('numero_legajo');
        });
    }

    public function down(): void
    {
        Schema::table('legajos', function (Blueprint $table) {
            $table->dropColumn('motivo_ingreso');
        });
    }
};
