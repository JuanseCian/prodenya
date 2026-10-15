<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('administrators can access the backend home', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->get(route('admin.home'))
        ->assertOk()
        ->assertViewIs('backend.home.home');
});

test('non administrators cannot access the backend home', function () {
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($operador)
        ->get(route('admin.home'))
        ->assertForbidden();
});
