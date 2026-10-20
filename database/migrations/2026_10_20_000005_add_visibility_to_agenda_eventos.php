<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_eventos', function (Blueprint $table) {
            $table->string('visibilidad', 20)->default('general')->index()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_eventos', function (Blueprint $table) {
            $table->dropIndex(['visibilidad']);
            $table->dropColumn('visibilidad');
        });
    }
};
