<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@prodenya.local'],
            [
                'name' => 'Administrador',
                'password' => 'cambiar123', // CAMBIAR después del primer login
            ],
        );
    }
}
