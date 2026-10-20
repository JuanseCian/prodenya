<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_messages', function (Blueprint $table) {
            $table->string('estado', 20)->default('Nuevo')->after('is_read')->index();
            $table->foreignId('clasificado_por')->nullable()->after('estado')->constrained('users')->nullOnDelete();
            $table->timestamp('clasificado_en')->nullable()->after('clasificado_por');
        });

        Schema::create('mail_message_legajo', function (Blueprint $table) {
            $table->foreignId('mail_message_id')->constrained('mail_messages')->cascadeOnDelete();
            $table->unsignedInteger('legajo_id');
            $table->primary(['mail_message_id', 'legajo_id']);
            $table->foreign('legajo_id')->references('id')->on('legajos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_message_legajo');

        Schema::table('mail_messages', function (Blueprint $table) {
            $table->dropForeign(['clasificado_por']);
            $table->dropColumn(['estado', 'clasificado_por', 'clasificado_en']);
        });
    }
};
