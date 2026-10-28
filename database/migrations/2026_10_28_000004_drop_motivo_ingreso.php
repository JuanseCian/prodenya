<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('legajos', 'motivo_ingreso_id')) {
            $tieneForeign = collect(Schema::getForeignKeys('legajos'))
                ->contains(fn (array $fk) => $fk['columns'] === ['motivo_ingreso_id']);

            Schema::table('legajos', function (Blueprint $table) use ($tieneForeign) {
                if ($tieneForeign) {
                    $table->dropForeign(['motivo_ingreso_id']);
                }

                $table->dropColumn('motivo_ingreso_id');
            });
        }

        if (Schema::hasColumn('legajos', 'motivo_ingreso')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->dropColumn('motivo_ingreso');
            });
        }

        Schema::dropIfExists('motivos_ingreso');
    }

    public function down(): void
    {
        Schema::create('motivos_ingreso', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 190)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        if (! Schema::hasColumn('legajos', 'motivo_ingreso')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->text('motivo_ingreso')->nullable()->after('numero_legajo');
            });
        }

        if (! Schema::hasColumn('legajos', 'motivo_ingreso_id')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->foreignId('motivo_ingreso_id')
                    ->nullable()
                    ->after('motivo_ingreso')
                    ->constrained('motivos_ingreso')
                    ->nullOnDelete();
            });
        }
    }
};
