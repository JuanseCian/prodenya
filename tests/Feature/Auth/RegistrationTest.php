<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('registration screen includes team invitation context', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Laravel Team']);
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->get(route('register', ['invitation' => $invitation->code]));

    $response->assertOk();
    $response
        ->assertSee('Crear cuenta')
        ->assertSee('Laravel Team');
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'dni' => '30123456',
        'telefono' => '3364000000',
        'profesion' => 'Profesional',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();

    $user = User::where('email', 'test@example.com')->first();
    $response->assertRedirect(route('register.pending'));
    expect($user->estado_cuenta)->toBe(User::ESTADO_PENDIENTE);
    expect($user->activo)->toBeFalse();
});
