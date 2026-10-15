<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('only administrators can access the mailbox', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($admin)->get(route('mailbox.index'))->assertOk();
    $this->actingAs($operador)->get(route('mailbox.index'))->assertForbidden();
});
