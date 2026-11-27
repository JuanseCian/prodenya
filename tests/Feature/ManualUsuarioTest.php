<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('approved users can download the user manual as a pdf', function () {
    $usuario = User::factory()->create([
        'rol_id' => 1,
        'estado_cuenta' => User::ESTADO_APROBADO,
    ]);

    $this->actingAs($usuario)
        ->get(route('manual.download'))
        ->assertOk()
        ->assertDownload('manual-usuario-prodenya.pdf');
});

test('guests cannot download the user manual', function () {
    $this->get(route('manual.download'))
        ->assertRedirect(route('login'));
});
