<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\RegistroUsuarioNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la página de registro.
     */
    public function create(Request $request): View
    {
        return view('auth.access', [
            'teamInvitation' => $this->teamInvitation($request),
            'showRegister' => true,
        ]);
    }

    /**
     * Procesa un registro nuevo y lo guarda en la tabla users.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'dni' => preg_replace('/\D+/', '', (string) $request->input('dni')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'dni' => ['required', 'digits_between:7,12', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'max:40'],
            'profesion' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dni' => $validated['dni'],
            'telefono' => $validated['telefono'] ?? null,
            'profesion' => $validated['profesion'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);
        $user->forceFill([
            'estado_cuenta' => User::ESTADO_PENDIENTE,
            'activo' => false,
        ])->saveQuietly();

        $user->notify(new RegistroUsuarioNotification('verificacion'));

        Auth::logout();

        return redirect()->route('register.pending')
            ->with('registration_email', $user->email);
    }

    public function pending(Request $request): View
    {
        return view('auth.registration-pending', [
            'email' => $request->session()->get('registration_email'),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower($validated['email']);
        $user = User::query()
            ->where('email', $email)
            ->whereNull('email_verified_at')
            ->where('estado_cuenta', User::ESTADO_PENDIENTE)
            ->first();

        if ($user) {
            $user->notify(new RegistroUsuarioNotification('verificacion'));
        }

        return redirect()->route('register.pending')
            ->with('registration_email', $email)
            ->with('status', 'verification-link-sent');
    }

    public function verifyRegistration(Request $request, User $user): View
    {
        abort_unless(hash_equals(sha1($user->email), (string) $request->query('hash')), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->saveQuietly();
            event(new Verified($user));
        }

        return view('auth.registration-verified', compact('user'));
    }

    private function teamInvitation(Request $request): ?array
    {
        $invitationCode = $request->query('invitation');

        if (! is_string($invitationCode)) {
            return null;
        }

        $invitation = TeamInvitation::query()
            ->with('team')
            ->where('code', $invitationCode)
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->first();

        return $invitation ? [
            'code' => $invitation->code,
            'teamName' => $invitation->team->name,
        ] : null;
    }
}
