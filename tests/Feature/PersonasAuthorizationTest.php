<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);
});

test('an operator can list and create personas', function () {
    $user = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($user)
        ->get(route('personas.index'))
        ->assertRedirectToRoute('legajos.index');

    $this->actingAs($user)
        ->get(route('personas.create'))
        ->assertRedirectToRoute('legajos.create');
});

test('a consulta user can list personas but cannot create them', function () {
    $user = User::factory()->create(['rol_id' => 3]);

    $this->actingAs($user)
        ->get(route('personas.index'))
        ->assertRedirectToRoute('legajos.index');

    $this->actingAs($user)
        ->get(route('personas.create'))
        ->assertForbidden();
});

test('a user without a role cannot access personas', function () {
    $user = User::factory()->create(['rol_id' => null]);

    $this->actingAs($user)
        ->get(route('personas.index'))
        ->assertForbidden();
});
