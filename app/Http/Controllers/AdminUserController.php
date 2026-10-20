<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\RegistroUsuarioNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = (string) $request->query('estado', '');
        $rolId = $request->integer('rol_id') ?: null;
        $orden = in_array($request->query('orden'), ['created_at', 'name', 'ultimo_acceso', 'estado_cuenta'], true)
            ? $request->query('orden')
            : 'created_at';
        $direccion = $request->query('direccion') === 'asc' ? 'asc' : 'desc';

        $usuarios = User::query()
            ->with('rol')
            ->when($buscar !== '', fn ($query) => $query->where(function ($query) use ($buscar) {
                $query->where('name', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%")
                    ->orWhere('dni', 'like', "%{$buscar}%");
            }))
            ->when(in_array($estado, $this->estados(), true), fn ($query) => $query->where('estado_cuenta', $estado))
            ->when($rolId, fn ($query) => $query->where('rol_id', $rolId))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('hasta')))
            ->orderBy($orden, $direccion)
            ->paginate(18)
            ->withQueryString();

        $seleccionado = null;
        $actividad = collect();

        if ($request->filled('usuario')) {
            $seleccionado = User::query()->with('rol')->findOrFail($request->integer('usuario'));
            $this->authorizeManagement($request->user(), $seleccionado);
            $actividad = AuditoriaEvento::query()
                ->with('usuario')
                ->where('auditable_type', (new User)->getMorphClass())
                ->where('auditable_id', $seleccionado->id)
                ->latest('creado_en')
                ->limit(12)
                ->get();
        }

        $roles = Rol::query()
            ->when($request->user()->tieneRol('coordinador'), fn ($query) => $query->where('nombre', '!=', 'admin'))
            ->orderBy('nombre')
            ->get();

        return view('backend.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'seleccionado' => $seleccionado,
            'actividad' => $actividad,
            'buscar' => $buscar,
            'estado' => $estado,
            'rolId' => $rolId,
            'orden' => $orden,
            'direccion' => $direccion,
            'estados' => $this->estados(),
            'conteos' => $this->conteos(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        $datos = $request->validate(['rol_id' => ['required', 'integer', Rule::exists(Rol::class, 'id')]]);
        $rol = Rol::findOrFail($datos['rol_id']);
        $this->authorizeRole($request->user(), $rol);

        abort_if($user->estado_cuenta !== User::ESTADO_PENDIENTE, 422, 'Solo se pueden aprobar solicitudes pendientes.');
        abort_if(! $user->hasVerifiedEmail(), 422, 'El usuario debe verificar su correo antes de ser aprobado.');
        abort_if($user->estado_cuenta === User::ESTADO_ARCHIVADO, 422, 'Una cuenta archivada debe ser restaurada antes de aprobarse.');

        $antes = $this->snapshot($user);
        $user->forceFill([
            'rol_id' => $rol->id,
            'estado_cuenta' => User::ESTADO_APROBADO,
            'activo' => true,
            'aprobado_en' => now(),
            'aprobado_por' => $request->user()->id,
            'rechazado_en' => null,
            'rechazado_por' => null,
            'motivo_rechazo' => null,
            'suspendido_en' => null,
            'suspendido_por' => null,
            'motivo_suspension' => null,
        ])->saveQuietly();
        $this->audit('usuario_aprobado', $user, $antes);
        $user->notify(new RegistroUsuarioNotification('aprobado'));

        return back()->with('success', 'Usuario aprobado y rol asignado correctamente.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        $datos = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);
        abort_if($user->estado_cuenta !== User::ESTADO_PENDIENTE, 422, 'Solo se pueden rechazar solicitudes pendientes.');

        $antes = $this->snapshot($user);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_RECHAZADO,
            'activo' => false,
            'rechazado_en' => now(),
            'rechazado_por' => $request->user()->id,
            'motivo_rechazo' => $datos['motivo'],
        ])->saveQuietly();
        $this->audit('usuario_rechazado', $user, $antes, ['motivo' => $datos['motivo']]);
        $user->notify(new RegistroUsuarioNotification('rechazado', $datos['motivo']));

        return back()->with('success', 'Solicitud rechazada y motivo registrado.');
    }

    public function reopen(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        abort_if($user->estado_cuenta !== User::ESTADO_RECHAZADO, 422, 'Solo se pueden reabrir solicitudes rechazadas.');

        $antes = $this->snapshot($user);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_PENDIENTE,
            'activo' => false,
            'rechazado_en' => null,
            'rechazado_por' => null,
            'motivo_rechazo' => null,
        ])->saveQuietly();
        $this->audit('usuario_reabierto', $user, $antes);

        return back()->with('success', 'La solicitud volvió a quedar pendiente.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        $datos = $request->validate(['rol_id' => ['required', 'integer', Rule::exists(Rol::class, 'id')]]);
        $rol = Rol::findOrFail($datos['rol_id']);
        $this->authorizeRole($request->user(), $rol);
        abort_if($user->estado_cuenta !== User::ESTADO_APROBADO, 422, 'Solo se puede cambiar el rol de una cuenta aprobada.');
        abort_if($user->id === $request->user()->id, 422, 'No podés cambiar tu propio rol.');
        if ($user->rol?->nombre === 'admin' && $rol->nombre !== 'admin') {
            $this->ensureNotLastAdmin($user);
        }

        $antes = $this->snapshot($user);
        $user->forceFill(['rol_id' => $rol->id])->saveQuietly();
        $this->audit('rol_usuario_actualizado', $user, $antes, ['rol_nuevo' => $rol->nombre]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        abort_if($user->id === $request->user()->id, 422, 'No podés suspender tu propia cuenta.');
        $datos = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);
        $this->ensureNotLastAdmin($user);
        abort_if($user->estado_cuenta !== User::ESTADO_APROBADO, 422, 'Solo se pueden suspender cuentas aprobadas.');

        $antes = $this->snapshot($user);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_SUSPENDIDO,
            'activo' => false,
            'suspendido_en' => now(),
            'suspendido_por' => $request->user()->id,
            'motivo_suspension' => $datos['motivo'],
        ])->saveQuietly();
        $this->audit('usuario_suspendido', $user, $antes, ['motivo' => $datos['motivo']]);
        $user->notify(new RegistroUsuarioNotification('suspendido', $datos['motivo']));

        return back()->with('success', 'Cuenta suspendida y motivo registrado.');
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        abort_if($user->estado_cuenta !== User::ESTADO_SUSPENDIDO, 422, 'Solo se pueden reactivar cuentas suspendidas.');
        abort_if(! $user->rol_id, 422, 'La cuenta necesita un rol antes de ser reactivada.');

        $antes = $this->snapshot($user);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_APROBADO,
            'activo' => true,
            'suspendido_en' => null,
            'suspendido_por' => null,
            'motivo_suspension' => null,
        ])->saveQuietly();
        $this->audit('usuario_reactivado', $user, $antes);
        $user->notify(new RegistroUsuarioNotification('aprobado'));

        return back()->with('success', 'Cuenta reactivada correctamente.');
    }

    public function archive(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        abort_if($user->id === $request->user()->id, 422, 'No podés archivar tu propia cuenta.');
        $this->ensureNotLastAdmin($user);

        $antes = $this->snapshot($user);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_ARCHIVADO,
            'activo' => false,
            'archivado_en' => now(),
            'archivado_por' => $request->user()->id,
        ])->saveQuietly();
        $this->audit('usuario_archivado', $user, $antes);

        return back()->with('success', 'Cuenta archivada correctamente.');
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManagement($request->user(), $user);
        abort_if($user->estado_cuenta !== User::ESTADO_ARCHIVADO, 422, 'Solo se pueden restaurar cuentas archivadas.');
        abort_if(! $user->rol_id, 422, 'La cuenta necesita un rol antes de ser restaurada.');

        $antes = $this->snapshot($user);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_APROBADO,
            'activo' => true,
            'archivado_en' => null,
            'archivado_por' => null,
        ])->saveQuietly();
        $this->audit('usuario_restaurado', $user, $antes);

        return back()->with('success', 'Cuenta restaurada correctamente.');
    }

    private function authorizeManagement(User $actor, User $target): void
    {
        abort_unless(
            $actor->esAdmin() || ($actor->tieneRol('coordinador') && $target->rol?->nombre !== 'admin'),
            403,
            'No tenés permiso para gestionar este usuario.',
        );
    }

    private function authorizeRole(User $actor, Rol $rol): void
    {
        abort_unless($rol->nombre !== 'admin' || $actor->esAdmin(), 403, 'Solo un administrador puede asignar el rol admin.');
    }

    private function ensureNotLastAdmin(User $target): void
    {
        if ($target->rol?->nombre !== 'admin' || ! $target->activo) {
            return;
        }

        abort_if(
            User::query()->where('rol_id', $target->rol_id)->where('estado_cuenta', User::ESTADO_APROBADO)->where('activo', true)->count() <= 1,
            422,
            'No se puede dejar al sistema sin un administrador activo.',
        );
    }

    private function snapshot(User $user): array
    {
        return $user->only(['estado_cuenta', 'activo', 'rol_id', 'motivo_rechazo', 'motivo_suspension']);
    }

    private function audit(string $evento, User $user, array $antes, ?array $despues = null): void
    {
        AuditoriaEvento::registrar($evento, $user, $antes, $despues ?? $this->snapshot($user));
    }

    private function estados(): array
    {
        return [
            User::ESTADO_PENDIENTE,
            User::ESTADO_APROBADO,
            User::ESTADO_RECHAZADO,
            User::ESTADO_SUSPENDIDO,
            User::ESTADO_ARCHIVADO,
        ];
    }

    private function conteos(): array
    {
        return collect($this->estados())
            ->mapWithKeys(fn (string $estado) => [$estado => User::where('estado_cuenta', $estado)->count()])
            ->all();
    }
}
