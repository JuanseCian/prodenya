<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los familiares registrados sin legajo (padres, abuelos, tutores) muchas
     * veces no tienen fecha de nacimiento conocida.
     */
    public function up(): void
    {
        $this->sinVistaEnSqlite(fn () => Schema::table('personas', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->change();
            // En MySQL ya era opcional (2026_10_12_000004); se alinea SQLite, que esa migración omitía.
            $table->string('dni', 15)->nullable()->change();
        }));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->sinVistaEnSqlite(fn () => Schema::table('personas', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable(false)->change();
        }));
    }

    /**
     * SQLite reconstruye la tabla al cambiar una columna, lo que rompe
     * `vista_personas_completa`: se quita y se recrea igual que estaba.
     */
    private function sinVistaEnSqlite(callable $cambio): void
    {
        $vista = DB::getDriverName() === 'sqlite'
            ? DB::scalar("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = 'vista_personas_completa'")
            : null;

        if ($vista) {
            DB::statement('DROP VIEW vista_personas_completa');
        }

        $cambio();

        if ($vista) {
            DB::statement($vista);
        }
    }
};
