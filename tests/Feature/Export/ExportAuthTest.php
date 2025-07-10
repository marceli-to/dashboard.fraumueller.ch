<?php

use App\Models\User;

describe('Export Authentication', function () {
    it('requires authentication for comments export', function () {
        $response = $this->get('/export/comments');
        
        $response->assertRedirect('/login');
    });

    it('requires authentication for votes export', function () {
        $response = $this->get('/export/votes');
        
        $response->assertRedirect('/login');
    });

    it('requires email verification for comments export', function () {
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/export/comments');
        
        $response->assertRedirect('/verify-email');
    });

    it('requires email verification for votes export', function () {
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/export/votes');
        
        $response->assertRedirect('/verify-email');
    });

    it('allows verified user to access comments export', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/export/comments');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('allows verified user to access votes export', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/export/votes');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('uses auth and verified middleware', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/export/comments');
        
        $response->assertStatus(200);
        $this->assertAuthenticated();
        expect($user->hasVerifiedEmail())->toBeTrue();
    });
});