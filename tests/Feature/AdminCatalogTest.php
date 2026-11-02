<?php

use App\Models\Provincia;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 2, 'nombre' => 'operador', 'descripcion' => 'Carga y edición', 'creado_en' => now()],
    ]);
});

test('administrators can manage province catalog entries', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    $this->actingAs($admin)
        ->get(route('admin.catalogos.index', ['catalog' => 'provincias']))
        ->assertOk()
        ->assertViewIs('backend.catalogos.index');

    $this->actingAs($admin)
        ->post(route('admin.catalogos.store', ['catalog' => 'provincias']), ['nombre' => 'Provincia de prueba'])
        ->assertRedirect();

    $provincia = Provincia::where('nombre', 'Provincia de prueba')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.catalogos.update', ['catalog' => 'provincias', 'item' => $provincia->id]), ['nombre' => 'Provincia actualizada'])
        ->assertRedirect();

    expect($provincia->fresh()->nombre)->toBe('Provincia actualizada');

    $this->actingAs($admin)
        ->delete(route('admin.catalogos.destroy', ['catalog' => 'provincias', 'item' => $provincia->id]))
        ->assertRedirect();

    expect(Provincia::withTrashed()->find($provincia->id))->not->toBeNull();
});

test('non administrators cannot access catalog management', function () {
    $operador = User::factory()->create(['rol_id' => 2]);

    $this->actingAs($operador)
        ->get(route('admin.catalogos.index', ['catalog' => 'provincias']))
        ->assertForbidden();
});

test('all administrative catalogs are available', function () {
    $admin = User::factory()->create(['rol_id' => 1]);

    foreach (['roles', 'provincias', 'localidades', 'barrios', 'tipos-documento', 'problematicas'] as $catalog) {
        $this->actingAs($admin)
            ->get(route('admin.catalogos.index', ['catalog' => $catalog]))
            ->assertOk();
    }
});
