<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $secciones = [
            'todas' => 'Todas',
            'usuarios' => 'Usuarios',
            'legajos' => 'Legajos',
            'personas' => 'Personas',
            'intervenciones' => 'Intervenciones',
            'actas' => 'Actas',
            'tareas' => 'Tareas',
            'comunicaciones' => 'Comunicaciones',
            'puntaje' => 'Puntaje',
            'sistema' => 'Sistema',
        ];
        $seccion = (string) $request->query('seccion', 'todas');

        if (! array_key_exists($seccion, $secciones)) {
            $seccion = 'todas';
        }

        $seccionSql = "CASE
            WHEN a.evento LIKE 'usuario.%' OR a.auditable_type LIKE '%User' THEN 'usuarios'
            WHEN a.evento LIKE 'legajo.%' OR a.auditable_type LIKE '%Legajo' THEN 'legajos'
            WHEN a.evento LIKE 'persona.%' OR a.auditable_type LIKE '%Persona' THEN 'personas'
            WHEN a.evento LIKE 'intervencion.%' OR a.auditable_type LIKE '%Intervencion' THEN 'intervenciones'
            WHEN a.evento LIKE 'acta.%' OR a.auditable_type LIKE '%Acta' THEN 'actas'
            WHEN a.evento LIKE 'tarea.%' OR a.auditable_type LIKE '%Tarea' THEN 'tareas'
            WHEN a.evento LIKE 'mail.%' OR a.auditable_type LIKE '%Mail%' THEN 'comunicaciones'
            ELSE 'sistema'
        END";

        $auditoria = DB::table('auditoria_eventos as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.usuario_id')
            ->select([
                DB::raw("'auditoria' as tipo_movimiento"),
                'a.id',
                'a.evento',
                'a.auditable_type as recurso',
                'a.auditable_id as recurso_id',
                'u.name as usuario',
                'a.creado_en',
                'a.datos_antes',
                'a.datos_despues',
                DB::raw('NULL as puntos'),
                DB::raw('NULL as descripcion'),
                'a.ip_origen',
                DB::raw("{$seccionSql} as seccion"),
            ]);

        $puntaje = DB::table('puntaje_movimientos as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.usuario_id')
            ->select([
                DB::raw("'puntaje' as tipo_movimiento"),
                'p.id',
                'p.evento',
                'p.origen_type as recurso',
                'p.origen_id as recurso_id',
                'u.name as usuario',
                'p.creado_en',
                DB::raw('NULL as datos_antes'),
                'p.metadata as datos_despues',
                'p.puntos',
                'p.descripcion',
                DB::raw('NULL as ip_origen'),
                DB::raw("'puntaje' as seccion"),
            ]);

        $movimientos = DB::query()
            ->fromSub($auditoria->unionAll($puntaje), 'movimientos')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($busqueda) use ($buscar) {
                    $busqueda
                        ->where('evento', 'like', "%{$buscar}%")
                        ->orWhere('recurso', 'like', "%{$buscar}%")
                        ->orWhere('usuario', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when($seccion !== 'todas', fn ($query) => $query->where('seccion', $seccion))
            ->orderByDesc('creado_en')
            ->orderByRaw("CASE WHEN tipo_movimiento = 'puntaje' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate(40)
            ->withQueryString()
            ->through(function (object $movimiento): array {
                $decode = static function (mixed $value): ?array {
                    if (is_array($value)) {
                        return $value;
                    }

                    if (! is_string($value) || $value === '') {
                        return null;
                    }

                    $decoded = json_decode($value, true);

                    return is_array($decoded) ? $decoded : null;
                };

                return [
                    'id' => (int) $movimiento->id,
                    'tipo_movimiento' => $movimiento->tipo_movimiento,
                    'evento' => $movimiento->evento,
                    'seccion' => $movimiento->seccion,
                    'recurso' => $movimiento->recurso ? class_basename($movimiento->recurso) : 'Sistema',
                    'recurso_id' => $movimiento->recurso_id ? (int) $movimiento->recurso_id : null,
                    'usuario' => $movimiento->usuario ?? 'Sistema',
                    'creado_en' => $movimiento->creado_en,
                    'datos_antes' => $decode($movimiento->datos_antes),
                    'datos_despues' => $decode($movimiento->datos_despues),
                    'puntos' => $movimiento->puntos !== null ? (int) $movimiento->puntos : null,
                    'descripcion' => $movimiento->descripcion,
                    'ip_origen' => $movimiento->ip_origen,
                ];
            });

        return Inertia::render('auditoria/index', [
            'eventos' => $movimientos,
            'referencias' => $this->resolverReferencias($movimientos->getCollection()->all()),
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'seccion' => $seccion,
            ],
            'secciones' => collect($secciones)->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])->values()->all(),
        ]);
    }

    /**
     * Traduce los identificadores presentes en los movimientos a nombres legibles.
     *
     * @param  array<int, array<string, mixed>>  $movimientos
     * @return array<string, object>
     */
    private function resolverReferencias(array $movimientos): array
    {
        $camposPorTabla = [
            'usuarios' => ['profesional_id', 'destinatario_id', 'solicitado_por', 'usuario_id', 'aprobado_por', 'eliminacion_autorizada_por'],
            'legajos' => ['legajo_id'],
            'personas' => ['persona_id'],
            'localidades' => ['localidad_id'],
            'barrios' => ['barrio_id'],
            'tipos_intervencion' => ['tipo_intervencion_id'],
            'roles' => ['rol_id'],
        ];
        $recursoPorTabla = ['User' => 'usuarios', 'Legajo' => 'legajos', 'Persona' => 'personas'];
        $ids = array_fill_keys(array_keys($camposPorTabla), []);

        foreach ($movimientos as $movimiento) {
            foreach ([$movimiento['datos_antes'] ?? [], $movimiento['datos_despues'] ?? []] as $datos) {
                foreach ($camposPorTabla as $tabla => $campos) {
                    foreach ($campos as $campo) {
                        if (isset($datos[$campo]) && is_numeric($datos[$campo])) {
                            $ids[$tabla][] = (int) $datos[$campo];
                        }
                    }
                }
            }

            $tablaRecurso = $recursoPorTabla[$movimiento['recurso']] ?? null;

            if ($tablaRecurso !== null && $movimiento['recurso_id'] !== null) {
                $ids[$tablaRecurso][] = $movimiento['recurso_id'];
            }
        }

        $consultas = [
            'usuarios' => fn (array $valores) => DB::table('users')->whereIn('id', $valores)->pluck('name', 'id'),
            'legajos' => fn (array $valores) => DB::table('legajos')->whereIn('id', $valores)->pluck('numero_legajo', 'id'),
            'personas' => fn (array $valores) => DB::table('personas')->whereIn('id', $valores)
                ->get(['id', 'nombre', 'apellido'])
                ->mapWithKeys(fn (object $persona): array => [$persona->id => trim(ucwords(mb_strtolower("{$persona->nombre} {$persona->apellido}")))]),
            'localidades' => fn (array $valores) => DB::table('localidades')->whereIn('id', $valores)->pluck('nombre', 'id'),
            'barrios' => fn (array $valores) => DB::table('barrios')->whereIn('id', $valores)->pluck('nombre', 'id'),
            'tipos_intervencion' => fn (array $valores) => DB::table('tipos_intervencion')->whereIn('id', $valores)->pluck('nombre', 'id'),
            'roles' => fn (array $valores) => DB::table('roles')->whereIn('id', $valores)->pluck('nombre', 'id'),
        ];

        return collect($consultas)
            ->map(function (callable $consulta, string $tabla) use ($ids): object {
                $valores = array_values(array_unique($ids[$tabla]));

                return (object) ($valores === [] ? [] : $consulta($valores)->all());
            })
            ->all();
    }
}
