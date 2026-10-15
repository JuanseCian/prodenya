<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('the authenticated home shows the account menu', function () {
    $user = User::factory()->create([
        'name' => 'María González',
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('María González')
        ->assertSee('Mi perfil')
        ->assertSee('Seguridad y contraseña')
        ->assertSee('Cerrar sesión');
});

test('a user can upload and remove their own avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $upload = $this->actingAs($user)->post(route('profile.avatar.update'), [
        'avatar' => UploadedFile::fake()->image('avatar.png'),
    ]);

    $upload->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

    $path = $user->refresh()->avatar_path;

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);

    $this->actingAs($user)
        ->delete(route('profile.avatar.destroy'))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('a user can close their other database sessions after confirming their password', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $otherSessionId = 'other-session-id';

    DB::table(config('session.table'))->insert([
        'id' => $otherSessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test browser',
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($user)
        ->delete(route('sessions.destroy-other'), [
            'current_password' => 'password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(DB::table(config('session.table'))->where('id', $otherSessionId)->exists())->toBeFalse();
});

test('account settings require authentication', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->post(route('profile.avatar.update'))->assertRedirect(route('login'));
});
