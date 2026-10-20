<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intenta autenticar la request. Lanza ValidationException si falla.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $usuario = User::where('email', strtolower((string) $this->input('email')))->first();

        if ($usuario && ! $usuario->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => 'Primero verificá tu correo electrónico desde el enlace que recibiste.',
            ]);
        }

        if ($usuario && ($usuario->estado_cuenta !== User::ESTADO_APROBADO || ! $usuario->activo)) {
            $mensaje = match ($usuario->estado_cuenta) {
                User::ESTADO_PENDIENTE => 'Tu solicitud todavía está pendiente de aprobación.',
                User::ESTADO_RECHAZADO => 'Tu solicitud de acceso fue rechazada.',
                User::ESTADO_SUSPENDIDO => 'Tu cuenta está suspendida. Comunicate con la administración.',
                User::ESTADO_ARCHIVADO => 'Tu cuenta está archivada y no puede ingresar.',
                default => 'Tu cuenta no está habilitada para ingresar.',
            };

            throw ValidationException::withMessages(['email' => $mensaje]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Los datos ingresados no coinciden con ningún registro.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        $usuario?->updateQuietly(['ultimo_acceso' => now()]);
    }

    /**
     * Corta el login si hubo demasiados intentos fallidos.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Probá de nuevo en {$seconds} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
