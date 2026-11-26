<?php

use App\Models\Acta;
use App\Models\User;
use App\Services\ActaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 5, 'nombre' => 'profesional', 'descripcion' => 'Intervenciones', 'creado_en' => now()],
        ['id' => 7, 'nombre' => 'administrativo', 'descripcion' => 'Carga administrativa', 'creado_en' => now()],
    ]);
    Storage::fake('local');

    $this->profesional = User::factory()->create(['rol_id' => 5, 'name' => 'Paula Díaz']);
    $this->administrativo = User::factory()->create(['rol_id' => 7, 'name' => 'Ana Ruiz']);
});

function firmaResponsableDePrueba(): string
{
    $imagen = imagecreatetruecolor(320, 80);
    imagefill($imagen, 0, 0, imagecolorallocate($imagen, 255, 255, 255));
    imageline($imagen, 10, 60, 300, 20, imagecolorallocate($imagen, 44, 74, 94));
    ob_start();
    imagepng($imagen);

    return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
}

test('the acta of a professional has them as its fixed responsible', function () {
    $this->actingAs($this->profesional)
        ->get(route('actas.create', 'inicial'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('responsableFijo', 'Paula Díaz')
            ->where('datos.operador', 'Paula Díaz'));

    $this->actingAs($this->profesional)
        ->post(route('actas.store', 'inicial'), [
            'operador' => 'Otra persona',
            'compareciente_nombre' => 'Gómez, Marta',
            'firmas' => ['equipo' => ['nombre' => 'Otra persona', 'dibujo' => firmaResponsableDePrueba()]],
        ])
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();

    expect($acta->datos['operador'])->toBe('Paula Díaz')
        ->and($acta->firmas[0]['nombre'])->toBe('Paula Díaz');
});

test('an administrative user must say who made the acta', function () {
    $this->actingAs($this->administrativo)
        ->get(route('actas.create', 'inicial'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('responsableFijo', null)
            ->where('datos.operador', ''));

    $this->actingAs($this->administrativo)
        ->post(route('actas.store', 'inicial'), ['compareciente_nombre' => 'Gómez, Marta'])
        ->assertSessionHasErrors('operador');

    $this->actingAs($this->administrativo)
        ->post(route('actas.store', 'inicial'), [
            'operador' => 'Lic. Carla Sosa',
            'compareciente_nombre' => 'Gómez, Marta',
            'firmas' => ['equipo' => ['nombre' => 'Lic. Carla Sosa', 'dibujo' => firmaResponsableDePrueba()]],
        ])
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();

    expect($acta->datos['operador'])->toBe('Lic. Carla Sosa')
        ->and($acta->firmas[0]['nombre'])->toBe('Lic. Carla Sosa');
});

test('correcting an acta keeps the person who loaded it as responsible', function () {
    $this->actingAs($this->profesional)
        ->post(route('actas.store', 'inicial'), ['compareciente_nombre' => 'Gómez, Marta'])
        ->assertSessionHasNoErrors();

    $acta = Acta::sole();
    $admin = User::factory()->create(['rol_id' => 1, 'name' => 'Admin General']);

    app(ActaService::class)->actualizar($acta, ['operador' => 'Admin General', 'relato' => 'Corregida.'], [], $admin);

    expect($acta->refresh()->datos['operador'])->toBe('Paula Díaz')
        ->and(app(ActaService::class)->hoja($acta)['responsable_fijo'])->toBe('Paula Díaz');
});
