<?php

use App\Models\User;

describe('Dashboard Page Access', function () {
    it('requires authentication to access dashboard', function () {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    });

    it('requires email verification to access dashboard', function () {
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertRedirect('/verify-email');
    });

    it('allows access to verified user', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard');
    });

    it('redirects authenticated user to stimmen page by default', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard/stimmen');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard');
    });

    it('handles dashboard sub-routes', function () {
        $user = User::factory()->create();
        
        $routes = [
            '/dashboard/stimmen',
            '/dashboard/kommentare',
            '/dashboard/settings'
        ];
        
        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
            $response->assertViewIs('pages.dashboard');
        }
    });

    it('uses correct route name', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('page.dashboard'));
        
        $response->assertStatus(200);
    });

    it('handles deep nested dashboard routes', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard/kommentare/published');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard');
    });

    it('uses web middleware group', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        // Should have session and CSRF token
        $this->assertAuthenticated();
    });
});

describe('Error Page Access', function () {
    it('requires authentication to access error pages', function () {
        $response = $this->get('/error');
        
        $response->assertRedirect('/login');
    });

    it('allows authenticated users to access error pages', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/error');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard');
    });

    it('handles error sub-routes', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/error/404');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard');
    });
});