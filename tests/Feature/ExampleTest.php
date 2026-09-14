<?php

use App\Models\User;

test('guests are redirected to the login page from the home', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the home', function () {
    $response = $this
        ->actingAs(User::factory()->create())
        ->get(route('home'));

    $response->assertOk();
});
