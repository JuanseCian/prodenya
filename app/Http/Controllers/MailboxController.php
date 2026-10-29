<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\Tarea;
use App\Models\User;
use App\Services\DerivacionNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MailboxController extends Controller
{
    /** @var array<int, string> */
    private const RESPONSABLE_ROLES = [
        'admin',
        'oficios',
        'coordinador',
        'profesional',
        'administrativo',
        'operador',
    ];

    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $soloNoLeidos = $request->boolean('no_leidos');
        $estado = $request->query('estado');
        $estados = ['Nuevo', 'Clasificado', 'Derivado', 'Respondido', 'Desestimado'];

        $mensajes = MailMessage::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('subject', 'like', "%{$buscar}%")
                        ->orWhere('from_name', 'like', "%{$buscar}%")
                        ->orWhere('from_email', 'like', "%{$buscar}%")
                        ->orWhere('body_text', 'like', "%{$buscar}%");
                });
            })
            ->when($soloNoLeidos, fn ($query) => $query->where('is_read', false))
            ->when(in_array($estado, $estados, true), fn ($query) => $query->where('estado', $estado))
            ->with([
                'legajos',
                'tareas' => fn ($query) => $query
                    ->whereNotIn('estado', ['completada', 'cancelada'])
                    ->latest(),
                'derivaciones' => fn ($query) => $query
                    ->whereNotIn('estado', ['rechazada', 'cancelada'])
                    ->latest(),
            ])
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (MailMessage $mensaje) => [
                'id' => $mensaje->id,
                'subject' => $mensaje->subject ?: '(Sin asunto)',
                'from_name' => $mensaje->from_name,
                'from_email' => $mensaje->from_email,
                'received_at' => $mensaje->received_at?->toIso8601String(),
                'is_read' => $mensaje->is_read,
                'estado' => $mensaje->estado,
                'legajos' => $mensaje->legajos->map(fn (Legajo $legajo) => [
                    'id' => $legajo->id,
                    'numero_legajo' => $legajo->numero_legajo,
                ])->values()->all(),
                'derivacion' => ($derivacion = $mensaje->derivaciones->first()) ? [
                    'asignado_a' => $derivacion->destinatario_id,
                    'fecha_limite' => $derivacion->fecha_limite?->toDateString(),
                ] : ($mensaje->tareas->first() ? [
                    'asignado_a' => $mensaje->tareas->first()->asignado_a,
                    'fecha_limite' => $mensaje->tareas->first()->fecha_limite?->toDateString(),
                ] : null),
                'preview' => str($mensaje->body_text)->squish()->limit(180)->toString(),
            ]);

        return Inertia::render('mailbox/index', [
            'mensajes' => $mensajes,
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'no_leidos' => $soloNoLeidos,
                'estado' => in_array($estado, $estados, true) ? $estado : null,
            ],
            'noLeidos' => MailMessage::where('is_read', false)->count(),
            'estados' => $estados,
            'responsables' => User::query()
                ->with('rol')
                ->where('activo', true)
                ->whereHas('rol', fn ($query) => $query->whereIn('nombre', self::RESPONSABLE_ROLES))
                ->orderBy('name')
                ->get(['id', 'name', 'profesion', 'rol_id'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profesion' => $user->profesion,
                    'rol' => $user->rol?->nombre,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function markRead(MailMessage $mailMessage): RedirectResponse
    {
        $mailMessage->update(['is_read' => true]);

        return back();
    }

    public function buscarLegajos(Request $request): JsonResponse
    {
        $buscar = trim((string) $request->query('buscar', ''));

        if (mb_strlen($buscar) < 2) {
            return response()->json([]);
        }

        $digitos = preg_replace('/\D/', '', $buscar);
        $legajos = Legajo::query()
            ->with('persona')
            ->where(function ($query) use ($buscar, $digitos) {
                $query->where('numero_legajo', 'like', "%{$buscar}%")
                    ->orWhereHas('persona', function ($persona) use ($buscar, $digitos) {
                        $persona->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('apellido', 'like', "%{$buscar}%");

                        if ($digitos !== '') {
                            $persona->orWhere('dni', 'like', "%{$digitos}%");
                        }
                    });
            })
            ->orderBy('numero_legajo')
            ->limit(12)
            ->get();

        return response()->json($legajos->map(fn (Legajo $legajo) => [
            'id' => $legajo->id,
            'numero_legajo' => $legajo->numero_legajo,
            'nombre' => trim(($legajo->persona?->apellido ?? '').', '.($legajo->persona?->nombre ?? '')),
            'dni' => $legajo->persona?->dni,
        ])->values());
    }

    public function classify(
        Request $request,
        MailMessage $mailMessage,
        DerivacionNotificationService $notificador,
    ): RedirectResponse {
        $estados = ['Nuevo', 'Clasificado', 'Derivado', 'Respondido', 'Desestimado'];
        $datos = $request->validate([
            'estado' => ['required', Rule::in($estados)],
            'legajo_ids' => ['nullable', 'array'],
            'legajo_ids.*' => ['integer', Rule::exists(Legajo::class, 'id')],
            'asignado_a' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'fecha_limite' => ['nullable', 'date'],
        ]);

        if ($datos['estado'] === 'Derivado' && empty($datos['asignado_a'])) {
            return back()->withErrors(['asignado_a' => 'Seleccioná una persona responsable para derivar el oficio.']);
        }

        if ($datos['estado'] === 'Derivado' && empty($datos['legajo_ids'])) {
            return back()->withErrors(['legajo_ids' => 'Vinculá al menos un legajo antes de derivar el oficio.']);
        }

        if ($datos['estado'] === 'Derivado' && ! $this->responsablesQuery()->whereKey($datos['asignado_a'])->exists()) {
            return back()->withErrors(['asignado_a' => 'La persona seleccionada no está habilitada para recibir derivaciones.']);
        }

        $destinatario = $datos['estado'] === 'Derivado'
            ? User::findOrFail($datos['asignado_a'])
            : null;

        DB::transaction(function () use ($datos, $mailMessage, $request, $notificador, $destinatario) {
            $legajoIds = array_values(array_unique($datos['legajo_ids'] ?? []));
            $legajosAnteriores = $mailMessage->legajos()->pluck('legajos.id')->map(fn ($id) => (int) $id)->values()->all();

            $mailMessage->update([
                'estado' => $datos['estado'],
                'clasificado_por' => $request->user()->id,
                'clasificado_en' => now(),
            ]);
            $mailMessage->legajos()->sync($legajoIds);
            if ($legajosAnteriores !== $legajoIds) {
                AuditoriaEvento::registrar('mail.legajos_vinculados', $mailMessage, [
                    'legajo_ids' => $legajosAnteriores,
                ], [
                    'legajo_ids' => $legajoIds,
                ]);
            }

            if ($datos['estado'] !== 'Derivado') {
                $mailMessage->tareas()
                    ->whereNotIn('estado', ['completada', 'cancelada'])
                    ->update([
                        'estado' => 'cancelada',
                        'completada_en' => null,
                    ]);
                $mailMessage->derivaciones()
                    ->whereIn('estado', [DerivacionCaso::ESTADO_PENDIENTE, DerivacionCaso::ESTADO_ACEPTADA])
                    ->update([
                        'estado' => DerivacionCaso::ESTADO_CANCELADA,
                        'cancelada_en' => now(),
                    ]);

                return;
            }

            $tarea = Tarea::query()
                ->where('mail_message_id', $mailMessage->id)
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->first();

            if (! $tarea) {
                $tarea = Tarea::create([
                    'tipo' => 'oficio',
                    'titulo' => 'Revisar: '.($mailMessage->subject ?: 'comunicación sin asunto'),
                    'descripcion' => $mailMessage->body_text,
                    'prioridad' => 'normal',
                    'estado' => 'pendiente',
                    'fecha_limite' => $datos['fecha_limite'] ?? null,
                    'asignado_a' => $datos['asignado_a'],
                    'creado_por' => $request->user()->id,
                    'legajo_id' => $legajoIds[0] ?? null,
                    'mail_message_id' => $mailMessage->id,
                ]);
            } else {
                $tarea->update([
                    'asignado_a' => $datos['asignado_a'],
                    'fecha_limite' => $datos['fecha_limite'] ?? $tarea->fecha_limite,
                    'legajo_id' => $legajoIds[0] ?? null,
                ]);
            }

            $tarea->recursos()->where('tipo_recurso', 'legajo')->delete();
            foreach ($legajoIds as $legajoId) {
                $tarea->recursos()->create([
                    'tipo_recurso' => 'legajo',
                    'recurso_id' => $legajoId,
                ]);
            }
            $tarea->recursos()->firstOrCreate([
                'tipo_recurso' => 'mail',
                'recurso_id' => $mailMessage->id,
            ]);

            $derivacionesActivas = $mailMessage->derivaciones()
                ->whereIn('estado', [DerivacionCaso::ESTADO_PENDIENTE, DerivacionCaso::ESTADO_ACEPTADA])
                ->get();

            foreach ($derivacionesActivas as $derivacion) {
                if (! in_array($derivacion->legajo_id, $legajoIds, true)) {
                    $derivacion->update([
                        'estado' => DerivacionCaso::ESTADO_CANCELADA,
                        'cancelada_en' => now(),
                    ]);
                }
            }

            foreach ($legajoIds as $legajoId) {
                $derivacion = $derivacionesActivas->firstWhere('legajo_id', $legajoId);

                if ($derivacion?->estado === DerivacionCaso::ESTADO_ACEPTADA) {
                    continue;
                }

                if ($derivacion) {
                    $destinatarioCambio = $derivacion->destinatario_id !== $destinatario?->id;
                    $derivacion->update([
                        'destinatario_id' => $datos['asignado_a'],
                        'motivo' => str($mailMessage->subject ?: 'Comunicación sin asunto')->limit(120)->toString(),
                        'detalle' => $mailMessage->body_text,
                        'fecha_limite' => $datos['fecha_limite'] ?? null,
                    ]);

                    if ($destinatarioCambio && $destinatario) {
                        $notificador->notificar(
                            $derivacion,
                            Legajo::findOrFail($legajoId),
                            $request->user(),
                            $destinatario,
                        );
                    }

                    continue;
                }

                $derivacion = DerivacionCaso::create([
                    'legajo_id' => $legajoId,
                    'solicitado_por' => $request->user()->id,
                    'destinatario_id' => $datos['asignado_a'],
                    'modo' => DerivacionCaso::MODO_ADICIONAL,
                    'estado' => DerivacionCaso::ESTADO_PENDIENTE,
                    'motivo' => str($mailMessage->subject ?: 'Comunicación sin asunto')->limit(120)->toString(),
                    'detalle' => $mailMessage->body_text,
                    'grupo_derivacion_id' => (string) Str::uuid(),
                    'fecha_limite' => $datos['fecha_limite'] ?? null,
                    'mail_message_id' => $mailMessage->id,
                ]);

                if ($destinatario) {
                    $notificador->notificar(
                        $derivacion,
                        Legajo::findOrFail($legajoId),
                        $request->user(),
                        $destinatario,
                    );
                }
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Clasificación y derivación guardadas.',
        ]);

        return back();
    }

    /**
     * @return Builder<User>
     */
    private function responsablesQuery(): Builder
    {
        return User::query()
            ->where('activo', true)
            ->whereHas('rol', fn ($query) => $query->whereIn('nombre', self::RESPONSABLE_ROLES));
    }
}
