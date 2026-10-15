<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileAvatarUpdateRequest;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $currentTeam = $user->currentTeam;
        $teamRole = $currentTeam ? $user->teamRole($currentTeam)?->label() : null;
        $role = match ($user->rol?->nombre) {
            'admin' => 'Administrador',
            'operador' => 'Operador',
            default => $user->rol?->nombre ?? $teamRole,
        };

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'account' => [
                'team' => $currentTeam?->name,
                'role' => $role,
                'createdAt' => $user->created_at?->toIso8601String(),
                'lastAccess' => $user->ultimo_acceso?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update the user's profile avatar.
     */
    public function updateAvatar(ProfileAvatarUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldPath = $user->avatar_path;
        $newPath = $request->file('avatar')->store('avatars', 'public');

        $user->forceFill(['avatar_path' => $newPath])->save();

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Foto de perfil actualizada.']);

        return to_route('profile.edit');
    }

    /**
     * Remove the user's profile avatar.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->forceFill(['avatar_path' => null])->save();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Foto de perfil eliminada.']);

        return to_route('profile.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
