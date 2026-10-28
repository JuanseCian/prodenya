<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use App\Models\Conversacion;
use App\Models\Documento;
use App\Models\Intervencion;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ComunicacionController extends Controller
{
    private const ROLES = [
        'admin',
        'operador',
        'consulta',
        'oficios',
        'coordinador',
        'profesional',
        'administrativo',
    ];

    public function index(Request $request): Response
    {
        $conversacion = null;
        $id = $request->integer('conversacion');

        if ($id > 0) {
            $conversacion = Conversacion::findOrFail($id);
            $this->autorizarParticipante($conversacion, $request->user());
        }

        return $this->render($request, $conversacion);
    }

    public function show(Request $request, Conversacion $conversacion): Response
    {
        $this->autorizarParticipante($conversacion, $request->user());

        return $this->render($request, $conversacion);
    }

    public function noLeidos(Request $request): JsonResponse
    {
        return response()->json([
            'no_leidos' => $this->cantidadNoLeidos($request->user()),
        ]);
    }

    public function crearConversacion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'usuario_id' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id'),
            ],
        ]);

        abort_if((int) $datos['usuario_id'] === $request->user()->id, 422, 'No podés iniciar una conversación con vos mismo.');
        $destinatario = User::with('rol')->findOrFail($datos['usuario_id']);
        abort_unless($destinatario->activo && in_array($destinatario->rol?->nombre, self::ROLES, true), 422, 'El usuario seleccionado no está disponible.');

        $existente = Conversacion::query()
            ->where('tipo', 'directa')
            ->whereHas('usuarios', fn ($query) => $query->whereKey($request->user()->id))
            ->whereHas('usuarios', fn ($query) => $query->whereKey($destinatario->id))
            ->withCount('usuarios')
            ->get()
            ->first(fn (Conversacion $conversacion) => $conversacion->usuarios_count === 2);

        if ($existente) {
            return redirect()->route('comunicacion.show', $existente);
        }

        $conversacion = DB::transaction(function () use ($request, $datos) {
            $conversacion = Conversacion::create([
                'tipo' => 'directa',
                'creado_por' => $request->user()->id,
            ]);

            $conversacion->usuarios()->attach([
                $request->user()->id => ['ultimo_leido_en' => now()],
                $datos['usuario_id'] => ['ultimo_leido_en' => null],
            ]);

            return $conversacion;
        });

        return redirect()->route('comunicacion.show', $conversacion);
    }

    public function enviarMensaje(Request $request, Conversacion $conversacion): RedirectResponse
    {
        $this->autorizarParticipante($conversacion, $request->user());

        $datos = $request->validate([
            'cuerpo' => ['nullable', 'string', 'max:5000'],
            'tipo_recurso' => ['nullable', 'string', Rule::in(['legajo', 'intervencion', 'documento', 'mail'])],
            'recurso_id' => ['nullable', 'integer', 'required_with:tipo_recurso'],
        ]);

        $cuerpo = trim((string) ($datos['cuerpo'] ?? ''));
        $tipoRecurso = $datos['tipo_recurso'] ?? null;
        $recursoId = $datos['recurso_id'] ?? null;

        if ($cuerpo === '' && ! $tipoRecurso) {
            return back()->withErrors(['cuerpo' => 'Escribí un mensaje o seleccioná un recurso para compartir.']);
        }

        if ($tipoRecurso) {
            abort_unless($this->buscarRecurso($tipoRecurso, (int) $recursoId, $request->user()), 403, 'No tenés acceso a ese recurso.');
        }

        DB::transaction(function () use ($request, $conversacion, $cuerpo, $tipoRecurso, $recursoId) {
            $mensaje = $conversacion->mensajes()->create([
                'usuario_id' => $request->user()->id,
                'cuerpo' => $cuerpo !== '' ? $cuerpo : null,
            ]);

            if ($tipoRecurso) {
                $mensaje->recursos()->create([
                    'tipo_recurso' => $tipoRecurso,
                    'recurso_id' => $recursoId,
                ]);
            }

            $conversacion->update(['ultimo_mensaje_en' => now()]);
        });

        return back()->with('status', 'Mensaje enviado.');
    }

    public function marcarLeida(Request $request, Conversacion $conversacion): RedirectResponse
    {
        $this->autorizarParticipante($conversacion, $request->user());

        $conversacion->usuarios()->updateExistingPivot($request->user()->id, [
            'ultimo_leido_en' => now(),
        ]);
        AuditoriaEvento::registrar('comunicacion.leida', $conversacion, null, [
            'usuario_id' => $request->user()->id,
        ]);

        return back();
    }

    public function buscarRecursos(Request $request): JsonResponse
    {
        $buscar = trim((string) $request->query('buscar', ''));

        if ($buscar === '') {
            return response()->json([]);
        }

        $recursos = collect();

        $recursos = $recursos->merge(
            Legajo::with('persona')
                ->where(function ($query) use ($buscar) {
                    $query->where('numero_legajo', 'like', "%{$buscar}%")
                        ->orWhereHas('persona', function ($persona) use ($buscar) {
                            $persona->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('apellido', 'like', "%{$buscar}%")
                                ->orWhere('dni', 'like', "%{$buscar}%");
                        });
                })
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Legajo $legajo) => $this->opcionRecurso(
                    'legajo',
                    $legajo->id,
                    "Legajo {$legajo->numero_legajo}",
                    $legajo->persona ? "{$legajo->persona->apellido}, {$legajo->persona->nombre}" : null,
                )),
        );

        $recursos = $recursos->merge(
            Intervencion::with(['tipo', 'legajo'])
                ->where(function ($query) use ($buscar) {
                    $query->where('detalle', 'like', "%{$buscar}%")
                        ->orWhereHas('tipo', fn ($tipo) => $tipo->where('nombre', 'like', "%{$buscar}%"))
                        ->orWhereHas('legajo', fn ($legajo) => $legajo->where('numero_legajo', 'like', "%{$buscar}%"));
                })
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Intervencion $intervencion) => $this->opcionRecurso(
                    'intervencion',
                    $intervencion->id,
                    'Intervención'.($intervencion->tipo ? " · {$intervencion->tipo->nombre}" : ''),
                    $intervencion->legajo ? "Legajo {$intervencion->legajo->numero_legajo}" : null,
                )),
        );

        $recursos = $recursos->merge(
            Documento::with('intervencion.legajo')
                ->where('activo', true)
                ->whereNotNull('intervencion_id')
                ->where('nombre_archivo', 'like', "%{$buscar}%")
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Documento $documento) => $this->opcionRecurso(
                    'documento',
                    $documento->id,
                    "Documento · {$documento->nombre_archivo}",
                    $documento->intervencion?->legajo?->numero_legajo
                        ? "Legajo {$documento->intervencion->legajo->numero_legajo}"
                        : null,
                )),
        );

        if ($request->user()->esAdmin()) {
            $recursos = $recursos->merge(
                MailMessage::query()
                    ->where(function ($query) use ($buscar) {
                        $query->where('subject', 'like', "%{$buscar}%")
                            ->orWhere('from_name', 'like', "%{$buscar}%")
                            ->orWhere('from_email', 'like', "%{$buscar}%");
                    })
                    ->latest('received_at')
                    ->limit(5)
                    ->get()
                    ->map(fn (MailMessage $mail) => $this->opcionRecurso(
                        'mail',
                        $mail->id,
                        'Mail · '.($mail->subject ?: '(Sin asunto)'),
                        $mail->from_name ?: $mail->from_email,
                    )),
            );
        }

        return response()->json($recursos->take(15)->values());
    }

    public function abrirRecurso(Request $request, string $tipo, int $id): RedirectResponse
    {
        $recurso = $this->buscarRecurso($tipo, $id, $request->user());
        abort_unless($recurso, 404);

        return match ($tipo) {
            'legajo' => redirect()->route('legajos.show', $recurso->id),
            'intervencion' => redirect()->route('legajos.show', $recurso->legajo_id),
            'documento' => redirect()->route('intervenciones.documentos.descargar', $recurso),
            'mail' => redirect()->route('mailbox.index', ['buscar' => $recurso->subject ?: $recurso->from_email]),
            default => abort(404),
        };
    }

    private function render(Request $request, ?Conversacion $seleccionada): Response
    {
        $usuario = $request->user();
        $conversaciones = Conversacion::query()
            ->whereHas('usuarios', fn ($query) => $query->whereKey($usuario->id))
            ->with('usuarios:id,name')
            ->orderByDesc('ultimo_mensaje_en')
            ->orderByDesc('id')
            ->get()
            ->map(function (Conversacion $conversacion) use ($usuario) {
                $ultimo = $conversacion->mensajes()->latest()->first();
                $participantes = $conversacion->usuarios->where('id', '!=', $usuario->id);
                $pivot = $conversacion->usuarios->firstWhere('id', $usuario->id)?->pivot;
                $ultimoLeido = $pivot?->ultimo_leido_en;

                return [
                    'id' => $conversacion->id,
                    'nombre' => $conversacion->titulo ?: $participantes->pluck('name')->join(', '),
                    'ultimo_mensaje' => $ultimo?->cuerpo ?: ($ultimo ? 'Recurso compartido' : 'Sin mensajes'),
                    'ultimo_mensaje_en' => $ultimo?->created_at?->toIso8601String(),
                    'no_leidos' => $ultimoLeido
                        ? $conversacion->mensajes()->where('created_at', '>', $ultimoLeido)->where('usuario_id', '!=', $usuario->id)->count()
                        : $conversacion->mensajes()->where('usuario_id', '!=', $usuario->id)->count(),
                ];
            })
            ->values();

        $mensajes = [];
        $nombreConversacion = null;

        if ($seleccionada) {
            $this->marcarConversacionComoLeida($seleccionada, $usuario);
            $seleccionada->load('usuarios:id,name');
            $nombreConversacion = $seleccionada->titulo
                ?: $seleccionada->usuarios->where('id', '!=', $usuario->id)->pluck('name')->join(', ');
            $mensajes = $seleccionada->mensajes()
                ->with(['usuario:id,name', 'recursos'])
                ->latest()
                ->limit(100)
                ->get()
                ->reverse()
                ->values()
                ->map(fn (Mensaje $mensaje) => [
                    'id' => $mensaje->id,
                    'cuerpo' => $mensaje->cuerpo,
                    'usuario_id' => $mensaje->usuario_id,
                    'usuario' => $mensaje->usuario?->name,
                    'creado_en' => $mensaje->created_at?->toIso8601String(),
                    'recursos' => $mensaje->recursos->map(fn ($recurso) => $this->infoRecurso($recurso->tipo_recurso, $recurso->recurso_id, $usuario))->values(),
                ])
                ->all();
        }

        return Inertia::render('comunicacion/index', [
            'conversaciones' => $conversaciones,
            'conversacionSeleccionada' => $seleccionada?->id,
            'nombreConversacion' => $nombreConversacion,
            'mensajes' => $mensajes,
            'usuarios' => User::query()
                ->where('activo', true)
                ->where('id', '!=', $usuario->id)
                ->whereHas('rol', fn ($query) => $query->whereIn('nombre', self::ROLES))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'profesion']),
        ]);
    }

    private function autorizarParticipante(Conversacion $conversacion, User $usuario): void
    {
        abort_unless($conversacion->usuarios()->whereKey($usuario->id)->exists(), 403);
    }

    private function marcarConversacionComoLeida(Conversacion $conversacion, User $usuario): void
    {
        $conversacion->usuarios()->updateExistingPivot($usuario->id, ['ultimo_leido_en' => now()]);
    }

    private function cantidadNoLeidos(User $usuario): int
    {
        return (int) DB::table('mensajes')
            ->join('conversacion_usuario', 'conversacion_usuario.conversacion_id', '=', 'mensajes.conversacion_id')
            ->where('conversacion_usuario.usuario_id', $usuario->id)
            ->where('mensajes.usuario_id', '!=', $usuario->id)
            ->where(function ($query) {
                $query->whereNull('conversacion_usuario.ultimo_leido_en')
                    ->orWhereColumn('mensajes.created_at', '>', 'conversacion_usuario.ultimo_leido_en');
            })
            ->count();
    }

    private function buscarRecurso(string $tipo, int $id, User $usuario): ?Model
    {
        return match ($tipo) {
            'legajo' => Legajo::find($id),
            'intervencion' => Intervencion::with('legajo')->find($id),
            'documento' => Documento::where('activo', true)->whereNotNull('intervencion_id')->find($id),
            'mail' => $usuario->esAdmin() ? MailMessage::find($id) : null,
            default => null,
        };
    }

    private function infoRecurso(string $tipo, int $id, User $usuario): array
    {
        $recurso = $this->buscarRecurso($tipo, $id, $usuario);

        if (! $recurso) {
            return [
                'tipo' => $tipo,
                'id' => $id,
                'label' => 'Recurso sin acceso',
                'accesible' => false,
                'url' => null,
            ];
        }

        return [
            'tipo' => $tipo,
            'id' => $id,
            'label' => $this->etiquetaRecurso($tipo, $recurso),
            'accesible' => true,
            'url' => route('comunicacion.recursos.abrir', [$tipo, $id]),
        ];
    }

    private function etiquetaRecurso(string $tipo, Model $recurso): string
    {
        return match ($tipo) {
            'legajo' => "Legajo {$recurso->numero_legajo}",
            'intervencion' => 'Intervención'.($recurso->tipo?->nombre ? " · {$recurso->tipo->nombre}" : ''),
            'documento' => "Documento · {$recurso->nombre_archivo}",
            'mail' => 'Mail · '.($recurso->subject ?: '(Sin asunto)'),
            default => 'Recurso compartido',
        };
    }

    private function opcionRecurso(string $tipo, int $id, string $label, ?string $detail): array
    {
        return [
            'tipo' => $tipo,
            'id' => $id,
            'label' => $label,
            'detail' => $detail,
        ];
    }
}
