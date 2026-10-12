<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Crea (o actualiza) el administrador en `users`, que ahora es la única
     * tabla de usuarios. Requiere que RolesSeeder ya se haya ejecutado.
     */
    public function run(): void
    {
        $rolAdminId = DB::table('roles')->where('nombre', 'admin')->value('id');

        if ($rolAdminId === null) {
            throw new RuntimeException('Falta el rol "admin": ejecutá RolesSeeder antes que UserSeeder.');
        }

        $admin = User::firstOrNew(['email' => 'admin@prodenya.local']);

        if (! $admin->exists) {
            $admin->name = 'Administrador';
            $admin->password = Hash::make('cambiar123'); // CAMBIAR después del primer login
        }

        // forceFill: rol_id y activo no están en el $fillable de User
        $admin->forceFill([
            'rol_id' => $rolAdminId,
            'activo' => true,
        ])->save();
    }
}
