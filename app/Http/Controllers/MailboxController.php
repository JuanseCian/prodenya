<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use App\Models\DerivacionCaso;
use App\Models\Legajo;
use App\Models\MailMessage;
use App\Models\Tarea;
use App\Models\User;
use App\Services\DerivacionNotificationService;
use App\Services\GoogleGmailService;
use App\Services\HostingerMailboxService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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

    /** @var array<int, string> */
    private const MAILBOX_FOLDERS = ['inbox', 'sent', 'archive', 'spam', 'trash'];

    public function index(Request $request, GoogleGmailService $google): Response
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $soloNoLeidos = $request->boolean('no_leidos');
        $estado = $request->query('estado');
        $folder = $request->query('folder', 'inbox');
        $estados = ['Nuevo', 'Clasificado', 'Derivado', 'Respondido', 'Desestimado'];

        $folder = in_array($folder, self::MAILBOX_FOLDERS, true) ? $folder : 'inbox';
        $messageQuery = MailMessage::query()
            ->select([
                'id',
                'message_id',
                'provider',
                'provider_message_id',
                'folder',
                'direction',
                'thread_id',
                'from_name',
                'from_email',
                'to_email',
                'subject',
                'preview',
                'received_at',
                'sent_at',
                'is_read',
                'is_starred',
                'has_attachments',
                'estado',
            ])
            ->when($folder === 'sent', fn (Builder $query) => $query->where('direction', 'outgoing'))
            ->when($folder !== 'sent', fn (Builder $query) => $query
                ->where('folder', $folder)
                ->when($folder === 'inbox', fn (Builder $query) => $query->where('direction', 'incoming')))
            ->when($buscar !== '', function (Builder $query) use ($buscar): void {
                $query->where(function (Builder $query) use ($buscar): void {
                    $query->where('subject', 'like', "%{$buscar}%")
                        ->orWhere('from_name', 'like', "%{$buscar}%")
                        ->orWhere('from_email', 'like', "%{$buscar}%")
                        ->orWhere('to_email', 'like', "%{$buscar}%")
                        ->orWhere('preview', 'like', "%{$buscar}%")
                        ->orWhere('body_text', 'like', "%{$buscar}%");
                });
            })
            ->when($soloNoLeidos, fn (Builder $query) => $query->where('is_read', false))
            ->when(in_array($estado, $estados, true), fn (Builder $query) => $query->where('estado', $estado))
            ->with([
                'legajos:id,numero_legajo',
                'tareas' => fn ($query) => $query
                    ->whereNotIn('estado', ['completada', 'cancelada'])
                    ->latest(),
                'derivaciones' => fn ($query) => $query
                    ->whereNotIn('estado', ['rechazada', 'cancelada'])
                    ->latest(),
            ])
            ->orderByDesc('received_at')
            ->orderByDesc('id');

        $mensajes = $messageQuery->paginate(25)->withQueryString();
        $mensajes->setCollection($mensajes->getCollection()->map(
            fn (MailMessage $mensaje): array => $this->mapMensaje($mensaje),
        ));

        return Inertia::render('mailbox/index', [
            'mensajes' => $mensajes,
            'columnas' => [[
                'asunto' => 'Bandeja de entrada',
                'mensajes' => $mensajes->getCollection()->all(),
            ]],
            'filtros' => [
                'buscar' => $buscar !== '' ? $buscar : null,
                'no_leidos' => $soloNoLeidos,
                'estado' => in_array($estado, $estados, true) ? $estado : null,
                'folder' => $folder,
            ],
            'carpetas' => [
                'inbox' => MailMessage::where('folder', 'inbox')->count(),
                'sent' => MailMessage::where('direction', 'outgoing')->count(),
                'archive' => MailMessage::where('folder', 'archive')->count(),
                'spam' => MailMessage::where('folder', 'spam')->count(),
                'trash' => MailMessage::where('folder', 'trash')->count(),
            ],
            'noLeidos' => MailMessage::where('folder', 'inbox')->where('is_read', false)->count(),
            'estados' => $estados,
            'syncError' => session('errors')?->getBag('default')->first('mailbox'),
            'googleConfigured' => $google->isConfigured(),
            'googleConnected' => $google->isConnected(),
            'googleEmail' => $google->connectedEmail(),
            'googleSendAccess' => $google->hasSendAccess(),
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

    public function show(Request $request, MailMessage $mailMessage): JsonResponse
    {
        abort_unless($request->user()?->tieneRol('admin', 'oficios', 'coordinador'), 403);

        $mailMessage->load([
            'legajos:id,numero_legajo',
            'tareas' => fn ($query) => $query->whereNotIn('estado', ['completada', 'cancelada'])->latest(),
            'derivaciones' => fn ($query) => $query->whereNotIn('estado', ['rechazada', 'cancelada'])->latest(),
        ]);

        return response()->json(['message' => $this->mapMensaje($mailMessage, true)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapMensaje(MailMessage $mensaje, bool $includeContent = false): array
    {
        $attachments = collect($mensaje->attachments ?? [])->values()->map(
            fn (array $attachment, int $index): array => [
                'index' => $index,
                'name' => $attachment['name'] ?? 'Adjunto',
                'mime' => $attachment['mime'] ?? 'application/octet-stream',
                'size' => (int) ($attachment['size'] ?? 0),
            ],
        )->all();

        return [
            'id' => $mensaje->id,
            'message_id' => $mensaje->message_id,
            'provider' => $mensaje->provider,
            'provider_message_id' => $mensaje->provider_message_id,
            'folder' => $mensaje->folder,
            'direction' => $mensaje->direction,
            'subject' => $mensaje->subject ?: '(Sin asunto)',
            'from_name' => $mensaje->from_name,
            'from_email' => $mensaje->from_email,
            'to_email' => $mensaje->to_email,
            'cc_email' => $mensaje->cc_email,
            'bcc_email' => $mensaje->bcc_email,
            'received_at' => $mensaje->received_at?->toIso8601String(),
            'sent_at' => $mensaje->sent_at?->toIso8601String(),
            'is_read' => $mensaje->is_read,
            'is_starred' => $mensaje->is_starred,
            'has_attachments' => $mensaje->has_attachments || $attachments !== [],
            'estado' => $mensaje->estado,
            'body_text' => $includeContent ? $mensaje->body_text : null,
            'attachments' => $attachments,
            'legajos' => $mensaje->legajos->filter(fn (Legajo $legajo): bool => $this->puedeAccederLegajo(
                request()->user(),
                $legajo,
                'persona',
            ))->map(fn (Legajo $legajo) => [
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
            'preview' => $mensaje->preview ?: str($mensaje->body_text)->squish()->limit(180)->toString(),
        ];
    }

    public function sync(HostingerMailboxService $mailbox, GoogleGmailService $google): RedirectResponse
    {
        if ($google->isEnabled()) {
            if (! $google->isConnected()) {
                return back()->withErrors(['mailbox' => 'Vinculá la cuenta de Gmail antes de sincronizar.']);
            }

            try {
                $synchronized = $google->sync();
                Inertia::flash('toast', [
                    'type' => 'success',
                    'message' => $synchronized === 1
                        ? 'Se sincronizó 1 correo nuevo desde Gmail.'
                        : "Se sincronizaron {$synchronized} correos nuevos desde Gmail.",
                ]);

                return back();
            } catch (Throwable $exception) {
                report($exception);

                $message = str_contains($exception->getMessage(), 'Quota exceeded')
                    ? 'Google alcanzó el límite temporal de Gmail. Esperá un minuto y volvé a intentar.'
                    : 'No se pudo sincronizar Gmail. Volvé a vincular la cuenta.';

                return back()->withErrors(['mailbox' => $message]);
            }
        }

        if (! config('services.hostinger_mailbox.enabled')) {
            return back()->withErrors([
                'mailbox' => 'La sincronización del buzón está deshabilitada en este entorno.',
            ]);
        }

        try {
            $synchronized = $mailbox->sync();
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => $synchronized === 1
                    ? 'Se sincronizó 1 correo nuevo.'
                    : "Se sincronizaron {$synchronized} correos nuevos.",
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['mailbox' => 'No se pudo sincronizar el buzón. Verificá la configuración de Hostinger.']);
        }

        return back();
    }

    public function googleRedirect(Request $request, GoogleGmailService $google): RedirectResponse
    {
        if (! $google->isConfigured()) {
            return redirect()->route('mailbox.index')->withErrors([
                'mailbox' => 'Faltan las credenciales OAuth de Google en el entorno.',
            ]);
        }

        $state = Str::random(64);
        $request->session()->put('google_oauth_state', $state);

        return redirect()->away($google->authorizationUrl($state));
    }

    public function googleCallback(Request $request, GoogleGmailService $google): RedirectResponse
    {
        $state = (string) $request->session()->pull('google_oauth_state');

        if ($state === '' || ! hash_equals($state, (string) $request->query('state'))) {
            return redirect()->route('mailbox.index')->withErrors([
                'mailbox' => 'La validación de seguridad de Google no fue válida.',
            ]);
        }

        if ($request->filled('error')) {
            return redirect()->route('mailbox.index')->withErrors([
                'mailbox' => 'La autorización de Gmail fue cancelada.',
            ]);
        }

        $code = (string) $request->query('code');

        if ($code === '') {
            return redirect()->route('mailbox.index')->withErrors([
                'mailbox' => 'Google no devolvió un código de autorización.',
            ]);
        }

        try {
            $token = $google->authorize($code);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('mailbox.index')->withErrors([
                'mailbox' => 'No se pudo vincular la cuenta de Gmail.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Gmail vinculado: {$token->email}.",
        ]);

        return redirect()->route('mailbox.index');
    }

    public function googleDisconnect(GoogleGmailService $google): RedirectResponse
    {
        $google->disconnect();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'La cuenta de Gmail fue desvinculada.',
        ]);

        return back();
    }

    public function send(Request $request, HostingerMailboxService $mailbox, GoogleGmailService $google): RedirectResponse
    {
        $data = $request->validate([
            'to_email' => ['required', 'email', 'max:255'],
            'cc_email' => ['nullable', 'email', 'max:255'],
            'bcc_email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:100000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,zip'],
        ]);

        $attachments = $this->storeAttachments($request);

        try {
            if ($google->isEnabled()) {
                $google->send($data['to_email'], $data['subject'], $data['body'], $attachments, null, $data['cc_email'] ?? null, $data['bcc_email'] ?? null);
            } else {
                $mailbox->send($data['to_email'], $data['subject'], $data['body'], $attachments, null, $data['cc_email'] ?? null, $data['bcc_email'] ?? null);
            }
        } catch (Throwable $exception) {
            $this->deleteAttachments($attachments);
            report($exception);

            $message = $google->isEnabled()
                ? 'No se pudo enviar el correo por Gmail. Volvé a autorizar la cuenta.'
                : 'No se pudo enviar el correo. Verificá la configuración SMTP.';

            return back()->withErrors(['mailbox' => $message]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Correo enviado correctamente.']);

        return back();
    }

    public function reply(Request $request, MailMessage $mailMessage, HostingerMailboxService $mailbox, GoogleGmailService $google): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'cc_email' => ['nullable', 'email', 'max:255'],
            'bcc_email' => ['nullable', 'email', 'max:255'],
            'body' => ['required', 'string', 'max:100000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,zip'],
        ]);

        if (! filter_var($mailMessage->from_email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['mailbox' => 'El remitente no tiene una dirección válida para responder.']);
        }

        $attachments = $this->storeAttachments($request);

        try {
            if ($google->isEnabled()) {
                $google->send($mailMessage->from_email, $data['subject'], $data['body'], $attachments, $mailMessage, $data['cc_email'] ?? null, $data['bcc_email'] ?? null);
            } else {
                $mailbox->send($mailMessage->from_email, $data['subject'], $data['body'], $attachments, $mailMessage, $data['cc_email'] ?? null, $data['bcc_email'] ?? null);
            }
        } catch (Throwable $exception) {
            $this->deleteAttachments($attachments);
            report($exception);

            $message = $google->isEnabled()
                ? 'No se pudo enviar la respuesta por Gmail. Volvé a autorizar la cuenta.'
                : 'No se pudo enviar la respuesta. Verificá la configuración SMTP.';

            return back()->withErrors(['mailbox' => $message]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Respuesta enviada correctamente.']);

        return back();
    }

    public function downloadAttachment(MailMessage $mailMessage, int $attachment): StreamedResponse
    {
        abort_unless($mailMessage->attachments[$attachment] ?? null, 404);
        $file = $mailMessage->attachments[$attachment];

        abort_unless(Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name'], [
            'Content-Type' => $file['mime'],
        ]);
    }

    /**
     * @return array<int, array{path: string, name: string, mime: string, size: int}>
     */
    private function storeAttachments(Request $request): array
    {
        $attachments = [];

        foreach ((array) $request->file('attachments', []) as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $attachments[] = [
                'path' => $file->store('mail-attachments'),
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => (int) ($file->getSize() ?: 0),
            ];
        }

        return $attachments;
    }

    /**
     * @param  array<int, array{path: string, name: string, mime: string, size: int}>  $attachments
     */
    private function deleteAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            Storage::disk('local')->delete($attachment['path']);
        }
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
            ->where('estado', '!=', 'eliminado')
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
            ->get()
            ->filter(fn (Legajo $legajo): bool => $this->puedeAccederLegajo(
                $request->user(),
                $legajo,
                'persona',
            ));

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

        $legajoIds = array_values(array_unique($datos['legajo_ids'] ?? []));
        $legajosVisibles = Legajo::query()
            ->whereKey($legajoIds)
            ->get()
            ->filter(fn (Legajo $legajo): bool => $this->puedeAccederLegajo(
                $request->user(),
                $legajo,
                'persona',
            ));

        if ($legajosVisibles->count() !== count($legajoIds)) {
            return back()->withErrors(['legajo_ids' => 'No tenés permiso para vincular uno de los legajos seleccionados.']);
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
                    ->get()
                    ->each(function (DerivacionCaso $derivacion) use ($notificador, $request): void {
                        $derivacion->update([
                            'estado' => DerivacionCaso::ESTADO_CANCELADA,
                            'cancelada_en' => now(),
                        ]);
                        $notificador->notificarCancelacion($derivacion, $request->user());
                    });

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
                    $notificador->notificarCancelacion($derivacion, $request->user());
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
