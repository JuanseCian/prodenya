<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provincias', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::table('localidades', function (Blueprint $table) {
            $table->integer('codigo_postal');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::table('barrios', function (Blueprint $table) {
            $table->unsignedBigInteger('zona_barrio_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('barrios', function (Blueprint $table) {
            $table->dropColumn([
                'zona_barrio_id',
                'created_at',
                'updated_by',
                'deleted_by',
                'updated_at',
                'deleted_at',
            ]);
        });

        Schema::table('localidades', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_postal',
                'created_at',
                'updated_at',
                'deleted_at',
                'updated_by',
                'deleted_by',
            ]);
        });

        Schema::table('provincias', function (Blueprint $table) {
            $table->dropColumn([
                'updated_at',
                'updated_by',
                'deleted_by',
                'deleted_at',
                'created_at',
            ]);
        });
    }
};
