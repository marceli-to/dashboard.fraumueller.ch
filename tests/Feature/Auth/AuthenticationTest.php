<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;

test('login screen can be rendered', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/logout');
    $this->assertGuest();
    $response->assertRedirect('/');
});
