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

        $admin = User::firstOrNew(['email' => 'admin@gmai.com']);

        // forceFill: los atributos de estado y rol no están en el $fillable de User.
        $admin->forceFill([
            'name' => 'Administrador',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'rol_id' => $rolAdminId,
            'activo' => true,
            'estado_cuenta' => User::ESTADO_APROBADO,
            'aprobado_en' => now(),
        ])->save();
    }
}
