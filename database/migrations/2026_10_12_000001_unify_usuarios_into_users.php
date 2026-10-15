<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unifica las dos tablas de usuarios: desaparece `usuarios` y todo pasa a `users`
 * (la que usa el login).
 *
 *  - `users` incorpora rol_id, activo y ultimo_acceso.
 *  - Cada fila de `usuarios` se empareja con `users` por email (si no existe, se crea).
 *  - Las columnas que apuntaban a `usuarios` pasan a apuntar a `users` (bigint unsigned).
 *
 * IMPORTANTE: hacé un backup de la base antes de correrla (no se puede revertir).
 */
return new class extends Migration
{
    /** [tabla, columna, admite null, acción al borrar] */
    private array $referencias = [
        ['legajos', 'creado_por', false, 'restrict'],
        ['legajos', 'profesional_id', true, 'set null'],
        ['documentos', 'subido_por', false, 'restrict'],
        ['auditoria_accesos_legajo', 'usuario_id', false, 'restrict'],
        ['auditoria_descargas', 'usuario_id', false, 'restrict'],
    ];

    private function tieneForeignKey(string $tabla, string $columna): bool
    {
        return collect(Schema::getForeignKeys($tabla))
            ->contains(fn (array $foreignKey) => in_array($columna, $foreignKey['columns'], true));
    }

    public function up(): void
    {
        // 1) users incorpora lo que tenía usuarios
        if (! Schema::hasColumn('users', 'rol_id')) {
            Schema::table('users', fn (Blueprint $table) => $table->unsignedInteger('rol_id')->nullable()->after('password'));
        }

        if (! Schema::hasColumn('users', 'activo')) {
            Schema::table('users', fn (Blueprint $table) => $table->boolean('activo')->default(true)->after('rol_id'));
        }

        if (! Schema::hasColumn('users', 'ultimo_acceso')) {
            Schema::table('users', fn (Blueprint $table) => $table->dateTime('ultimo_acceso')->nullable()->after('activo'));
        }

        if (! $this->tieneForeignKey('users', 'rol_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('rol_id')->references('id')->on('roles')
                    ->onUpdate('cascade')->onDelete('restrict');
            });
        }

        // 2) Se sueltan las claves foráneas que apuntaban a usuarios
        foreach ($this->referencias as [$tabla, $columna]) {
            if ($this->tieneForeignKey($tabla, $columna)) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign([$columna]));
            }
        }

        // 3) Las columnas pasan a bigint unsigned (el tipo de users.id)
        foreach ($this->referencias as [$tabla, $columna, $admiteNull]) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` BIGINT UNSIGNED %s',
                    $tabla,
                    $columna,
                    $admiteNull ? 'NULL' : 'NOT NULL'
                ));
            }
        }

        // 4) Se emparejan los usuarios por email: usuarios.id => users.id
        $mapa = [];

        foreach (DB::table('usuarios')->orderBy('id')->get() as $usuario) {
            $existente = DB::table('users')->where('email', $usuario->email)->first();

            if ($existente) {
                DB::table('users')->where('id', $existente->id)->update([
                    'rol_id' => $usuario->rol_id,
                    'activo' => $usuario->activo,
                    'ultimo_acceso' => $usuario->ultimo_acceso,
                ]);
                $mapa[$usuario->id] = $existente->id;
            } else {
                $mapa[$usuario->id] = DB::table('users')->insertGetId([
                    'name' => $usuario->nombre_usuario,
                    'email' => $usuario->email,
                    'password' => $usuario->password_hash,
                    'email_verified_at' => now(),
                    'rol_id' => $usuario->rol_id,
                    'activo' => $usuario->activo,
                    'ultimo_acceso' => $usuario->ultimo_acceso,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5) Se reasignan los ids (con un desplazamiento temporal para evitar choques)
        $desplazamiento = 1_000_000_000;

        foreach ($this->referencias as [$tabla, $columna]) {
            foreach ($mapa as $viejo => $nuevo) {
                if ((int) $viejo !== (int) $nuevo) {
                    DB::table($tabla)->where($columna, $viejo)
                        ->update([$columna => $nuevo + $desplazamiento]);
                }
            }

            DB::table($tabla)->where($columna, '>=', $desplazamiento)
                ->update([$columna => DB::raw("`{$columna}` - {$desplazamiento}")]);
        }

        // 6) Nuevas claves foráneas hacia users
        foreach ($this->referencias as [$tabla, $columna, , $alBorrar]) {
            if (! $this->tieneForeignKey($tabla, $columna)) {
                Schema::table($tabla, function (Blueprint $t) use ($columna, $alBorrar) {
                    $fk = $t->foreign($columna)->references('id')->on('users')->onUpdate('cascade');

                    $alBorrar === 'set null' ? $fk->nullOnDelete() : $fk->restrictOnDelete();
                });
            }
        }

        // 7) usuarios ya no se usa
        Schema::drop('usuarios');
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Esta migración no se puede revertir: restaurá el backup de la base de datos.'
        );
    }
};
