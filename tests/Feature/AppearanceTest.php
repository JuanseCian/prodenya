<?php

use App\Models\User;

test('appearance defaults to system when no cookie is set', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertViewHas('appearance', 'system');
    $response->assertDontSee('<html lang="es" class="dark">', false);
});

test('appearance middleware keeps a valid stored dark preference', function () {
    // The "appearance" cookie is excluded from encryption (bootstrap/app.php)
    // so the client-side anti-flash script can read it via document.cookie —
    // withUnencryptedCookie mirrors that real, unencrypted browser cookie.
    $response = $this->withUnencryptedCookie('appearance', 'dark')->get(route('login'));

    $response->assertOk();
    $response->assertViewHas('appearance', 'dark');
    $response->assertSee('<html lang="es" class="dark">', false);
});

test('appearance middleware ignores an invalid cookie value', function () {
    $response = $this->withUnencryptedCookie('appearance', 'not-a-real-theme')->get(route('login'));

    $response->assertOk();
    $response->assertViewHas('appearance', 'system');
});

test('a light preference does not apply the dark html class on login', function () {
    $response = $this->withUnencryptedCookie('appearance', 'light')->get(route('login'));

    $response->assertOk();
    $response->assertDontSee('<html lang="es" class="dark">', false);
});

test('authenticated pages honor a dark appearance cookie', function () {
    $response = $this->actingAs(User::factory()->create())
        ->withUnencryptedCookie('appearance', 'dark')
        ->get(route('home'));

    $response->assertOk();
    $response->assertViewHas('appearance', 'dark');
});
