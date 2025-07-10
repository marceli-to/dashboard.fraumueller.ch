<?php

use App\Models\User;

describe('Dashboard API Authentication', function () {
    it('requires authentication for votes endpoint', function () {
        $response = $this->getJson('/api/votes');
        
        $response->assertStatus(401);
    });

    it('requires authentication for comments endpoint', function () {
        $response = $this->getJson('/api/comments');
        
        $response->assertStatus(401);
    });

    it('requires authentication for comment update', function () {
        $response = $this->putJson('/api/comments/update/1');
        
        $response->assertStatus(401);
    });

    it('requires authentication for comment toggle', function () {
        $response = $this->putJson('/api/comments/toggle/1');
        
        $response->assertStatus(401);
    });

    it('requires authentication for comment delete', function () {
        $response = $this->deleteJson('/api/comments/1');
        
        $response->assertStatus(401);
    });

    it('requires authentication for comment restore', function () {
        $response = $this->putJson('/api/comments/restore/1');
        
        $response->assertStatus(401);
    });

    it('allows access to votes endpoint when authenticated', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/votes');
        
        $response->assertStatus(200);
    });

    it('allows access to comments endpoint when authenticated', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/comments');
        
        $response->assertStatus(200);
    });

    it('uses sanctum authentication guard', function () {
        $user = User::factory()->create();
        
        // Test with wrong guard (should fail)
        $response = $this->actingAs($user, 'web')->getJson('/api/votes');
        $response->assertStatus(401);
        
        // Test with correct guard (should succeed)
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/votes');
        $response->assertStatus(200);
    });

    it('rejects invalid tokens', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-12345'
        ])->getJson('/api/votes');
        
        $response->assertStatus(401);
    });

    it('accepts valid sanctum tokens', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/votes');
        
        $response->assertStatus(200);
    });
});