<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problematica', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 190);

            $table->timestamp('created_at')
                ->useCurrent();

            $table->unsignedInteger('updated_by')
                ->nullable();

            $table->unsignedInteger('deleted_by')
                ->nullable();

            $table->timestamp('updated_at')
                ->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problematica');
    }
};