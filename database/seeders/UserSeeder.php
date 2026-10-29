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
     * Crea (o actualiza) las cuentas iniciales del sistema.
     * Requiere que RolesSeeder ya se haya ejecutado.
     */
    public function run(): void
    {
        $rolAdminId = DB::table('roles')->where('nombre', 'admin')->value('id');
        $rolProfesionalId = DB::table('roles')->where('nombre', 'profesional')->value('id');

        if ($rolAdminId === null) {
            throw new RuntimeException('Falta el rol "admin": ejecutá RolesSeeder antes que UserSeeder.');
        }

        if ($rolProfesionalId === null) {
            throw new RuntimeException('Falta el rol "profesional": ejecutá RolesSeeder antes que UserSeeder.');
        }

        $admin = User::firstOrNew(['email' => 'admin@gmail.com']);

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

        $profesional = User::firstOrNew(['email' => 'profesional@gmail.com']);

        $profesional->forceFill([
            'name' => 'Profesional',
            'password' => Hash::make('profesional123'),
            'profesion' => 'ts',
            'email_verified_at' => now(),
            'rol_id' => $rolProfesionalId,
            'activo' => true,
            'estado_cuenta' => User::ESTADO_APROBADO,
            'aprobado_en' => now(),
        ])->save();
    }
}
