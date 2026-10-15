<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\RevokeOtherSessionsRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => Features::canManagePasskeys()
                ? $request->user()
                    ->passkeys()
                    ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
                    ->latest()
                    ->get()
                    ->map(fn ($passkey) => [
                        'id' => $passkey->id,
                        'name' => $passkey->name,
                        'authenticator' => $passkey->authenticator,
                        'created_at_diff' => $passkey->created_at->diffForHumans(),
                        'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
                    ])
                    ->values()
                    ->all()
                : [],
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'passwordRequirements' => app()->isProduction()
                ? 'Mínimo 12 caracteres, mayúsculas, minúsculas, números y símbolos.'
                : 'Se aplican las reglas estándar configuradas por el sistema.',
            'canManageSessions' => $this->canManageSessions(),
            'sessions' => $this->sessions($request),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return Inertia::render('settings/security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password updated.')]);

        return back();
    }

    /**
     * Revoke all database-backed sessions except the current one.
     */
    public function destroyOtherSessions(RevokeOtherSessionsRequest $request): RedirectResponse
    {
        if ($this->canManageSessions()) {
            DB::table(config('session.table'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Se cerraron las demás sesiones.']);

        return back();
    }

    /**
     * Determine whether real database sessions are available to manage.
     */
    private function canManageSessions(): bool
    {
        return config('session.driver') === 'database'
            && Schema::hasTable(config('session.table'));
    }

    /**
     * Return real sessions belonging to the authenticated user.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sessions(TwoFactorAuthenticationRequest $request): array
    {
        if (! $this->canManageSessions()) {
            return [];
        }

        return DB::table(config('session.table'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn (object $session): array => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent ?: 'Navegador no identificado',
                'last_active_at' => Carbon::createFromTimestamp((int) $session->last_activity)->toIso8601String(),
                'is_current' => $session->id === $request->session()->getId(),
            ])
            ->values()
            ->all();
    }
}
