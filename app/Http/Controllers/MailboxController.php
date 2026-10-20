<?php

namespace App\Http\Controllers;

use App\Models\Legajo;
use App\Models\AuditoriaEvento;
use App\Models\MailMessage;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MailboxController extends Controller
{
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
            ->with('legajos')
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
                ->whereHas('rol', fn ($query) => $query->whereIn('nombre', [
                    'admin', 'coordinador', 'profesional', 'operador', 'administrativo',
                ]))
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
            'nombre' => trim(($legajo->persona?->apellido ?? '') . ', ' . ($legajo->persona?->nombre ?? '')),
            'dni' => $legajo->persona?->dni,
        ])->values());
    }

    public function classify(Request $request, MailMessage $mailMessage): RedirectResponse
    {
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

        DB::transaction(function () use ($datos, $mailMessage, $request) {
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
                return;
            }

            $tarea = Tarea::query()
                ->where('mail_message_id', $mailMessage->id)
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->first();

            if (! $tarea) {
                $tarea = Tarea::create([
                    'tipo' => 'oficio',
                    'titulo' => 'Revisar: ' . ($mailMessage->subject ?: 'comunicación sin asunto'),
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
        });

        return back()->with('status', 'Clasificación y derivación guardadas.');
    }
}
