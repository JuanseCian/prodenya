<?php

use App\Models\DerivacionCaso;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\Persona;
use App\Models\TipoIntervencion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
        ['id' => 3, 'nombre' => 'consulta', 'descripcion' => 'Solo lectura', 'creado_en' => now()],
    ]);
    $this->travelTo('2026-11-20 10:00:00');
});

test('the statistics page summarizes activity, referrals and signatures in key points', function () {
    $admin = User::factory()->create(['rol_id' => 1, 'name' => 'Admin Auditor']);
    $profesionalA = User::factory()->create(['name' => 'Lic. Paula Díaz']);
    $profesionalB = User::factory()->create(['name' => 'Lic. Juan Ríos']);
    $legajo = crearLegajoParaEstadisticas($admin);

    $entrevista = TipoIntervencion::create(['nombre' => 'Entrevista', 'activo' => true]);
    $visita = TipoIntervencion::create(['nombre' => 'Visita domiciliaria', 'activo' => true]);
    crearIntervencionParaEstadisticas($legajo, $entrevista, $profesionalA, '2026-08-15', firmada: true);
    crearIntervencionParaEstadisticas($legajo, $entrevista, $profesionalA, '2026-09-01', firmada: false);
    crearIntervencionParaEstadisticas($legajo, $visita, $profesionalB, '2026-11-12', firmada: true);

    crearDerivacionParaEstadisticas($legajo, $admin, $profesionalB, 'pendiente', '2026-11-15 10:00:00');
    crearDerivacionParaEstadisticas($legajo, $admin, $profesionalA, 'aceptada', '2026-10-01 10:00:00', aceptadaEn: '2026-10-03 10:00:00');

    $legajo->registrarAcceso($admin, 'Visualizacion');
    $legajo->registrarAcceso($admin, 'Descarga');

    $this->actingAs($admin)
        ->get(route('legajos.estadisticas', $legajo))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('legajos/estadisticas')
            ->where('legajo.nombre', 'Estadística, Luz')
            ->where('estadisticas.resumen.intervenciones', 3)
            ->where('estadisticas.resumen.intervenciones_ultimos_30_dias', 1)
            ->where('estadisticas.resumen.ultima_intervencion', '2026-11-12')
            ->where('estadisticas.resumen.dias_desde_ultima', 8)
            ->where('estadisticas.resumen.dias_abierto', 102)
            ->where('estadisticas.resumen.derivaciones_pendientes', 1)
            ->where('estadisticas.resumen.firmas.porcentaje', 67)
            ->where('estadisticas.actividad.por_mes.valores', [1, 1, 0, 1])
            ->where('estadisticas.actividad.por_mes.intervalos.0.label', 'ago 26')
            ->where('estadisticas.actividad.dias_promedio_entre', 45)
            ->where('estadisticas.actividad.por_tipo.0', ['nombre' => 'Entrevista', 'total' => 2])
            ->where('estadisticas.circuito.dias_promedio_respuesta', 2)
            ->where('estadisticas.circuito.pendiente_mas_antigua_dias', 5)
            ->where('estadisticas.documentacion.firmas.sin_firma', 1)
            ->where('estadisticas.auditoria.total', 2)
            ->where('estadisticas.auditoria.descargas', 1)
            ->where('estadisticas.auditoria.por_usuario.0', ['nombre' => 'Admin Auditor', 'total' => 2])
            ->where('estadisticas.puntos_clave', [
                ['tema' => 'actividad', 'texto' => 'Se registraron 3 intervenciones desde la apertura (0,8 por mes en promedio).'],
                ['tema' => 'actividad', 'texto' => 'La última intervención fue el 12/11/2026 (hace 8 días).'],
                ['tema' => 'actividad', 'texto' => 'El tipo más frecuente es «Entrevista» (2 de 3).'],
                ['tema' => 'actividad', 'texto' => 'Intervinieron 2 profesionales; quien más intervino es Lic. Paula Díaz (2).'],
                ['tema' => 'circuito', 'texto' => '1 derivación espera respuesta; la más antigua lleva 5 días.'],
                ['tema' => 'documentacion', 'texto' => '1 intervención está sin la firma del responsable.'],
                ['tema' => 'auditoria', 'texto' => 'El legajo fue consultado 2 veces por 1 usuario; último acceso el 20/11/2026.'],
            ]));
});

