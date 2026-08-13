<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = Rol::where('nombre', 'admin')->first();

        Usuario::firstOrCreate(
            ['email' => 'admin@prodenya.local'],
            [
                'nombre_usuario' => 'admin',
                'password_hash' => Hash::make('cambiar123'), // CAMBIAR después del primer login
                'rol_id' => $rolAdmin->id,
                'activo' => true,
            ]
        );
    }
}