test('an empty legajo explains that there is no activity yet', function () {
    $admin = User::factory()->create(['rol_id' => 1]);
    $legajo = crearLegajoParaEstadisticas($admin);

    $this->actingAs($admin)
        ->get(route('legajos.estadisticas', $legajo))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('estadisticas.resumen.intervenciones', 0)
            ->where('estadisticas.resumen.ultima_intervencion', null)
            ->where('estadisticas.resumen.firmas.porcentaje', null)
            ->where('estadisticas.puntos_clave', [
                ['tema' => 'actividad', 'texto' => 'Todavía no hay intervenciones registradas en este legajo.'],
            ]));
});

test('only admins and coordinators see who accessed the legajo, and hidden sections stay hidden', function () {
    $consulta = User::factory()->create([
        'rol_id' => 3,
        'secciones_legajo_visibles' => ['estadisticas'],
    ]);
    $legajo = crearLegajoParaEstadisticas($consulta);
    $legajo->registrarAcceso($consulta, 'Visualizacion');

    $this->actingAs($consulta)
        ->get(route('legajos.estadisticas', $legajo))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('legajo.nombre', null)
            ->where('legajo.nivel_riesgo', null)
            ->where('estadisticas.auditoria.total', 1)
            ->where('estadisticas.auditoria.puede_ver_detalle', false)
            ->where('estadisticas.auditoria.por_usuario', [])
            ->where('estadisticas.documentacion.auh', null));
});

function crearLegajoParaEstadisticas(User $usuario): Legajo
{
    $provincia = DB::table('provincias')->where('nombre', 'Buenos Aires')->value('id')
        ?? DB::table('provincias')->insertGetId(['nombre' => 'Buenos Aires']);
    $localidad = DB::table('localidades')->where('nombre', 'San Nicolás')->value('id')
        ?? DB::table('localidades')->insertGetId(['nombre' => 'San Nicolás', 'provincia_id' => $provincia, 'codigo_postal' => 2900]);
    $persona = Persona::create([
        'nombre' => 'Luz',
        'apellido' => 'Estadística',
        'dni' => '41'.random_int(100000, 999999),
        'fecha_nacimiento' => '2014-01-01',
        'genero' => 'F',
        'localidad_id' => $localidad,
        'activo' => true,
    ]);

    return Legajo::create([
        'persona_id' => $persona->id,
        'numero_legajo' => 'EST-'.uniqid(),
        'fecha_apertura' => '2026-08-10',
        'nivel_riesgo' => 'Medio',
        'origen_derivacion' => 'Escuela',
        'estado' => 'Abierto',
        'creado_por' => $usuario->id,
    ]);
}

function crearIntervencionParaEstadisticas(Legajo $legajo, TipoIntervencion $tipo, User $profesional, string $fecha, bool $firmada): Intervencion
{
    return Intervencion::create([
        'legajo_id' => $legajo->id,
        'tipo_intervencion_id' => $tipo->id,
        'profesional_id' => $profesional->id,
        'fecha_intervencion' => $fecha,
        'detalle' => 'Seguimiento.',
        'firma_nombre' => $firmada ? $profesional->name : null,
        'creado_por' => $profesional->id,
    ]);
}

function crearDerivacionParaEstadisticas(Legajo $legajo, User $solicitante, User $destinatario, string $estado, string $creadaEn, ?string $aceptadaEn = null): DerivacionCaso
{
    $derivacion = DerivacionCaso::create([
        'legajo_id' => $legajo->id,
        'solicitado_por' => $solicitante->id,
        'destinatario_id' => $destinatario->id,
        'modo' => 'compartir',
        'estado' => $estado,
        'motivo' => 'Seguimiento conjunto',
        'aceptada_en' => $aceptadaEn,
    ]);
    $derivacion->forceFill(['created_at' => $creadaEn])->save();

    return $derivacion;
}
